@extends('layouts.app')

@section('content')
@include('includes.nav')
@include('includes.sidebar')

<div class="content-wrapper">
<section class="content pt-3">
<div class="container-fluid">

<h2>{{ $target->title }}</h2>

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

    $sectionRecords = $target->sections->keyBy('section_code');

    $groupedItems = $target->items->groupBy(function ($item) {
        return $item->section_code ?? 'SUMMARY_TASKS';
    });

    $statusClass = match($target->status) {
        'not_submitted' => 'badge-secondary',
        'submitted' => 'badge-info',
        'approved_by_assessor' => 'badge-primary',
        'rejected_by_assessor' => 'badge-danger',
        'reviewed_by_hr' => 'badge-success',
        default => 'badge-dark',
    };
@endphp


{{-- ========================================================= --}}
{{-- STAFF / TARGET INFORMATION --}}
{{-- ========================================================= --}}

<div class="card mb-3">
<div class="card-body">

<p>
    <strong>Name of Staff Member Being Assessed:</strong>
    {{ $target->user->fullName() }}
</p>

<p>
    <strong>Department:</strong>
    {{ $target->user->department?->name ?? 'N/A' }}
</p>

<p>
    <strong>Section:</strong>
    {{ $target->user->section?->name ?? 'N/A' }}
</p>

<p>
    <strong>Job Title:</strong>
    {{ $target->user->job_title }}
</p>

<p>
    <strong>Grade:</strong>
    {{ $target->user->grade }}
</p>

<p>
    <strong>Assessor:</strong>
    {{ $target->assessor?->fullName() ?? 'N/A' }}
</p>

<p>
    <strong>Reviewer:</strong>
    {{ $target->reviewer?->fullName() ?? 'N/A' }}
</p>

<p>
    <strong>Review Period:</strong>
    {{ $target->period->name ?? $target->period->year }}
</p>

<p>
    <strong>Status:</strong>

    <span class="badge {{ $statusClass }}">
        {{ ucwords(str_replace('_', ' ', $target->status)) }}
    </span>
</p>

<a href="{{ route('performance-targets.print', $target->id) }}" class="btn btn-secondary" target="_blank">
    Generate Form
</a>
@if(
    auth()->id() === $target->user_id &&
    (
        $target->status === 'reviewed_by_hr' ||
        ($target->status === 'approved_by_assessor' && $target->assessor?->is_ceo)
    )
)

    @if($target->assessment)
        <a href="{{ route('performance-assessments.show', $target->assessment->id) }}" class="btn btn-success">
            Open Performance Assessment
        </a>
    @else
        <form action="{{ route('performance-assessments.start', $target->id) }}" method="POST" class="d-inline">
            @csrf

            <button type="submit" class="btn btn-success">
                Start Performance Assessment
            </button>
        </form>
    @endif

@endif

@if(auth()->id() === $target->user_id && $target->isEditable())
    <a href="{{ route('performance-targets.edit', $target->id) }}" class="btn btn-primary">
        Edit Targets
    </a>
@endif

</div>
</div>


{{-- ========================================================= --}}
{{-- SECTION WEIGHTS --}}
{{-- ========================================================= --}}

<div class="card mb-4">

<div class="card-header bg-dark text-white">
    <strong>Performance Section Weights</strong>
</div>

<div class="card-body">

<div class="table-responsive">

<table class="table table-bordered table-striped mb-0">

<thead>
<tr>
    <th>Section</th>
    <th style="width:180px;">Section Weight</th>
</tr>
</thead>

<tbody>

@foreach($sectionTitles as $sectionCode => $sectionTitle)

    @php
        $section = $sectionRecords->get($sectionCode);
    @endphp

    <tr>
        <td>
            {{ $sectionTitle }}
        </td>

        <td>
            <strong>
                {{ number_format((float) ($section?->weight ?? 0), 2) }}%
            </strong>
        </td>
    </tr>

@endforeach

<tr class="table-secondary">
    <td>
        <strong>Total Section Weight</strong>
    </td>

    <td>
        <strong>
            {{ number_format((float) $target->sections->sum('weight'), 2) }}%
        </strong>
    </td>
</tr>

</tbody>

</table>

</div>

</div>
</div>


{{-- ========================================================= --}}
{{-- ASSESSOR REVIEW --}}
{{-- ========================================================= --}}

@if(auth()->id() === $target->assessor_id && $target->status === 'submitted')

<form action="{{ route('performance-targets.approve', $target->id) }}" method="POST">
@csrf

@foreach($sectionTitles as $sectionCode => $sectionTitle)

    @php
        $sectionItems = $groupedItems->get($sectionCode, collect());
        $sectionRecord = $sectionRecords->get($sectionCode);
    @endphp

    @if($sectionItems->count())

        <div class="card mb-4">

        <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">

            <div>
                <strong>{{ $sectionTitle }}</strong>
            </div>

            <div>
                <span class="badge badge-light">
                    Section Weight:
                    {{ number_format((float) ($sectionRecord?->weight ?? 0), 2) }}%
                </span>
            </div>

        </div>

        <div class="card-body table-responsive">

        <table class="table table-bordered table-striped">

        <thead>
        <tr>
            <th style="width:4%;">No</th>
            <th style="width:8%;">Weight</th>
            <th style="width:15%;">Task</th>
            <th style="width:17%;">How To Achieve</th>
            <th style="width:18%;">Measure / Target</th>
            <th style="width:10%;">Target Value</th>
            <th style="width:10%;">Due</th>
            <th style="width:18%;">Assessor Comment</th>
        </tr>
        </thead>

        <tbody>

        @foreach($sectionItems as $item)

            <tr>

                <td>
                    {{ $loop->iteration }}
                </td>

                <td>
                    <strong>
                        {{ number_format((float) $item->weight, 2) }}%
                    </strong>
                </td>

                <td>
                    {{ $item->task }}
                </td>

                <td>
                    {{ $item->how_to_achieve ?: '-' }}
                </td>

                <td>
                    {{ $item->measure_target }}
                </td>

                <td>
                    @if($item->per_cycle_target_value !== null)
                        <strong>Per Cycle:</strong>
                        {{ number_format((float) $item->per_cycle_target_value, 2) }}
                        {{ $item->unit_of_measure }}
                        <br>
                    @endif

                    @if($item->period_target_value !== null)
                        <strong>Period:</strong>
                        {{ number_format((float) $item->period_target_value, 2) }}
                        {{ $item->unit_of_measure }}
                    @endif
                </td>

                <td>
                    {{ $item->deadlineLabel() }}
                </td>

                <td>
                    <textarea
                        name="item_comments[{{ $item->id }}]"
                        class="form-control"
                        rows="4"
                        placeholder="Enter assessor comment">{{ old('item_comments.' . $item->id, $item->assessor_comment) }}</textarea>
                </td>

            </tr>

        @endforeach

        <tr class="table-secondary">
            <td colspan="1"></td>

            <td>
                <strong>
                    {{ number_format((float) $sectionItems->sum('weight'), 2) }}%
                </strong>
            </td>

            <td colspan="6">
                <strong>Total Task Weight for {{ $sectionTitle }}</strong>
            </td>
        </tr>

        </tbody>

        </table>

        </div>
        </div>

    @endif

@endforeach


<div class="card mb-3">

<div class="card-header">
    <strong>Assessor General Comment</strong>
</div>

<div class="card-body">

<textarea
    name="assessor_general_comment"
    class="form-control"
    rows="4"
    placeholder="Enter general assessor comment">{{ old('assessor_general_comment', $target->assessor_general_comment) }}</textarea>

</div>

</div>

<button type="submit" class="btn btn-success">
    Approve Performance Target
</button>

</form>


<form action="{{ route('performance-targets.reject', $target->id) }}" method="POST" class="mt-3">
@csrf

<div class="card">

<div class="card-header">
    <strong>Reject Performance Target</strong>
</div>

<div class="card-body">

<div class="form-group">

<label>
    <strong>Rejection Comment</strong>
</label>

<textarea
    name="assessor_general_comment"
    class="form-control"
    rows="4"
    required
    placeholder="Explain why the targets are being rejected"></textarea>

</div>

<button type="submit" class="btn btn-danger">
    Reject Performance Target
</button>

</div>

</div>

</form>


{{-- ========================================================= --}}
{{-- HR REVIEW --}}
{{-- ========================================================= --}}

@elseif(
    (
        $target->hr_reviewer_id === auth()->id()
        || auth()->user()->is_hr
        || auth()->user()->is_admin
    )
    && $target->status === 'approved_by_assessor'
    && !$target->assessor?->is_ceo
)

<form action="{{ route('performance-targets.hr-review', $target->id) }}" method="POST">
@csrf

@foreach($sectionTitles as $sectionCode => $sectionTitle)

    @php
        $sectionItems = $groupedItems->get($sectionCode, collect());
        $sectionRecord = $sectionRecords->get($sectionCode);
    @endphp

    @if($sectionItems->count())

        <div class="card mb-4">

        <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">

            <strong>
                {{ $sectionTitle }}
            </strong>

            <span class="badge badge-light">
                Section Weight:
                {{ number_format((float) ($sectionRecord?->weight ?? 0), 2) }}%
            </span>

        </div>

        <div class="card-body table-responsive">

        <table class="table table-bordered table-striped">

        <thead>
        <tr>
            <th>No</th>
            <th>Weight</th>
            <th>Task</th>
            <th>How To Achieve</th>
            <th>Measure / Target</th>
            <th>Target Value</th>
            <th>Assessor Comment</th>
            <th>HR Comment</th>
        </tr>
        </thead>

        <tbody>

        @foreach($sectionItems as $item)

            <tr>

                <td>
                    {{ $loop->iteration }}
                </td>

                <td>
                    <strong>
                        {{ number_format((float) $item->weight, 2) }}%
                    </strong>
                </td>

                <td>
                    {{ $item->task }}
                </td>

                <td>
                    {{ $item->how_to_achieve ?: '-' }}
                </td>

                <td>
                    {{ $item->measure_target }}
                </td>

                <td>
                    @if($item->per_cycle_target_value !== null)
                        <strong>Per Cycle:</strong>
                        {{ number_format((float) $item->per_cycle_target_value, 2) }}
                        {{ $item->unit_of_measure }}
                        <br>
                    @endif

                    @if($item->period_target_value !== null)
                        <strong>Period:</strong>
                        {{ number_format((float) $item->period_target_value, 2) }}
                        {{ $item->unit_of_measure }}
                    @endif
                </td>

                <td>
                    {{ $item->assessor_comment ?: '-' }}
                </td>

                <td>
                    <textarea
                        name="hr_item_comments[{{ $item->id }}]"
                        class="form-control"
                        rows="4"
                        placeholder="Enter HR comment">{{ old('hr_item_comments.' . $item->id, $item->hr_comment) }}</textarea>
                </td>

            </tr>

        @endforeach

        <tr class="table-secondary">

            <td></td>

            <td>
                <strong>
                    {{ number_format((float) $sectionItems->sum('weight'), 2) }}%
                </strong>
            </td>

            <td colspan="6">
                <strong>
                    Total Task Weight for {{ $sectionTitle }}
                </strong>
            </td>

        </tr>

        </tbody>

        </table>

        </div>
        </div>

    @endif

@endforeach


<div class="card mb-3">

<div class="card-header">
    <strong>HR General Comment</strong>
</div>

<div class="card-body">

<textarea
    name="hr_general_comment"
    class="form-control"
    rows="4">{{ old('hr_general_comment', $target->hr_general_comment) }}</textarea>

</div>

</div>

<button type="submit" class="btn btn-primary">
    Complete HR Review
</button>

</form>


{{-- ========================================================= --}}
{{-- NORMAL VIEW --}}
{{-- ========================================================= --}}

@else

@foreach($sectionTitles as $sectionCode => $sectionTitle)

    @php
        $sectionItems = $groupedItems->get($sectionCode, collect());
        $sectionRecord = $sectionRecords->get($sectionCode);
    @endphp

    @if($sectionItems->count())

        <div class="card mb-4">

        <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">

            <strong>
                {{ $sectionTitle }}
            </strong>

            <span class="badge badge-light">
                Section Weight:
                {{ number_format((float) ($sectionRecord?->weight ?? 0), 2) }}%
            </span>

        </div>

        <div class="card-body table-responsive">

        <table class="table table-bordered table-striped">

        <thead>
        <tr>
            <th>No</th>
            <th>Weight</th>
            <th>Task</th>
            <th>How To Achieve</th>
            <th>Measure / Target</th>
            <th>Per Cycle</th>
            <th>Period Target</th>
            <th>Unit</th>
            <th>Evaluation</th>
            <th>Due</th>
            <th>Assessor Comment</th>
            <th>HR Comment</th>
        </tr>
        </thead>

        <tbody>

        @foreach($sectionItems as $item)

            <tr>

                <td>
                    {{ $loop->iteration }}
                </td>

                <td>
                    <strong>
                        {{ number_format((float) $item->weight, 2) }}%
                    </strong>
                </td>

                <td>
                    {{ $item->task }}
                </td>

                <td>
                    {{ $item->how_to_achieve ?: '-' }}
                </td>

                <td>
                    {{ $item->measure_target }}
                </td>

                <td>
                    {{ $item->per_cycle_target_value !== null
                        ? number_format((float) $item->per_cycle_target_value, 2)
                        : '-'
                    }}
                </td>

                <td>
                    {{ $item->period_target_value !== null
                        ? number_format((float) $item->period_target_value, 2)
                        : '-'
                    }}
                </td>

                <td>
                    {{ $item->unit_of_measure ?: '-' }}
                </td>

                <td>
                    {{ ucwords(str_replace('_', ' ', $item->evaluation_method)) }}
                </td>

                <td>
                    {{ $item->deadlineLabel() }}
                </td>

                <td>
                    {{ $item->assessor_comment ?: '-' }}
                </td>

                <td>
                    {{ $item->hr_comment ?: '-' }}
                </td>

            </tr>

        @endforeach


        <tr class="table-secondary">

            <td></td>

            <td>
                <strong>
                    {{ number_format((float) $sectionItems->sum('weight'), 2) }}%
                </strong>
            </td>

            <td colspan="10">
                <strong>
                    Total Task Weight for {{ $sectionTitle }}
                </strong>
            </td>

        </tr>

        </tbody>

        </table>

        </div>

        </div>

    @endif

@endforeach


@if($target->assessor_general_comment)

    <div class="card mb-3">

    <div class="card-header">
        <strong>Assessor General Comment</strong>
    </div>

    <div class="card-body">
        {{ $target->assessor_general_comment }}
    </div>

    </div>

@endif


@if($target->hr_general_comment)

    <div class="card mb-3">

    <div class="card-header">
        <strong>HR General Comment</strong>
    </div>

    <div class="card-body">
        {{ $target->hr_general_comment }}
    </div>

    </div>

@endif

@endif

</div>
</section>
</div>

@include('includes.footer')
@endsection