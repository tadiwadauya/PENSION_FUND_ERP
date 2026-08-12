@extends('layouts.app')

@section('content')
@include('includes.nav')
@include('includes.sidebar')

<div class="content-wrapper">
<section class="content pt-3">
<div class="container-fluid">

<h2>Reviewer Assessment</h2>
<h4>{{ $assessment->title }}</h4>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@php
    $sectionTitles = [
        'SUMMARY_TASKS' => 'SECTION 2 : SUMMARY OF PERFORMANCE ON TASKS',
        'PEOPLE' => 'SECTION A : PEOPLE',
        'CUSTOMERS' => 'SECTION B : CUSTOMERS',
        'FINANCIAL' => 'SECTION C : FINANCIAL',
        'OPERATIONAL' => 'SECTION D : OPERATIONAL EXCELLENCE',
        'VALUES' => 'SECTION E : VALUES & BEHAVIOURS',
    ];

    $groupedItems = $assessment->items->groupBy('section_code');
@endphp

<div class="card mb-4">
<div class="card-body">

<div class="row">
    <div class="col-md-6">
        <p><strong>Staff Member:</strong> {{ $assessment->user->fullName() }}</p>
        <p><strong>Department:</strong> {{ $assessment->user->department?->name }}</p>
        <p><strong>Job Title:</strong> {{ $assessment->user->job_title }}</p>
    </div>

    <div class="col-md-6">
        <p><strong>Assessor:</strong> {{ $assessment->assessor?->fullName() }}</p>
        <p><strong>Reviewer:</strong> {{ $assessment->reviewer?->fullName() }}</p>
        <p><strong>Period:</strong> {{ $assessment->period?->name }}</p>
    </div>
</div>

</div>
</div>

<form action="{{ route('performance-assessments.reviewer.save', $assessment->id) }}" method="POST">
@csrf

@foreach($sectionTitles as $sectionCode => $sectionTitle)

@php
    $sectionItems = $groupedItems->get($sectionCode, collect());
    $sectionWeight = $sectionItems->first()?->section_weight ?? 0;
@endphp

@if($sectionItems->count())

<div class="card mb-4">

<div class="card-header bg-secondary text-white d-flex justify-content-between">
    <strong>{{ $sectionTitle }}</strong>

    <span class="badge badge-light">
        Section Weight: {{ number_format((float) $sectionWeight, 2) }}%
    </span>
</div>

<div class="card-body">

@foreach($sectionItems as $item)

<div class="card mb-4">

<div class="card-header">
    <div class="row">
        <div class="col-md-9">
            <strong>{{ $loop->iteration }}. {{ $item->task }}</strong>
        </div>

        <div class="col-md-3 text-right">
            <span class="badge badge-primary">
                Item Weight: {{ number_format((float) $item->item_weight, 2) }}%
            </span>
        </div>
    </div>
</div>

<div class="card-body">

<p>
    <strong>Measure / Target:</strong><br>
    {{ $item->measure_target }}
</p>

<p>
    <strong>Target Description:</strong><br>
    {{ $item->target_description ?: '-' }}
</p>

<div class="table-responsive">

<table class="table table-bordered table-sm">

<thead>
<tr>
    <th>Cycle</th>
    <th>Target</th>
    <th>Employee</th>
    <th>Assessor</th>
    <th>Reviewer</th>
    <th>Reviewer Comment</th>
</tr>
</thead>

<tbody>

@foreach($item->cycles as $cycle)

<tr>

<td>
    {{ $cycle->cycle_label }}
</td>

<td>
    {{ $cycle->target_value !== null ? number_format((float) $cycle->target_value, 2) : '-' }}
    {{ $item->unit_of_measure }}
</td>

<td>
@if($item->evaluation_method === 'per_cycle')

    @if($cycle->employee_met_target === true)
        <span class="badge badge-success">Met</span>
    @elseif($cycle->employee_met_target === false)
        <span class="badge badge-danger">Not Met</span>
    @else
        -
    @endif

@else

    {{ $cycle->employee_actual_value !== null ? number_format((float) $cycle->employee_actual_value, 2) : '-' }}

@endif
</td>

<td>
@if($item->evaluation_method === 'per_cycle')

    @if($cycle->assessor_met_target === true)
        <span class="badge badge-success">Met</span>
    @elseif($cycle->assessor_met_target === false)
        <span class="badge badge-danger">Not Met</span>
    @else
        -
    @endif

@else

    {{ $cycle->assessor_actual_value !== null ? number_format((float) $cycle->assessor_actual_value, 2) : '-' }}

@endif
</td>

<td style="min-width:180px;">

@if($item->evaluation_method === 'per_cycle')

<select name="cycles[{{ $cycle->id }}][reviewer_met_target]" class="form-control">
    <option value="">-- Select --</option>

    <option value="1" {{ $cycle->reviewer_met_target === true ? 'selected' : '' }}>
        Met
    </option>

    <option value="0" {{ $cycle->reviewer_met_target === false ? 'selected' : '' }}>
        Not Met
    </option>
</select>

@else

<input type="number"
       step="0.01"
       min="0"
       name="cycles[{{ $cycle->id }}][reviewer_actual_value]"
       value="{{ $cycle->reviewer_actual_value }}"
       class="form-control">

@endif

</td>

<td style="min-width:220px;">
<textarea name="cycles[{{ $cycle->id }}][reviewer_comment]"
          class="form-control"
          rows="2">{{ $cycle->reviewer_comment }}</textarea>
</td>

</tr>

@endforeach

</tbody>

</table>

</div>

<div class="row mt-3">

<div class="col-md-4">
<strong>Employee Rating</strong><br>

@if($item->employeeRating)
    <span class="badge badge-info">{{ $item->employeeRating->code }}</span>
@else
    -
@endif
</div>

<div class="col-md-4">
<strong>Assessor Rating</strong><br>

@if($item->assessorRating)
    <span class="badge badge-primary">{{ $item->assessorRating->code }}</span>
@else
    -
@endif
</div>

<div class="col-md-4">
<strong>Reviewer Rating</strong><br>

@if($item->reviewerRating)
    <span class="badge badge-success">{{ $item->reviewerRating->code }}</span>
@else
    -
@endif
</div>

</div>

<div class="form-group mt-3">
<label><strong>Reviewer Overall Comment for this Target</strong></label>

<textarea name="items[{{ $item->id }}][reviewer_comment]"
          class="form-control"
          rows="3">{{ $item->reviewer_comment }}</textarea>
</div>

</div>
</div>

@endforeach

</div>
</div>

@endif

@endforeach

<div class="card mb-3">
<div class="card-header">
<strong>Reviewer General Comment</strong>
</div>

<div class="card-body">

<textarea name="reviewer_general_comment"
          class="form-control"
          rows="5">{{ $assessment->reviewer_general_comment }}</textarea>

</div>
</div>

<button type="submit" class="btn btn-primary">
Save Reviewer Assessment
</button>

</form>

<form action="{{ route('performance-assessments.reviewer.submit', $assessment->id) }}" method="POST" class="d-inline">
@csrf

<input type="hidden" name="reviewer_general_comment" value="{{ $assessment->reviewer_general_comment }}">

<button type="submit"
        class="btn btn-success"
        onclick="return confirm('Submit this as the final reviewer assessment?');">
Submit Final Reviewer Assessment
</button>

</form>

<a href="{{ route('performance-assessments.show', $assessment->id) }}" class="btn btn-secondary">
Back
</a>

</div>
</section>
</div>

@include('includes.footer')
@endsection