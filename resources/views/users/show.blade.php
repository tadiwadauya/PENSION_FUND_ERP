@extends('layouts.app')

@section('content')
<div class="wrapper">
@include('includes.nav')
@include('includes.sidebar')

<div class="content-wrapper">
<section class="content pt-3">
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h3>User Details</h3>
            <a href="{{ route('users.index') }}" class="btn btn-primary">Back</a>
        </div>
        <div class="card-body">
            <p><strong>Name:</strong> {{ $user->fullName() }}</p>
            <p><strong>Username:</strong> {{ $user->username }}</p>
            <p><strong>Email:</strong> {{ $user->email }}</p>
            <p><strong>Department:</strong> {{ $user->department?->name }}</p>
            <p><strong>Section:</strong> {{ $user->section?->name }}</p>
            <p><strong>Job Title:</strong> {{ $user->job_title }}</p>
            <p><strong>Supervisor:</strong> {{ $user->supervisor?->fullName() }}</p>
            <p><strong>Reviewer:</strong> {{ $user->reviewer?->fullName() }}</p>
            <p><strong>Admin:</strong> {{ $user->is_admin ? 'Yes' : 'No' }}</p>
            <p><strong>HR:</strong> {{ $user->is_hr ? 'Yes' : 'No' }}</p>
            <p><strong>Head of Department:</strong> {{ $user->is_head_of_department ? 'Yes' : 'No' }}</p>
            <p><strong>Head of Section:</strong> {{ $user->is_head_of_section ? 'Yes' : 'No' }}</p>
        </div>
    </div>
</div>
</section>
</div>
</div>
@endsection