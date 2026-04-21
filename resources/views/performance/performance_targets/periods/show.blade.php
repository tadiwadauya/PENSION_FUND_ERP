@extends('layouts.app')

@section('content')
@include('includes.nav')
@include('includes.sidebar')

<div class="content-wrapper">
    <section class="content pt-3">
        <div class="container-fluid">

            <div class="d-flex justify-content-between mb-3">
                <h2>Performance Period Details</h2>
                <a href="{{ route('performance-target-periods.index') }}" class="btn btn-secondary">Back</a>
            </div>

            <div class="card">
                <div class="card-body">
                    <p><strong>Name:</strong> {{ $period->name }}</p>
                    <p><strong>Year:</strong> {{ $period->year }}</p>
                    <p><strong>Review Type:</strong> {{ ucfirst(str_replace('_', ' ', $period->review_type)) }}</p>
                    <p><strong>Start Date:</strong> {{ optional($period->start_date)->format('d/m/Y') }}</p>
                    <p><strong>End Date:</strong> {{ optional($period->end_date)->format('d/m/Y') }}</p>
                    <p><strong>Review Start Date:</strong> {{ optional($period->review_start_date)->format('d/m/Y') }}</p>
                    <p><strong>Review End Date:</strong> {{ optional($period->review_end_date)->format('d/m/Y') }}</p>
                    <p><strong>Active:</strong> {{ $period->is_active ? 'Yes' : 'No' }}</p>
                </div>
            </div>

        </div>
    </section>
</div>

@include('includes.footer')
@endsection