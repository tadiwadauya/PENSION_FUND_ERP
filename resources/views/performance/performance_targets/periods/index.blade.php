@extends('layouts.app')

@section('content')
@include('includes.nav')
@include('includes.sidebar')

<div class="content-wrapper">
    <section class="content pt-3">
        <div class="container-fluid">

            <div class="d-flex justify-content-between mb-3">
                <h2>Performance Periods</h2>
                <a href="{{ route('performance-target-periods.create') }}" class="btn btn-primary">
                    Create Period
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="card">
                <div class="card-body">
                    <table id="periodsTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Name</th>
                                <th>Year</th>
                                <th>Review Type</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Review Start</th>
                                <th>Review End</th>
                                <th>Active</th>
                                <th width="180">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($periods as $period)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $period->name }}</td>
                                    <td>{{ $period->year }}</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $period->review_type)) }}</td>
                                    <td>{{ optional($period->start_date)->format('d/m/Y') }}</td>
                                    <td>{{ optional($period->end_date)->format('d/m/Y') }}</td>
                                    <td>{{ optional($period->review_start_date)->format('d/m/Y') }}</td>
                                    <td>{{ optional($period->review_end_date)->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="badge {{ $period->is_active ? 'bg-success' : 'bg-danger' }}">
                                            {{ $period->is_active ? 'Yes' : 'No' }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('performance-target-periods.show', $period->id) }}" class="btn btn-info btn-sm">Show</a>
                                        <a href="{{ route('performance-target-periods.edit', $period->id) }}" class="btn btn-primary btn-sm">Edit</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
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
        if ($('#periodsTable').length) {
            $('#periodsTable').DataTable({
                responsive: true,
                lengthChange: true,
                autoWidth: false,
                pageLength: 10,
                ordering: true,
                searching: true,
                info: true,
                buttons: ['copy', 'csv', 'excel', 'pdf', 'print', 'colvis']
            }).buttons().container().appendTo('#periodsTable_wrapper .col-md-6:eq(0)');
        }
    });
</script>
@endpush