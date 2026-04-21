@extends('layouts.app')

@section('content')
@include('includes.nav')
@include('includes.sidebar')

<div class="content-wrapper">
    <section class="content pt-3">
        <div class="container-fluid">
            <h2>Edit Performance Period</h2>

            <div class="card">
                <div class="card-body">
                    <form action="{{ route('performance-target-periods.update', $period->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Name</label>
                                <input type="text" name="name" class="form-control" value="{{ $period->name }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Year</label>
                                <input type="text" name="year" class="form-control" value="{{ $period->year }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label>Review Type</label>
                                <select name="review_type" class="form-control">
                                    <option value="annual" {{ $period->review_type === 'annual' ? 'selected' : '' }}>Annual</option>
                                    <option value="bi_annual" {{ $period->review_type === 'bi_annual' ? 'selected' : '' }}>Bi Annual</option>
                                    <option value="quarterly" {{ $period->review_type === 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label>Start Date</label>
                                <input type="date" name="start_date" class="form-control" value="{{ $period->start_date->format('Y-m-d') }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label>End Date</label>
                                <input type="date" name="end_date" class="form-control" value="{{ $period->end_date->format('Y-m-d') }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label>Review Start Date</label>
                                <input type="date" name="review_start_date" class="form-control" value="{{ optional($period->review_start_date)->format('Y-m-d') }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label>Review End Date</label>
                                <input type="date" name="review_end_date" class="form-control" value="{{ optional($period->review_end_date)->format('Y-m-d') }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label>Active</label>
                                <select name="is_active" class="form-control">
                                    <option value="1" {{ $period->is_active ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ !$period->is_active ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                        </div>

                        <button class="btn btn-primary">Update Period</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>

@include('includes.footer')
@endsection