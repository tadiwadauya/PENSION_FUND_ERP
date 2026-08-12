<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use App\Models\Performance\PerformanceAssessment;
use App\Models\Performance\PerformanceTarget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        /*
        |--------------------------------------------------------------------------
        | SYSTEM COUNTS
        |--------------------------------------------------------------------------
        */

        $usersCount = User::count();

        $departmentsCount = Department::count();

        /*
        |--------------------------------------------------------------------------
        | JOB TITLES
        |--------------------------------------------------------------------------
        |
        | If you have a JobTitle model, you can replace this with:
        |
        | JobTitle::count();
        |
        */

        $jobTitlesCount = User::whereNotNull('job_title')
            ->where('job_title', '!=', '')
            ->distinct()
            ->count('job_title');

        /*
        |--------------------------------------------------------------------------
        | NOTIFICATIONS
        |--------------------------------------------------------------------------
        */

        $totalNotificationsCount = $user->notifications()->count();

        $unreadNotificationsCount = $user->unreadNotifications()->count();

        /*
        |--------------------------------------------------------------------------
        | PERFORMANCE TARGET COUNTS
        |--------------------------------------------------------------------------
        */

        $mySubmittedTargetsCount = PerformanceTarget::where('user_id', $user->id)
            ->where('status', 'submitted')
            ->count();

        $awaitingMyApprovalCount = PerformanceTarget::where('assessor_id', $user->id)
            ->where('status', 'submitted')
            ->count();

        $awaitingHrReviewCount = 0;

        if ($user->is_hr || $user->is_admin) {
            $awaitingHrReviewCount = PerformanceTarget::where('status', 'approved_by_assessor')
                ->count();
        } else {
            $awaitingHrReviewCount = PerformanceTarget::where('hr_reviewer_id', $user->id)
                ->where('status', 'approved_by_assessor')
                ->count();
        }

        $reviewedTargetsCount = PerformanceTarget::where(function ($query) use ($user) {

            if ($user->is_admin || $user->is_hr) {
                $query->whereNotNull('id');
            } else {
                $query->where(function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                        ->orWhere('assessor_id', $user->id)
                        ->orWhere('hr_reviewer_id', $user->id);
                });
            }

        })
        ->where('status', 'reviewed_by_hr')
        ->count();

        /*
        |--------------------------------------------------------------------------
        | MY PERFORMANCE ASSESSMENTS
        |--------------------------------------------------------------------------
        */

        $myAssessmentsCount = PerformanceAssessment::where('user_id', $user->id)
            ->count();

        /*
        |--------------------------------------------------------------------------
        | ASSESSMENTS AWAITING ME AS ASSESSOR
        |--------------------------------------------------------------------------
        */

        $awaitingMyAssessmentCount = PerformanceAssessment::where('assessor_id', $user->id)
            ->where('status', 'submitted_by_employee')
            ->count();

        /*
        |--------------------------------------------------------------------------
        | ASSESSMENTS AWAITING ME AS REVIEWER
        |--------------------------------------------------------------------------
        */

        $awaitingMyReviewCount = PerformanceAssessment::where('reviewer_id', $user->id)
            ->where('status', 'submitted_to_reviewer')
            ->count();

        /*
        |--------------------------------------------------------------------------
        | COMPLETED ASSESSMENTS
        |--------------------------------------------------------------------------
        */

        if ($user->is_admin || $user->is_hr) {

            $completedAssessmentsCount = PerformanceAssessment::where('status', 'completed')
                ->count();

        } else {

            $completedAssessmentsCount = PerformanceAssessment::where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere('assessor_id', $user->id)
                    ->orWhere('reviewer_id', $user->id)
                    ->orWhere('hr_reviewer_id', $user->id);
            })
            ->where('status', 'completed')
            ->count();
        }

        /*
        |--------------------------------------------------------------------------
        | ASSESSMENTS AWAITING HR FINAL CONFIRMATION
        |--------------------------------------------------------------------------
        */

        $awaitingHrCompletionCount = 0;

        if ($user->is_hr || $user->is_admin) {

            $awaitingHrCompletionCount = PerformanceAssessment::where('status', 'reviewed')
                ->count();

        } elseif ($user->id) {

            $awaitingHrCompletionCount = PerformanceAssessment::where('hr_reviewer_id', $user->id)
                ->where('status', 'reviewed')
                ->count();
        }

        return view('dashboard', compact(
            'usersCount',
            'departmentsCount',
            'jobTitlesCount',

            'totalNotificationsCount',
            'unreadNotificationsCount',

            'mySubmittedTargetsCount',
            'awaitingMyApprovalCount',
            'awaitingHrReviewCount',
            'reviewedTargetsCount',

            'myAssessmentsCount',
            'awaitingMyAssessmentCount',
            'awaitingMyReviewCount',
            'completedAssessmentsCount',
            'awaitingHrCompletionCount'
        ));
    }
}