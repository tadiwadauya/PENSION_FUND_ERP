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
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif


            {{-- ========================================================= --}}
            {{-- SYSTEM INFORMATION --}}
            {{-- ========================================================= --}}

            @if(auth()->user()->is_admin || auth()->user()->is_hr)

            <h5 class="mb-3">System Overview</h5>

            <div class="row">

                <div class="col-lg-3 col-6">

                    <div class="small-box bg-info">

                        <div class="inner">
                            <h3>{{ $usersCount ?? 0 }}</h3>
                            <p>Users</p>
                        </div>

                        <div class="icon">
                            <i class="ion ion-person"></i>
                        </div>

                        <a href="{{ route('users.index') }}" class="small-box-footer">
                            More info
                            <i class="fas fa-arrow-circle-right"></i>
                        </a>

                    </div>

                </div>


                <div class="col-lg-3 col-6">

                    <div class="small-box bg-warning">

                        <div class="inner">
                            <h3>{{ $departmentsCount ?? 0 }}</h3>
                            <p>Departments</p>
                        </div>

                        <div class="icon">
                            <i class="fas fa-building"></i>
                        </div>

                        <a href="{{ route('departments.index') }}" class="small-box-footer">
                            More info
                            <i class="fas fa-arrow-circle-right"></i>
                        </a>

                    </div>

                </div>


                <div class="col-lg-3 col-6">

                    <div class="small-box bg-danger">

                        <div class="inner">
                            <h3>{{ $jobTitlesCount ?? 0 }}</h3>
                            <p>Job Titles</p>
                        </div>

                        <div class="icon">
                            <i class="fas fa-briefcase"></i>
                        </div>

                        <a href="{{ route('users.index') }}" class="small-box-footer">
                            More info
                            <i class="fas fa-arrow-circle-right"></i>
                        </a>

                    </div>

                </div>


                <div class="col-lg-3 col-6">

                    <div class="small-box bg-secondary">

                        <div class="inner">
                            <h3>{{ $totalNotificationsCount ?? 0 }}</h3>
                            <p>Total Notifications</p>
                        </div>

                        <div class="icon">
                            <i class="far fa-bell"></i>
                        </div>

                        <a href="{{ route('notifications.index') }}" class="small-box-footer">
                            View notifications
                            <i class="fas fa-arrow-circle-right"></i>
                        </a>

                    </div>

                </div>

            </div>

            @endif


            {{-- ========================================================= --}}
            {{-- PERFORMANCE TARGETS --}}
            {{-- ========================================================= --}}

            <h5 class="mb-3 mt-3">Performance Targets</h5>

            <div class="row">

                <div class="col-lg-3 col-6">

                    <div class="small-box bg-primary">

                        <div class="inner">
                            <h3>{{ $mySubmittedTargetsCount ?? 0 }}</h3>
                            <p>Submitted Targets</p>
                        </div>

                        <div class="icon">
                            <i class="fas fa-bullseye"></i>
                        </div>

                        <a href="{{ route('performance-targets.index', ['filter' => 'submitted']) }}" class="small-box-footer">
                            View targets
                            <i class="fas fa-arrow-circle-right"></i>
                        </a>

                    </div>

                </div>


                <div class="col-lg-3 col-6">

                    <div class="small-box bg-success">

                        <div class="inner">
                            <h3>{{ $awaitingMyApprovalCount ?? 0 }}</h3>
                            <p>Awaiting My Target Approval</p>
                        </div>

                        <div class="icon">
                            <i class="fas fa-user-check"></i>
                        </div>

                        <a href="{{ route('performance-targets.index', ['filter' => 'awaiting_my_approval']) }}" class="small-box-footer">
                            Review now
                            <i class="fas fa-arrow-circle-right"></i>
                        </a>

                    </div>

                </div>


                @if(auth()->user()->is_hr || auth()->user()->is_admin)

                <div class="col-lg-3 col-6">

                    <div class="small-box bg-maroon">

                        <div class="inner">
                            <h3>{{ $awaitingHrReviewCount ?? 0 }}</h3>
                            <p>Targets Awaiting HR Review</p>
                        </div>

                        <div class="icon">
                            <i class="fas fa-clipboard-check"></i>
                        </div>

                        <a href="{{ route('performance-targets.index', ['filter' => 'awaiting_hr_review']) }}" class="small-box-footer">
                            Review now
                            <i class="fas fa-arrow-circle-right"></i>
                        </a>

                    </div>

                </div>

                @endif


                <div class="col-lg-3 col-6">

                    <div class="small-box bg-teal">

                        <div class="inner">
                            <h3>{{ $reviewedTargetsCount ?? 0 }}</h3>
                            <p>Reviewed Targets</p>
                        </div>

                        <div class="icon">
                            <i class="fas fa-check-double"></i>
                        </div>

                        <a href="{{ route('performance-targets.index', ['filter' => 'reviewed_by_hr']) }}" class="small-box-footer">
                            View reviewed
                            <i class="fas fa-arrow-circle-right"></i>
                        </a>

                    </div>

                </div>

            </div>


            {{-- ========================================================= --}}
            {{-- PERFORMANCE ASSESSMENTS --}}
            {{-- ========================================================= --}}

            <h5 class="mb-3 mt-4">Performance Assessments</h5>

            <div class="row">

                {{-- MY ASSESSMENTS --}}

                <div class="col-lg-3 col-6">

                    <div class="small-box bg-info">

                        <div class="inner">

                            <h3>
                                {{ $myAssessmentsCount ?? 0 }}
                            </h3>

                            <p>My Assessments</p>

                        </div>

                        <div class="icon">
                            <i class="fas fa-file-signature"></i>
                        </div>

                        <a href="{{ route('performance-assessments.index', ['filter' => 'my_assessments']) }}" class="small-box-footer">
                            Open assessments
                            <i class="fas fa-arrow-circle-right"></i>
                        </a>

                    </div>

                </div>


                {{-- AWAITING ASSESSOR --}}

                <div class="col-lg-3 col-6">

                    <div class="small-box bg-warning">

                        <div class="inner">

                            <h3>
                                {{ $awaitingMyAssessmentCount ?? 0 }}
                            </h3>

                            <p>Awaiting My Assessment</p>

                        </div>

                        <div class="icon">
                            <i class="fas fa-user-edit"></i>
                        </div>

                        <a href="{{ route('performance-assessments.index', ['filter' => 'awaiting_assessor']) }}" class="small-box-footer">
                            Assess now
                            <i class="fas fa-arrow-circle-right"></i>
                        </a>

                    </div>

                </div>


                {{-- AWAITING REVIEWER --}}

                <div class="col-lg-3 col-6">

                    <div class="small-box bg-primary">

                        <div class="inner">

                            <h3>
                                {{ $awaitingMyReviewCount ?? 0 }}
                            </h3>

                            <p>Awaiting My Review</p>

                        </div>

                        <div class="icon">
                            <i class="fas fa-eye"></i>
                        </div>

                        <a href="{{ route('performance-assessments.index', ['filter' => 'awaiting_reviewer']) }}" class="small-box-footer">
                            Review now
                            <i class="fas fa-arrow-circle-right"></i>
                        </a>

                    </div>

                </div>


                {{-- COMPLETED --}}

                <div class="col-lg-3 col-6">

                    <div class="small-box bg-success">

                        <div class="inner">

                            <h3>
                                {{ $completedAssessmentsCount ?? 0 }}
                            </h3>

                            <p>Completed Assessments</p>

                        </div>

                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>

                        <a href="{{ route('performance-assessments.index', ['filter' => 'completed']) }}" class="small-box-footer">
                            View completed
                            <i class="fas fa-arrow-circle-right"></i>
                        </a>

                    </div>

                </div>

            </div>


            {{-- ========================================================= --}}
            {{-- HR ASSESSMENT COMPLETION --}}
            {{-- ========================================================= --}}

            @if(auth()->user()->is_hr || auth()->user()->is_admin)

            <div class="row">

                <div class="col-lg-3 col-6">

                    <div class="small-box bg-maroon">

                        <div class="inner">

                            <h3>
                                {{ $awaitingHrCompletionCount ?? 0 }}
                            </h3>

                            <p>Assessments Awaiting HR</p>

                        </div>

                        <div class="icon">
                            <i class="fas fa-user-shield"></i>
                        </div>

                        <a href="{{ route('performance-assessments.index') }}" class="small-box-footer">
                            View assessments
                            <i class="fas fa-arrow-circle-right"></i>
                        </a>

                    </div>

                </div>

            </div>

            @endif


            {{-- ========================================================= --}}
            {{-- SUMMARY --}}
            {{-- ========================================================= --}}

            <div class="row mt-3">

                <section class="col-lg-7 connectedSortable">

                    <div class="card">

                        <div class="card-header">

                            <h3 class="card-title">
                                <i class="fas fa-chart-pie mr-1"></i>
                                Performance Management Overview
                            </h3>

                        </div>

                        <div class="card-body">

                            <p>
                                The performance management module now covers target setting, assessor approval, HR review, self-assessment, assessor assessment, reviewer assessment and final HR confirmation.
                            </p>

                            <p class="mb-0">
                                Performance ratings are calculated automatically from measurable results using the configured dynamic rating scale.
                            </p>

                        </div>

                    </div>

                </section>


                <section class="col-lg-5 connectedSortable">

                    <div class="card">

                        <div class="card-header">
                            <h3 class="card-title">
                                My Quick Summary
                            </h3>
                        </div>

                        <div class="card-body">

                            <p>
                                <strong>Unread Notifications:</strong>
                                {{ $unreadNotificationsCount ?? 0 }}
                            </p>

                            <p>
                                <strong>Submitted Targets:</strong>
                                {{ $mySubmittedTargetsCount ?? 0 }}
                            </p>

                            <p>
                                <strong>Targets Awaiting My Approval:</strong>
                                {{ $awaitingMyApprovalCount ?? 0 }}
                            </p>

                            <hr>

                            <p>
                                <strong>My Assessments:</strong>
                                {{ $myAssessmentsCount ?? 0 }}
                            </p>

                            <p>
                                <strong>Awaiting My Assessment:</strong>
                                {{ $awaitingMyAssessmentCount ?? 0 }}
                            </p>

                            <p>
                                <strong>Awaiting My Review:</strong>
                                {{ $awaitingMyReviewCount ?? 0 }}
                            </p>

                            <p>
                                <strong>Completed Assessments:</strong>
                                {{ $completedAssessmentsCount ?? 0 }}
                            </p>

                            @if(auth()->user()->is_hr || auth()->user()->is_admin)

                                <hr>

                                <p>
                                    <strong>Targets Awaiting HR:</strong>
                                    {{ $awaitingHrReviewCount ?? 0 }}
                                </p>

                                <p>
                                    <strong>Assessments Awaiting HR:</strong>
                                    {{ $awaitingHrCompletionCount ?? 0 }}
                                </p>

                            @endif

                        </div>

                    </div>

                </section>

            </div>

        </div>

    </section>

</div>

@include('includes.footer')
@endsection