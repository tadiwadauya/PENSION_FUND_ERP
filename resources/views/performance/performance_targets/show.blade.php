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

            <div class="card mb-3">
                <div class="card-body">
                    <p><strong>Name of Staff Member Being Assessed:</strong> {{ $target->user->fullName() }}</p>
                    <p><strong>Department:</strong> {{ $target->user->department?->name }}</p>
                    <p><strong>Section:</strong> {{ $target->user->section?->name }}</p>
                    <p><strong>Job Title:</strong> {{ $target->user->job_title }}</p>
                    <p><strong>Grade:</strong> {{ $target->user->grade }}</p>
                    <p><strong>Assessor:</strong> {{ $target->assessor?->fullName() ?? 'N/A' }}</p>
                    <p><strong>Reviewer:</strong> {{ $target->reviewer?->fullName() ?? 'N/A' }}</p>
                    <p><strong>Review Period:</strong> {{ $target->period->year }}</p>

                    <p>
                        <strong>Status:</strong>

                        @php
                            $statusClass = match($target->status) {
                                'not_submitted' => 'badge-secondary',
                                'submitted' => 'badge-info',
                                'approved_by_assessor' => 'badge-primary',
                                'rejected_by_assessor' => 'badge-danger',
                                'reviewed_by_hr' => 'badge-success',
                                default => 'badge-dark',
                            };
                        @endphp

                        <span class="badge {{ $statusClass }}">
                            {{ ucwords(str_replace('_', ' ', $target->status)) }}
                        </span>
                    </p>

                    <a href="{{ route('performance-targets.print', $target->id) }}" class="btn btn-secondary" target="_blank">
                        Generate Form
                    </a>

                    @if(auth()->id() === $target->user_id && $target->isEditable())
                        <a href="{{ route('performance-targets.edit', $target->id) }}" class="btn btn-primary">
                            Edit Targets
                        </a>
                    @endif
                </div>
            </div>

            {{-- ========================================================= --}}
            {{-- ASSESSOR VIEW / ACTION --}}
            {{-- ========================================================= --}}

            @if(auth()->id() === $target->assessor_id && $target->status === 'submitted')

                <form action="{{ route('performance-targets.approve', $target->id) }}" method="POST">
                    @csrf

                    <div class="card">
                        <div class="card-header">
                            <strong>Targets - Assessor Review</strong>
                        </div>

                        <div class="card-body table-responsive">

                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th style="width: 5%;">No</th>
                                        <th style="width: 12%;">Perspective</th>
                                        <th style="width: 18%;">Task</th>
                                        <th style="width: 18%;">How To Achieve</th>
                                        <th style="width: 17%;">Measure / Target</th>
                                        <th style="width: 10%;">Due Date</th>
                                        <th style="width: 20%;">Assessor Comment</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach($target->items as $item)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>

                                            <td>
                                                {{ $item->perspective }}
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
                                                @if($item->due_date)
                                                    {{ optional($item->due_date)->format('d/m/Y') }}
                                                @elseif($item->frequency === 'monthly' && $item->due_day)
                                                    Every month by day {{ $item->due_day }}
                                                @elseif($item->frequency === 'weekly' && $item->due_weekday)
                                                    Weekly
                                                @else
                                                    -
                                                @endif
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
                                </tbody>
                            </table>

                            <div class="form-group mt-3">
                                <label><strong>Assessor General Comment</strong></label>

                                <textarea
                                    name="assessor_general_comment"
                                    class="form-control"
                                    rows="4"
                                    placeholder="Enter general comment">{{ old('assessor_general_comment', $target->assessor_general_comment) }}</textarea>
                            </div>

                            <div class="mt-3">
                                <button type="submit" class="btn btn-success">
                                    Approve
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                <form action="{{ route('performance-targets.reject', $target->id) }}" method="POST" class="mt-3">
                    @csrf

                    <div class="card">
                        <div class="card-header">
                            <strong>Reject Performance Target</strong>
                        </div>

                        <div class="card-body">

                            {{-- Carry assessor comments into reject form too --}}
                            @foreach($target->items as $item)
                                <input
                                    type="hidden"
                                    name="item_comments[{{ $item->id }}]"
                                    value="{{ old('item_comments.' . $item->id, $item->assessor_comment) }}">
                            @endforeach

                            <div class="form-group">
                                <label><strong>Rejection Comment</strong></label>

                                <textarea
                                    name="assessor_general_comment"
                                    class="form-control"
                                    rows="4"
                                    required
                                    placeholder="Explain why the performance target is being rejected"></textarea>
                            </div>

                            <button type="submit" class="btn btn-danger">
                                Reject
                            </button>
                        </div>
                    </div>
                </form>

            {{-- ========================================================= --}}
            {{-- HR REVIEW --}}
            {{-- ========================================================= --}}

            @elseif(
                ($target->hr_reviewer_id === auth()->id() || auth()->user()->is_hr || auth()->user()->is_admin)
                && $target->status === 'approved_by_assessor'
                && !$target->assessor?->is_ceo
            )

                <form action="{{ route('performance-targets.hr-review', $target->id) }}" method="POST">
                    @csrf

                    <div class="card">
                        <div class="card-header">
                            <strong>Targets - HR Review</strong>
                        </div>

                        <div class="card-body table-responsive">

                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th style="width: 4%;">No</th>
                                        <th style="width: 11%;">Perspective</th>
                                        <th style="width: 17%;">Task</th>
                                        <th style="width: 16%;">How To Achieve</th>
                                        <th style="width: 15%;">Measure / Target</th>
                                        <th style="width: 9%;">Due Date</th>
                                        <th style="width: 14%;">Assessor Comment</th>
                                        <th style="width: 14%;">HR Comment</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach($target->items as $item)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>

                                            <td>
                                                {{ $item->perspective }}
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
                                                @if($item->due_date)
                                                    {{ optional($item->due_date)->format('d/m/Y') }}
                                                @elseif($item->frequency === 'monthly' && $item->due_day)
                                                    Every month by day {{ $item->due_day }}
                                                @elseif($item->frequency === 'weekly' && $item->due_weekday)
                                                    Weekly
                                                @else
                                                    -
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
                                </tbody>
                            </table>

                            <div class="form-group mt-3">
                                <label><strong>HR General Comment</strong></label>

                                <textarea
                                    name="hr_general_comment"
                                    class="form-control"
                                    rows="4"
                                    placeholder="Enter HR general comment">{{ old('hr_general_comment', $target->hr_general_comment) }}</textarea>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                Complete HR Review
                            </button>
                        </div>
                    </div>
                </form>

            {{-- ========================================================= --}}
            {{-- NORMAL VIEW --}}
            {{-- ========================================================= --}}

            @else

                <div class="card">
                    <div class="card-header">
                        <strong>Targets</strong>
                    </div>

                    <div class="card-body table-responsive">

                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Perspective</th>
                                    <th>Task</th>
                                    <th>How To Achieve</th>
                                    <th>Measure / Target</th>
                                    <th>Due Date</th>
                                    <th>Assessor Comment</th>
                                    <th>HR Comment</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($target->items as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>

                                        <td>
                                            {{ $item->perspective }}
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
                                            @if($item->due_date)
                                                {{ optional($item->due_date)->format('d/m/Y') }}
                                            @elseif($item->frequency === 'monthly' && $item->due_day)
                                                Every month by day {{ $item->due_day }}
                                            @elseif($item->frequency === 'weekly' && $item->due_weekday)
                                                Weekly
                                            @else
                                                -
                                            @endif
                                        </td>

                                        <td>
                                            {{ $item->assessor_comment ?: '-' }}
                                        </td>

                                        <td>
                                            {{ $item->hr_comment ?: '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        @if($target->assessor_general_comment)
                            <div class="mt-3">
                                <strong>Assessor General Comment:</strong>
                                <p>{{ $target->assessor_general_comment }}</p>
                            </div>
                        @endif

                        @if($target->hr_general_comment)
                            <div class="mt-3">
                                <strong>HR General Comment:</strong>
                                <p>{{ $target->hr_general_comment }}</p>
                            </div>
                        @endif

                    </div>
                </div>

            @endif

        </div>
    </section>
</div>

@include('includes.footer')
@endsection