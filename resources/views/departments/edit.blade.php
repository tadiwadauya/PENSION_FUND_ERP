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
            <h3>Edit Department</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('departments.update', $department->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label>Department Name</label>
                    <input type="text" name="name" class="form-control" value="{{ $department->name }}" required>
                </div>

                <div class="mb-3">
                    <label>Head of Department</label>
                    <select name="head_user_id" class="form-control">
                        <option value="">Select Head</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ $department->head_user_id == $user->id ? 'selected' : '' }}>
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
                            <option value="{{ $user->id }}" {{ $department->reports_to_user_id == $user->id ? 'selected' : '' }}>
                                {{ $user->fullName() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label>Description</label>
                    <textarea name="description" class="form-control">{{ $department->description }}</textarea>
                </div>

                <div class="mb-3">
                    <label>Status</label>
                    <select name="is_active" class="form-control">
                        <option value="1" {{ $department->is_active ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ !$department->is_active ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <button class="btn btn-primary">Update</button>
                <a href="{{ route('departments.index') }}" class="btn btn-secondary">Back</a>
            </form>
        </div>
    </div>
</div>
</section>
</div>
</div>
@endsection