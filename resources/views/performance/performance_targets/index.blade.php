@extends('layouts.app')

@section('content')
@include('includes.nav')
@include('includes.sidebar')

<div class="content-wrapper">
    <section class="content pt-3">
        <div class="container-fluid">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2>Performance Targets</h2>
                <a href="{{ route('performance-targets.create') }}" class="btn btn-primary">
                    Create Performance Target
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <!-- Filters -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('performance-targets.index') }}"
                           class="btn {{ empty($filter) ? 'btn-dark' : 'btn-outline-dark' }} btn-sm mr-2 mb-2">
                            All
                        </a>

                        <a href="{{ route('performance-targets.index', ['filter' => 'my_targets']) }}"
                           class="btn {{ $filter === 'my_targets' ? 'btn-primary' : 'btn-outline-primary' }} btn-sm mr-2 mb-2">
                            My Targets
                        </a>

                        <a href="{{ route('performance-targets.index', ['filter' => 'submitted']) }}"
                           class="btn {{ $filter === 'submitted' ? 'btn-info' : 'btn-outline-info' }} btn-sm mr-2 mb-2">
                            Submitted Only
                        </a>

                        <a href="{{ route('performance-targets.index', ['filter' => 'awaiting_my_approval']) }}"
                           class="btn {{ $filter === 'awaiting_my_approval' ? 'btn-warning' : 'btn-outline-warning' }} btn-sm mr-2 mb-2">
                            Awaiting My Approval
                        </a>

                        @if(auth()->user()->is_hr || auth()->user()->is_admin)
                            <a href="{{ route('performance-targets.index', ['filter' => 'awaiting_hr_review']) }}"
                               class="btn {{ $filter === 'awaiting_hr_review' ? 'btn-danger' : 'btn-outline-danger' }} btn-sm mr-2 mb-2">
                                Awaiting HR Review
                            </a>
                        @endif

                        <a href="{{ route('performance-targets.index', ['filter' => 'reviewed_by_hr']) }}"
                           class="btn {{ $filter === 'reviewed_by_hr' ? 'btn-success' : 'btn-outline-success' }} btn-sm mr-2 mb-2">
                            Reviewed by HR
                        </a>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <table id="performanceTargetsTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Period</th>
                                <th>Staff</th>
                                <th>Department</th>
                                <th>Section</th>
                                <th>Title</th>
                                <th>Assessor</th>
                                <th>HR Reviewer</th>
                                <th>Status</th>
                                <th>Submitted</th>
                                <th width="220">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($targets as $target)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $target->period?->name }}</td>
                                    <td>{{ $target->user?->fullName() }}</td>
                                    <td>{{ $target->user?->department?->name }}</td>
                                    <td>{{ $target->user?->section?->name }}</td>
                                    <td>{{ $target->title }}</td>
                                    <td>{{ $target->assessor?->fullName() ?? 'N/A' }}</td>
                                    <td>{{ $target->hrReviewer?->fullName() ?? 'N/A' }}</td>
                                    <td>
                                        @php
                                            $statusClass = match($target->status) {
                                                'not_submitted' => 'bg-secondary',
                                                'submitted' => 'bg-info',
                                                'approved_by_assessor' => 'bg-primary',
                                                'rejected_by_assessor' => 'bg-danger',
                                                'reviewed_by_hr' => 'bg-success',
                                                default => 'bg-dark',
                                            };
                                        @endphp
                                        <span class="badge {{ $statusClass }}">
                                            {{ ucwords(str_replace('_', ' ', $target->status)) }}
                                        </span>
                                    </td>
                                    <td>{{ optional($target->submitted_at)->format('d/m/Y') }}</td>
                                    <td>
                                        <a href="{{ route('performance-targets.show', $target->id) }}" class="btn btn-info btn-sm">
                                            View
                                        </a>

                                        @if(auth()->id() === $target->user_id && $target->isEditable())
                                            <a href="{{ route('performance-targets.edit', $target->id) }}" class="btn btn-primary btn-sm">
                                                Edit
                                            </a>
                                        @endif

                                        <a href="{{ route('performance-targets.print', $target->id) }}" class="btn btn-secondary btn-sm" target="_blank">
                                            Generate Form
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    @if($targets->isEmpty())
                        <p class="mb-0">No performance targets found for this filter.</p>
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
    if ($('#performanceTargetsTable').length) {
        $('#performanceTargetsTable').DataTable({
            responsive: true,
            lengthChange: true,
            autoWidth: false,
            pageLength: 10,
            ordering: true,
            searching: true,
            info: true,
            buttons: ['copy', 'csv', 'excel', 'pdf', 'print', 'colvis']
        }).buttons().container().appendTo('#performanceTargetsTable_wrapper .col-md-6:eq(0)');
    }
});
</script>
@endpush