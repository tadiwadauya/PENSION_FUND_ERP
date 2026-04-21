@extends('layouts.app')

@section('content')
@include('includes.nav')
@include('includes.sidebar')

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Dashboard</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ $usersCount }}</h3>
                            <p>Users</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-person"></i>
                        </div>
                        <a href="{{ route('users.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ $departmentsCount }}</h3>
                            <p>Departments</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-person-add"></i>
                        </div>
                        <a href="{{ route('departments.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>{{ $jobTitlesCount }}</h3>
                            <p>Job Titles</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-briefcase"></i>
                        </div>
                        <a href="{{ route('users.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-secondary">
                        <div class="inner">
                            <h3>{{ $totalNotificationsCount }}</h3>
                            <p>Total Notifications</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-ios-bell"></i>
                        </div>
                        <a href="{{ route('notifications.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3>{{ $mySubmittedTargetsCount }}</h3>
                            <p>Submitted Targets</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-checkmark-circled"></i>
                        </div>
                        <a href="{{ route('performance-targets.index', ['filter' => 'submitted']) }}" class="small-box-footer">
    View targets <i class="fas fa-arrow-circle-right"></i>
</a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ $awaitingMyApprovalCount }}</h3>
                            <p>Awaiting My Approval</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-ios-compose"></i>
                        </div>
                        <a href="{{ route('performance-targets.index', ['filter' => 'awaiting_my_approval']) }}" class="small-box-footer">
    Review now <i class="fas fa-arrow-circle-right"></i>
</a>
                    </div>
                </div>

                @if(auth()->user()->is_hr || auth()->user()->is_admin)
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-maroon">
                        <div class="inner">
                            <h3>{{ $awaitingHrReviewCount }}</h3>
                            <p>Awaiting HR Review</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-ios-paper"></i>
                        </div>
                        <a href="{{ route('performance-targets.index', ['filter' => 'awaiting_hr_review']) }}" class="small-box-footer">
    Review now <i class="fas fa-arrow-circle-right"></i>
</a>
                    </div>
                </div>
                @endif

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-teal">
                        <div class="inner">
                            <h3>{{ $reviewedTargetsCount }}</h3>
                            <p>Reviewed Targets</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-clipboard"></i>
                        </div>
                        <a href="{{ route('performance-targets.index') }}" class="small-box-footer">View reviewed <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
            </div>

            <div class="row">
                <section class="col-lg-7 connectedSortable">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-pie mr-1"></i>
                                Performance Overview
                            </h3>
                        </div>
                        <div class="card-body">
                            <p>Performance targets, approvals, and HR reviews are now available in the system.</p>
                            <p>Next phase will include appraisal scoring, monthly actuals, and KPI dashboards.</p>
                        </div>
                    </div>
                </section>

                <section class="col-lg-5 connectedSortable">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Quick Summary</h3>
                        </div>
                        <div class="card-body">
                            <p><strong>Unread Notifications:</strong> {{ $unreadNotificationsCount }}</p>
                            <p><strong>Submitted Targets:</strong> {{ $mySubmittedTargetsCount }}</p>
                            <p><strong>Awaiting My Approval:</strong> {{ $awaitingMyApprovalCount }}</p>
                            @if(auth()->user()->is_hr || auth()->user()->is_admin)
                                <p><strong>Awaiting HR Review:</strong> {{ $awaitingHrReviewCount }}</p>
                            @endif
                            <p><strong>Reviewed Targets:</strong> {{ $reviewedTargetsCount }}</p>
                        </div>
                    </div>
                </section>
            </div>

        </div>
    </section>
</div>

@include('includes.footer')
@endsection