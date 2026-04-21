@extends('layouts.app')

@section('content')
@include('includes.nav')
@include('includes.sidebar')

<div class="content-wrapper">
    <section class="content pt-3">
        <div class="container-fluid">
            <h2>Create Performance Target Form</h2>

            <div class="card">
                <div class="card-body">
                    <form action="{{ route('performance-targets.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label>Performance Period</label>
                            <select name="performance_period_id" class="form-control @error('performance_period_id') is-invalid @enderror">
                                <option value="">Select Period</option>
                                @foreach($periods as $period)
                                    <option value="{{ $period->id }}">{{ $period->name }}</option>
                                @endforeach
                            </select>
                            @error('performance_period_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">Create Form</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>

@include('includes.footer')
@endsection