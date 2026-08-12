@extends('layouts.app')

@section('content')
@include('includes.nav')
@include('includes.sidebar')

<div class="content-wrapper">
<section class="content pt-3">
<div class="container-fluid">

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Performance Assessments</h2>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card mb-3">
<div class="card-body">

<a href="{{ route('performance-assessments.index') }}" class="btn {{ empty($filter) ? 'btn-dark' : 'btn-outline-dark' }} btn-sm mb-2">
    All
</a>

<a href="{{ route('performance-assessments.index', ['filter' => 'my_assessments']) }}" class="btn {{ $filter === 'my_assessments' ? 'btn-primary' : 'btn-outline-primary' }} btn-sm mb-2">
    My Assessments
</a>

<a href="{{ route('performance-assessments.index', ['filter' => 'awaiting_assessor']) }}" class="btn {{ $filter === 'awaiting_assessor' ? 'btn-warning' : 'btn-outline-warning' }} btn-sm mb-2">
    Awaiting My Assessment
</a>

<a href="{{ route('performance-assessments.index', ['filter' => 'awaiting_reviewer']) }}" class="btn {{ $filter === 'awaiting_reviewer' ? 'btn-info' : 'btn-outline-info' }} btn-sm mb-2">
    Awaiting My Review
</a>

<a href="{{ route('performance-assessments.index', ['filter' => 'completed']) }}" class="btn {{ $filter === 'completed' ? 'btn-success' : 'btn-outline-success' }} btn-sm mb-2">
    Completed
</a>

</div>
</div>

<div class="card">
<div class="card-body table-responsive">

<table id="assessmentTable" class="table table-bordered table-striped">

<thead>
<tr>
    <th>No</th>
    <th>Period</th>
    <th>Staff Member</th>
    <th>Department</th>
    <th>Job Title</th>
    <th>Assessor</th>
    <th>Status</th>
    <th>Submitted</th>
    <th>Action</th>
</tr>
</thead>

<tbody>

@foreach($assessments as $assessment)

<tr>
    <td>{{ $loop->iteration }}</td>

    <td>{{ $assessment->period?->name }}</td>

    <td>{{ $assessment->user?->fullName() }}</td>

    <td>{{ $assessment->user?->department?->name }}</td>

    <td>{{ $assessment->user?->job_title }}</td>

    <td>{{ $assessment->assessor?->fullName() ?? 'N/A' }}</td>

    <td>
        @php
            $statusClass = match($assessment->status) {
                'not_started' => 'badge-secondary',
                'self_assessment_in_progress' => 'badge-info',
                'submitted_by_employee' => 'badge-warning',
                'assessed_by_assessor' => 'badge-primary',
                'rejected_by_assessor' => 'badge-danger',
                'submitted_to_reviewer' => 'badge-info',
                'reviewed' => 'badge-success',
                'completed' => 'badge-success',
                default => 'badge-dark',
            };
        @endphp

        <span class="badge {{ $statusClass }}">
            {{ ucwords(str_replace('_', ' ', $assessment->status)) }}
        </span>
    </td>

    <td>
        {{ optional($assessment->employee_submitted_at)->format('d/m/Y H:i') ?? '-' }}
    </td>

    <td>
        <a href="{{ route('performance-assessments.show', $assessment->id) }}" class="btn btn-info btn-sm">
            Open
        </a>
        <a href="{{ route('performance-assessments.print', $assessment->id) }}" class="btn btn-secondary btn-sm" target="_blank">
    Generate Form
</a>
        @if(
    auth()->id() === $assessment->assessor_id &&
    in_array($assessment->status, [
        'submitted_by_employee',
        'assessed_by_assessor'
    ])
)

<a href="{{ route('performance-assessments.assessor', $assessment->id) }}" class="btn btn-warning btn-sm">
    Assess
</a>

@endif
    </td>
</tr>

@endforeach

</tbody>

</table>

@if($assessments->isEmpty())
    <p class="mb-0">No performance assessments found.</p>
@endif

</div>
</div>

</div>
</section>
</div>

@include('includes.footer')
@endsection

@push('scripts')
<script>
$(function () {
    if ($('#assessmentTable').length) {
        $('#assessmentTable').DataTable({
            responsive: true,
            lengthChange: true,
            autoWidth: false,
            pageLength: 10,
            ordering: true,
            searching: true,
            info: true
        });
    }
});
</script>
@endpush