@extends('layouts.app')

@section('content')
<div class="wrapper">
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
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3>0</h3>
                                <p>Performance Contracts</p>
                            </div>
                            <div class="icon">
                                <i class="ion ion-stats-bars"></i>
                            </div>
                            <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
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
                </div>

                <div class="row">
                    <section class="col-lg-7 connectedSortable">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-chart-pie mr-1"></i>
                                    Performances
                                </h3>
                            </div>
                            <div class="card-body">
                                <p>Performance charts will come in the next phase.</p>
                            </div>
                        </div>
                    </section>

                    <section class="col-lg-5 connectedSortable">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Quick Summary</h3>
                            </div>
                            <div class="card-body">
                                <p><strong>Users:</strong> {{ $usersCount }}</p>
                                <p><strong>Departments:</strong> {{ $departmentsCount }}</p>
                                <p><strong>Sections:</strong> {{ $sectionsCount }}</p>
                                <p><strong>Job Titles:</strong> {{ $jobTitlesCount }}</p>
                            </div>
                        </div>
                    </section>
                </div>

            </div>
        </section>
    </div>

    @include('includes.footer')
</div>
@endsection