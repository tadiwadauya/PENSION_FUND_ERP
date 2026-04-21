<?php

namespace App\Http\Controllers\Performance\PerformanceTarget;

use App\Http\Controllers\Controller;
use App\Models\Performance\PerformancePeriod;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PerformancePeriodController extends Controller
{
    public function index()
    {
        $periods = PerformancePeriod::latest()->get();
        return view('performance.performance_targets.periods.index', compact('periods'));
    }

    public function create()
    {
        return view('performance.performance_targets.periods.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'year' => ['required', 'string', 'max:20'],
            'review_type' => ['required', Rule::in(['annual', 'bi_annual', 'quarterly'])],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'review_start_date' => ['nullable', 'date'],
            'review_end_date' => ['nullable', 'date'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        PerformancePeriod::create([
            'name' => $request->name,
            'year' => $request->year,
            'review_type' => $request->review_type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'review_start_date' => $request->review_start_date,
            'review_end_date' => $request->review_end_date,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('performance-target-periods.index')
            ->with('success', 'Performance period created successfully.');
    }

    public function show(PerformancePeriod $performance_target_period)
    {
        return view('performance.performance_targets.periods.show', [
            'period' => $performance_target_period,
        ]);
    }

    public function edit(PerformancePeriod $performance_target_period)
    {
        return view('performance.performance_targets.periods.edit', [
            'period' => $performance_target_period,
        ]);
    }

    public function update(Request $request, PerformancePeriod $performance_target_period)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'year' => ['required', 'string', 'max:20'],
            'review_type' => ['required', Rule::in(['annual', 'bi_annual', 'quarterly'])],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'review_start_date' => ['nullable', 'date'],
            'review_end_date' => ['nullable', 'date'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $performance_target_period->update([
            'name' => $request->name,
            'year' => $request->year,
            'review_type' => $request->review_type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'review_start_date' => $request->review_start_date,
            'review_end_date' => $request->review_end_date,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('performance-target-periods.index')
            ->with('success', 'Performance period updated successfully.');
    }

    public function destroy(PerformancePeriod $performance_target_period)
    {
        $performance_target_period->delete();

        return redirect()->route('performance-target-periods.index')
            ->with('success', 'Performance period deleted successfully.');
    }
}