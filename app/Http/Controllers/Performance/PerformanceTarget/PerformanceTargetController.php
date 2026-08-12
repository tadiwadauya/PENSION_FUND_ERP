<?php

namespace App\Http\Controllers\Performance\PerformanceTarget;

use App\Http\Controllers\Controller;
use App\Models\Performance\PerformancePeriod;
use App\Models\Performance\PerformanceTarget;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PerformanceTargetController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $filter = $request->get('filter');

        $query = PerformanceTarget::with([
            'user',
            'period',
            'assessor',
            'reviewer',
            'hrReviewer',
            'user.department',
            'user.section',
            'sections',
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
            case 'submitted':
                $query->where('status', 'submitted');
                break;

            case 'awaiting_my_approval':
                $query->where('assessor_id', $user->id)
                    ->where('status', 'submitted');
                break;

            case 'awaiting_hr_review':
                if ($user->is_hr || $user->is_admin) {
                    $query->where('status', 'approved_by_assessor');
                } else {
                    $query->where('hr_reviewer_id', $user->id)
                        ->where('status', 'approved_by_assessor');
                }
                break;

            case 'reviewed_by_hr':
                $query->where('status', 'reviewed_by_hr');
                break;

            case 'my_targets':
                $query->where('user_id', $user->id);
                break;
        }

        $targets = $query->get();

        return view('performance.performance_targets.index', compact('targets', 'filter'));
    }

    public function create()
    {
        $periods = PerformancePeriod::where('is_active', true)
            ->latest()
            ->get();

        return view('performance.performance_targets.create', compact('periods'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'performance_period_id' => ['required', 'exists:performance_periods,id'],
        ]);

        $user = auth()->user();

        $existing = PerformanceTarget::where('performance_period_id', $request->performance_period_id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            return redirect()->route('performance-targets.show', $existing->id)
                ->with('success', 'Performance target for this period already exists.');
        }

        $assessor = $user->supervisor;

        if (!$assessor && $user->reviewer) {
            $assessor = $user->reviewer;
        }

        $hrReviewer = User::where('is_hr', true)->first();

        $reviewerId = null;
        $hrReviewerId = null;

        if ($assessor && $assessor->is_ceo) {
            $reviewerId = $assessor->id;
            $hrReviewerId = null;
        } else {
            $reviewerId = $hrReviewer?->id;
            $hrReviewerId = $hrReviewer?->id;
        }

        $period = PerformancePeriod::findOrFail($request->performance_period_id);

        $title = strtoupper($user->job_title ?? 'STAFF') .
            ' - PERFORMANCE TARGETS FOR THE PERIOD ' .
            strtoupper($period->name);

        $target = DB::transaction(function () use (
            $period,
            $user,
            $assessor,
            $reviewerId,
            $hrReviewerId,
            $title
        ) {
            $target = PerformanceTarget::create([
                'performance_period_id' => $period->id,
                'user_id' => $user->id,
                'assessor_id' => $assessor?->id,
                'reviewer_id' => $reviewerId,
                'hr_reviewer_id' => $hrReviewerId,
                'title' => $title,
                'status' => 'not_submitted',
            ]);

            $this->createDefaultSections($target);
            $this->createDefaultCompetencyItems($target);

            return $target;
        });

        return redirect()->route('performance-targets.show', $target->id)
            ->with('success', 'Performance target form created successfully.');
    }

    public function show(PerformanceTarget $performance_target)
    {
        $performance_target->load([
            'period',
            'user.department',
            'user.section',
            'assessor',
            'reviewer',
            'hrReviewer',
            'sections',
            'items' => fn ($q) => $q->orderBy('sort_order'),
        ]);

        $this->authorizeView($performance_target);

        return view('performance.performance_targets.show', [
            'target' => $performance_target,
        ]);
    }

    public function edit(PerformanceTarget $performance_target)
    {
        $this->authorizeOwnerEdit($performance_target);

        $performance_target->load([
            'period',
            'user.department',
            'user.section',
            'assessor',
            'reviewer',
            'hrReviewer',
            'sections',
            'items' => fn ($q) => $q->orderBy('sort_order'),
        ]);

        return view('performance.performance_targets.edit', [
            'target' => $performance_target,
        ]);
    }

    public function update(Request $request, PerformanceTarget $performance_target)
{
    $this->authorizeOwnerEdit($performance_target);

    $request->validate([
        'items' => ['required', 'array', 'min:1'],

        'items.*.section_code' => ['nullable', 'string', 'max:100'],
        'items.*.section_title' => ['nullable', 'string', 'max:255'],
        'items.*.is_default' => ['nullable', 'boolean'],

        'items.*.perspective' => ['nullable', 'string', 'max:255'],

        'items.*.target_type' => [
            'required',
            'in:one_time,recurring',
        ],

        'items.*.frequency' => [
            'required',
            'in:once,daily,weekly,monthly,quarterly,annual',
        ],

        'items.*.due_day' => [
            'nullable',
            'integer',
            'min:1',
            'max:31',
        ],

        'items.*.due_month' => [
            'nullable',
            'integer',
            'min:1',
            'max:12',
        ],

        'items.*.due_weekday' => [
            'nullable',
            'integer',
            'min:1',
            'max:7',
        ],

        'items.*.task' => [
            'required',
            'string',
        ],

        'items.*.how_to_achieve' => [
            'nullable',
            'string',
        ],

        'items.*.measure_target' => [
            'required',
            'string',
        ],

        'items.*.per_cycle_target_value' => [
            'nullable',
            'numeric',
            'min:0',
        ],

        'items.*.period_target_value' => [
            'nullable',
            'numeric',
            'min:0',
        ],

        'items.*.unit_of_measure' => [
            'nullable',
            'string',
            'max:100',
        ],

        'items.*.evaluation_method' => [
            'required',
            'in:per_cycle,cumulative,average',
        ],

        'items.*.target_description' => [
            'nullable',
            'string',
        ],

        'items.*.weight' => [
            'required',
            'numeric',
            'min:0',
            'max:100',
        ],

        'items.*.due_date' => [
            'nullable',
            'date',
        ],
    ]);


    /*
    |--------------------------------------------------------------------------
    | Validate task weights by section
    |--------------------------------------------------------------------------
    |
    | Every section containing targets must have target weights totalling
    | exactly 100%.
    |
    */

    $itemsBySection = collect($request->items)
        ->groupBy(function ($item) {
            return $item['section_code'] ?? 'SUMMARY_TASKS';
        });


    $sectionNames = [
        'SUMMARY_TASKS' =>
            'SECTION 2 : SUMMARY OF PERFORMANCE ON TASKS',

        'PEOPLE' =>
            'SECTION A : PEOPLE',

        'CUSTOMERS' =>
            'SECTION B : CUSTOMERS',

        'FINANCIAL' =>
            'SECTION C : FINANCIAL',

        'OPERATIONAL' =>
            'SECTION D : OPERATIONAL EXCELLENCE',

        'VALUES' =>
            'SECTION E : VALUES & BEHAVIOURS',
    ];


    $weightErrors = [];


    foreach ($itemsBySection as $sectionCode => $sectionItems) {

        $totalWeight = $sectionItems->sum(function ($item) {
            return (float) ($item['weight'] ?? 0);
        });


        $totalWeight =
            round($totalWeight, 2);


        if (abs($totalWeight - 100) > 0.01) {

            $sectionName =
                $sectionNames[$sectionCode]
                ?? $sectionCode;


            $weightErrors[] =
                $sectionName .
                ' target weights must total exactly 100%. ' .
                'Current total: ' .
                number_format($totalWeight, 2) .
                '%.';

        }

    }


    if (!empty($weightErrors)) {

        return back()
            ->withInput()
            ->withErrors($weightErrors);

    }


    /*
    |--------------------------------------------------------------------------
    | Validate overall section weights
    |--------------------------------------------------------------------------
    */

    $performance_target->load('sections');


    $sectionWeightTotal =
        round(
            (float) $performance_target
                ->sections
                ->sum('weight'),
            2
        );


    if (
        abs(
            $sectionWeightTotal - 100
        ) > 0.01
    ) {

        return back()
            ->withInput()
            ->withErrors([
                'sections' =>
                    'Performance section weights must total 100%. ' .
                    'Current total: ' .
                    number_format(
                        $sectionWeightTotal,
                        2
                    ) .
                    '%.',
            ]);

    }


    /*
    |--------------------------------------------------------------------------
    | Replace target items
    |--------------------------------------------------------------------------
    */

    $performance_target
        ->items()
        ->delete();


    foreach (
        $request->items
        as $index => $item
    ) {

        $performance_target
            ->items()
            ->create([

                'sort_order' =>
                    $index + 1,

                'section_code' =>
                    $item['section_code']
                    ?? 'SUMMARY_TASKS',

                'section_title' =>
                    $item['section_title']
                    ?? 'SECTION 2 : SUMMARY OF PERFORMANCE ON TASKS',

                'is_default' =>
                    !empty(
                        $item['is_default']
                    ),

                'perspective' =>
                    $item['perspective']
                    ?? null,

                'target_type' =>
                    $item['target_type'],

                'frequency' =>
                    $item['frequency'],

                'due_day' =>
                    $item['due_day']
                    ?? null,

                'due_month' =>
                    $item['due_month']
                    ?? null,

                'due_weekday' =>
                    $item['due_weekday']
                    ?? null,

                'task' =>
                    $item['task'],

                'how_to_achieve' =>
                    $item['how_to_achieve']
                    ?? null,

                'measure_target' =>
                    $item['measure_target'],

                'per_cycle_target_value' =>
                    $item['per_cycle_target_value']
                    ?? null,

                'period_target_value' =>
                    $item['period_target_value']
                    ?? null,

                'unit_of_measure' =>
                    $item['unit_of_measure']
                    ?? null,

                'evaluation_method' =>
                    $item['evaluation_method']
                    ?? 'per_cycle',

                'target_description' =>
                    $item['target_description']
                    ?? null,

                'weight' =>
                    $item['weight'],

                'due_date' =>
                    $item['due_date']
                    ?? null,

            ]);

    }


    return redirect()
        ->route(
            'performance-targets.show',
            $performance_target->id
        )
        ->with(
            'success',
            'Performance target updated successfully.'
        );
}

    public function destroy(PerformanceTarget $performance_target)
    {
        $this->authorizeOwnerEdit($performance_target);

        $performance_target->delete();

        return redirect()->route('performance-targets.index')
            ->with('success', 'Performance target deleted successfully.');
    }

    public function print(PerformanceTarget $performance_target)
    {
        $this->authorizeView($performance_target);

        $performance_target->load([
            'period',
            'user.department',
            'user.section',
            'assessor',
            'reviewer',
            'hrReviewer',
            'sections',
            'items' => fn ($q) => $q->orderBy('sort_order'),
        ]);

        return view('performance.performance_targets.print', [
            'target' => $performance_target,
        ]);
    }

    private function createDefaultSections(PerformanceTarget $target): void
    {
        $sections = [
            [
                'section_code' => 'SUMMARY_TASKS',
                'section_title' => 'SECTION 2 : SUMMARY OF PERFORMANCE ON TASKS',
                'weight' => 60,
                'sort_order' => 1,
            ],
            [
                'section_code' => 'PEOPLE',
                'section_title' => 'SECTION A : PEOPLE',
                'weight' => 10,
                'sort_order' => 2,
            ],
            [
                'section_code' => 'CUSTOMERS',
                'section_title' => 'SECTION B : CUSTOMERS',
                'weight' => 10,
                'sort_order' => 3,
            ],
            [
                'section_code' => 'FINANCIAL',
                'section_title' => 'SECTION C : FINANCIAL',
                'weight' => 5,
                'sort_order' => 4,
            ],
            [
                'section_code' => 'OPERATIONAL',
                'section_title' => 'SECTION D : OPERATIONAL EXCELLENCE',
                'weight' => 10,
                'sort_order' => 5,
            ],
            [
                'section_code' => 'VALUES',
                'section_title' => 'SECTION E : VALUES & BEHAVIOURS',
                'weight' => 5,
                'sort_order' => 6,
            ],
        ];

        foreach ($sections as $section) {
            $target->sections()->create($section);
        }
    }

    private function createDefaultCompetencyItems(PerformanceTarget $target): void
    {
        $sortOrder = 1;

        foreach ($this->defaultCompetencyItems() as $sectionCode => $section) {
            $taskCount = count($section['tasks']);

            $defaultWeight = $taskCount > 0
                ? round(100 / $taskCount, 2)
                : null;

            foreach ($section['tasks'] as $taskIndex => $taskName) {
                $weight = $defaultWeight;

                /*
                 * This ensures a section always totals exactly 100%.
                 *
                 * Example:
                 * 3 tasks:
                 * 33.33 + 33.33 + 33.34 = 100
                 */
                if ($taskIndex === $taskCount - 1 && $taskCount > 0) {
                    $weightAlreadyAllocated = round($defaultWeight * ($taskCount - 1), 2);
                    $weight = round(100 - $weightAlreadyAllocated, 2);
                }

                $target->items()->create([
                    'sort_order' => $sortOrder++,

                    'section_code' => $sectionCode,
                    'section_title' => $section['title'],
                    'is_default' => true,

                    'perspective' => $section['title'],

                    'target_type' => 'recurring',
                    'frequency' => 'monthly',

                    'due_day' => null,
                    'due_month' => null,
                    'due_weekday' => null,

                    'task' => $taskName,

                    'how_to_achieve' => null,

                    'measure_target' => 'To be completed by staff member',

                    'per_cycle_target_value' => null,
                    'period_target_value' => null,

                    'unit_of_measure' => null,

                    'evaluation_method' => 'average',

                    'target_description' => null,

                    'weight' => $weight,

                    'due_date' => null,
                ]);
            }
        }
    }

    private function defaultCompetencyItems(): array
    {
        return [
            'PEOPLE' => [
                'title' => 'SECTION A : PEOPLE',
                'tasks' => [
                    'Working relations with colleagues.',
                    'Working relations with subordinates.',
                    'Working relations with superiors.',
                    'Communication and participation.',
                ],
            ],

            'CUSTOMERS' => [
                'title' => 'SECTION B : CUSTOMERS',
                'tasks' => [
                    'Customer relations.',
                    'Handling of procedures.',
                    'Responsiveness to customer needs.',
                    'Quality of work.',
                ],
            ],

            'FINANCIAL' => [
                'title' => 'SECTION C : FINANCIAL',
                'tasks' => [
                    'Use of company resources.',
                    'Cost consciousness.',
                    'Awareness of business issues. Avoid personal errands while using Fund resources and time.',
                    'Planning all work.',
                ],
            ],

            'OPERATIONAL' => [
                'title' => 'SECTION D : OPERATIONAL EXCELLENCE',
                'tasks' => [
                    'Job knowledge.',
                    'Time management.',
                    'Dependability.',
                    'Compliance with instructions & laid down policies & procedures.',
                ],
            ],

            'VALUES' => [
                'title' => 'SECTION E : VALUES & BEHAVIOURS',
                'tasks' => [
                    'Quality Service. Perform assigned duties to expected standards.',
                    'Teamwork. Carry out assigned work as part of a team.',
                    'Respect for Individuals.',
                    'Continuous Improvement.',
                ],
            ],
        ];
    }

    protected function authorizeView(PerformanceTarget $target): void
    {
        $user = auth()->user();

        if (
            $user->id !== $target->user_id &&
            $user->id !== $target->assessor_id &&
            $user->id !== $target->reviewer_id &&
            $user->id !== $target->hr_reviewer_id &&
            !$user->is_hr &&
            !$user->is_admin
        ) {
            abort(403);
        }
    }

    protected function authorizeOwnerEdit(PerformanceTarget $target): void
    {
        if (
            auth()->id() !== $target->user_id ||
            !$target->isEditable()
        ) {
            abort(403);
        }
    }
}