@extends('layouts.app')

@section('content')
<div class="wrapper">
@include('includes.nav')
@include('includes.sidebar')

<div class="content-wrapper">
<section class="content pt-3">
<div class="container-fluid">

<div class="row">
    <div class="col-lg-12 margin-tb">
        <div class="d-flex justify-content-between mb-3">
            <h2>Edit User</h2>
            <a class="btn btn-primary" href="{{ route('users.index') }}">Back</a>
        </div>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <strong>Whoops!</strong> There were some problems with your input.
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('users.update', $user->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row">

        <div class="col-md-4 mb-3">
            <label>User Name</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
        </div>

        <div class="col-md-4 mb-3">
            <label>Username</label>
            <input type="text" name="username" class="form-control" value="{{ old('username', $user->username) }}" required>
        </div>

        <div class="col-md-4 mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
        </div>

        <div class="col-md-4 mb-3">
            <label>First Name</label>
            <input type="text" name="first_name" class="form-control" value="{{ old('first_name', $user->first_name) }}" required>
        </div>

        <div class="col-md-4 mb-3">
            <label>Last Name</label>
            <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $user->last_name) }}" required>
        </div>

        <div class="col-md-4 mb-3">
            <label>Job Title</label>
            <input type="text" name="job_title" class="form-control" value="{{ old('job_title', $user->job_title) }}">
        </div>

        <div class="col-md-4 mb-3">
            <label>Department</label>
            <select class="form-control" name="department_id">
                <option value="">Select Department</option>
                @foreach($departments as $department)
                    <option value="{{ $department->id }}" {{ $user->department_id == $department->id ? 'selected' : '' }}>
                        {{ $department->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4 mb-3">
            <label>Section</label>
            <select class="form-control" name="section_id">
                <option value="">Select Section</option>
                @foreach($sections as $section)
                    <option value="{{ $section->id }}" {{ $user->section_id == $section->id ? 'selected' : '' }}>
                        {{ $section->name }} @if($section->department) - {{ $section->department->name }} @endif
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4 mb-3">
            <label>Grade</label>
            <input type="number" name="grade" class="form-control" value="{{ old('grade', $user->grade) }}">
        </div>

        <div class="col-md-4 mb-3">
            <label>Mobile</label>
            <input type="text" name="mobile" class="form-control" value="{{ old('mobile', $user->mobile) }}">
        </div>

        <div class="col-md-4 mb-3">
            <label>Extension</label>
            <input type="text" name="extension" class="form-control" value="{{ old('extension', $user->extension) }}">
        </div>

        <div class="col-md-4 mb-3">
            <label>Address</label>
            <input type="text" name="address" class="form-control" value="{{ old('address', $user->address) }}">
        </div>

        <div class="col-md-4 mb-3">
            <label>Gender</label>
            <select name="gender" class="form-control">
                <option value="">Select Gender</option>
                <option value="male" {{ $user->gender == 'male' ? 'selected' : '' }}>Male</option>
                <option value="female" {{ $user->gender == 'female' ? 'selected' : '' }}>Female</option>
                <option value="other" {{ $user->gender == 'other' ? 'selected' : '' }}>Other</option>
            </select>
        </div>

        <div class="col-md-4 mb-3">
            <label>Date of Birth</label>
            <input type="date" name="dob" class="form-control" value="{{ old('dob', optional($user->dob)->format('Y-m-d')) }}">
        </div>

        <div class="col-md-4 mb-3">
            <label>Supervisor</label>
            <select class="form-control" name="supervisor_id">
                <option value="">Select Supervisor</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ $user->supervisor_id == $u->id ? 'selected' : '' }}>
                        {{ $u->first_name }} {{ $u->last_name }} ({{ $u->username }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4 mb-3">
            <label>Reviewer</label>
            <select class="form-control" name="reviewer_id">
                <option value="">Select Reviewer</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ $user->reviewer_id == $u->id ? 'selected' : '' }}>
                        {{ $u->first_name }} {{ $u->last_name }} ({{ $u->username }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2 mb-3">
            <label>Admin</label>
            <select name="is_admin" class="form-control">
                <option value="0" {{ !$user->is_admin ? 'selected' : '' }}>No</option>
                <option value="1" {{ $user->is_admin ? 'selected' : '' }}>Yes</option>
            </select>
        </div>

        <div class="col-md-2 mb-3">
            <label>HR</label>
            <select name="is_hr" class="form-control">
                <option value="0" {{ !$user->is_hr ? 'selected' : '' }}>No</option>
                <option value="1" {{ $user->is_hr ? 'selected' : '' }}>Yes</option>
            </select>
        </div>

        <div class="col-md-2 mb-3">
            <label>CEO</label>
            <select name="is_ceo" class="form-control">
                <option value="0" {{ !$user->is_ceo ? 'selected' : '' }}>No</option>
                <option value="1" {{ $user->is_ceo ? 'selected' : '' }}>Yes</option>
            </select>
        </div>

        <div class="col-md-3 mb-3">
            <label>Head of Department</label>
            <select name="is_head_of_department" class="form-control">
                <option value="0" {{ !$user->is_head_of_department ? 'selected' : '' }}>No</option>
                <option value="1" {{ $user->is_head_of_department ? 'selected' : '' }}>Yes</option>
            </select>
        </div>

        <div class="col-md-3 mb-3">
            <label>Head of Section</label>
            <select name="is_head_of_section" class="form-control">
                <option value="0" {{ !$user->is_head_of_section ? 'selected' : '' }}>No</option>
                <option value="1" {{ $user->is_head_of_section ? 'selected' : '' }}>Yes</option>
            </select>
        </div>

        <div class="col-md-12 mb-3">
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="reset_password" name="reset_password" value="1">
                <label class="form-check-label" for="reset_password">
                    Reset password and force user to change it on next login
                </label>
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <label>New Password (optional if reset is checked)</label>
            <input type="password" name="password" class="form-control">
        </div>

        <div class="col-md-6 mb-3">
            <label>Confirm New Password</label>
            <input type="password" name="password_confirmation" class="form-control">
        </div>

        <div class="col-md-12 text-center">
            <button type="submit" class="btn btn-primary">Update</button>
        </div>

    </div>
</form>

</div>
</section>
</div>
</div>
@endsection