@extends('layouts.app')

@section('content')
@include('includes.nav')
@include('includes.sidebar')

<div class="content-wrapper">
    <section class="content pt-3">
        <div class="container-fluid">

            <div class="row">
                <div class="col-lg-12 margin-tb">
                    <div class="d-flex justify-content-between mb-3">
                        <h2>Create New User</h2>
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

            <div class="card">
                <div class="card-body">
                    <form action="{{ route('users.store') }}" method="POST">
                        @csrf

                        <div class="row">

                            <div class="col-md-4 mb-3">
                                <label for="username">Username <span class="text-danger">*</span></label>
                                <input type="text"
                                       id="username"
                                       name="username"
                                       value="{{ old('username') }}"
                                       class="form-control @error('username') is-invalid @enderror"
                                       placeholder="Enter username">
                                @error('username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="first_name">First Name <span class="text-danger">*</span></label>
                                <input type="text"
                                       id="first_name"
                                       name="first_name"
                                       value="{{ old('first_name') }}"
                                       class="form-control @error('first_name') is-invalid @enderror"
                                       placeholder="Enter first name">
                                @error('first_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="last_name">Last Name <span class="text-danger">*</span></label>
                                <input type="text"
                                       id="last_name"
                                       name="last_name"
                                       value="{{ old('last_name') }}"
                                       class="form-control @error('last_name') is-invalid @enderror"
                                       placeholder="Enter last name">
                                @error('last_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="email">Email <span class="text-danger">*</span></label>
                                <input type="email"
                                       id="email"
                                       name="email"
                                       value="{{ old('email') }}"
                                       class="form-control @error('email') is-invalid @enderror"
                                       placeholder="Enter email address">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="mobile">Mobile</label>
                                <input type="text"
                                       id="mobile"
                                       name="mobile"
                                       value="{{ old('mobile') }}"
                                       class="form-control @error('mobile') is-invalid @enderror"
                                       placeholder="Enter mobile number">
                                @error('mobile')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="extension">Extension</label>
                                <input type="text"
                                       id="extension"
                                       name="extension"
                                       value="{{ old('extension') }}"
                                       class="form-control @error('extension') is-invalid @enderror"
                                       placeholder="Enter extension">
                                @error('extension')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="job_title">Job Title</label>
                                <input type="text"
                                       id="job_title"
                                       name="job_title"
                                       value="{{ old('job_title') }}"
                                       class="form-control @error('job_title') is-invalid @enderror"
                                       placeholder="Enter job title">
                                @error('job_title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="department_id">Department</label>
                                <select id="department_id"
                                        name="department_id"
                                        class="form-control select2 @error('department_id') is-invalid @enderror">
                                    <option value="">Select Department</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('department_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="section_id">Section</label>
                                <select id="section_id"
                                        name="section_id"
                                        class="form-control select2 @error('section_id') is-invalid @enderror">
                                    <option value="">Select Section</option>
                                    @foreach($sections as $section)
                                        <option value="{{ $section->id }}" {{ old('section_id') == $section->id ? 'selected' : '' }}>
                                            {{ $section->name }}@if($section->department) - {{ $section->department->name }} @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('section_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="grade">Grade</label>
                                <input type="number"
                                       id="grade"
                                       name="grade"
                                       value="{{ old('grade') }}"
                                       class="form-control @error('grade') is-invalid @enderror"
                                       placeholder="Enter grade">
                                @error('grade')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="address">Address</label>
                                <input type="text"
                                       id="address"
                                       name="address"
                                       value="{{ old('address') }}"
                                       class="form-control @error('address') is-invalid @enderror"
                                       placeholder="Enter address">
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="gender">Gender</label>
                                <select id="gender"
                                        name="gender"
                                        class="form-control @error('gender') is-invalid @enderror">
                                    <option value="">Select Gender</option>
                                    <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                    <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('gender')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="dob">Date of Birth</label>
                                <input type="date"
                                       id="dob"
                                       name="dob"
                                       value="{{ old('dob') }}"
                                       class="form-control @error('dob') is-invalid @enderror">
                                @error('dob')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="supervisor_id">Supervisor</label>
                                <select id="supervisor_id"
                                        name="supervisor_id"
                                        class="form-control select2 @error('supervisor_id') is-invalid @enderror">
                                    <option value="">Select Supervisor</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('supervisor_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->first_name }} {{ $user->last_name }} ({{ $user->username }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('supervisor_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="reviewer_id">Reviewer</label>
                                <select id="reviewer_id"
                                        name="reviewer_id"
                                        class="form-control select2 @error('reviewer_id') is-invalid @enderror">
                                    <option value="">Select Reviewer</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('reviewer_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->first_name }} {{ $user->last_name }} ({{ $user->username }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('reviewer_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-2 mb-3">
                                <label for="is_admin">Admin</label>
                                <select id="is_admin"
                                        name="is_admin"
                                        class="form-control @error('is_admin') is-invalid @enderror">
                                    <option value="0" {{ old('is_admin', '0') == '0' ? 'selected' : '' }}>No</option>
                                    <option value="1" {{ old('is_admin') == '1' ? 'selected' : '' }}>Yes</option>
                                </select>
                                @error('is_admin')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-2 mb-3">
                                <label for="is_hr">HR</label>
                                <select id="is_hr"
                                        name="is_hr"
                                        class="form-control @error('is_hr') is-invalid @enderror">
                                    <option value="0" {{ old('is_hr', '0') == '0' ? 'selected' : '' }}>No</option>
                                    <option value="1" {{ old('is_hr') == '1' ? 'selected' : '' }}>Yes</option>
                                </select>
                                @error('is_hr')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-2 mb-3">
                                <label for="is_ceo">CEO</label>
                                <select id="is_ceo"
                                        name="is_ceo"
                                        class="form-control @error('is_ceo') is-invalid @enderror">
                                    <option value="0" {{ old('is_ceo', '0') == '0' ? 'selected' : '' }}>No</option>
                                    <option value="1" {{ old('is_ceo') == '1' ? 'selected' : '' }}>Yes</option>
                                </select>
                                @error('is_ceo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="is_head_of_department">Head of Department</label>
                                <select id="is_head_of_department"
                                        name="is_head_of_department"
                                        class="form-control @error('is_head_of_department') is-invalid @enderror">
                                    <option value="0" {{ old('is_head_of_department', '0') == '0' ? 'selected' : '' }}>No</option>
                                    <option value="1" {{ old('is_head_of_department') == '1' ? 'selected' : '' }}>Yes</option>
                                </select>
                                @error('is_head_of_department')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="is_head_of_section">Head of Section</label>
                                <select id="is_head_of_section"
                                        name="is_head_of_section"
                                        class="form-control @error('is_head_of_section') is-invalid @enderror">
                                    <option value="0" {{ old('is_head_of_section', '0') == '0' ? 'selected' : '' }}>No</option>
                                    <option value="1" {{ old('is_head_of_section') == '1' ? 'selected' : '' }}>Yes</option>
                                </select>
                                @error('is_head_of_section')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="password">Password <span class="text-danger">*</span></label>
                                <input type="password"
                                       id="password"
                                       name="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       placeholder="Enter password">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password"
                                       id="password_confirmation"
                                       name="password_confirmation"
                                       class="form-control @error('password_confirmation') is-invalid @enderror"
                                       placeholder="Confirm password">
                            </div>

                            <div class="col-md-12 text-center">
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>

                        </div>
                    </form>
                </div>
            </div>

        </div>
    </section>
</div>

@include('includes.footer')
@endsection