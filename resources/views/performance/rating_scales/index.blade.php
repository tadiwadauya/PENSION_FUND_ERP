@extends('layouts.app')

@section('content')
@include('includes.nav')
@include('includes.sidebar')

<div class="content-wrapper">
<section class="content pt-3">
<div class="container-fluid">

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2>Performance Rating Scales</h2>
        <p class="text-muted mb-0">
            Configure the percentage ranges used to automatically calculate performance ratings.
        </p>
    </div>

    <a href="{{ route('performance-rating-scales.create') }}" class="btn btn-primary">
        Add Rating
    </a>
</div>

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

<div class="card">
<div class="card-header">
    <strong>Rating Configuration</strong>
</div>

<div class="card-body table-responsive">

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>No</th>
            <th>Rating</th>
            <th>Score</th>
            <th>Name</th>
            <th>Minimum %</th>
            <th>Maximum %</th>
            <th>Range</th>
            <th>Description</th>
            <th>Status</th>
            <th width="160">Action</th>
        </tr>
    </thead>

    <tbody>
        @forelse($ratings as $rating)
            <tr>
                <td>{{ $loop->iteration }}</td>

                <td>
                    <strong>{{ $rating->code }}</strong>
                </td>

                <td>{{ $rating->score }}</td>

                <td>{{ $rating->name ?: '-' }}</td>

                <td>{{ number_format($rating->min_percentage, 2) }}%</td>

                <td>
                    @if($rating->max_percentage >= 999)
                        No Maximum
                    @else
                        {{ number_format($rating->max_percentage, 2) }}%
                    @endif
                </td>

                <td>
                    @if($rating->max_percentage >= 999)
                        {{ number_format($rating->min_percentage, 2) }}% and above
                    @else
                        {{ number_format($rating->min_percentage, 2) }}%
                        -
                        {{ number_format($rating->max_percentage, 2) }}%
                    @endif
                </td>

                <td>{{ $rating->description ?: '-' }}</td>

                <td>
                    @if($rating->is_active)
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-secondary">Inactive</span>
                    @endif
                </td>

                <td>
                    <a href="{{ route('performance-rating-scales.edit', $rating->id) }}" class="btn btn-primary btn-sm">
                        Edit
                    </a>

                    <form action="{{ route('performance-rating-scales.destroy', $rating->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this rating?');">
                        @csrf
                        @method('DELETE')

                        <button type="submit" class="btn btn-danger btn-sm">
                            Delete
                        </button>
                    </form>
                </td>
            </tr>

        @empty
            <tr>
                <td colspan="10" class="text-center">
                    No rating scales have been configured.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

</div>
</div>

</div>
</section>
</div>

@include('includes.footer')
@endsection