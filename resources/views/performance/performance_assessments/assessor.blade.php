@extends('layouts.app')

@section('content')
@include('includes.nav')
@include('includes.sidebar')

<div class="content-wrapper">
<section class="content pt-3">
<div class="container-fluid">

<h2>Assessor Assessment</h2>

<h4 class="mb-3">
    {{ $assessment->title }}
</h4>

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


{{-- ========================================================= --}}
{{-- STAFF DETAILS --}}
{{-- ========================================================= --}}

<div class="card mb-4">

<div class="card-header bg-dark text-white">
    <strong>Performance Assessment Details</strong>
</div>

<div class="card-body">

<div class="row">

<div class="col-md-6">

<p>
    <strong>Staff Member:</strong>
    {{ $assessment->user->fullName() }}
</p>

<p>
    <strong>Department:</strong>
    {{ $assessment->user->department?->name ?? 'N/A' }}
</p>

<p>
    <strong>Section:</strong>
    {{ $assessment->user->section?->name ?? 'N/A' }}
</p>

<p>
    <strong>Job Title:</strong>
    {{ $assessment->user->job_title }}
</p>

<p>
    <strong>Grade:</strong>
    {{ $assessment->user->grade }}
</p>

</div>


<div class="col-md-6">

<p>
    <strong>Assessor:</strong>
    {{ $assessment->assessor?->fullName() ?? 'N/A' }}
</p>

<p>
    <strong>Reviewer:</strong>
    {{ $assessment->reviewer?->fullName() ?? 'N/A' }}
</p>

<p>
    <strong>Performance Period:</strong>
    {{ $assessment->period?->name }}
</p>

<p>
    <strong>Employee Submitted:</strong>

    {{ $assessment->employee_submitted_at
        ? $assessment->employee_submitted_at->format('d/m/Y H:i')
        : '-'
    }}
</p>

</div>

</div>

</div>

</div>


{{-- ========================================================= --}}
{{-- RATING SCALE --}}
{{-- ========================================================= --}}

<div class="card mb-4">

<div class="card-header">
    <strong>Current Performance Rating Scale</strong>
</div>

<div class="card-body table-responsive">

<table class="table table-bordered table-sm">

<thead>
<tr>
    <th>Rating</th>
    <th>Score</th>
    <th>Range</th>
    <th>Description</th>
</tr>
</thead>

<tbody>

@foreach($ratings as $rating)

<tr>

<td>
    <strong>{{ $rating->code }}</strong>
</td>

<td>
    {{ $rating->score }}
</td>

<td>
    {{ number_format((float) $rating->min_percentage, 2) }}%
    -
    {{ number_format((float) $rating->max_percentage, 2) }}%
</td>

<td>
    {{ $rating->description }}
</td>

</tr>

@endforeach

</tbody>

</table>

</div>

</div>


<form action="{{ route('performance-assessments.assessor.save', $assessment->id) }}" method="POST">
@csrf


{{-- ========================================================= --}}
{{-- SECTIONS --}}
{{-- ========================================================= --}}

@foreach($sectionTitles as $sectionCode => $sectionTitle)

@php
    $sectionItems =
        $groupedItems->get(
            $sectionCode,
            collect()
        );

    $sectionWeight =
        $sectionItems->first()?->section_weight
        ?? 0;

    $employeeSectionWeightedScore =
        $sectionItems->sum(function ($item) {
            return (float) ($item->employee_weighted_score ?? 0);
        });

    $assessorSectionWeightedScore =
        $sectionItems->sum(function ($item) {
            return (float) ($item->assessor_weighted_score ?? 0);
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

<div class="row">

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


{{-- TARGET INFORMATION --}}

<div class="row">

<div class="col-md-4 mb-3">

<strong>How To Achieve</strong>

<p class="mb-0">
    {{ $item->how_to_achieve ?: '-' }}
</p>

</div>


<div class="col-md-4 mb-3">

<strong>Measure / Target</strong>

<p class="mb-0">
    {{ $item->measure_target }}
</p>

</div>


<div class="col-md-4 mb-3">

<strong>Target Description</strong>

<p class="mb-0">
    {{ $item->target_description ?: '-' }}
</p>

</div>

</div>


<div class="row mb-3">

<div class="col-md-3">

<strong>Evaluation Method</strong><br>

{{ ucwords(
    str_replace(
        '_',
        ' ',
        $item->evaluation_method
    )
) }}

</div>


<div class="col-md-3">

<strong>Per Cycle Target</strong><br>

@if($item->per_cycle_target_value !== null)

{{ number_format(
    (float) $item->per_cycle_target_value,
    2
) }}

{{ $item->unit_of_measure }}

@else

-

@endif

</div>


<div class="col-md-3">

<strong>Period Target</strong><br>

@if($item->period_target_value !== null)

{{ number_format(
    (float) $item->period_target_value,
    2
) }}

{{ $item->unit_of_measure }}

@else

-

@endif

</div>


<div class="col-md-3">

<strong>Weight</strong><br>

{{ number_format(
    (float) $item->item_weight,
    2
) }}%

</div>

</div>


{{-- ========================================================= --}}
{{-- EMPLOYEE VS ASSESSOR --}}
{{-- ========================================================= --}}

<div class="table-responsive">

<table class="table table-bordered table-striped">

<thead>

<tr>

<th>Cycle</th>

<th>Due Date</th>

<th>Target</th>

<th>Employee Result</th>

<th>Employee Comment / Evidence</th>

<th>Assessor Result</th>

<th>Assessor Comment</th>

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

{{ number_format(
    (float) $cycle->target_value,
    2
) }}

{{ $item->unit_of_measure }}

@else

-

@endif

</td>


{{-- EMPLOYEE RESULT --}}

<td>

@if($item->evaluation_method === 'per_cycle')

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

@else

    @if($cycle->employee_actual_value !== null)

        {{ number_format(
            (float) $cycle->employee_actual_value,
            2
        ) }}

        {{ $item->unit_of_measure }}

    @else

        -

    @endif

@endif

</td>


{{-- EMPLOYEE COMMENT --}}

<td>

@if($cycle->employee_comment)

<strong>Comment:</strong><br>

{{ $cycle->employee_comment }}

<br><br>

@endif


@if($cycle->employee_evidence)

<strong>Evidence:</strong><br>

{{ $cycle->employee_evidence }}

@endif


@if(
    !$cycle->employee_comment &&
    !$cycle->employee_evidence
)

-

@endif

</td>


{{-- ASSESSOR RESULT --}}

<td style="min-width:180px;">

@if($item->evaluation_method === 'per_cycle')

<select
    name="cycles[{{ $cycle->id }}][assessor_met_target]"
    class="form-control">

<option value="">
    -- Select --
</option>

<option value="1"
    {{ $cycle->assessor_met_target === true ? 'selected' : '' }}>
    Met
</option>

<option value="0"
    {{ $cycle->assessor_met_target === false ? 'selected' : '' }}>
    Not Met
</option>

</select>

@else

<div class="input-group">

<input
    type="number"
    step="0.01"
    min="0"
    name="cycles[{{ $cycle->id }}][assessor_actual_value]"
    value="{{ $cycle->assessor_actual_value }}"
    class="form-control">

@if($item->unit_of_measure)

<div class="input-group-append">

<span class="input-group-text">
    {{ $item->unit_of_measure }}
</span>

</div>

@endif

</div>

@endif

</td>


{{-- ASSESSOR COMMENT --}}

<td style="min-width:220px;">

<textarea
    name="cycles[{{ $cycle->id }}][assessor_comment]"
    class="form-control"
    rows="3"
    placeholder="Comment on this cycle">{{ $cycle->assessor_comment }}</textarea>

</td>

</tr>

@endforeach

</tbody>

</table>

</div>


{{-- ========================================================= --}}
{{-- CALCULATED COMPARISON --}}
{{-- ========================================================= --}}

<div class="card bg-light mt-3">

<div class="card-body">

<div class="row text-center">


<div class="col-md-2">

<strong>Employee Actual</strong>

<br>

{{ $item->employee_actual_value !== null
    ? number_format(
        (float) $item->employee_actual_value,
        2
    )
    : '-'
}}

</div>


<div class="col-md-2">

<strong>Employee Achievement</strong>

<br>

@if($item->employee_achievement_percentage !== null)

{{ number_format(
    (float) $item->employee_achievement_percentage,
    2
) }}%

@else

-

@endif

</div>


<div class="col-md-2">

<strong>Employee Rating</strong>

<br>

@if($item->employeeRating)

<span class="badge badge-info">

{{ $item->employeeRating->code }}

</span>

@else

-

@endif

</div>


<div class="col-md-2">

<strong>Assessor Actual</strong>

<br>

{{ $item->assessor_actual_value !== null
    ? number_format(
        (float) $item->assessor_actual_value,
        2
    )
    : '-'
}}

</div>


<div class="col-md-2">

<strong>Assessor Achievement</strong>

<br>

@if($item->assessor_achievement_percentage !== null)

{{ number_format(
    (float) $item->assessor_achievement_percentage,
    2
) }}%

@else

-

@endif

</div>


<div class="col-md-2">

<strong>Assessor Rating</strong>

<br>

@if($item->assessorRating)

<span class="badge badge-primary">

{{ $item->assessorRating->code }}

</span>

@else

-

@endif

</div>

</div>

</div>

</div>


{{-- OVERALL ITEM COMMENTS --}}

<div class="row mt-3">

<div class="col-md-6">

<label>
    <strong>Employee Overall Comment</strong>
</label>

<div class="form-control" style="height:auto; min-height:90px;">

{{ $item->employee_comment ?: '-' }}

</div>

</div>


<div class="col-md-6">

<label>
    <strong>Assessor Overall Comment</strong>
</label>

<textarea
    name="items[{{ $item->id }}][assessor_comment]"
    class="form-control"
    rows="4"
    placeholder="Enter overall assessor comment for this target">{{ $item->assessor_comment }}</textarea>

</div>

</div>


</div>

</div>

@endforeach


{{-- SECTION SUMMARY --}}

<div class="alert alert-light border">

<div class="row">

<div class="col-md-4">

<strong>Section Weight</strong>

<br>

{{ number_format(
    (float) $sectionWeight,
    2
) }}%

</div>


<div class="col-md-4">

<strong>Employee Weighted Score</strong>

<br>

{{ number_format(
    (float) $employeeSectionWeightedScore,
    4
) }}

</div>


<div class="col-md-4">

<strong>Assessor Weighted Score</strong>

<br>

{{ number_format(
    (float) $assessorSectionWeightedScore,
    4
) }}

</div>

</div>

</div>


</div>

</div>

@endif

@endforeach


{{-- ========================================================= --}}
{{-- GENERAL COMMENTS --}}
{{-- ========================================================= --}}

<div class="card mb-4">

<div class="card-header">

<strong>General Assessment Comments</strong>

</div>


<div class="card-body">

<div class="row">


<div class="col-md-6">

<label>
    <strong>Employee General Comment</strong>
</label>

<div class="form-control"
     style="height:auto; min-height:120px;">

{{ $assessment->employee_general_comment ?: '-' }}

</div>

</div>


<div class="col-md-6">

<label>
    <strong>Assessor General Comment</strong>
</label>

<textarea
    name="assessor_general_comment"
    class="form-control"
    rows="5"
    placeholder="Enter your overall assessment comment">{{ $assessment->assessor_general_comment }}</textarea>

</div>


</div>

</div>

</div>


<button
    type="submit"
    class="btn btn-primary">

Save Assessor Assessment

</button>

</form>


<form
    action="{{ route('performance-assessments.assessor.submit', $assessment->id) }}"
    method="POST"
    class="d-inline">

@csrf

<button
    type="submit"
    class="btn btn-success"
    onclick="return confirm('Are you sure you want to submit this assessment to the reviewer?');">

Submit Assessment to Reviewer

</button>

</form>


<a
    href="{{ route('performance-assessments.show', $assessment->id) }}"
    class="btn btn-secondary">

Back

</a>


</div>
</section>
</div>

@include('includes.footer')
@endsection