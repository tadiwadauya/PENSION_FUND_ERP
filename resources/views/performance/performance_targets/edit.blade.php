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
    <div class="alert alert-success">{{ session('success') }}</div>
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
    $sectionTitles = [
        'SUMMARY_TASKS' => 'SECTION 2 : SUMMARY OF PERFORMANCE ON TASKS',
        'PEOPLE' => 'SECTION A : PEOPLE',
        'CUSTOMERS' => 'SECTION B : CUSTOMERS',
        'FINANCIAL' => 'SECTION C : FINANCIAL',
        'OPERATIONAL' => 'SECTION D : OPERATIONAL EXCELLENCE',
        'VALUES' => 'SECTION E : VALUES & BEHAVIOURS',
    ];

    $items = old('items', $target->items->toArray());

    $groupedItems = collect($items)->groupBy(function ($item) {
        return $item['section_code'] ?? 'SUMMARY_TASKS';
    });

    $sections = $target->sections->sortBy('sort_order')->values();

    $sectionByCode = $sections->keyBy('section_code');

    $globalIndex = 0;
@endphp

{{-- ========================================================= --}}
{{-- PERFORMANCE SECTION WEIGHTS --}}
{{-- ========================================================= --}}

<div class="card mb-4">
    <div class="card-header bg-dark text-white">
        <strong>Performance Section Weights</strong>
    </div>

    <div class="card-body">

        <p class="text-muted">
            Set how much each section contributes to the final performance score.
            All section weights must total exactly 100%.
        </p>

        <div class="table-responsive">
            <table class="table table-bordered mb-2">
                <thead>
                    <tr>
                        <th>Section</th>
                        <th style="width: 180px;">Section Weight (%)</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($sectionTitles as $sectionCode => $sectionTitle)

                        @php
                            $section = $sectionByCode->get($sectionCode);
                        @endphp

                        @if($section)
                            <tr>
                                <td>{{ $sectionTitle }}</td>

                                <td>
                                    <input type="hidden" name="sections[{{ $section->id }}][id]" value="{{ $section->id }}">

                                    <div class="input-group">
                                        <input type="number"
                                               step="0.01"
                                               min="0"
                                               max="100"
                                               name="sections[{{ $section->id }}][weight]"
                                               value="{{ old('sections.' . $section->id . '.weight', $section->weight) }}"
                                               class="form-control section-weight-input"
                                               oninput="calculateSectionWeightTotal()"
                                               required>

                                        <div class="input-group-append">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endif

                    @endforeach

                    <tr class="table-secondary">
                        <td><strong>Total Section Weight</strong></td>
                        <td>
                            <strong id="section-weight-total">0.00%</strong>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div id="section-weight-message" class="mt-2"></div>

    </div>
</div>


{{-- ========================================================= --}}
{{-- TARGET ITEMS --}}
{{-- ========================================================= --}}

<div id="items-wrapper">

@foreach($sectionTitles as $sectionCode => $sectionTitle)

    @php
        $sectionItems = $groupedItems->get($sectionCode, collect());
        $sectionRecord = $sectionByCode->get($sectionCode);
    @endphp

    @if($sectionItems->count() > 0 || $sectionCode === 'SUMMARY_TASKS')

        <div class="card mb-4 performance-section" data-section="{{ $sectionCode }}">

            <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">

                <div>
                    <strong>{{ $sectionTitle }}</strong>

                    @if($sectionRecord)
                        <span class="badge badge-light ml-2 section-header-weight" data-section-header-weight="{{ $sectionRecord->id }}">
                            Section Weight: {{ number_format((float) old('sections.' . $sectionRecord->id . '.weight', $sectionRecord->weight), 2) }}%
                        </span>
                    @endif
                </div>

                <div>
                    <strong>Task Weight Total:</strong>
                    <span class="badge badge-warning section-task-total" data-section-total="{{ $sectionCode }}">0.00%</span>
                </div>

            </div>

            <div class="card-body">

                @if($sectionCode === 'SUMMARY_TASKS')
                    <p class="text-muted">
                        Add the employee's actual job-related performance tasks here.
                        The weights of all Section 2 targets must total 100%.
                    </p>
                @else
                    <p class="text-muted">
                        These are default appraisal tasks. The task itself is fixed.
                        Complete the measurable target information and adjust the task weights where necessary.
                        Task weights within this section must total 100%.
                    </p>
                @endif

                <div class="section-items" data-section-items="{{ $sectionCode }}">

                @forelse($sectionItems as $item)

                    @php
                        $isDefault = !empty($item['is_default']);
                        $currentIndex = $globalIndex++;
                    @endphp

                    <div class="card border mb-3 target-item" data-section-code="{{ $sectionCode }}">

                        <div class="card-header d-flex justify-content-between align-items-center">

                            <strong>Target Line {{ $currentIndex + 1 }}</strong>

                            @if(!$isDefault)
                                <button type="button" class="btn btn-danger btn-sm" onclick="removeItem(this)">
                                    Remove
                                </button>
                            @endif
                            

                        </div>

                        <div class="card-body row">

                            <input type="hidden" name="items[{{ $currentIndex }}][section_code]" value="{{ $item['section_code'] ?? $sectionCode }}">
                            <input type="hidden" name="items[{{ $currentIndex }}][section_title]" value="{{ $item['section_title'] ?? $sectionTitle }}">
                            <input type="hidden" name="items[{{ $currentIndex }}][is_default]" value="{{ $isDefault ? 1 : 0 }}">

                            <div class="col-md-3 mb-3">
                                <label>Perspective</label>

                                <input type="text"
                                       name="items[{{ $currentIndex }}][perspective]"
                                       class="form-control"
                                       value="{{ $item['perspective'] ?? $sectionTitle }}"
                                       {{ $isDefault ? 'readonly' : '' }}>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label>Target Type</label>

                                <select name="items[{{ $currentIndex }}][target_type]" class="form-control target-type-select" onchange="toggleTargetTypeFields(this)">
                                    <option value="one_time" {{ ($item['target_type'] ?? '') === 'one_time' ? 'selected' : '' }}>One Time</option>
                                    <option value="recurring" {{ ($item['target_type'] ?? '') === 'recurring' ? 'selected' : '' }}>Recurring</option>
                                </select>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label>Frequency</label>

                                <select name="items[{{ $currentIndex }}][frequency]" class="form-control frequency-select" onchange="toggleFrequencyFields(this)">
                                    <option value="once" {{ ($item['frequency'] ?? '') === 'once' ? 'selected' : '' }}>Once</option>
                                    <option value="daily" {{ ($item['frequency'] ?? '') === 'daily' ? 'selected' : '' }}>Daily</option>
                                    <option value="weekly" {{ ($item['frequency'] ?? '') === 'weekly' ? 'selected' : '' }}>Weekly</option>
                                    <option value="monthly" {{ ($item['frequency'] ?? '') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                    <option value="quarterly" {{ ($item['frequency'] ?? '') === 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                                    <option value="annual" {{ ($item['frequency'] ?? '') === 'annual' ? 'selected' : '' }}>Annual</option>
                                </select>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label>Weight (%) <span class="text-danger">*</span></label>

                                <input type="number"
                                       step="0.01"
                                       min="0"
                                       max="100"
                                       name="items[{{ $currentIndex }}][weight]"
                                       value="{{ $item['weight'] ?? '' }}"
                                       class="form-control item-weight"
                                       data-section="{{ $sectionCode }}"
                                       oninput="calculateTaskWeights()"
                                       required>

                                <small class="text-muted">
                                    Importance of this target within this section.
                                </small>
                            </div>

                            <div class="col-md-3 mb-3 one-time-date-field">
                                <label>Due Date</label>

                                <input type="date"
                                       name="items[{{ $currentIndex }}][due_date]"
                                       class="form-control"
                                       value="{{ !empty($item['due_date']) ? \Illuminate\Support\Str::substr($item['due_date'], 0, 10) : '' }}">
                            </div>

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

                            <div class="col-md-3 mb-3 due-weekday-field">
                                <label>Due Weekday</label>

                                <select name="items[{{ $currentIndex }}][due_weekday]" class="form-control">
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

                            <div class="col-md-12 mb-3">
                                <label>Task</label>

                                @if($isDefault)
                                    <textarea class="form-control" readonly>{{ $item['task'] ?? '' }}</textarea>
                                    <input type="hidden" name="items[{{ $currentIndex }}][task]" value="{{ $item['task'] ?? '' }}">
                                @else
                                    <textarea name="items[{{ $currentIndex }}][task]" class="form-control" required>{{ $item['task'] ?? '' }}</textarea>
                                @endif
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>How To Achieve</label>

                                <textarea name="items[{{ $currentIndex }}][how_to_achieve]" class="form-control">{{ $item['how_to_achieve'] ?? '' }}</textarea>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Measure / Target</label>

                                <textarea name="items[{{ $currentIndex }}][measure_target]" class="form-control" required>{{ $item['measure_target'] ?? '' }}</textarea>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label>Per Cycle Target Value</label>

                                <input type="number"
                                       step="0.01"
                                       min="0"
                                       name="items[{{ $currentIndex }}][per_cycle_target_value]"
                                       class="form-control"
                                       value="{{ $item['per_cycle_target_value'] ?? '' }}"
                                       placeholder="e.g. 10">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label>Period Target Value</label>

                                <input type="number"
                                       step="0.01"
                                       min="0"
                                       name="items[{{ $currentIndex }}][period_target_value]"
                                       class="form-control"
                                       value="{{ $item['period_target_value'] ?? '' }}"
                                       placeholder="e.g. 60">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label>Unit of Measure</label>

                                <input type="text"
                                       name="items[{{ $currentIndex }}][unit_of_measure]"
                                       class="form-control"
                                       value="{{ $item['unit_of_measure'] ?? '' }}"
                                       placeholder="%, Count, Days, Properties">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label>Evaluation Method</label>

                                <select name="items[{{ $currentIndex }}][evaluation_method]" class="form-control">
                                    <option value="per_cycle" {{ ($item['evaluation_method'] ?? '') === 'per_cycle' ? 'selected' : '' }}>Per Cycle</option>
                                    <option value="cumulative" {{ ($item['evaluation_method'] ?? '') === 'cumulative' ? 'selected' : '' }}>Cumulative</option>
                                    <option value="average" {{ ($item['evaluation_method'] ?? '') === 'average' ? 'selected' : '' }}>Average</option>
                                </select>
                            </div>

                            <div class="col-md-8 mb-3">
                                <label>Target Description</label>

                                <textarea name="items[{{ $currentIndex }}][target_description]" class="form-control">{{ $item['target_description'] ?? '' }}</textarea>
                            </div>

                        </div>
                    </div>
                    {{-- Add specifically into the existing Section 2 --}}
<button type="button" class="btn btn-secondary mb-3" onclick="addItem()">
    Add Section 2 Target Line
</button>

                @empty
                    @if($sectionCode === 'SUMMARY_TASKS')
                        <div class="alert alert-info summary-empty-message">
                            No Section 2 targets have been added yet. Click <strong>Add Section 2 Target Line</strong> below.
                        </div>
                    @endif
                @endforelse

                </div>

                <div class="alert alert-light border mt-3 mb-0">
                    <div class="d-flex justify-content-between">
                        <strong>Total Task Weight for {{ $sectionTitle }}</strong>
                        <strong class="section-total-text" data-section-total-text="{{ $sectionCode }}">0.00%</strong>
                    </div>

                    <div class="progress mt-2" style="height:20px;">
                        <div class="progress-bar section-weight-progress" data-section-progress="{{ $sectionCode }}" style="width:0%">0%</div>
                    </div>

                    <small class="section-weight-message" data-section-message="{{ $sectionCode }}"></small>
                </div>

            </div>
        </div>

    @endif

@endforeach

</div>


{{-- Add specifically into the existing Section 2 --}}
<button type="button" class="btn btn-secondary mb-3" onclick="addItem()">
    Add Section 2 Target Line
</button>

<button type="submit" class="btn btn-primary mb-3">
    Save Targets and Weights
</button>

</form>


@if($target->isEditable())
    <form action="{{ route('performance-targets.submit', $target->id) }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-success">
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

function addItem() {
    const wrapper = document.querySelector('[data-section-items="SUMMARY_TASKS"]');

    if (!wrapper) {
        alert('SECTION 2 : SUMMARY OF PERFORMANCE ON TASKS could not be found.');
        return;
    }

    const emptyMessage = wrapper.querySelector('.summary-empty-message');

    if (emptyMessage) {
        emptyMessage.remove();
    }

    const html = `
        <div class="card border mb-3 target-item" data-section-code="SUMMARY_TASKS">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>New Section 2 Target</strong>
                <button type="button" class="btn btn-danger btn-sm" onclick="removeItem(this)">Remove</button>
            </div>

            <div class="card-body row">
                <input type="hidden" name="items[${itemIndex}][section_code]" value="SUMMARY_TASKS">
                <input type="hidden" name="items[${itemIndex}][section_title]" value="SECTION 2 : SUMMARY OF PERFORMANCE ON TASKS">
                <input type="hidden" name="items[${itemIndex}][is_default]" value="0">

                <div class="col-md-3 mb-3">
                    <label>Perspective</label>
                    <input type="text" name="items[${itemIndex}][perspective]" class="form-control" value="Summary of Performance on Tasks">
                </div>

                <div class="col-md-3 mb-3">
                    <label>Target Type</label>
                    <select name="items[${itemIndex}][target_type]" class="form-control target-type-select" onchange="toggleTargetTypeFields(this)">
                        <option value="one_time">One Time</option>
                        <option value="recurring">Recurring</option>
                    </select>
                </div>

                <div class="col-md-3 mb-3">
                    <label>Frequency</label>
                    <select name="items[${itemIndex}][frequency]" class="form-control frequency-select" onchange="toggleFrequencyFields(this)">
                        <option value="once">Once</option>
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                        <option value="quarterly">Quarterly</option>
                        <option value="annual">Annual</option>
                    </select>
                </div>

                <div class="col-md-3 mb-3">
                    <label>Weight (%) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0" max="100" name="items[${itemIndex}][weight]" class="form-control item-weight" data-section="SUMMARY_TASKS" oninput="calculateTaskWeights()" placeholder="e.g. 20" required>
                    <small class="text-muted">Importance of this target within Section 2.</small>
                </div>

                <div class="col-md-3 mb-3 one-time-date-field">
                    <label>Due Date</label>
                    <input type="date" name="items[${itemIndex}][due_date]" class="form-control">
                </div>

                <div class="col-md-3 mb-3 due-day-field" style="display:none;">
                    <label>Due Day</label>
                    <input type="number" min="1" max="31" name="items[${itemIndex}][due_day]" class="form-control" placeholder="e.g. 25">
                </div>

                <div class="col-md-3 mb-3 due-weekday-field" style="display:none;">
                    <label>Due Weekday</label>
                    <select name="items[${itemIndex}][due_weekday]" class="form-control">
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

                <div class="col-md-3 mb-3 due-month-field" style="display:none;">
                    <label>Due Month</label>
                    <input type="number" min="1" max="12" name="items[${itemIndex}][due_month]" class="form-control" placeholder="1 - 12">
                </div>

                <div class="col-md-12 mb-3">
                    <label>Task</label>
                    <textarea name="items[${itemIndex}][task]" class="form-control" required></textarea>
                </div>

                <div class="col-md-6 mb-3">
                    <label>How To Achieve</label>
                    <textarea name="items[${itemIndex}][how_to_achieve]" class="form-control"></textarea>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Measure / Target</label>
                    <textarea name="items[${itemIndex}][measure_target]" class="form-control" required></textarea>
                </div>

                <div class="col-md-4 mb-3">
                    <label>Per Cycle Target Value</label>
                    <input type="number" step="0.01" min="0" name="items[${itemIndex}][per_cycle_target_value]" class="form-control" placeholder="e.g. 10">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Period Target Value</label>
                    <input type="number" step="0.01" min="0" name="items[${itemIndex}][period_target_value]" class="form-control" placeholder="e.g. 60">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Unit of Measure</label>
                    <input type="text" name="items[${itemIndex}][unit_of_measure]" class="form-control" placeholder="%, Count, Days, Properties">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Evaluation Method</label>
                    <select name="items[${itemIndex}][evaluation_method]" class="form-control">
                        <option value="per_cycle">Per Cycle</option>
                        <option value="cumulative">Cumulative</option>
                        <option value="average">Average</option>
                    </select>
                </div>

                <div class="col-md-8 mb-3">
                    <label>Target Description</label>
                    <textarea name="items[${itemIndex}][target_description]" class="form-control"></textarea>
                </div>
            </div>
        </div>
    `;

    wrapper.insertAdjacentHTML('beforeend', html);

    itemIndex++;

    calculateTaskWeights();
}

function removeItem(button) {
    const item = button.closest('.target-item');

    if (item) {
        item.remove();
    }

    calculateTaskWeights();
}

function toggleTargetTypeFields(select) {
    if (!select) {
        return;
    }

    const cardBody = select.closest('.card-body');

    if (!cardBody) {
        return;
    }

    const dueDateField = cardBody.querySelector('.one-time-date-field');
    const frequencySelect = cardBody.querySelector('.frequency-select');

    if (select.value === 'one_time') {
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

        if (frequencySelect && frequencySelect.value === 'once') {
            frequencySelect.value = 'monthly';
        }

        toggleFrequencyFields(frequencySelect);
    }
}

function toggleFrequencyFields(select) {
    if (!select) {
        return;
    }

    const cardBody = select.closest('.card-body');

    if (!cardBody) {
        return;
    }

    const targetTypeSelect = cardBody.querySelector('.target-type-select');

    hideRecurringFields(cardBody);

    if (!targetTypeSelect || targetTypeSelect.value !== 'recurring') {
        return;
    }

    const frequency = select.value;

    if (frequency === 'weekly') {
        const field = cardBody.querySelector('.due-weekday-field');

        if (field) {
            field.style.display = 'block';
        }
    }

    if (frequency === 'monthly' || frequency === 'quarterly') {
        const field = cardBody.querySelector('.due-day-field');

        if (field) {
            field.style.display = 'block';
        }
    }

    if (frequency === 'annual') {
        const dueDay = cardBody.querySelector('.due-day-field');
        const dueMonth = cardBody.querySelector('.due-month-field');

        if (dueDay) {
            dueDay.style.display = 'block';
        }

        if (dueMonth) {
            dueMonth.style.display = 'block';
        }
    }
}

function hideRecurringFields(cardBody) {
    const dueDay = cardBody.querySelector('.due-day-field');
    const dueWeekday = cardBody.querySelector('.due-weekday-field');
    const dueMonth = cardBody.querySelector('.due-month-field');

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

function calculateSectionWeightTotal() {
    let total = 0;

    document.querySelectorAll('.section-weight-input').forEach(function(input) {
        const value = parseFloat(input.value);

        if (!isNaN(value)) {
            total += value;
        }

        const sectionId = input.name.match(/sections\[(\d+)\]/);

        if (sectionId) {
            const badge = document.querySelector('[data-section-header-weight="' + sectionId[1] + '"]');

            if (badge) {
                badge.textContent = 'Section Weight: ' + (isNaN(value) ? '0.00' : value.toFixed(2)) + '%';
            }
        }
    });

    total = Math.round(total * 100) / 100;

    const totalElement = document.getElementById('section-weight-total');
    const message = document.getElementById('section-weight-message');

    if (totalElement) {
        totalElement.textContent = total.toFixed(2) + '%';

        totalElement.classList.remove('text-success', 'text-danger', 'text-warning');

        if (Math.abs(total - 100) <= 0.01) {
            totalElement.classList.add('text-success');
        } else if (total > 100) {
            totalElement.classList.add('text-danger');
        } else {
            totalElement.classList.add('text-warning');
        }
    }

    if (message) {
        if (Math.abs(total - 100) <= 0.01) {
            message.className = 'text-success font-weight-bold';
            message.textContent = 'Section weight allocation is complete.';
        } else if (total < 100) {
            message.className = 'text-warning font-weight-bold';
            message.textContent = (100 - total).toFixed(2) + '% still needs to be allocated.';
        } else {
            message.className = 'text-danger font-weight-bold';
            message.textContent = 'Section weights exceed 100% by ' + (total - 100).toFixed(2) + '%.';
        }
    }
}

function calculateTaskWeights() {
    const sectionCodes = [
        'SUMMARY_TASKS',
        'PEOPLE',
        'CUSTOMERS',
        'FINANCIAL',
        'OPERATIONAL',
        'VALUES'
    ];

    sectionCodes.forEach(function(sectionCode) {
        const inputs = document.querySelectorAll('.item-weight[data-section="' + sectionCode + '"]');

        let total = 0;

        inputs.forEach(function(input) {
            const value = parseFloat(input.value);

            if (!isNaN(value)) {
                total += value;
            }
        });

        total = Math.round(total * 100) / 100;

        const badge = document.querySelector('[data-section-total="' + sectionCode + '"]');
        const totalText = document.querySelector('[data-section-total-text="' + sectionCode + '"]');
        const progress = document.querySelector('[data-section-progress="' + sectionCode + '"]');
        const message = document.querySelector('[data-section-message="' + sectionCode + '"]');

        if (badge) {
            badge.textContent = total.toFixed(2) + '%';
            badge.classList.remove('badge-success', 'badge-danger', 'badge-warning');

            if (Math.abs(total - 100) <= 0.01) {
                badge.classList.add('badge-success');
            } else {
                badge.classList.add('badge-danger');
            }
        }

        if (totalText) {
            totalText.textContent = total.toFixed(2) + '%';
        }

        if (progress) {
            const visualWidth = Math.min(total, 100);

            progress.style.width = visualWidth + '%';
            progress.textContent = total.toFixed(2) + '%';
            progress.classList.remove('bg-success', 'bg-danger', 'bg-warning');

            if (Math.abs(total - 100) <= 0.01) {
                progress.classList.add('bg-success');
            } else if (total > 100) {
                progress.classList.add('bg-danger');
            } else {
                progress.classList.add('bg-warning');
            }
        }

        if (message) {
            if (inputs.length === 0) {
                message.className = 'section-weight-message text-muted';
                message.textContent = sectionCode === 'SUMMARY_TASKS'
                    ? 'Add at least one job-related target to Section 2.'
                    : 'No tasks are available in this section.';
            } else if (Math.abs(total - 100) <= 0.01) {
                message.className = 'section-weight-message text-success';
                message.textContent = 'Task weight allocation is complete.';
            } else if (total < 100) {
                message.className = 'section-weight-message text-warning';
                message.textContent = (100 - total).toFixed(2) + '% still needs to be allocated.';
            } else {
                message.className = 'section-weight-message text-danger';
                message.textContent = 'Task weights exceed 100% by ' + (total - 100).toFixed(2) + '%.';
            }
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.target-type-select').forEach(function(select) {
        toggleTargetTypeFields(select);
    });

    document.querySelectorAll('.frequency-select').forEach(function(select) {
        toggleFrequencyFields(select);
    });

    calculateSectionWeightTotal();
    calculateTaskWeights();
});
</script>
@endpush