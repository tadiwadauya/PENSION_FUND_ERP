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
            <h3>Sections</h3>
            <a href="{{ route('sections.create') }}" class="btn btn-primary">Create Section</a>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Head</th>
                        <th>Reports To</th>
                        <th>Direct to CEO</th>
                        <th width="250px">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sections as $section)
                        <tr>
                            <td>{{ $section->name }}</td>
                            <td>{{ $section->department?->name }}</td>
                            <td>{{ $section->head?->fullName() }}</td>
                            <td>{{ $section->reportsTo?->fullName() }}</td>
                            <td>{{ $section->reports_directly_to_ceo ? 'Yes' : 'No' }}</td>
                            <td>
                                <form action="{{ route('sections.destroy', $section->id) }}" method="POST">
                                    <a href="{{ route('sections.show', $section->id) }}" class="btn btn-info btn-sm">Show</a>
                                    <a href="{{ route('sections.edit', $section->id) }}" class="btn btn-primary btn-sm">Edit</a>
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{ $sections->links() }}
        </div>
    </div>
</div>
</section>
</div>
</div>
@endsection