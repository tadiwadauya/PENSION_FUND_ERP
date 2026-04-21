<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Performance\PerformanceTarget;
use App\Models\Section;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index()
    {
        $user = auth()->user();

        $usersCount = User::count();
        $departmentsCount = Department::count();
        $sectionsCount = Section::count();
        $jobTitlesCount = User::whereNotNull('job_title')->distinct('job_title')->count('job_title');

        $mySubmittedTargetsCount = PerformanceTarget::where('user_id', $user->id)
            ->whereIn('status', ['submitted', 'approved_by_assessor', 'reviewed_by_hr'])
            ->count();

        $awaitingMyApprovalCount = PerformanceTarget::where('assessor_id', $user->id)
            ->where('status', 'submitted')
            ->count();

        $awaitingHrReviewCount = 0;
        if ($user->is_hr || $user->is_admin) {
            $awaitingHrReviewCount = PerformanceTarget::where('status', 'approved_by_assessor')->count();
        }

        $reviewedTargetsCount = PerformanceTarget::where('user_id', $user->id)
            ->where('status', 'reviewed_by_hr')
            ->count();

        $unreadNotificationsCount = $user->unreadNotifications()->count();
        $totalNotificationsCount = $user->notifications()->count();

        return view('dashboard', compact(
            'usersCount',
            'departmentsCount',
            'sectionsCount',
            'jobTitlesCount',
            'mySubmittedTargetsCount',
            'awaitingMyApprovalCount',
            'awaitingHrReviewCount',
            'reviewedTargetsCount',
            'unreadNotificationsCount',
            'totalNotificationsCount'
        ));
    }
}