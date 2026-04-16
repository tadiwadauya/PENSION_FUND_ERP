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
            <h3>Section Details</h3>
        </div>
        <div class="card-body">
            <p><strong>Name:</strong> {{ $section->name }}</p>
            <p><strong>Department:</strong> {{ $section->department?->name }}</p>
            <p><strong>Head:</strong> {{ $section->head?->fullName() }}</p>
            <p><strong>Reports To:</strong> {{ $section->reportsTo?->fullName() }}</p>
            <p><strong>Reports Directly to CEO:</strong> {{ $section->reports_directly_to_ceo ? 'Yes' : 'No' }}</p>
            <p><strong>Description:</strong> {{ $section->description }}</p>
            <p><strong>Status:</strong> {{ $section->is_active ? 'Active' : 'Inactive' }}</p>
        </div>
    </div>
</div>
</section>
</div>
</div>
@endsection