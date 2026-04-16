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
            <h3>Departments</h3>
            <a href="{{ route('departments.create') }}" class="btn btn-primary">Create Department</a>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Head</th>
                        <th>Reports To</th>
                        <th>Status</th>
                        <th width="250px">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($departments as $department)
                        <tr>
                            <td>{{ $department->name }}</td>
                            <td>{{ $department->head?->fullName() }}</td>
                            <td>{{ $department->reportsTo?->fullName() }}</td>
                            <td>{{ $department->is_active ? 'Active' : 'Inactive' }}</td>
                            <td>
                                <form action="{{ route('departments.destroy', $department->id) }}" method="POST">
                                    <a href="{{ route('departments.show', $department->id) }}" class="btn btn-info btn-sm">Show</a>
                                    <a href="{{ route('departments.edit', $department->id) }}" class="btn btn-primary btn-sm">Edit</a>
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{ $departments->links() }}
        </div>
    </div>
</div>
</section>
</div>
</div>
@endsection