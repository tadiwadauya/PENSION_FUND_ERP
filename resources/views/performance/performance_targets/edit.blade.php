@extends('layouts.app')

@section('content')
@include('includes.nav')
@include('includes.sidebar')

<div class="content-wrapper">
<section class="content pt-3">
<div class="container-fluid">

<h2>Edit Performance Target</h2>

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

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<div class="card">
<div class="card-header">
    <strong>{{ $target->title }}</strong>
</div>

<div class="card-body">

<form action="{{ route('performance-targets.update', $target->id) }}" method="POST" id="performance-target-form">
@csrf
@method('PUT')

@php
    $items = old('items', $target->items->count()
        ? $target->items->toArray()
        : [[
            'section_code' => 'SUMMARY_TASKS',
            'section_title' => 'SECTION 2 : SUMMARY OF PERFORMANCE ON TASKS',
            'is_default' => false,
            'perspective' => 'Summary of Performance on Tasks',
            'target_type' => 'one_time',
            'frequency' => 'once',
            'due_day' => '',
            'due_month' => '',
            'due_weekday' => '',
            'task' => '',
            'how_to_achieve' => '',
            'measure_target' => '',
            'per_cycle_target_value' => '',
            'period_target_value' => '',
            'unit_of_measure' => '',
            'evaluation_method' => 'per_cycle',
            'target_description' => '',
            'weight' => '',
            'due_date' => '',
        ]]
    );

    $groupedItems = collect($items)->groupBy(function ($item) {
        return $item['section_code'] ?? 'SUMMARY_TASKS';
    });

    $sectionTitles = [
        'SUMMARY_TASKS' => 'SECTION 2 : SUMMARY OF PERFORMANCE ON TASKS',
        'PEOPLE' => 'SECTION A : PEOPLE',
        'CUSTOMERS' => 'SECTION B : CUSTOMERS',
        'FINANCIAL' => 'SECTION C : FINANCIAL',
        'OPERATIONAL' => 'SECTION D : OPERATIONAL EXCELLENCE',
        'VALUES' => 'SECTION E : VALUES & BEHAVIOURS',
    ];

    $sectionWeights = $target->sections
        ->keyBy('section_code');

    $globalIndex = 0;
@endphp


{{-- ========================================================= --}}
{{-- SECTION WEIGHTS SUMMARY --}}
{{-- ========================================================= --}}

<div class="card mb-4">
    <div class="card-header bg-dark text-white">
        <strong>Performance Section Weights</strong>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered mb-0">
                <thead>
                    <tr>
                        <th>Section</th>
                        <th style="width: 150px;">Section Weight</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sectionTitles as $code => $title)
                        @php
                            $sectionWeight = $sectionWeights->get($code);
                        @endphp

                        <tr>
                            <td>{{ $title }}</td>
                            <td>
                                <strong>
                                    {{ number_format((float) ($sectionWeight?->weight ?? 0), 2) }}%
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

        <small class="text-muted">
            Section weights determine how much each appraisal section contributes to the final performance score.
        </small>
    </div>
</div>


<div id="items-wrapper">

@foreach($sectionTitles as $sectionCode => $sectionTitle)

    @php
        $sectionItems = $groupedItems->get($sectionCode, collect());
        $sectionWeight = $sectionWeights->get($sectionCode)?->weight ?? 0;
    @endphp

    @if($sectionItems->count() > 0 || $sectionCode === 'SUMMARY_TASKS')

        <div class="card mb-4 performance-section" data-section="{{ $sectionCode }}">

            <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">

                <div>
                    <strong>{{ $sectionTitle }}</strong>

                    <span class="badge badge-light ml-2">
                        Section Weight:
                        {{ number_format((float) $sectionWeight, 2) }}%
                    </span>
                </div>

                <div>
                    <strong>Task Weight Total:</strong>

                    <span class="section-weight-total badge badge-warning"
                          data-section-total="{{ $sectionCode }}">
                        0.00%
                    </span>
                </div>

            </div>

            <div class="card-body section-wrapper" data-section="{{ $sectionCode }}">

                @if($sectionCode === 'SUMMARY_TASKS')
                    <p class="text-muted">
                        Add the employee's actual job-related performance tasks here.
                        The weights of all tasks in this section must total 100%.
                    </p>
                @else
                    <p class="text-muted">
                        These are default appraisal tasks. The task description is fixed.
                        Complete the measurable target fields and allocate weights totalling 100% for this section.
                    </p>
                @endif


                <div class="section-items" data-section-items="{{ $sectionCode }}">

                @forelse($sectionItems as $item)

                    @php
                        $isDefault = !empty($item['is_default']);
                        $currentIndex = $globalIndex++;
                    @endphp

                    <div class="card border mb-3 target-item"
                         data-section-code="{{ $sectionCode }}">

                        <div class="card-header d-flex justify-content-between align-items-center">

                            <strong>
                                Target Line {{ $currentIndex + 1 }}
                            </strong>

                            @if(!$isDefault)
                                <button type="button"
                                        class="btn btn-danger btn-sm"
                                        onclick="removeItem(this)">
                                    Remove
                                </button>
                            @endif

                        </div>

                        <div class="card-body row">

                            <input type="hidden"
                                   name="items[{{ $currentIndex }}][section_code]"
                                   value="{{ $item['section_code'] ?? $sectionCode }}">

                            <input type="hidden"
                                   name="items[{{ $currentIndex }}][section_title]"
                                   value="{{ $item['section_title'] ?? $sectionTitle }}">

                            <input type="hidden"
                                   name="items[{{ $currentIndex }}][is_default]"
                                   value="{{ $isDefault ? 1 : 0 }}">


                            {{-- Perspective --}}
                            <div class="col-md-3 mb-3">
                                <label>Perspective</label>

                                <input type="text"
                                       name="items[{{ $currentIndex }}][perspective]"
                                       class="form-control"
                                       value="{{ $item['perspective'] ?? $sectionTitle }}"
                                       {{ $isDefault ? 'readonly' : '' }}>
                            </div>


                            {{-- Target Type --}}
                            <div class="col-md-3 mb-3">
                                <label>Target Type</label>

                                <select name="items[{{ $currentIndex }}][target_type]"
                                        class="form-control target-type-select"
                                        onchange="toggleTargetTypeFields(this)">

                                    <option value="one_time"
                                        {{ ($item['target_type'] ?? '') === 'one_time' ? 'selected' : '' }}>
                                        One Time
                                    </option>

                                    <option value="recurring"
                                        {{ ($item['target_type'] ?? '') === 'recurring' ? 'selected' : '' }}>
                                        Recurring
                                    </option>

                                </select>
                            </div>


                            {{-- Frequency --}}
                            <div class="col-md-3 mb-3">
                                <label>Frequency</label>

                                <select name="items[{{ $currentIndex }}][frequency]"
                                        class="form-control frequency-select"
                                        onchange="toggleFrequencyFields(this)">

                                    <option value="once"
                                        {{ ($item['frequency'] ?? '') === 'once' ? 'selected' : '' }}>
                                        Once
                                    </option>

                                    <option value="daily"
                                        {{ ($item['frequency'] ?? '') === 'daily' ? 'selected' : '' }}>
                                        Daily
                                    </option>

                                    <option value="weekly"
                                        {{ ($item['frequency'] ?? '') === 'weekly' ? 'selected' : '' }}>
                                        Weekly
                                    </option>

                                    <option value="monthly"
                                        {{ ($item['frequency'] ?? '') === 'monthly' ? 'selected' : '' }}>
                                        Monthly
                                    </option>

                                    <option value="quarterly"
                                        {{ ($item['frequency'] ?? '') === 'quarterly' ? 'selected' : '' }}>
                                        Quarterly
                                    </option>

                                    <option value="annual"
                                        {{ ($item['frequency'] ?? '') === 'annual' ? 'selected' : '' }}>
                                        Annual
                                    </option>

                                </select>
                            </div>


                            {{-- Weight --}}
                            <div class="col-md-3 mb-3">
                                <label>
                                    Weight (%)
                                    <span class="text-danger">*</span>
                                </label>

                                <input type="number"
                                       step="0.01"
                                       min="0"
                                       max="100"
                                       name="items[{{ $currentIndex }}][weight]"
                                       class="form-control item-weight"
                                       data-section="{{ $sectionCode }}"
                                       value="{{ $item['weight'] ?? '' }}"
                                       placeholder="e.g. 25"
                                       oninput="calculateSectionWeights()"
                                       required>

                                <small class="text-muted">
                                    Contribution of this target within this section.
                                </small>
                            </div>


                            {{-- Due Date --}}
                            <div class="col-md-3 mb-3 one-time-date-field">
                                <label>Due Date</label>

                                <input type="date"
                                       name="items[{{ $currentIndex }}][due_date]"
                                       class="form-control"
                                       value="{{ $item['due_date'] ?? '' }}">
                            </div>


                            {{-- Due Day --}}
                            <div class="col-md-3 mb-3 due-day-field">
                                <label>Due Day</label>

                                <input type="number"
                                       min="1"
                                       max="31"
                                       name="items[{{ $currentIndex }}][due_day]"
                                       class="form-control"
                                       value="{{ $item['due_day'] ?? '' }}"
                                       placeholder="e.g. 25">
                            </div>


                            {{-- Due Weekday --}}
                            <div class="col-md-3 mb-3 due-weekday-field">

                                <label>Due Weekday</label>

                                <select name="items[{{ $currentIndex }}][due_weekday]"
                                        class="form-control">

                                    <option value="">Select Day</option>

                                    <option value="1" {{ ($item['due_weekday'] ?? '') == 1 ? 'selected' : '' }}>Monday</option>
                                    <option value="2" {{ ($item['due_weekday'] ?? '') == 2 ? 'selected' : '' }}>Tuesday</option>
                                    <option value="3" {{ ($item['due_weekday'] ?? '') == 3 ? 'selected' : '' }}>Wednesday</option>
                                    <option value="4" {{ ($item['due_weekday'] ?? '') == 4 ? 'selected' : '' }}>Thursday</option>
                                    <option value="5" {{ ($item['due_weekday'] ?? '') == 5 ? 'selected' : '' }}>Friday</option>
                                    <option value="6" {{ ($item['due_weekday'] ?? '') == 6 ? 'selected' : '' }}>Saturday</option>
                                    <option value="7" {{ ($item['due_weekday'] ?? '') == 7 ? 'selected' : '' }}>Sunday</option>

                                </select>

                            </div>


                            {{-- Due Month --}}
                            <div class="col-md-3 mb-3 due-month-field">

                                <label>Due Month</label>

                                <input type="number"
                                       min="1"
                                       max="12"
                                       name="items[{{ $currentIndex }}][due_month]"
                                       class="form-control"
                                       value="{{ $item['due_month'] ?? '' }}"
                                       placeholder="1 - 12">

                            </div>


                            {{-- Task --}}
                            <div class="col-md-12 mb-3">

                                <label>Task</label>

                                @if($isDefault)

                                    <textarea class="form-control"
                                              readonly>{{ $item['task'] ?? '' }}</textarea>

                                    <input type="hidden"
                                           name="items[{{ $currentIndex }}][task]"
                                           value="{{ $item['task'] ?? '' }}">

                                @else

                                    <textarea name="items[{{ $currentIndex }}][task]"
                                              class="form-control"
                                              required>{{ $item['task'] ?? '' }}</textarea>

                                @endif

                            </div>


                            {{-- How To Achieve --}}
                            <div class="col-md-6 mb-3">

                                <label>How To Achieve</label>

                                <textarea name="items[{{ $currentIndex }}][how_to_achieve]"
                                          class="form-control">{{ $item['how_to_achieve'] ?? '' }}</textarea>

                            </div>


                            {{-- Measure --}}
                            <div class="col-md-6 mb-3">

                                <label>Measure / Target</label>

                                <textarea name="items[{{ $currentIndex }}][measure_target]"
                                          class="form-control"
                                          required>{{ $item['measure_target'] ?? '' }}</textarea>

                            </div>


                            {{-- Per Cycle --}}
                            <div class="col-md-4 mb-3">

                                <label>Per Cycle Target Value</label>

                                <input type="number"
                                       step="0.01"
                                       name="items[{{ $currentIndex }}][per_cycle_target_value]"
                                       class="form-control"
                                       value="{{ $item['per_cycle_target_value'] ?? '' }}"
                                       placeholder="e.g. 10">

                            </div>


                            {{-- Period Target --}}
                            <div class="col-md-4 mb-3">

                                <label>Period Target Value</label>

                                <input type="number"
                                       step="0.01"
                                       name="items[{{ $currentIndex }}][period_target_value]"
                                       class="form-control"
                                       value="{{ $item['period_target_value'] ?? '' }}"
                                       placeholder="e.g. 60">

                            </div>


                            {{-- Unit --}}
                            <div class="col-md-4 mb-3">

                                <label>Unit of Measure</label>

                                <input type="text"
                                       name="items[{{ $currentIndex }}][unit_of_measure]"
                                       class="form-control"
                                       value="{{ $item['unit_of_measure'] ?? '' }}"
                                       placeholder="%, Count, Days, Properties">

                            </div>


                            {{-- Evaluation --}}
                            <div class="col-md-4 mb-3">

                                <label>Evaluation Method</label>

                                <select name="items[{{ $currentIndex }}][evaluation_method]"
                                        class="form-control">

                                    <option value="per_cycle"
                                        {{ ($item['evaluation_method'] ?? '') === 'per_cycle' ? 'selected' : '' }}>
                                        Per Cycle
                                    </option>

                                    <option value="cumulative"
                                        {{ ($item['evaluation_method'] ?? '') === 'cumulative' ? 'selected' : '' }}>
                                        Cumulative
                                    </option>

                                    <option value="average"
                                        {{ ($item['evaluation_method'] ?? '') === 'average' ? 'selected' : '' }}>
                                        Average
                                    </option>

                                </select>

                            </div>


                            {{-- Description --}}
                            <div class="col-md-8 mb-3">

                                <label>Target Description</label>

                                <textarea name="items[{{ $currentIndex }}][target_description]"
                                          class="form-control">{{ $item['target_description'] ?? '' }}</textarea>

                            </div>

                        </div>

                    </div>

                @empty

                    @if($sectionCode !== 'SUMMARY_TASKS')
                        <p class="text-muted">
                            No default tasks found for this section.
                        </p>
                    @endif

                @endforelse

                </div>


                {{-- Section Total --}}
                <div class="alert alert-light border mt-3 mb-0">

                    <div class="d-flex justify-content-between">

                        <strong>
                            Total Task Weight for {{ $sectionTitle }}
                        </strong>

                        <strong>
                            <span class="section-total-text"
                                  data-section-total-text="{{ $sectionCode }}">
                                0.00%
                            </span>
                        </strong>

                    </div>

                    <div class="progress mt-2" style="height: 20px;">
                        <div class="progress-bar section-weight-progress"
                             data-section-progress="{{ $sectionCode }}"
                             role="progressbar"
                             style="width:0%">
                            0%
                        </div>
                    </div>

                    <small class="section-weight-message"
                           data-section-message="{{ $sectionCode }}">
                    </small>

                </div>

            </div>

        </div>

    @endif

@endforeach

</div>


{{-- ========================================================= --}}
{{-- ADD SECTION 2 TASK --}}
{{-- ========================================================= --}}

<button type="button"
        class="btn btn-secondary mb-3"
        onclick="addItem()">

    Add Section 2 Target Line

</button>


<button type="submit"
        class="btn btn-primary mb-3">

    Save Targets

</button>

</form>


@if($target->isEditable())

    <form action="{{ route('performance-targets.submit', $target->id) }}"
          method="POST"
          id="submit-performance-target-form">

        @csrf

        <button type="submit"
                class="btn btn-success">

            Submit Performance Target

        </button>

    </form>

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

let itemIndex = {{ $globalIndex }};


/* ============================================================
   ADD NEW SUMMARY TASK
============================================================ */

function addItem() {

    const sectionItems = document.querySelector(
        '[data-section-items="SUMMARY_TASKS"]'
    );

    if (!sectionItems) {
        alert('Summary of Performance on Tasks section was not found.');
        return;
    }

    const html = `
        <div class="card border mb-3 target-item"
             data-section-code="SUMMARY_TASKS">

            <div class="card-header d-flex justify-content-between align-items-center">

                <strong>Target Line ${itemIndex + 1}</strong>

                <button type="button"
                        class="btn btn-danger btn-sm"
                        onclick="removeItem(this)">
                    Remove
                </button>

            </div>

            <div class="card-body row">

                <input type="hidden"
                       name="items[${itemIndex}][section_code]"
                       value="SUMMARY_TASKS">

                <input type="hidden"
                       name="items[${itemIndex}][section_title]"
                       value="SECTION 2 : SUMMARY OF PERFORMANCE ON TASKS">

                <input type="hidden"
                       name="items[${itemIndex}][is_default]"
                       value="0">


                <div class="col-md-3 mb-3">

                    <label>Perspective</label>

                    <input type="text"
                           name="items[${itemIndex}][perspective]"
                           class="form-control"
                           value="Summary of Performance on Tasks">

                </div>


                <div class="col-md-3 mb-3">

                    <label>Target Type</label>

                    <select name="items[${itemIndex}][target_type]"
                            class="form-control target-type-select"
                            onchange="toggleTargetTypeFields(this)">

                        <option value="one_time">One Time</option>
                        <option value="recurring">Recurring</option>

                    </select>

                </div>


                <div class="col-md-3 mb-3">

                    <label>Frequency</label>

                    <select name="items[${itemIndex}][frequency]"
                            class="form-control frequency-select"
                            onchange="toggleFrequencyFields(this)">

                        <option value="once">Once</option>
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                        <option value="quarterly">Quarterly</option>
                        <option value="annual">Annual</option>

                    </select>

                </div>


                <div class="col-md-3 mb-3">

                    <label>
                        Weight (%)
                        <span class="text-danger">*</span>
                    </label>

                    <input type="number"
                           step="0.01"
                           min="0"
                           max="100"
                           name="items[${itemIndex}][weight]"
                           class="form-control item-weight"
                           data-section="SUMMARY_TASKS"
                           placeholder="e.g. 25"
                           oninput="calculateSectionWeights()"
                           required>

                </div>


                <div class="col-md-3 mb-3 one-time-date-field">

                    <label>Due Date</label>

                    <input type="date"
                           name="items[${itemIndex}][due_date]"
                           class="form-control">

                </div>


                <div class="col-md-3 mb-3 due-day-field"
                     style="display:none;">

                    <label>Due Day</label>

                    <input type="number"
                           min="1"
                           max="31"
                           name="items[${itemIndex}][due_day]"
                           class="form-control"
                           placeholder="e.g. 25">

                </div>


                <div class="col-md-3 mb-3 due-weekday-field"
                     style="display:none;">

                    <label>Due Weekday</label>

                    <select name="items[${itemIndex}][due_weekday]"
                            class="form-control">

                        <option value="">Select Day</option>
                        <option value="1">Monday</option>
                        <option value="2">Tuesday</option>
                        <option value="3">Wednesday</option>
                        <option value="4">Thursday</option>
                        <option value="5">Friday</option>
                        <option value="6">Saturday</option>
                        <option value="7">Sunday</option>

                    </select>

                </div>


                <div class="col-md-3 mb-3 due-month-field"
                     style="display:none;">

                    <label>Due Month</label>

                    <input type="number"
                           min="1"
                           max="12"
                           name="items[${itemIndex}][due_month]"
                           class="form-control"
                           placeholder="1 - 12">

                </div>


                <div class="col-md-12 mb-3">

                    <label>Task</label>

                    <textarea name="items[${itemIndex}][task]"
                              class="form-control"
                              required></textarea>

                </div>


                <div class="col-md-6 mb-3">

                    <label>How To Achieve</label>

                    <textarea name="items[${itemIndex}][how_to_achieve]"
                              class="form-control"></textarea>

                </div>


                <div class="col-md-6 mb-3">

                    <label>Measure / Target</label>

                    <textarea name="items[${itemIndex}][measure_target]"
                              class="form-control"
                              required></textarea>

                </div>


                <div class="col-md-4 mb-3">

                    <label>Per Cycle Target Value</label>

                    <input type="number"
                           step="0.01"
                           name="items[${itemIndex}][per_cycle_target_value]"
                           class="form-control">

                </div>


                <div class="col-md-4 mb-3">

                    <label>Period Target Value</label>

                    <input type="number"
                           step="0.01"
                           name="items[${itemIndex}][period_target_value]"
                           class="form-control">

                </div>


                <div class="col-md-4 mb-3">

                    <label>Unit of Measure</label>

                    <input type="text"
                           name="items[${itemIndex}][unit_of_measure]"
                           class="form-control"
                           placeholder="%, Count, Days, Properties">

                </div>


                <div class="col-md-4 mb-3">

                    <label>Evaluation Method</label>

                    <select name="items[${itemIndex}][evaluation_method]"
                            class="form-control">

                        <option value="per_cycle">Per Cycle</option>
                        <option value="cumulative">Cumulative</option>
                        <option value="average">Average</option>

                    </select>

                </div>


                <div class="col-md-8 mb-3">

                    <label>Target Description</label>

                    <textarea name="items[${itemIndex}][target_description]"
                              class="form-control"></textarea>

                </div>

            </div>

        </div>
    `;

    sectionItems.insertAdjacentHTML('beforeend', html);

    itemIndex++;

    calculateSectionWeights();

}


/* ============================================================
   REMOVE TARGET
============================================================ */

function removeItem(button) {

    const item = button.closest('.target-item');

    if (item) {
        item.remove();
    }

    calculateSectionWeights();

}


/* ============================================================
   TARGET TYPE
============================================================ */

function toggleTargetTypeFields(select) {

    const cardBody = select.closest('.card-body');

    if (!cardBody) {
        return;
    }

    const targetType = select.value;

    const dueDateField =
        cardBody.querySelector('.one-time-date-field');

    const frequencySelect =
        cardBody.querySelector('.frequency-select');


    if (targetType === 'one_time') {

        if (frequencySelect) {
            frequencySelect.value = 'once';
        }

        if (dueDateField) {
            dueDateField.style.display = 'block';
        }

        hideRecurringFields(cardBody);

    } else {

        if (dueDateField) {
            dueDateField.style.display = 'none';
        }

        toggleFrequencyFields(frequencySelect);

    }

}


/* ============================================================
   FREQUENCY
============================================================ */

function toggleFrequencyFields(select) {

    if (!select) {
        return;
    }

    const cardBody = select.closest('.card-body');

    if (!cardBody) {
        return;
    }

    const frequency = select.value;

    const targetTypeSelect =
        cardBody.querySelector('.target-type-select');

    if (!targetTypeSelect) {
        return;
    }

    const targetType =
        targetTypeSelect.value;

    hideRecurringFields(cardBody);


    if (targetType !== 'recurring') {
        return;
    }


    if (
        frequency === 'monthly' ||
        frequency === 'quarterly'
    ) {

        const dueDay =
            cardBody.querySelector('.due-day-field');

        if (dueDay) {
            dueDay.style.display = 'block';
        }

    }


    if (frequency === 'weekly') {

        const dueWeekday =
            cardBody.querySelector('.due-weekday-field');

        if (dueWeekday) {
            dueWeekday.style.display = 'block';
        }

    }


    if (frequency === 'annual') {

        const dueDay =
            cardBody.querySelector('.due-day-field');

        const dueMonth =
            cardBody.querySelector('.due-month-field');

        if (dueDay) {
            dueDay.style.display = 'block';
        }

        if (dueMonth) {
            dueMonth.style.display = 'block';
        }

    }

}


/* ============================================================
   HIDE RECURRING FIELDS
============================================================ */

function hideRecurringFields(cardBody) {

    const dueDay =
        cardBody.querySelector('.due-day-field');

    const dueWeekday =
        cardBody.querySelector('.due-weekday-field');

    const dueMonth =
        cardBody.querySelector('.due-month-field');


    if (dueDay) {
        dueDay.style.display = 'none';
    }

    if (dueWeekday) {
        dueWeekday.style.display = 'none';
    }

    if (dueMonth) {
        dueMonth.style.display = 'none';
    }

}


/* ============================================================
   CALCULATE WEIGHTS
============================================================ */

function calculateSectionWeights() {

    const sectionCodes = [
        'SUMMARY_TASKS',
        'PEOPLE',
        'CUSTOMERS',
        'FINANCIAL',
        'OPERATIONAL',
        'VALUES'
    ];


    sectionCodes.forEach(function (sectionCode) {

        const inputs =
            document.querySelectorAll(
                '.item-weight[data-section="' +
                sectionCode +
                '"]'
            );

        let total = 0;


        inputs.forEach(function (input) {

            const value =
                parseFloat(input.value);

            if (!isNaN(value)) {
                total += value;
            }

        });


        total =
            Math.round(total * 100) / 100;


        const badge =
            document.querySelector(
                '[data-section-total="' +
                sectionCode +
                '"]'
            );

        const totalText =
            document.querySelector(
                '[data-section-total-text="' +
                sectionCode +
                '"]'
            );

        const progress =
            document.querySelector(
                '[data-section-progress="' +
                sectionCode +
                '"]'
            );

        const message =
            document.querySelector(
                '[data-section-message="' +
                sectionCode +
                '"]'
            );


        if (badge) {

            badge.textContent =
                total.toFixed(2) + '%';

            badge.classList.remove(
                'badge-success',
                'badge-danger',
                'badge-warning'
            );


            if (total === 100) {

                badge.classList.add(
                    'badge-success'
                );

            } else {

                badge.classList.add(
                    'badge-danger'
                );

            }

        }


        if (totalText) {

            totalText.textContent =
                total.toFixed(2) + '%';

        }


        if (progress) {

            const width =
                Math.min(total, 100);

            progress.style.width =
                width + '%';

            progress.textContent =
                total.toFixed(2) + '%';

            progress.classList.remove(
                'bg-success',
                'bg-danger',
                'bg-warning'
            );


            if (total === 100) {

                progress.classList.add(
                    'bg-success'
                );

            } else if (total > 100) {

                progress.classList.add(
                    'bg-danger'
                );

            } else {

                progress.classList.add(
                    'bg-warning'
                );

            }

        }


        if (message) {

            if (inputs.length === 0) {

                message.textContent =
                    'No targets in this section.';

                message.className =
                    'section-weight-message text-muted';


            } else if (total === 100) {

                message.textContent =
                    'Weight allocation is complete.';

                message.className =
                    'section-weight-message text-success';


            } else if (total < 100) {

                const remaining =
                    100 - total;

                message.textContent =
                    remaining.toFixed(2) +
                    '% still needs to be allocated.';

                message.className =
                    'section-weight-message text-warning';


            } else {

                const excess =
                    total - 100;

                message.textContent =
                    'Weight exceeds 100% by ' +
                    excess.toFixed(2) +
                    '%.';

                message.className =
                    'section-weight-message text-danger';

            }

        }

    });

}


/* ============================================================
   PAGE INITIALISATION
============================================================ */

document.addEventListener(
    'DOMContentLoaded',
    function () {

        document.querySelectorAll(
            '.target-type-select'
        ).forEach(function (select) {

            toggleTargetTypeFields(select);

        });


        document.querySelectorAll(
            '.frequency-select'
        ).forEach(function (select) {

            toggleFrequencyFields(select);

        });


        calculateSectionWeights();

    }
);

</script>

@endpush