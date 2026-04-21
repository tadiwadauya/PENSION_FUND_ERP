@extends('layouts.app')

@section('content')
@include('includes.nav')
@include('includes.sidebar')

<div class="content-wrapper">
    <section class="content pt-3">
        <div class="container-fluid">
            <h2>Edit Performance Target</h2>

            <div class="card">
                <div class="card-header">
                    <strong>{{ $target->title }}</strong>
                </div>
                <div class="card-body">
                    <form action="{{ route('performance-targets.update', $target->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div id="items-wrapper">
                            @php
                                $items = old('items', $target->items->count()
                                    ? $target->items->toArray()
                                    : [[
                                        'perspective' => '',
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
                                        'due_date' => '',
                                    ]]);
                            @endphp

                            @foreach($items as $index => $item)
                                <div class="card border mb-3 target-item">
                                    <div class="card-header">
                                        <strong>Target Line {{ $index + 1 }}</strong>
                                    </div>

                                    <div class="card-body row">
                                        <div class="col-md-3 mb-3">
                                            <label>Perspective</label>
                                            <input type="text" name="items[{{ $index }}][perspective]" class="form-control" value="{{ $item['perspective'] ?? '' }}">
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <label>Target Type</label>
                                            <select name="items[{{ $index }}][target_type]" class="form-control target-type-select" onchange="toggleTargetTypeFields(this)">
                                                <option value="one_time" {{ ($item['target_type'] ?? '') === 'one_time' ? 'selected' : '' }}>One Time</option>
                                                <option value="recurring" {{ ($item['target_type'] ?? '') === 'recurring' ? 'selected' : '' }}>Recurring</option>
                                            </select>
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <label>Frequency</label>
                                            <select name="items[{{ $index }}][frequency]" class="form-control frequency-select" onchange="toggleFrequencyFields(this)">
                                                <option value="once" {{ ($item['frequency'] ?? '') === 'once' ? 'selected' : '' }}>Once</option>
                                                <option value="daily" {{ ($item['frequency'] ?? '') === 'daily' ? 'selected' : '' }}>Daily</option>
                                                <option value="weekly" {{ ($item['frequency'] ?? '') === 'weekly' ? 'selected' : '' }}>Weekly</option>
                                                <option value="monthly" {{ ($item['frequency'] ?? '') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                                <option value="quarterly" {{ ($item['frequency'] ?? '') === 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                                                <option value="annual" {{ ($item['frequency'] ?? '') === 'annual' ? 'selected' : '' }}>Annual</option>
                                            </select>
                                        </div>

                                        <div class="col-md-3 mb-3 one-time-date-field">
                                            <label>Due Date</label>
                                            <input type="date" name="items[{{ $index }}][due_date]" class="form-control" value="{{ $item['due_date'] ?? '' }}">
                                        </div>

                                        <div class="col-md-3 mb-3 due-day-field">
                                            <label>Due Day</label>
                                            <input type="number" min="1" max="31" name="items[{{ $index }}][due_day]" class="form-control" value="{{ $item['due_day'] ?? '' }}" placeholder="e.g. 5">
                                        </div>

                                        <div class="col-md-3 mb-3 due-weekday-field">
                                            <label>Due Weekday</label>
                                            <select name="items[{{ $index }}][due_weekday]" class="form-control">
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
                                            <input type="number" min="1" max="12" name="items[{{ $index }}][due_month]" class="form-control" value="{{ $item['due_month'] ?? '' }}" placeholder="1 - 12">
                                        </div>

                                        <div class="col-md-12 mb-3">
                                            <label>Task</label>
                                            <textarea name="items[{{ $index }}][task]" class="form-control" required>{{ $item['task'] ?? '' }}</textarea>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label>How To Achieve</label>
                                            <textarea name="items[{{ $index }}][how_to_achieve]" class="form-control">{{ $item['how_to_achieve'] ?? '' }}</textarea>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label>Measure / Target</label>
                                            <textarea name="items[{{ $index }}][measure_target]" class="form-control" required>{{ $item['measure_target'] ?? '' }}</textarea>
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label>Per Cycle Target Value</label>
                                            <input type="number" step="0.01" name="items[{{ $index }}][per_cycle_target_value]" class="form-control" value="{{ $item['per_cycle_target_value'] ?? '' }}" placeholder="e.g. 95">
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label>Period Target Value</label>
                                            <input type="number" step="0.01" name="items[{{ $index }}][period_target_value]" class="form-control" value="{{ $item['period_target_value'] ?? '' }}" placeholder="e.g. 570">
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label>Unit of Measure</label>
                                            <input type="text" name="items[{{ $index }}][unit_of_measure]" class="form-control" value="{{ $item['unit_of_measure'] ?? '' }}" placeholder="%, USD, count">
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label>Evaluation Method</label>
                                            <select name="items[{{ $index }}][evaluation_method]" class="form-control">
                                                <option value="per_cycle" {{ ($item['evaluation_method'] ?? '') === 'per_cycle' ? 'selected' : '' }}>Per Cycle</option>
                                                <option value="cumulative" {{ ($item['evaluation_method'] ?? '') === 'cumulative' ? 'selected' : '' }}>Cumulative</option>
                                                <option value="average" {{ ($item['evaluation_method'] ?? '') === 'average' ? 'selected' : '' }}>Average</option>
                                            </select>
                                        </div>

                                        <div class="col-md-8 mb-3">
                                            <label>Target Description</label>
                                            <textarea name="items[{{ $index }}][target_description]" class="form-control">{{ $item['target_description'] ?? '' }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <button type="button" class="btn btn-secondary mb-3" onclick="addItem()">Add Target Line</button>
                        <button type="submit" class="btn btn-primary mb-3">Save Targets</button>
                    </form>

                    @if($target->isEditable())
                        <form action="{{ route('performance-targets.submit', $target->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success">Submit Performance Target</button>
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
let itemIndex = {{ count($items) }};

function addItem() {
    let wrapper = document.getElementById('items-wrapper');

    let html = `
        <div class="card border mb-3 target-item">
            <div class="card-header">
                <strong>Target Line ${itemIndex + 1}</strong>
            </div>
            <div class="card-body row">
                <div class="col-md-3 mb-3">
                    <label>Perspective</label>
                    <input type="text" name="items[${itemIndex}][perspective]" class="form-control">
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

                <div class="col-md-3 mb-3 one-time-date-field">
                    <label>Due Date</label>
                    <input type="date" name="items[${itemIndex}][due_date]" class="form-control">
                </div>

                <div class="col-md-3 mb-3 due-day-field" style="display:none;">
                    <label>Due Day</label>
                    <input type="number" min="1" max="31" name="items[${itemIndex}][due_day]" class="form-control" placeholder="e.g. 5">
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
                    <input type="number" step="0.01" name="items[${itemIndex}][per_cycle_target_value]" class="form-control">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Period Target Value</label>
                    <input type="number" step="0.01" name="items[${itemIndex}][period_target_value]" class="form-control">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Unit of Measure</label>
                    <input type="text" name="items[${itemIndex}][unit_of_measure]" class="form-control" placeholder="%, USD, count">
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
}

function toggleTargetTypeFields(select) {
    let cardBody = select.closest('.card-body');
    let targetType = select.value;
    let dueDateField = cardBody.querySelector('.one-time-date-field');
    let frequencySelect = cardBody.querySelector('.frequency-select');

    if (targetType === 'one_time') {
        frequencySelect.value = 'once';
        dueDateField.style.display = 'block';
        hideRecurringFields(cardBody);
    } else {
        dueDateField.style.display = 'none';
        toggleFrequencyFields(frequencySelect);
    }
}

function toggleFrequencyFields(select) {
    let cardBody = select.closest('.card-body');
    let frequency = select.value;
    let targetType = cardBody.querySelector('.target-type-select').value;

    hideRecurringFields(cardBody);

    if (targetType !== 'recurring') {
        return;
    }

    if (frequency === 'monthly' || frequency === 'quarterly') {
        cardBody.querySelector('.due-day-field').style.display = 'block';
    }

    if (frequency === 'weekly') {
        cardBody.querySelector('.due-weekday-field').style.display = 'block';
    }

    if (frequency === 'annual') {
        cardBody.querySelector('.due-day-field').style.display = 'block';
        cardBody.querySelector('.due-month-field').style.display = 'block';
    }
}

function hideRecurringFields(cardBody) {
    cardBody.querySelector('.due-day-field').style.display = 'none';
    cardBody.querySelector('.due-weekday-field').style.display = 'none';
    cardBody.querySelector('.due-month-field').style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.target-type-select').forEach(function (select) {
        toggleTargetTypeFields(select);
    });

    document.querySelectorAll('.frequency-select').forEach(function (select) {
        toggleFrequencyFields(select);
    });
});
</script>
@endpush