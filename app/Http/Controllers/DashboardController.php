<?php

namespace App\Http\Controllers;

use App\Models\Department;
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
        $usersCount = User::count();
        $departmentsCount = Department::count();
        $sectionsCount = Section::count();
        $jobTitlesCount = User::whereNotNull('job_title')->distinct('job_title')->count('job_title');

        return view('dashboard', compact(
            'usersCount',
            'departmentsCount',
            'sectionsCount',
            'jobTitlesCount'
        ));
    }
}