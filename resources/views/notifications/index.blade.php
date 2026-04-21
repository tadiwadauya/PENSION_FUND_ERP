@extends('layouts.app')

@section('content')
@include('includes.nav')
@include('includes.sidebar')

<div class="content-wrapper">
    <section class="content pt-3">
        <div class="container-fluid">

            <div class="d-flex justify-content-between mb-3">
                <h2>Notifications</h2>

                <form action="{{ route('notifications.markAllAsRead') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        Mark All as Read
                    </button>
                </form>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="card">
                <div class="card-body">
                    <table id="notificationsTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Title</th>
                                <th>Message</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th width="220">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($notifications as $notification)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $notification->data['title'] ?? 'Notification' }}</td>
                                    <td>{{ $notification->data['message'] ?? '' }}</td>
                                    <td>
                                        <span class="badge {{ is_null($notification->read_at) ? 'bg-warning' : 'bg-success' }}">
                                            {{ is_null($notification->read_at) ? 'Unread' : 'Read' }}
                                        </span>
                                    </td>
                                    <td>{{ $notification->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        @if(is_null($notification->read_at))
                                            <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST" style="display:inline-block;">
                                                @csrf
                                                <button type="submit" class="btn btn-primary btn-sm">Mark as Read</button>
                                            </form>
                                        @endif

                                        <a href="{{ $notification->data['url'] ?? '#' }}" class="btn btn-info btn-sm">Open</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    @if($notifications->isEmpty())
                        <p class="mb-0">No notifications found.</p>
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
    if ($('#notificationsTable').length) {
        $('#notificationsTable').DataTable({
            responsive: true,
            lengthChange: true,
            autoWidth: false,
            pageLength: 10,
            ordering: true,
            searching: true,
            info: true,
            buttons: ['copy', 'csv', 'excel', 'pdf', 'print', 'colvis']
        }).buttons().container().appendTo('#notificationsTable_wrapper .col-md-6:eq(0)');
    }
});
</script>
@endpush