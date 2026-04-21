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
                    <p><strong>Status:</strong> {{ str_replace('_', ' ', ucfirst($target->status)) }}</p>

                    <a href="{{ route('performance-targets.print', $target->id) }}" class="btn btn-secondary" target="_blank">Generate Form</a>

                    @if(auth()->id() === $target->user_id && $target->isEditable())
                        <a href="{{ route('performance-targets.edit', $target->id) }}" class="btn btn-primary">Edit Targets</a>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <strong>Targets</strong>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
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
                                    <td>{{ $item->perspective }}</td>
                                    <td>{{ $item->task }}</td>
                                    <td>{{ $item->how_to_achieve }}</td>
                                    <td>{{ $item->measure_target }}</td>
                                    <td>{{ optional($item->due_date)->format('d/m/Y') }}</td>
                                    <td>{{ $item->assessor_comment }}</td>
                                    <td>{{ $item->hr_comment }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            @if(auth()->id() === $target->assessor_id && $target->status === 'submitted')
                <div class="card mt-3">
                    <div class="card-header"><strong>Assessor Action</strong></div>
                    <div class="card-body">
                        <form action="{{ route('performance-targets.approve', $target->id) }}" method="POST" class="mb-4">
                            @csrf
                            @foreach($target->items as $item)
                                <div class="mb-3">
                                    <label>Comment for Item {{ $loop->iteration }}</label>
                                    <textarea name="item_comments[{{ $item->id }}]" class="form-control">{{ $item->assessor_comment }}</textarea>
                                </div>
                            @endforeach
                            <div class="mb-3">
                                <label>General Comment</label>
                                <textarea name="assessor_general_comment" class="form-control"></textarea>
                            </div>
                            <button type="submit" class="btn btn-success">Approve</button>
                        </form>

                        <form action="{{ route('performance-targets.reject', $target->id) }}" method="POST">
                            @csrf
                            @foreach($target->items as $item)
                                <div class="mb-3">
                                    <label>Comment for Item {{ $loop->iteration }}</label>
                                    <textarea name="item_comments[{{ $item->id }}]" class="form-control">{{ $item->assessor_comment }}</textarea>
                                </div>
                            @endforeach
                            <div class="mb-3">
                                <label>Rejection Comment</label>
                                <textarea name="assessor_general_comment" class="form-control" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-danger">Reject</button>
                        </form>
                    </div>
                </div>
            @endif

            @if(($target->hr_reviewer_id === auth()->id() || auth()->user()->is_hr) && $target->status === 'approved_by_assessor' && !$target->assessor?->is_ceo)
                <div class="card mt-3">
                    <div class="card-header"><strong>HR Review</strong></div>
                    <div class="card-body">
                        <form action="{{ route('performance-targets.hr-review', $target->id) }}" method="POST">
                            @csrf
                            @foreach($target->items as $item)
                                <div class="mb-3">
                                    <label>HR Comment for Item {{ $loop->iteration }}</label>
                                    <textarea name="hr_item_comments[{{ $item->id }}]" class="form-control">{{ $item->hr_comment }}</textarea>
                                </div>
                            @endforeach
                            <div class="mb-3">
                                <label>HR General Comment</label>
                                <textarea name="hr_general_comment" class="form-control"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Complete HR Review</button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </section>
</div>

@include('includes.footer')
@endsection