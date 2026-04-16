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
            <h3>Department Details</h3>
        </div>
        <div class="card-body">
            <p><strong>Name:</strong> {{ $department->name }}</p>
            <p><strong>Head:</strong> {{ $department->head?->fullName() }}</p>
            <p><strong>Reports To:</strong> {{ $department->reportsTo?->fullName() }}</p>
            <p><strong>Description:</strong> {{ $department->description }}</p>
            <p><strong>Status:</strong> {{ $department->is_active ? 'Active' : 'Inactive' }}</p>

            <h5 class="mt-4">Sections</h5>
            <ul>
                @foreach($department->sections as $section)
                    <li>{{ $section->name }}</li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
</section>
</div>
</div>
@endsection