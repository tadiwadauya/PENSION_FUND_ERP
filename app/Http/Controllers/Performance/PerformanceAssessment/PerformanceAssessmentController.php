<?php

namespace App\Http\Controllers\Performance\PerformanceAssessment;

use App\Http\Controllers\Controller;
use App\Models\Performance\PerformanceAssessment;
use App\Models\Performance\PerformanceAssessmentCycle;
use App\Models\Performance\PerformanceAssessmentItem;
use App\Models\Performance\PerformanceRatingScale;
use App\Models\Performance\PerformanceTarget;
use App\Notifications\PerformanceAssessmentSubmittedNotification;
use App\Notifications\PerformanceAssessmentAssessorSubmittedNotification;
use App\Notifications\PerformanceAssessmentReviewerSubmittedNotification;
use App\Notifications\PerformanceAssessmentCompletedNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PerformanceAssessmentController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $filter = $request->get('filter');

        $query = PerformanceAssessment::with([
            'performanceTarget',
            'period',
            'user.department',
            'user.section',
            'assessor',
            'reviewer',
            'hrReviewer',
        ])->latest();

        if (!$user->is_admin && !$user->is_hr) {
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhere('assessor_id', $user->id)
                    ->orWhere('reviewer_id', $user->id)
                    ->orWhere('hr_reviewer_id', $user->id);
            });
        }

        switch ($filter) {
            case 'my_assessments':
                $query->where('user_id', $user->id);
                break;

            case 'awaiting_assessor':
                $query->where('assessor_id', $user->id)
                    ->where('status', 'submitted_by_employee');
                break;

            case 'awaiting_reviewer':
                $query->where('reviewer_id', $user->id)
                    ->where('status', 'submitted_to_reviewer');
                break;

            case 'completed':
                $query->where('status', 'completed');
                break;
        }

        $assessments = $query->get();

        return view('performance.performance_assessments.index', compact('assessments', 'filter'));
    }

    public function start(PerformanceTarget $performance_target)
    {
        $this->authorizeStart($performance_target);

        $existing = PerformanceAssessment::where('performance_target_id', $performance_target->id)->first();

        if ($existing) {
            return redirect()->route('performance-assessments.show', $existing->id)
                ->with('success', 'Performance assessment already exists.');
        }

        $performance_target->load([
            'period',
            'sections',
            'items',
            'user',
            'assessor',
            'reviewer',
            'hrReviewer',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Validate Section Weights
        |--------------------------------------------------------------------------
        */

        $sectionWeightTotal = round((float) $performance_target->sections->sum('weight'), 2);

        if (abs($sectionWeightTotal - 100) > 0.01) {
            return back()->withErrors([
                'assessment' => 'Section weights must total exactly 100%. Current total: ' .
                    number_format($sectionWeightTotal, 2) . '%.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Validate Item Weights
        |--------------------------------------------------------------------------
        */

        $groupedItems = $performance_target->items->groupBy('section_code');

        foreach ($groupedItems as $sectionCode => $items) {
            $itemWeightTotal = round((float) $items->sum('weight'), 2);

            if (abs($itemWeightTotal - 100) > 0.01) {
                return back()->withErrors([
                    'assessment' => ($items->first()?->section_title ?? $sectionCode) .
                        ' task weights must total exactly 100%. Current total: ' .
                        number_format($itemWeightTotal, 2) . '%.',
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Create Assessment Snapshot
        |--------------------------------------------------------------------------
        */

        $assessment = DB::transaction(function () use ($performance_target) {
            $assessment = PerformanceAssessment::create([
                'performance_target_id' => $performance_target->id,
                'performance_period_id' => $performance_target->performance_period_id,
                'user_id' => $performance_target->user_id,
                'assessor_id' => $performance_target->assessor_id,
                'reviewer_id' => $performance_target->reviewer_id,
                'hr_reviewer_id' => $performance_target->hr_reviewer_id,
                'title' => str_replace(
                    'PERFORMANCE TARGETS',
                    'PERFORMANCE ASSESSMENT',
                    $performance_target->title
                ),
                'status' => 'not_started',
            ]);

            $sectionWeights = $performance_target->sections->keyBy('section_code');

            foreach ($performance_target->items as $targetItem) {
                $assessmentItem = PerformanceAssessmentItem::create([
                    'performance_assessment_id' => $assessment->id,
                    'performance_target_item_id' => $targetItem->id,

                    'section_code' => $targetItem->section_code,
                    'section_title' => $targetItem->section_title,
                    'perspective' => $targetItem->perspective,

                    'task' => $targetItem->task,
                    'how_to_achieve' => $targetItem->how_to_achieve,
                    'measure_target' => $targetItem->measure_target,
                    'target_description' => $targetItem->target_description,

                    'section_weight' => $sectionWeights->get($targetItem->section_code)?->weight ?? 0,
                    'item_weight' => $targetItem->weight,

                    'target_type' => $targetItem->target_type,
                    'frequency' => $targetItem->frequency,

                    'per_cycle_target_value' => $targetItem->per_cycle_target_value,
                    'period_target_value' => $targetItem->period_target_value,

                    'unit_of_measure' => $targetItem->unit_of_measure,
                    'evaluation_method' => $targetItem->evaluation_method,
                ]);

                $this->generateCycles(
                    $assessmentItem,
                    $targetItem,
                    $performance_target->period
                );
            }

            return $assessment;
        });

        return redirect()->route('performance-assessments.show', $assessment->id)
            ->with('success', 'Performance assessment created successfully.');
    }

    public function show(PerformanceAssessment $performance_assessment)
    {
        $this->authorizeView($performance_assessment);

        $performance_assessment->load([
            'performanceTarget',
            'period',
            'user.department',
            'user.section',
            'assessor',
            'reviewer',
            'hrReviewer',

            'items' => function ($q) {
                $q->with([
                    'employeeRating',
                    'assessorRating',
                    'reviewerRating',
                    'cycles',
                ])->orderBy('id');
            },
        ]);

        $ratings = PerformanceRatingScale::where('is_active', true)
            ->orderByDesc('score')
            ->get();

        return view('performance.performance_assessments.show', [
            'assessment' => $performance_assessment,
            'ratings' => $ratings,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | EMPLOYEE SELF-ASSESSMENT
    |--------------------------------------------------------------------------
    */

    public function saveSelfAssessment(Request $request, PerformanceAssessment $performance_assessment)
    {
        $this->authorizeEmployeeEdit($performance_assessment);

        $request->validate([
            'cycles' => ['nullable', 'array'],
            'cycles.*.employee_actual_value' => ['nullable', 'numeric', 'min:0'],
            'cycles.*.employee_met_target' => ['nullable', 'in:0,1'],
            'cycles.*.employee_comment' => ['nullable', 'string', 'max:2000'],
            'cycles.*.employee_evidence' => ['nullable', 'string', 'max:2000'],

            'items' => ['nullable', 'array'],
            'items.*.employee_comment' => ['nullable', 'string', 'max:3000'],
            'items.*.employee_evidence' => ['nullable', 'string', 'max:3000'],

            'employee_general_comment' => ['nullable', 'string', 'max:5000'],
        ]);

        DB::transaction(function () use ($request, $performance_assessment) {
            foreach ($request->input('cycles', []) as $cycleId => $data) {
                $cycle = PerformanceAssessmentCycle::whereHas('assessmentItem', function ($q) use ($performance_assessment) {
                    $q->where('performance_assessment_id', $performance_assessment->id);
                })
                    ->where('id', $cycleId)
                    ->firstOrFail();

                $cycle->update([
                    'employee_actual_value' => $data['employee_actual_value'] ?? null,

                    'employee_met_target' => array_key_exists('employee_met_target', $data)
                        ? (bool) $data['employee_met_target']
                        : null,

                    'employee_comment' => $data['employee_comment'] ?? null,
                    'employee_evidence' => $data['employee_evidence'] ?? null,
                ]);
            }

            foreach ($request->input('items', []) as $itemId => $data) {
                $item = $performance_assessment->items()
                    ->where('id', $itemId)
                    ->firstOrFail();

                $item->update([
                    'employee_comment' => $data['employee_comment'] ?? null,
                    'employee_evidence' => $data['employee_evidence'] ?? null,
                ]);
            }

            $performance_assessment->update([
                'employee_general_comment' => $request->employee_general_comment,
                'status' => 'self_assessment_in_progress',
            ]);

            $this->recalculateEmployeeAssessment($performance_assessment);
        });

        return redirect()->route('performance-assessments.show', $performance_assessment->id)
            ->with('success', 'Self-assessment saved successfully.');
    }

   public function submitSelfAssessment(Request $request, PerformanceAssessment $performance_assessment)
{
    $this->authorizeEmployeeEdit($performance_assessment);

    $performance_assessment->load([
        'items.cycles',
        'user',
        'assessor',
    ]);

    foreach ($performance_assessment->items as $item) {
        if ($item->cycles->isEmpty()) {
            continue;
        }

        if ($item->evaluation_method === 'per_cycle') {
            $incomplete = $item->cycles->contains(function ($cycle) {
                return $cycle->employee_met_target === null;
            });
        } else {
            $incomplete = $item->cycles->contains(function ($cycle) {
                return $cycle->employee_actual_value === null;
            });
        }

        if ($incomplete) {
            return back()->withErrors([
                'assessment' => 'Please complete all assessment cycles for "' . $item->task . '" before submitting.',
            ]);
        }
    }

    DB::transaction(function () use ($performance_assessment) {
        $this->recalculateEmployeeAssessment($performance_assessment);

        $performance_assessment->update([
            'status' => 'submitted_by_employee',
            'employee_submitted_at' => now(),
        ]);
    });

    /*
    |--------------------------------------------------------------------------
    | Notify Assessor
    |--------------------------------------------------------------------------
    */

    if ($performance_assessment->assessor) {
        $performance_assessment->assessor->notify(
            new PerformanceAssessmentSubmittedNotification($performance_assessment)
        );
    }

    return redirect()->route('performance-assessments.show', $performance_assessment->id)
        ->with('success', 'Performance self-assessment submitted to the assessor successfully.');
}

    /*
    |--------------------------------------------------------------------------
    | ASSESSOR PAGE
    |--------------------------------------------------------------------------
    */

    public function assessor(PerformanceAssessment $performance_assessment)
    {
        $user = auth()->user();

        if (
            $user->id !== $performance_assessment->assessor_id &&
            !$user->is_admin
        ) {
            abort(403);
        }

        if (!in_array($performance_assessment->status, [
            'submitted_by_employee',
            'assessed_by_assessor',
        ])) {
            abort(403, 'This assessment is not currently available for assessor assessment.');
        }

        $performance_assessment->load([
            'performanceTarget',
            'period',
            'user.department',
            'user.section',
            'assessor',
            'reviewer',
            'hrReviewer',

            'items' => function ($q) {
                $q->with([
                    'employeeRating',
                    'assessorRating',
                    'reviewerRating',
                    'cycles',
                ])->orderBy('id');
            },
        ]);

        $ratings = PerformanceRatingScale::where('is_active', true)
            ->orderByDesc('score')
            ->get();

        return view('performance.performance_assessments.assessor', [
            'assessment' => $performance_assessment,
            'ratings' => $ratings,
        ]);
    }

    public function saveAssessorAssessment(Request $request, PerformanceAssessment $performance_assessment)
    {
        $this->authorizeAssessorEdit($performance_assessment);

        $request->validate([
            'cycles' => ['nullable', 'array'],
            'cycles.*.assessor_actual_value' => ['nullable', 'numeric', 'min:0'],
            'cycles.*.assessor_met_target' => ['nullable', 'in:0,1'],
            'cycles.*.assessor_comment' => ['nullable', 'string', 'max:2000'],

            'items' => ['nullable', 'array'],
            'items.*.assessor_comment' => ['nullable', 'string', 'max:3000'],

            'assessor_general_comment' => ['nullable', 'string', 'max:5000'],
        ]);

        DB::transaction(function () use ($request, $performance_assessment) {
            foreach ($request->input('cycles', []) as $cycleId => $data) {
                $cycle = PerformanceAssessmentCycle::whereHas('assessmentItem', function ($q) use ($performance_assessment) {
                    $q->where('performance_assessment_id', $performance_assessment->id);
                })
                    ->where('id', $cycleId)
                    ->firstOrFail();

                $cycle->update([
                    'assessor_actual_value' => $data['assessor_actual_value'] ?? null,

                    'assessor_met_target' => array_key_exists('assessor_met_target', $data)
                        ? (bool) $data['assessor_met_target']
                        : null,

                    'assessor_comment' => $data['assessor_comment'] ?? null,
                ]);
            }

            foreach ($request->input('items', []) as $itemId => $data) {
                $item = $performance_assessment->items()
                    ->where('id', $itemId)
                    ->firstOrFail();

                $item->update([
                    'assessor_comment' => $data['assessor_comment'] ?? null,
                ]);
            }

            $performance_assessment->update([
                'assessor_general_comment' => $request->assessor_general_comment,
                'status' => 'assessed_by_assessor',
            ]);

            $this->recalculateAssessorAssessment($performance_assessment);
        });

        return redirect()->route('performance-assessments.assessor', $performance_assessment->id)
            ->with('success', 'Assessor assessment saved successfully.');
    }

  public function submitAssessorAssessment(Request $request, PerformanceAssessment $performance_assessment)
{
    $this->authorizeAssessorEdit($performance_assessment);

    $performance_assessment->load([
        'items.cycles',
        'user',
        'assessor',
        'reviewer',
    ]);

    foreach ($performance_assessment->items as $item) {
        if ($item->cycles->isEmpty()) {
            continue;
        }

        if ($item->evaluation_method === 'per_cycle') {
            $incomplete = $item->cycles->contains(function ($cycle) {
                return $cycle->assessor_met_target === null;
            });
        } else {
            $incomplete = $item->cycles->contains(function ($cycle) {
                return $cycle->assessor_actual_value === null;
            });
        }

        if ($incomplete) {
            return back()->withErrors([
                'assessment' => 'Please complete the assessor assessment for "' . $item->task . '" before submitting.',
            ]);
        }
    }

    DB::transaction(function () use ($performance_assessment) {
        $this->recalculateAssessorAssessment($performance_assessment);

        $performance_assessment->update([
            'status' => 'submitted_to_reviewer',
            'assessor_assessed_at' => now(),
        ]);
    });

    /*
    |--------------------------------------------------------------------------
    | Notify Reviewer
    |--------------------------------------------------------------------------
    */

    if ($performance_assessment->reviewer) {
        $performance_assessment->reviewer->notify(
            new PerformanceAssessmentAssessorSubmittedNotification($performance_assessment)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Notify Employee
    |--------------------------------------------------------------------------
    */

    if ($performance_assessment->user) {
        $performance_assessment->user->notify(
            new PerformanceAssessmentAssessorSubmittedNotification(
                $performance_assessment,
                'Your performance assessment has been completed by the assessor and submitted to the reviewer.'
            )
        );
    }

    return redirect()->route('performance-assessments.show', $performance_assessment->id)
        ->with('success', 'Assessor assessment submitted to the reviewer successfully.');
}

    /*
    |--------------------------------------------------------------------------
    | EMPLOYEE CALCULATION
    |--------------------------------------------------------------------------
    */

    private function recalculateEmployeeAssessment(PerformanceAssessment $assessment): void
    {
        $assessment->load('items.cycles');

        foreach ($assessment->items as $item) {
            $performanceIndex = $this->calculateEmployeePerformanceIndex($item);

            $rating = $this->findRatingForPerformanceIndex($performanceIndex);

            $ratingScore = $rating
                ? (float) $rating->score
                : null;

            $weightedScore = null;

            if ($ratingScore !== null) {
                $weightedScore =
                    $ratingScore *
                    ((float) $item->item_weight / 100) *
                    ((float) $item->section_weight / 100);
            }

            $actualValue = $this->calculateEmployeeActualValue($item);

            $item->update([
                'employee_actual_value' => $actualValue,

                /*
                |--------------------------------------------------------------------------
                | IMPORTANT
                |--------------------------------------------------------------------------
                |
                | This field now stores the PERFORMANCE INDEX used to determine the
                | rating. It is no longer simply Actual ÷ Target × 100.
                |
                */

                'employee_achievement_percentage' => $performanceIndex,

                'employee_rating_scale_id' => $rating?->id,
                'employee_rating_score' => $ratingScore,
                'employee_weighted_score' => $weightedScore,
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ASSESSOR CALCULATION
    |--------------------------------------------------------------------------
    */

    private function recalculateAssessorAssessment(PerformanceAssessment $assessment): void
    {
        $assessment->load('items.cycles');

        foreach ($assessment->items as $item) {
            $performanceIndex = $this->calculateAssessorPerformanceIndex($item);

            $rating = $this->findRatingForPerformanceIndex($performanceIndex);

            $ratingScore = $rating
                ? (float) $rating->score
                : null;

            $weightedScore = null;

            if ($ratingScore !== null) {
                $weightedScore =
                    $ratingScore *
                    ((float) $item->item_weight / 100) *
                    ((float) $item->section_weight / 100);
            }

            $actualValue = $this->calculateAssessorActualValue($item);

            $item->update([
                'assessor_actual_value' => $actualValue,
                'assessor_achievement_percentage' => $performanceIndex,
                'assessor_rating_scale_id' => $rating?->id,
                'assessor_rating_score' => $ratingScore,
                'assessor_weighted_score' => $weightedScore,
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | EMPLOYEE PERFORMANCE INDEX
    |--------------------------------------------------------------------------
    */

    private function calculateEmployeePerformanceIndex(PerformanceAssessmentItem $item): ?float
    {
        if ($item->evaluation_method === 'per_cycle') {
            return $this->calculateBooleanCyclePerformanceIndex(
                $item->cycles,
                'employee_met_target'
            );
        }

        if ($item->target_type === 'recurring') {
            return $this->calculateNumericCyclePerformanceIndex(
                $item->cycles,
                'employee_actual_value'
            );
        }

        return $this->calculateOneTimePerformanceIndex(
            $item->cycles,
            'employee_actual_value'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ASSESSOR PERFORMANCE INDEX
    |--------------------------------------------------------------------------
    */

    private function calculateAssessorPerformanceIndex(PerformanceAssessmentItem $item): ?float
    {
        if ($item->evaluation_method === 'per_cycle') {
            return $this->calculateBooleanCyclePerformanceIndex(
                $item->cycles,
                'assessor_met_target'
            );
        }

        if ($item->target_type === 'recurring') {
            return $this->calculateNumericCyclePerformanceIndex(
                $item->cycles,
                'assessor_actual_value'
            );
        }

        return $this->calculateOneTimePerformanceIndex(
            $item->cycles,
            'assessor_actual_value'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | BOOLEAN / PER-CYCLE PERFORMANCE
    |--------------------------------------------------------------------------
    |
    | Used where each cycle is simply:
    |
    | Met / Not Met
    |
    | Example:
    | 6 deadlines expected
    | 6 met = Meets Requirements = B2
    | 5 met = 83.33% = C1
    |
    | Because we only know Met/Not Met here, the system cannot automatically
    | award B1/A2/A1. There is no information showing HOW MUCH the employee
    | exceeded the target.
    |
    */

    private function calculateBooleanCyclePerformanceIndex($cycles, string $field): ?float
    {
        $completed = $cycles->filter(function ($cycle) use ($field) {
            return $cycle->{$field} !== null;
        });

        if ($completed->isEmpty()) {
            return null;
        }

        $total = $completed->count();

        $met = $completed->filter(function ($cycle) use ($field) {
            return $cycle->{$field} === true || $cycle->{$field} == 1;
        })->count();

        $metPercentage = ($met / $total) * 100;

        if ($metPercentage < 70) {
            return 60;
        }

        if ($metPercentage < 85) {
            return 80;
        }

        /*
        |--------------------------------------------------------------------------
        | 85% - 99.99%
        |--------------------------------------------------------------------------
        |
        | Mostly met requirement.
        |
        */

        if ($metPercentage < 100) {
            return 90;
        }

        /*
        |--------------------------------------------------------------------------
        | 100%
        |--------------------------------------------------------------------------
        |
        | Requirement met in every cycle = B2.
        |
        */

        return 90;
    }

    /*
    |--------------------------------------------------------------------------
    | RECURRING NUMERIC PERFORMANCE
    |--------------------------------------------------------------------------
    |
    | This is the important new logic.
    |
    | Example target:
    | Process 10 transactions each month.
    |
    | We DO NOT use:
    |
    | 64 / 60 = 106.67% = A2
    |
    | Instead each month is evaluated separately.
    |
    */

    private function calculateNumericCyclePerformanceIndex($cycles, string $actualField): ?float
    {
        $completed = $cycles->filter(function ($cycle) use ($actualField) {
            return $cycle->{$actualField} !== null &&
                $cycle->target_value !== null &&
                (float) $cycle->target_value > 0;
        });

        if ($completed->isEmpty()) {
            return null;
        }

        $totalCycles = $completed->count();

        $metCycles = 0;
        $exceededCycles = 0;
        $substantiallyExceededCycles = 0;

        foreach ($completed as $cycle) {
            $target = (float) $cycle->target_value;
            $actual = (float) $cycle->{$actualField};

            $ratio = ($actual / $target) * 100;

            /*
            |--------------------------------------------------------------------------
            | Met Requirement
            |--------------------------------------------------------------------------
            */

            if ($ratio >= 100) {
                $metCycles++;
            }

            /*
            |--------------------------------------------------------------------------
            | Exceeded Requirement
            |--------------------------------------------------------------------------
            |
            | Anything above the agreed cycle target counts as exceeding.
            |
            */

            if ($ratio > 100) {
                $exceededCycles++;
            }

            /*
            |--------------------------------------------------------------------------
            | Outstanding / Substantial Exceeding
            |--------------------------------------------------------------------------
            |
            | At least 20% above the target.
            |
            */

            if ($ratio >= 120) {
                $substantiallyExceededCycles++;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | First Determine Whether Requirements Were Met
        |--------------------------------------------------------------------------
        */

        $metRate = ($metCycles / $totalCycles) * 100;

        /*
        |--------------------------------------------------------------------------
        | C2
        |--------------------------------------------------------------------------
        */

        if ($metRate < 70) {
            return 60;
        }

        /*
        |--------------------------------------------------------------------------
        | C1
        |--------------------------------------------------------------------------
        */

        if ($metRate < 85) {
            return 80;
        }

        /*
        |--------------------------------------------------------------------------
        | B2
        |--------------------------------------------------------------------------
        |
        | Most requirements were met, but not all cycles.
        |
        */

        if ($metRate < 100) {
            return 90;
        }

        /*
        |--------------------------------------------------------------------------
        | At this point ALL cycles met the basic requirement.
        |--------------------------------------------------------------------------
        */

        /*
        |--------------------------------------------------------------------------
        | A1 - Outstanding
        |--------------------------------------------------------------------------
        |
        | Every cycle substantially exceeded the target by at least 20%.
        |
        | Example with target 10:
        | 12, 13, 12, 14, 13, 12
        |
        */

        if ($substantiallyExceededCycles === $totalCycles) {
            return 120;
        }

        /*
        |--------------------------------------------------------------------------
        | A2 - Consistently Exceeds Requirements
        |--------------------------------------------------------------------------
        |
        | Employee exceeded the requirement in at least half of all cycles.
        |
        | Example:
        | 11, 12, 10, 13, 11, 10
        |
        | 4 out of 6 months exceeded.
        |
        */

        if ($exceededCycles >= ceil($totalCycles / 2)) {
            return 110;
        }

        /*
        |--------------------------------------------------------------------------
        | B1 - Occasionally Exceeds Requirements
        |--------------------------------------------------------------------------
        |
        | Employee exceeded the target in at least one cycle, but not enough
        | cycles to be regarded as consistently exceeding.
        |
        */

        if ($exceededCycles > 0) {
            return 100;
        }

        /*
        |--------------------------------------------------------------------------
        | B2 - Meets Requirements
        |--------------------------------------------------------------------------
        |
        | Requirement was met in every cycle without evidence of exceeding it.
        |
        | Example:
        | 10, 10, 10, 10, 10, 10
        |
        */

        return 90;
    }

    /*
    |--------------------------------------------------------------------------
    | ONE-TIME NUMERIC TARGET
    |--------------------------------------------------------------------------
    */

    private function calculateOneTimePerformanceIndex($cycles, string $actualField): ?float
    {
        $cycle = $cycles->first();

        if (
            !$cycle ||
            $cycle->{$actualField} === null ||
            $cycle->target_value === null ||
            (float) $cycle->target_value <= 0
        ) {
            return null;
        }

        $actual = (float) $cycle->{$actualField};
        $target = (float) $cycle->target_value;

        $ratio = ($actual / $target) * 100;

        /*
        |--------------------------------------------------------------------------
        | C2 - Far Below Requirement
        |--------------------------------------------------------------------------
        */

        if ($ratio < 70) {
            return 60;
        }

        /*
        |--------------------------------------------------------------------------
        | C1 - Partially Meets Requirement
        |--------------------------------------------------------------------------
        */

        if ($ratio < 100) {
            return 80;
        }

        /*
        |--------------------------------------------------------------------------
        | B2 - Meets Requirement Exactly
        |--------------------------------------------------------------------------
        */

        if (abs($ratio - 100) < 0.01) {
            return 90;
        }

        /*
        |--------------------------------------------------------------------------
        | B1 - Exceeds Requirement
        |--------------------------------------------------------------------------
        */

        if ($ratio < 110) {
            return 100;
        }

        /*
        |--------------------------------------------------------------------------
        | A2 - Consistently / Significantly Exceeds
        |--------------------------------------------------------------------------
        */

        if ($ratio < 120) {
            return 110;
        }

        /*
        |--------------------------------------------------------------------------
        | A1 - Outstanding
        |--------------------------------------------------------------------------
        */

        return 120;
    }

    /*
    |--------------------------------------------------------------------------
    | FIND DYNAMIC RATING
    |--------------------------------------------------------------------------
    */

    private function findRatingForPerformanceIndex(?float $performanceIndex): ?PerformanceRatingScale
    {
        if ($performanceIndex === null) {
            return null;
        }

        return PerformanceRatingScale::where('is_active', true)
            ->where('min_percentage', '<=', $performanceIndex)
            ->where('max_percentage', '>=', $performanceIndex)
            ->orderByDesc('score')
            ->first();
    }

    /*
    |--------------------------------------------------------------------------
    | ACTUAL VALUE - EMPLOYEE
    |--------------------------------------------------------------------------
    */

    private function calculateEmployeeActualValue(PerformanceAssessmentItem $item): ?float
    {
        $cycles = $item->cycles;

        if ($cycles->isEmpty()) {
            return null;
        }

        if ($item->evaluation_method === 'per_cycle') {
            return (float) $cycles->filter(function ($cycle) {
                return $cycle->employee_met_target === true ||
                    $cycle->employee_met_target == 1;
            })->count();
        }

        if ($item->evaluation_method === 'cumulative') {
            $actuals = $cycles->whereNotNull('employee_actual_value');

            return $actuals->isEmpty()
                ? null
                : round((float) $actuals->sum('employee_actual_value'), 2);
        }

        if ($item->evaluation_method === 'average') {
            $actuals = $cycles->whereNotNull('employee_actual_value');

            return $actuals->isEmpty()
                ? null
                : round((float) $actuals->avg('employee_actual_value'), 2);
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | ACTUAL VALUE - ASSESSOR
    |--------------------------------------------------------------------------
    */

    private function calculateAssessorActualValue(PerformanceAssessmentItem $item): ?float
    {
        $cycles = $item->cycles;

        if ($cycles->isEmpty()) {
            return null;
        }

        if ($item->evaluation_method === 'per_cycle') {
            return (float) $cycles->filter(function ($cycle) {
                return $cycle->assessor_met_target === true ||
                    $cycle->assessor_met_target == 1;
            })->count();
        }

        if ($item->evaluation_method === 'cumulative') {
            $actuals = $cycles->whereNotNull('assessor_actual_value');

            return $actuals->isEmpty()
                ? null
                : round((float) $actuals->sum('assessor_actual_value'), 2);
        }

        if ($item->evaluation_method === 'average') {
            $actuals = $cycles->whereNotNull('assessor_actual_value');

            return $actuals->isEmpty()
                ? null
                : round((float) $actuals->avg('assessor_actual_value'), 2);
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | GENERATE ASSESSMENT CYCLES
    |--------------------------------------------------------------------------
    */

    private function generateCycles(
        PerformanceAssessmentItem $assessmentItem,
        $targetItem,
        $period
    ): void {
        $start = Carbon::parse($period->start_date)->startOfDay();
        $end = Carbon::parse($period->end_date)->endOfDay();

        /*
        |--------------------------------------------------------------------------
        | One Time
        |--------------------------------------------------------------------------
        */

        if (
            $targetItem->target_type === 'one_time' ||
            $targetItem->frequency === 'once'
        ) {
            $dueDate = $targetItem->due_date
                ? Carbon::parse($targetItem->due_date)
                : $end->copy();

            $this->createCycle(
                $assessmentItem,
                1,
                'One Time',
                $start,
                $end,
                $dueDate,
                $targetItem->period_target_value ??
                    $targetItem->per_cycle_target_value
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Daily
        |--------------------------------------------------------------------------
        */

        if ($targetItem->frequency === 'daily') {
            $cycleNumber = 1;
            $cursor = $start->copy();

            while ($cursor->lte($end)) {
                $this->createCycle(
                    $assessmentItem,
                    $cycleNumber,
                    $cursor->format('d M Y'),
                    $cursor->copy(),
                    $cursor->copy(),
                    $cursor->copy(),
                    $targetItem->per_cycle_target_value
                );

                $cycleNumber++;
                $cursor->addDay();
            }

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Weekly
        |--------------------------------------------------------------------------
        */

        if ($targetItem->frequency === 'weekly') {
            $cycleNumber = 1;
            $cursor = $start->copy();

            while ($cursor->lte($end)) {
                $cycleStart = $cursor->copy();
                $cycleEnd = $cycleStart->copy()->addDays(6);

                if ($cycleEnd->gt($end)) {
                    $cycleEnd = $end->copy();
                }

                $dueDate = $this->weeklyDueDate(
                    $cycleStart,
                    $cycleEnd,
                    (int) ($targetItem->due_weekday ?? 5)
                );

                $this->createCycle(
                    $assessmentItem,
                    $cycleNumber,
                    'Week ' . $cycleNumber,
                    $cycleStart,
                    $cycleEnd,
                    $dueDate,
                    $targetItem->per_cycle_target_value
                );

                $cycleNumber++;
                $cursor = $cycleEnd->copy()->addDay();
            }

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Monthly
        |--------------------------------------------------------------------------
        */

        if ($targetItem->frequency === 'monthly') {
            $cycleNumber = 1;
            $cursor = $start->copy()->startOfMonth();

            while ($cursor->lte($end)) {
                $cycleStart = $cursor->copy();

                if ($cycleNumber === 1 && $start->gt($cycleStart)) {
                    $cycleStart = $start->copy();
                }

                $cycleEnd = $cursor->copy()->endOfMonth();

                if ($cycleEnd->gt($end)) {
                    $cycleEnd = $end->copy();
                }

                $dueDate = $this->monthlyDueDate(
                    $cursor,
                    (int) ($targetItem->due_day ?? $cursor->daysInMonth)
                );

                if ($dueDate->gt($cycleEnd)) {
                    $dueDate = $cycleEnd->copy();
                }

                $this->createCycle(
                    $assessmentItem,
                    $cycleNumber,
                    $cursor->format('F Y'),
                    $cycleStart,
                    $cycleEnd,
                    $dueDate,
                    $targetItem->per_cycle_target_value
                );

                $cycleNumber++;
                $cursor->addMonthNoOverflow()->startOfMonth();
            }

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Quarterly
        |--------------------------------------------------------------------------
        */

        if ($targetItem->frequency === 'quarterly') {
            $cycleNumber = 1;
            $cursor = $start->copy();

            while ($cursor->lte($end)) {
                $cycleStart = $cursor->copy();
                $cycleEnd = $cycleStart->copy()
                    ->addMonthsNoOverflow(3)
                    ->subDay();

                if ($cycleEnd->gt($end)) {
                    $cycleEnd = $end->copy();
                }

                $dueDay = (int) ($targetItem->due_day ?? $cycleEnd->day);

                $dueDate = $cycleEnd->copy();

                $dueDate->day(
                    min(
                        max(1, $dueDay),
                        $dueDate->daysInMonth
                    )
                );

                $this->createCycle(
                    $assessmentItem,
                    $cycleNumber,
                    'Quarter ' . $cycleNumber,
                    $cycleStart,
                    $cycleEnd,
                    $dueDate,
                    $targetItem->per_cycle_target_value
                );

                $cycleNumber++;
                $cursor = $cycleEnd->copy()->addDay();
            }

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Annual
        |--------------------------------------------------------------------------
        */

        if ($targetItem->frequency === 'annual') {
            $dueMonth = (int) ($targetItem->due_month ?? $end->month);
            $dueMonth = max(1, min(12, $dueMonth));

            $dueDate = Carbon::create(
                $end->year,
                $dueMonth,
                1
            );

            $dueDay = (int) ($targetItem->due_day ?? $dueDate->daysInMonth);

            $dueDate->day(
                min(
                    max(1, $dueDay),
                    $dueDate->daysInMonth
                )
            );

            if ($dueDate->lt($start)) {
                $dueDate = $start->copy();
            }

            if ($dueDate->gt($end)) {
                $dueDate = $end->copy();
            }

            $this->createCycle(
                $assessmentItem,
                1,
                $period->year ?? 'Annual',
                $start,
                $end,
                $dueDate,
                $targetItem->period_target_value ??
                    $targetItem->per_cycle_target_value
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Fallback
        |--------------------------------------------------------------------------
        */

        $this->createCycle(
            $assessmentItem,
            1,
            'Assessment Period',
            $start,
            $end,
            $end,
            $targetItem->period_target_value ??
                $targetItem->per_cycle_target_value
        );
    }

    private function createCycle(
        PerformanceAssessmentItem $assessmentItem,
        int $cycleNumber,
        string $label,
        Carbon $start,
        Carbon $end,
        Carbon $dueDate,
        $targetValue
    ): void {
        PerformanceAssessmentCycle::create([
            'performance_assessment_item_id' => $assessmentItem->id,
            'cycle_number' => $cycleNumber,
            'cycle_label' => $label,
            'cycle_start_date' => $start->toDateString(),
            'cycle_end_date' => $end->toDateString(),
            'due_date' => $dueDate->toDateString(),
            'target_value' => $targetValue,
        ]);
    }

    private function monthlyDueDate(Carbon $month, int $dueDay): Carbon
    {
        $date = $month->copy()->startOfMonth();

        return $date->day(
            min(
                max(1, $dueDay),
                $date->daysInMonth
            )
        );
    }

    private function weeklyDueDate(
        Carbon $cycleStart,
        Carbon $cycleEnd,
        int $dueWeekday
    ): Carbon {
        $dueWeekday = max(1, min(7, $dueWeekday));

        $dueDate = $cycleStart->copy()
            ->addDays($dueWeekday - 1);

        return $dueDate->gt($cycleEnd)
            ? $cycleEnd->copy()
            : $dueDate;
    }

    /*
    |--------------------------------------------------------------------------
    | AUTHORISATION
    |--------------------------------------------------------------------------
    */

    protected function authorizeStart(PerformanceTarget $target): void
    {
        if (auth()->id() !== $target->user_id) {
            abort(403);
        }

        $allowed =
            $target->status === 'reviewed_by_hr' ||
            (
                $target->status === 'approved_by_assessor' &&
                $target->assessor?->is_ceo
            );

        if (!$allowed) {
            abort(
                403,
                'The performance target must be fully approved before assessment can begin.'
            );
        }
    }

    protected function authorizeView(PerformanceAssessment $assessment): void
    {
        $user = auth()->user();

        if (
            $user->id !== $assessment->user_id &&
            $user->id !== $assessment->assessor_id &&
            $user->id !== $assessment->reviewer_id &&
            $user->id !== $assessment->hr_reviewer_id &&
            !$user->is_hr &&
            !$user->is_admin
        ) {
            abort(403);
        }
    }

    protected function authorizeEmployeeEdit(PerformanceAssessment $assessment): void
    {
        if (
            auth()->id() !== $assessment->user_id ||
            !$assessment->isEmployeeEditable()
        ) {
            abort(403);
        }
    }

    protected function authorizeAssessorEdit(PerformanceAssessment $assessment): void
    {
        $user = auth()->user();

        if (
            $user->id !== $assessment->assessor_id &&
            !$user->is_admin
        ) {
            abort(403);
        }

        if (!in_array($assessment->status, [
            'submitted_by_employee',
            'assessed_by_assessor',
        ])) {
            abort(
                403,
                'This assessment is not currently editable by the assessor.'
            );
        }
    }
    public function print(PerformanceAssessment $performance_assessment)
{
    $this->authorizeView($performance_assessment);

    $performance_assessment->load([
        'performanceTarget',
        'period',
        'user.department',
        'user.section',
        'assessor',
        'reviewer',
        'hrReviewer',
        'items' => function ($q) {
            $q->with([
                'employeeRating',
                'assessorRating',
                'reviewerRating',
                'cycles',
            ])->orderBy('id');
        },
    ]);

    $ratings = PerformanceRatingScale::where('is_active', true)
        ->orderByDesc('score')
        ->get();

    return view('performance.performance_assessments.print', [
        'assessment' => $performance_assessment,
        'ratings' => $ratings,
    ]);
}

public function reviewer(PerformanceAssessment $performance_assessment)
{
    $user = auth()->user();

    if (
        $user->id !== $performance_assessment->reviewer_id &&
        !$user->is_admin
    ) {
        abort(403);
    }

    if (!in_array($performance_assessment->status, [
        'submitted_to_reviewer',
        'reviewed',
    ])) {
        abort(403, 'This assessment is not currently available for reviewer assessment.');
    }

    $performance_assessment->load([
        'performanceTarget',
        'period',
        'user.department',
        'user.section',
        'assessor',
        'reviewer',
        'hrReviewer',
        'items' => function ($q) {
            $q->with([
                'employeeRating',
                'assessorRating',
                'reviewerRating',
                'cycles',
            ])->orderBy('id');
        },
    ]);

    $ratings = PerformanceRatingScale::where('is_active', true)
        ->orderByDesc('score')
        ->get();

    return view('performance.performance_assessments.reviewer', [
        'assessment' => $performance_assessment,
        'ratings' => $ratings,
    ]);
}

public function saveReviewerAssessment(Request $request, PerformanceAssessment $performance_assessment)
{
    $this->authorizeReviewerEdit($performance_assessment);

    $request->validate([
        'cycles' => ['nullable', 'array'],
        'cycles.*.reviewer_actual_value' => ['nullable', 'numeric', 'min:0'],
        'cycles.*.reviewer_met_target' => ['nullable', 'in:0,1'],
        'cycles.*.reviewer_comment' => ['nullable', 'string', 'max:2000'],

        'items' => ['nullable', 'array'],
        'items.*.reviewer_comment' => ['nullable', 'string', 'max:3000'],

        'reviewer_general_comment' => ['nullable', 'string', 'max:5000'],
    ]);

    DB::transaction(function () use ($request, $performance_assessment) {
        foreach ($request->input('cycles', []) as $cycleId => $data) {
            $cycle = PerformanceAssessmentCycle::whereHas('assessmentItem', function ($q) use ($performance_assessment) {
                $q->where('performance_assessment_id', $performance_assessment->id);
            })
                ->where('id', $cycleId)
                ->firstOrFail();

            $cycle->update([
                'reviewer_actual_value' => $data['reviewer_actual_value'] ?? null,

                'reviewer_met_target' => array_key_exists('reviewer_met_target', $data)
                    ? (bool) $data['reviewer_met_target']
                    : null,

                'reviewer_comment' => $data['reviewer_comment'] ?? null,
            ]);
        }

        foreach ($request->input('items', []) as $itemId => $data) {
            $item = $performance_assessment->items()
                ->where('id', $itemId)
                ->firstOrFail();

            $item->update([
                'reviewer_comment' => $data['reviewer_comment'] ?? null,
            ]);
        }

        $performance_assessment->update([
            'reviewer_general_comment' => $request->reviewer_general_comment,
        ]);

        $this->recalculateReviewerAssessment($performance_assessment);
    });

    return redirect()
        ->route('performance-assessments.reviewer', $performance_assessment->id)
        ->with('success', 'Reviewer assessment saved successfully.');
}

public function submitReviewerAssessment(Request $request, PerformanceAssessment $performance_assessment)
{
    $this->authorizeReviewerEdit($performance_assessment);

    $performance_assessment->load([
        'items.cycles',
        'user',
        'assessor',
        'reviewer',
        'hrReviewer',
    ]);

    foreach ($performance_assessment->items as $item) {
        if ($item->cycles->isEmpty()) {
            continue;
        }

        if ($item->evaluation_method === 'per_cycle') {
            $incomplete = $item->cycles->contains(function ($cycle) {
                return $cycle->reviewer_met_target === null;
            });
        } else {
            $incomplete = $item->cycles->contains(function ($cycle) {
                return $cycle->reviewer_actual_value === null;
            });
        }

        if ($incomplete) {
            return back()->withErrors([
                'assessment' => 'Please complete the reviewer assessment for "' . $item->task . '" before submitting.',
            ]);
        }
    }

    DB::transaction(function () use ($request, $performance_assessment) {
        $this->recalculateReviewerAssessment($performance_assessment);

        $performance_assessment->update([
            'reviewer_general_comment' => $request->reviewer_general_comment ?? $performance_assessment->reviewer_general_comment,
            'status' => 'reviewed',
            'reviewer_reviewed_at' => now(),
        ]);
    });

    /*
    |--------------------------------------------------------------------------
    | Notify HR Reviewer
    |--------------------------------------------------------------------------
    */

    if ($performance_assessment->hrReviewer) {
        $performance_assessment->hrReviewer->notify(
            new PerformanceAssessmentReviewerSubmittedNotification($performance_assessment)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Notify Employee
    |--------------------------------------------------------------------------
    */

    if ($performance_assessment->user) {
        $performance_assessment->user->notify(
            new PerformanceAssessmentReviewerSubmittedNotification(
                $performance_assessment,
                'Your performance assessment has been reviewed and is awaiting HR final confirmation.'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Notify Assessor
    |--------------------------------------------------------------------------
    */

    if (
        $performance_assessment->assessor &&
        $performance_assessment->assessor_id !== $performance_assessment->user_id
    ) {
        $performance_assessment->assessor->notify(
            new PerformanceAssessmentReviewerSubmittedNotification(
                $performance_assessment,
                'The performance assessment for ' . $performance_assessment->user->fullName() . ' has been reviewed and is awaiting HR final confirmation.'
            )
        );
    }

    return redirect()->route('performance-assessments.show', $performance_assessment->id)
        ->with('success', 'Reviewer assessment submitted successfully. The appraisal is now awaiting HR final confirmation.');
}

private function recalculateReviewerAssessment(PerformanceAssessment $assessment): void
{
    $assessment->load('items.cycles');

    foreach ($assessment->items as $item) {
        $performanceIndex = $this->calculateReviewerPerformanceIndex($item);

        $rating = $this->findRatingForPerformanceIndex($performanceIndex);

        $ratingScore = $rating
            ? (float) $rating->score
            : null;

        $weightedScore = null;

        if ($ratingScore !== null) {
            $weightedScore =
                $ratingScore *
                ((float) $item->item_weight / 100) *
                ((float) $item->section_weight / 100);
        }

        $actualValue = $this->calculateReviewerActualValue($item);

        $item->update([
            'reviewer_actual_value' => $actualValue,
            'reviewer_achievement_percentage' => $performanceIndex,
            'reviewer_rating_scale_id' => $rating?->id,
            'reviewer_rating_score' => $ratingScore,
            'reviewer_weighted_score' => $weightedScore,
        ]);
    }
}

private function calculateReviewerPerformanceIndex(PerformanceAssessmentItem $item): ?float
{
    if ($item->evaluation_method === 'per_cycle') {
        return $this->calculateBooleanCyclePerformanceIndex(
            $item->cycles,
            'reviewer_met_target'
        );
    }

    if ($item->target_type === 'recurring') {
        return $this->calculateNumericCyclePerformanceIndex(
            $item->cycles,
            'reviewer_actual_value'
        );
    }

    return $this->calculateOneTimePerformanceIndex(
        $item->cycles,
        'reviewer_actual_value'
    );
}

private function calculateReviewerActualValue(PerformanceAssessmentItem $item): ?float
{
    $cycles = $item->cycles;

    if ($cycles->isEmpty()) {
        return null;
    }

    if ($item->evaluation_method === 'per_cycle') {
        return (float) $cycles->filter(function ($cycle) {
            return $cycle->reviewer_met_target === true ||
                $cycle->reviewer_met_target == 1;
        })->count();
    }

    if ($item->evaluation_method === 'cumulative') {
        $actuals = $cycles->whereNotNull('reviewer_actual_value');

        return $actuals->isEmpty()
            ? null
            : round((float) $actuals->sum('reviewer_actual_value'), 2);
    }

    if ($item->evaluation_method === 'average') {
        $actuals = $cycles->whereNotNull('reviewer_actual_value');

        return $actuals->isEmpty()
            ? null
            : round((float) $actuals->avg('reviewer_actual_value'), 2);
    }

    return null;
}

protected function authorizeReviewerEdit(PerformanceAssessment $assessment): void
{
    $user = auth()->user();

    if (
        $user->id !== $assessment->reviewer_id &&
        !$user->is_admin
    ) {
        abort(403);
    }

    if ($assessment->status !== 'submitted_to_reviewer') {
        abort(
            403,
            'This assessment is not currently editable by the reviewer.'
        );
    }
}

public function completeAssessment(Request $request, PerformanceAssessment $performance_assessment)
{
    $user = auth()->user();

    if (
        !$user->is_hr &&
        !$user->is_admin &&
        $user->id !== $performance_assessment->hr_reviewer_id
    ) {
        abort(403);
    }

    if ($performance_assessment->status !== 'reviewed') {
        return back()->withErrors([
            'assessment' => 'The reviewer must complete the assessment before HR can finalise it.',
        ]);
    }

    $request->validate([
        'hr_general_comment' => ['nullable', 'string', 'max:5000'],
    ]);

    $performance_assessment->load([
        'user',
        'assessor',
        'reviewer',
        'hrReviewer',
        'period',
    ]);

    $performance_assessment->update([
        'hr_general_comment' => $request->hr_general_comment,
        'status' => 'completed',
        'completed_at' => now(),
    ]);

    /*
    |--------------------------------------------------------------------------
    | Notify Employee
    |--------------------------------------------------------------------------
    */

    if ($performance_assessment->user) {
        $performance_assessment->user->notify(
            new PerformanceAssessmentCompletedNotification($performance_assessment)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Notify Assessor
    |--------------------------------------------------------------------------
    */

    if (
        $performance_assessment->assessor &&
        $performance_assessment->assessor_id !== $performance_assessment->user_id
    ) {
        $performance_assessment->assessor->notify(
            new PerformanceAssessmentCompletedNotification(
                $performance_assessment,
                'The performance appraisal for ' . $performance_assessment->user->fullName() . ' has been completed and confirmed by Human Resources.'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Notify Reviewer
    |--------------------------------------------------------------------------
    */

    if (
        $performance_assessment->reviewer &&
        $performance_assessment->reviewer_id !== $performance_assessment->user_id &&
        $performance_assessment->reviewer_id !== $performance_assessment->assessor_id
    ) {
        $performance_assessment->reviewer->notify(
            new PerformanceAssessmentCompletedNotification(
                $performance_assessment,
                'The performance appraisal for ' . $performance_assessment->user->fullName() . ' has been completed and confirmed by Human Resources.'
            )
        );
    }

    return redirect()->route('performance-assessments.show', $performance_assessment->id)
        ->with('success', 'Performance appraisal completed successfully.');
}

}