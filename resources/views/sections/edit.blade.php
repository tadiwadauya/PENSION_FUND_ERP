@extends('layouts.app')

@section('content')
<div class="wrapper">
@include('includes.nav')
@include('includes.sidebar')

<div class="content-wrapper">
<section class="content pt-3">
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3>Edit Section</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('sections.update', $section->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label>Department (Optional)</label>
                    <select name="department_id" class="form-control">
                        <option value="">No Department</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" {{ $section->department_id == $department->id ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label>Section Name</label>
                    <input type="text" name="name" class="form-control" value="{{ $section->name }}" required>
                </div>

                <div class="mb-3">
                    <label>Head of Section</label>
                    <select name="head_user_id" class="form-control">
                        <option value="">Select Head</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ $section->head_user_id == $user->id ? 'selected' : '' }}>
                                {{ $user->fullName() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label>Reports To</label>
                    <select name="reports_to_user_id" class="form-control">
                        <option value="">Select Officer</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ $section->reports_to_user_id == $user->id ? 'selected' : '' }}>
                                {{ $user->fullName() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label>Reports Directly to CEO</label>
                    <select name="reports_directly_to_ceo" class="form-control">
                        <option value="0" {{ !$section->reports_directly_to_ceo ? 'selected' : '' }}>No</option>
                        <option value="1" {{ $section->reports_directly_to_ceo ? 'selected' : '' }}>Yes</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label>Description</label>
                    <textarea name="description" class="form-control">{{ $section->description }}</textarea>
                </div>

                <div class="mb-3">
                    <label>Status</label>
                    <select name="is_active" class="form-control">
                        <option value="1" {{ $section->is_active ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ !$section->is_active ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <button class="btn btn-primary">Update</button>
                <a href="{{ route('sections.index') }}" class="btn btn-secondary">Back</a>
            </form>
        </div>
    </div>
</div>
</section>
</div>
</div>
@endsection