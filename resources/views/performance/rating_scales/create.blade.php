@extends('layouts.app')

@section('content')
@include('includes.nav')
@include('includes.sidebar')

<div class="content-wrapper">
<section class="content pt-3">
<div class="container-fluid">

<h2>Create Performance Rating</h2>

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
<div class="card-body">

<form action="{{ route('performance-rating-scales.store') }}" method="POST">
@csrf

<div class="row">

    <div class="col-md-3 mb-3">
        <label>Rating Code</label>

        <input type="text"
               name="code"
               value="{{ old('code') }}"
               class="form-control"
               placeholder="e.g. A1"
               required>
    </div>

    <div class="col-md-3 mb-3">
        <label>Score</label>

        <input type="number"
               name="score"
               value="{{ old('score') }}"
               class="form-control"
               placeholder="e.g. 6"
               required>
    </div>

    <div class="col-md-6 mb-3">
        <label>Rating Name</label>

        <input type="text"
               name="name"
               value="{{ old('name') }}"
               class="form-control"
               placeholder="e.g. Outstanding Performance">
    </div>

    <div class="col-md-4 mb-3">
        <label>Minimum Percentage</label>

        <input type="number"
               name="min_percentage"
               value="{{ old('min_percentage') }}"
               step="0.01"
               min="0"
               class="form-control"
               placeholder="e.g. 120"
               required>
    </div>

    <div class="col-md-4 mb-3">
        <label>Maximum Percentage</label>

        <input type="number"
               name="max_percentage"
               value="{{ old('max_percentage') }}"
               step="0.01"
               min="0"
               class="form-control"
               placeholder="e.g. 999"
               required>
    </div>

    <div class="col-md-4 mb-3">
        <label>Sort Order</label>

        <input type="number"
               name="sort_order"
               value="{{ old('sort_order', 1) }}"
               min="1"
               class="form-control">
    </div>

    <div class="col-md-12 mb-3">
        <label>Description</label>

        <textarea name="description"
                  class="form-control"
                  rows="3"
                  placeholder="Describe what this rating means">{{ old('description') }}</textarea>
    </div>

    <div class="col-md-12 mb-3">
        <div class="form-check">
            <input type="hidden" name="is_active" value="0">

            <input type="checkbox"
                   name="is_active"
                   value="1"
                   class="form-check-input"
                   id="is_active"
                   {{ old('is_active', true) ? 'checked' : '' }}>

            <label class="form-check-label" for="is_active">
                Active
            </label>
        </div>
    </div>

</div>

<button type="submit" class="btn btn-primary">
    Save Rating
</button>

<a href="{{ route('performance-rating-scales.index') }}" class="btn btn-secondary">
    Cancel
</a>

</form>

</div>
</div>

</div>
</section>
</div>

@include('includes.footer')
@endsection