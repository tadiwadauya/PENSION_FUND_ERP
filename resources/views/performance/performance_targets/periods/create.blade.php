@extends('layouts.app')

@section('content')
@include('includes.nav')
@include('includes.sidebar')

<div class="content-wrapper">
    <section class="content pt-3">
        <div class="container-fluid">
            <h2>Create Performance Period</h2>

            <div class="card">
                <div class="card-body">
                    <form action="{{ route('performance-target-periods.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Name</label>
                                <input type="text" name="name" class="form-control" placeholder="JANUARY TO JUNE 2026">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Year</label>
                                <input type="text" name="year" class="form-control" placeholder="2026">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label>Review Type</label>
                                <select name="review_type" class="form-control">
                                    <option value="annual">Annual</option>
                                    <option value="bi_annual">Bi Annual</option>
                                    <option value="quarterly">Quarterly</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label>Start Date</label>
                                <input type="date" name="start_date" class="form-control">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label>End Date</label>
                                <input type="date" name="end_date" class="form-control">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label>Review Start Date</label>
                                <input type="date" name="review_start_date" class="form-control">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label>Review End Date</label>
                                <input type="date" name="review_end_date" class="form-control">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label>Active</label>
                                <select name="is_active" class="form-control">
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                            </div>
                        </div>

                        <button class="btn btn-primary">Save Period</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>

@include('includes.footer')
@endsection