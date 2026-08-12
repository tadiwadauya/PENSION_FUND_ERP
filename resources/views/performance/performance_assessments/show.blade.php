@extends('layouts.app')

@section('content')
@include('includes.nav')
@include('includes.sidebar')

<div class="content-wrapper">
<section class="content pt-3">
<div class="container-fluid">

<h2>{{ $assessment->title }}</h2>

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <strong>Please correct the following:</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

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

    $sectionTitles = [
        'SUMMARY_TASKS' => 'SECTION 2 : SUMMARY OF PERFORMANCE ON TASKS',
        'PEOPLE' => 'SECTION A : PEOPLE',
        'CUSTOMERS' => 'SECTION B : CUSTOMERS',
        'FINANCIAL' => 'SECTION C : FINANCIAL',
        'OPERATIONAL' => 'SECTION D : OPERATIONAL EXCELLENCE',
        'VALUES' => 'SECTION E : VALUES & BEHAVIOURS',
    ];

    $groupedItems = $assessment->items->groupBy('section_code');

    $employeeCanEdit =
        auth()->id() === $assessment->user_id &&
        $assessment->isEmployeeEditable();
@endphp

{{-- ========================================================= --}}
{{-- STAFF INFORMATION --}}
{{-- ========================================================= --}}

<div class="card mb-3">
<div class="card-body">

<div class="row">

<div class="col-md-6">
    <p><strong>Staff Member:</strong> {{ $assessment->user->fullName() }}</p>
    <p><strong>Department:</strong> {{ $assessment->user->department?->name ?? 'N/A' }}</p>
    <p><strong>Section:</strong> {{ $assessment->user->section?->name ?? 'N/A' }}</p>
    <p><strong>Job Title:</strong> {{ $assessment->user->job_title }}</p>
    <p><strong>Grade:</strong> {{ $assessment->user->grade }}</p>
</div>

<div class="col-md-6">
    <p><strong>Assessor:</strong> {{ $assessment->assessor?->fullName() ?? 'N/A' }}</p>
    <p><strong>Reviewer:</strong> {{ $assessment->reviewer?->fullName() ?? 'N/A' }}</p>
    <p><strong>Review Period:</strong> {{ $assessment->period?->name ?? $assessment->period?->year }}</p>

    <p>
        <strong>Status:</strong>
        <span class="badge {{ $statusClass }}">
            {{ ucwords(str_replace('_', ' ', $assessment->status)) }}
        </span>
    </p>
</div>

</div>

</div>
</div>
<a href="{{ route('performance-assessments.print', $assessment->id) }}" class="btn btn-secondary mb-3" target="_blank">
    Generate Assessment Form
</a>

{{-- ========================================================= --}}
{{-- RATING SCALE --}}
{{-- ========================================================= --}}

<div class="card mb-4">

<div class="card-header bg-dark text-white">
    <strong>Performance Rating Scale</strong>
</div>

<div class="card-body">

<p class="text-muted">
    Ratings are calculated automatically from actual performance. Staff members do not manually select A1, A2, B1, B2, C1 or C2.
</p>

<div class="table-responsive">

<table class="table table-bordered table-sm mb-0">

<thead>
<tr>
    <th>Rating</th>
    <th>Score</th>
    <th>Performance Index Range</th>
    <th>Description</th>
</tr>
</thead>

<tbody>

@forelse($ratings as $rating)

<tr>
    <td>
        <strong>{{ $rating->code }}</strong>
    </td>

    <td>
        {{ $rating->score }}
    </td>

    <td>
        {{ number_format((float) $rating->min_percentage, 2) }}
        -
        {{ number_format((float) $rating->max_percentage, 2) }}
    </td>

    <td>
        {{ $rating->description }}
    </td>
</tr>

@empty

<tr>
    <td colspan="4" class="text-center">
        No active performance rating scale has been configured.
    </td>
</tr>

@endforelse

</tbody>

</table>

</div>

</div>
</div>

{{-- ========================================================= --}}
{{-- SELF ASSESSMENT FORM --}}
{{-- ========================================================= --}}

@if($employeeCanEdit)

<form action="{{ route('performance-assessments.save-self', $assessment->id) }}" method="POST">
@csrf

@endif


{{-- ========================================================= --}}
{{-- PERFORMANCE SECTIONS --}}
{{-- ========================================================= --}}

@foreach($sectionTitles as $sectionCode => $sectionTitle)

@php
    $sectionItems = $groupedItems->get($sectionCode, collect());

    $sectionWeight = $sectionItems->first()?->section_weight ?? 0;

    $employeeSectionWeightedScore = $sectionItems->sum(function ($item) {
        return (float) ($item->employee_weighted_score ?? 0);
    });
@endphp

@if($sectionItems->count())

<div class="card mb-4">

<div class="card-header bg-secondary text-white">

<div class="d-flex justify-content-between align-items-center">

<strong>
    {{ $sectionTitle }}
</strong>

<span class="badge badge-light">
    Section Weight:
    {{ number_format((float) $sectionWeight, 2) }}%
</span>

</div>

</div>


<div class="card-body">

@foreach($sectionItems as $item)

<div class="card border mb-4">

<div class="card-header">

<div class="row align-items-center">

<div class="col-md-9">

<strong>
    {{ $loop->iteration }}.
    {{ $item->task }}
</strong>

</div>

<div class="col-md-3 text-md-right">

<span class="badge badge-primary">
    Item Weight:
    {{ number_format((float) $item->item_weight, 2) }}%
</span>

</div>

</div>

</div>


<div class="card-body">

{{-- ========================================================= --}}
{{-- TARGET DETAILS --}}
{{-- ========================================================= --}}

<div class="row mb-3">

<div class="col-md-4">

<strong>How To Achieve</strong>

<p class="mb-0">
    {{ $item->how_to_achieve ?: '-' }}
</p>

</div>


<div class="col-md-4">

<strong>Measure / Target</strong>

<p class="mb-0">
    {{ $item->measure_target }}
</p>

</div>


<div class="col-md-4">

<strong>Target Description</strong>

<p class="mb-0">
    {{ $item->target_description ?: '-' }}
</p>

</div>

</div>


<div class="row mb-3">

<div class="col-md-3">

<strong>Target Type</strong>

<br>

{{ ucwords(str_replace('_', ' ', $item->target_type)) }}

</div>


<div class="col-md-3">

<strong>Frequency</strong>

<br>

{{ ucfirst($item->frequency) }}

</div>


<div class="col-md-3">

<strong>Evaluation Method</strong>

<br>

{{ ucwords(str_replace('_', ' ', $item->evaluation_method)) }}

</div>


<div class="col-md-3">

<strong>Unit of Measure</strong>

<br>

{{ $item->unit_of_measure ?: '-' }}

</div>

</div>


<div class="row mb-3">

<div class="col-md-4">

<strong>Per Cycle Target</strong>

<br>

@if($item->per_cycle_target_value !== null)

{{ number_format((float) $item->per_cycle_target_value, 2) }}
{{ $item->unit_of_measure }}

@else

-

@endif

</div>


<div class="col-md-4">

<strong>Period Target</strong>

<br>

@if($item->period_target_value !== null)

{{ number_format((float) $item->period_target_value, 2) }}
{{ $item->unit_of_measure }}

@else

-

@endif

</div>


<div class="col-md-4">

<strong>Item Weight</strong>

<br>

{{ number_format((float) $item->item_weight, 2) }}%

</div>

</div>


{{-- ========================================================= --}}
{{-- CYCLE TABLE --}}
{{-- ========================================================= --}}

<div class="table-responsive">

<table class="table table-bordered table-striped table-sm">

<thead>

<tr>
    <th>Cycle</th>
    <th>Due Date</th>
    <th>Target</th>

    @if($item->evaluation_method === 'per_cycle')
        <th>Target Met?</th>
    @else
        <th>Actual Result</th>
    @endif

    <th>Comment</th>
    <th>Evidence / Reference</th>
</tr>

</thead>


<tbody>

@foreach($item->cycles as $cycle)

<tr>

<td>
    {{ $cycle->cycle_label }}
</td>


<td>

{{ $cycle->due_date
    ? $cycle->due_date->format('d/m/Y')
    : '-'
}}

</td>


<td>

@if($cycle->target_value !== null)

{{ number_format((float) $cycle->target_value, 2) }}
{{ $item->unit_of_measure }}

@else

-

@endif

</td>


{{-- ========================================================= --}}
{{-- PER CYCLE MET / NOT MET --}}
{{-- ========================================================= --}}

@if($item->evaluation_method === 'per_cycle')

<td style="min-width:150px;">

@if($employeeCanEdit)

<select name="cycles[{{ $cycle->id }}][employee_met_target]" class="form-control form-control-sm">

<option value="">
    -- Select --
</option>

<option value="1" {{ $cycle->employee_met_target === true ? 'selected' : '' }}>
    Met
</option>

<option value="0" {{ $cycle->employee_met_target === false ? 'selected' : '' }}>
    Not Met
</option>

</select>

@else

@if($cycle->employee_met_target === true)

<span class="badge badge-success">
    Met
</span>

@elseif($cycle->employee_met_target === false)

<span class="badge badge-danger">
    Not Met
</span>

@else

-

@endif

@endif

</td>


{{-- ========================================================= --}}
{{-- NUMERIC ACTUAL --}}
{{-- ========================================================= --}}

@else

<td style="min-width:180px;">

@if($employeeCanEdit)

<div class="input-group">

<input type="number"
       step="0.01"
       min="0"
       name="cycles[{{ $cycle->id }}][employee_actual_value]"
       value="{{ $cycle->employee_actual_value }}"
       class="form-control form-control-sm">

@if($item->unit_of_measure)

<div class="input-group-append">

<span class="input-group-text">
    {{ $item->unit_of_measure }}
</span>

</div>

@endif

</div>

@else

@if($cycle->employee_actual_value !== null)

{{ number_format((float) $cycle->employee_actual_value, 2) }}
{{ $item->unit_of_measure }}

@else

-

@endif

@endif

</td>

@endif


{{-- ========================================================= --}}
{{-- COMMENT --}}
{{-- ========================================================= --}}

<td style="min-width:220px;">

@if($employeeCanEdit)

<textarea name="cycles[{{ $cycle->id }}][employee_comment]"
          class="form-control form-control-sm"
          rows="3"
          placeholder="Explain the result for this cycle">{{ $cycle->employee_comment }}</textarea>

@else

{{ $cycle->employee_comment ?: '-' }}

@endif

</td>


{{-- ========================================================= --}}
{{-- EVIDENCE --}}
{{-- ========================================================= --}}

<td style="min-width:220px;">

@if($employeeCanEdit)

<textarea name="cycles[{{ $cycle->id }}][employee_evidence]"
          class="form-control form-control-sm"
          rows="3"
          placeholder="Ticket number, report, register, email, checklist, etc.">{{ $cycle->employee_evidence }}</textarea>

@else

{{ $cycle->employee_evidence ?: '-' }}

@endif

</td>

</tr>

@endforeach

</tbody>

</table>

</div>


{{-- ========================================================= --}}
{{-- AUTOMATIC CALCULATION RESULT --}}
{{-- ========================================================= --}}

<div class="card bg-light mt-3">

<div class="card-header">

<strong>
    Automatically Calculated Self-Assessment Result
</strong>

</div>


<div class="card-body">

<div class="row text-center">

<div class="col-md-3">

<strong>Actual Result</strong>

<br>

@if($item->employee_actual_value !== null)

{{ number_format((float) $item->employee_actual_value, 2) }}

@if($item->evaluation_method !== 'per_cycle')
    {{ $item->unit_of_measure }}
@endif

@else

-

@endif

</div>


<div class="col-md-3">

<strong>Performance Index</strong>

<br>

@if($item->employee_achievement_percentage !== null)

<span class="badge badge-secondary" style="font-size: 14px;">

{{ number_format((float) $item->employee_achievement_percentage, 2) }}

</span>

@else

-

@endif

</div>


<div class="col-md-3">

<strong>Calculated Rating</strong>

<br>

@if($item->employeeRating)

<span class="badge badge-info" style="font-size: 14px;">
    {{ $item->employeeRating->code }}
</span>

<br>

<small>
    {{ $item->employeeRating->description }}
</small>

@else

-

@endif

</div>


<div class="col-md-3">

<strong>Weighted Score</strong>

<br>

@if($item->employee_weighted_score !== null)

{{ number_format((float) $item->employee_weighted_score, 4) }}

@else

-

@endif

</div>

</div>

</div>

</div>


{{-- ========================================================= --}}
{{-- EXPLANATION --}}
{{-- ========================================================= --}}

@if($item->employeeRating)

<div class="alert alert-info mt-3 mb-3">

<strong>How this result is interpreted:</strong>

@if($item->employeeRating->code === 'B2')

The employee met the expected performance requirements for this target.

@elseif($item->employeeRating->code === 'B1')

The employee met the expected requirements and occasionally exceeded them.

@elseif($item->employeeRating->code === 'A2')

The employee consistently exceeded the expected requirements across the assessment cycles.

@elseif($item->employeeRating->code === 'A1')

The employee demonstrated outstanding performance and substantially exceeded the expected requirements.

@elseif($item->employeeRating->code === 'C1')

The employee partially met the expected requirements and improvement is required.

@elseif($item->employeeRating->code === 'C2')

The employee's performance was below the required standard.

@else

{{ $item->employeeRating->description }}

@endif

</div>

@endif


{{-- ========================================================= --}}
{{-- OVERALL EMPLOYEE COMMENT --}}
{{-- ========================================================= --}}

<div class="row mt-3">

<div class="col-md-6">

<label>
    <strong>Employee Overall Comment for this Target</strong>
</label>

@if($employeeCanEdit)

<textarea name="items[{{ $item->id }}][employee_comment]"
          class="form-control"
          rows="4"
          placeholder="Provide an overall comment on your performance against this target">{{ $item->employee_comment }}</textarea>

@else

<div class="form-control" style="height:auto; min-height:100px;">
    {{ $item->employee_comment ?: '-' }}
</div>

@endif

</div>


<div class="col-md-6">

<label>
    <strong>Overall Evidence / Reference</strong>
</label>

@if($employeeCanEdit)

<textarea name="items[{{ $item->id }}][employee_evidence]"
          class="form-control"
          rows="4"
          placeholder="Provide supporting evidence or references">{{ $item->employee_evidence }}</textarea>

@else

<div class="form-control" style="height:auto; min-height:100px;">
    {{ $item->employee_evidence ?: '-' }}
</div>

@endif

</div>

</div>


</div>

</div>

@endforeach


{{-- ========================================================= --}}
{{-- SECTION SUMMARY --}}
{{-- ========================================================= --}}

<div class="alert alert-light border">

<div class="row">

<div class="col-md-6">

<strong>
    {{ $sectionTitle }}
</strong>

<br>

Section Weight:
{{ number_format((float) $sectionWeight, 2) }}%

</div>


<div class="col-md-6 text-md-right">

<strong>
    Current Employee Weighted Score:
</strong>

<br>

{{ number_format((float) $employeeSectionWeightedScore, 4) }}

</div>

</div>

</div>


</div>

</div>

@endif

@endforeach


{{-- ========================================================= --}}
{{-- EMPLOYEE GENERAL COMMENT --}}
{{-- ========================================================= --}}

@if($employeeCanEdit)

<div class="card mb-3">

<div class="card-header">
    <strong>Employee General Assessment Comment</strong>
</div>

<div class="card-body">

<textarea name="employee_general_comment"
          class="form-control"
          rows="5"
          placeholder="Provide an overall summary of your performance during the assessment period">{{ $assessment->employee_general_comment }}</textarea>

</div>

</div>


<button type="submit" class="btn btn-primary">
    Save Self-Assessment
</button>

</form>
<br>

<form action="{{ route('performance-assessments.submit-self', $assessment->id) }}" method="POST" class="d-inline">

@csrf

<button type="submit"
        class="btn btn-success"
        onclick="return confirm('Are you sure you want to submit your performance assessment to your assessor? Once submitted, you will no longer be able to edit it unless it is returned to you.');">

Submit Self-Assessment to Assessor

</button>

</form>

@endif


{{-- ========================================================= --}}
{{-- AFTER EMPLOYEE SUBMISSION --}}
{{-- ========================================================= --}}

@if(!$employeeCanEdit)

<div class="card mb-3">

<div class="card-header">
    <strong>Employee General Assessment Comment</strong>
</div>

<div class="card-body">
    {{ $assessment->employee_general_comment ?: '-' }}
</div>

</div>

@endif


{{-- ========================================================= --}}
{{-- ASSESSOR ACTION BUTTON --}}
{{-- ========================================================= --}}

@if(
    auth()->id() === $assessment->assessor_id &&
    in_array($assessment->status, [
        'submitted_by_employee',
        'assessed_by_assessor'
    ])
)

<a href="{{ route('performance-assessments.assessor', $assessment->id) }}" class="btn btn-warning">
    Assess Employee Performance
</a>

@endif

@if(
    auth()->id() === $assessment->assessor_id &&
    in_array($assessment->status, [
        'submitted_by_employee',
        'assessed_by_assessor'
    ])
)
    <a href="{{ route('performance-assessments.assessor', $assessment->id) }}" class="btn btn-warning">
        Assess Employee Performance
    </a>
@endif

@if(
    auth()->id() === $assessment->reviewer_id &&
    $assessment->status === 'submitted_to_reviewer'
)
    <a href="{{ route('performance-assessments.reviewer', $assessment->id) }}" class="btn btn-primary">
        Review Performance Assessment
    </a>
    @if(
    (
        auth()->user()->is_hr ||
        auth()->user()->is_admin ||
        auth()->id() === $assessment->hr_reviewer_id
    ) &&
    $assessment->status === 'reviewed'
)

<div class="card mt-4">

<div class="card-header bg-success text-white">
    <strong>HR Final Confirmation</strong>
</div>

<div class="card-body">

<form action="{{ route('performance-assessments.complete', $assessment->id) }}" method="POST">
@csrf

<div class="form-group">

<label>
    <strong>HR Comment</strong>
</label>

<textarea name="hr_general_comment"
          class="form-control"
          rows="4">{{ $assessment->hr_general_comment }}</textarea>

</div>

<button type="submit"
        class="btn btn-success"
        onclick="return confirm('Confirm and complete this performance appraisal?');">

Approve and Complete Appraisal

</button>

</form>

</div>

</div>

@endif
@endif
<a href="{{ route('performance-assessments.index') }}" class="btn btn-secondary">
    Back to Assessments
</a>

</div>
</section>
</div>

@include('includes.footer')
@endsection