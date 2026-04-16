@extends('layouts.app')

@section('content')
@include('includes.nav')
@include('includes.sidebar')

<div class="content-wrapper">
    <section class="content pt-3">
        <div class="container-fluid">

            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Users</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">HR Dashboard</a></li>
                        <li class="breadcrumb-item active">Users</li>
                    </ol>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Showing All Users</h3>
                    <div>
                        <a class="btn btn-danger" href="{{ route('users.deleted') }}">
                            <i class="fa fa-user"></i> Deleted Users
                        </a>
                        <a class="btn btn-primary" href="{{ route('users.create') }}">
                            <i class="fa fa-user-plus"></i> Create User
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <table id="usersTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Username</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>HR</th>
                                <th>Department Head</th>
                                <th>Section Head</th>
                                <th>Department</th>
                                <th>Section</th>
                                <th width="220">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data as $user)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $user->username }}</td>
                                    <td>{{ $user->first_name }}</td>
                                    <td>{{ $user->last_name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        <span class="badge {{ $user->is_admin ? 'bg-success' : 'bg-info' }}">
                                            {{ $user->is_admin ? 'Admin' : 'User' }}
                                        </span>
                                    </td>
                                    <td>{{ $user->is_hr ? 'Yes' : 'No' }}</td>
                                    <td>{{ $user->is_head_of_department ? 'Yes' : 'No' }}</td>
                                    <td>{{ $user->is_head_of_section ? 'Yes' : 'No' }}</td>
                                    <td>{{ $user->department?->name }}</td>
                                    <td>{{ $user->section?->name }}</td>
                                    <td>
                                        <form action="{{ route('users.destroy', $user->id) }}" method="POST">
                                            <a class="btn btn-info btn-sm" href="{{ route('users.show', $user->id) }}">Show</a>
                                            <a class="btn btn-primary btn-sm" href="{{ route('users.edit', $user->id) }}">Edit</a>
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </section>
</div>

@include('includes.footer')
@endsection