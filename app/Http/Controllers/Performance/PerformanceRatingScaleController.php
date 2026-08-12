<?php

namespace App\Http\Controllers\Performance;

use App\Http\Controllers\Controller;
use App\Models\Performance\PerformanceRatingScale;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PerformanceRatingScaleController extends Controller
{
    public function index()
    {
        $ratings = PerformanceRatingScale::orderByDesc('score')->get();

        return view('performance.rating_scales.index', compact('ratings'));
    }

    public function create()
    {
        return view('performance.rating_scales.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:performance_rating_scales,code'],
            'score' => ['required', 'integer', 'min:1', 'max:100', 'unique:performance_rating_scales,score'],
            'min_percentage' => ['required', 'numeric', 'min:0'],
            'max_percentage' => ['required', 'numeric', 'gte:min_percentage'],
            'name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($this->rangeOverlaps(
            (float) $validated['min_percentage'],
            (float) $validated['max_percentage']
        )) {
            return back()
                ->withInput()
                ->withErrors([
                    'min_percentage' => 'This percentage range overlaps with an existing rating range.',
                ]);
        }

        PerformanceRatingScale::create([
            'code' => strtoupper($validated['code']),
            'score' => $validated['score'],
            'min_percentage' => $validated['min_percentage'],
            'max_percentage' => $validated['max_percentage'],
            'name' => $validated['name'] ?? null,
            'description' => $validated['description'] ?? null,
            'sort_order' => $validated['sort_order'] ?? $validated['score'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('performance-rating-scales.index')
            ->with('success', 'Performance rating created successfully.');
    }

    public function show(PerformanceRatingScale $performance_rating_scale)
    {
        return view('performance.rating_scales.show', [
            'rating' => $performance_rating_scale,
        ]);
    }

    public function edit(PerformanceRatingScale $performance_rating_scale)
    {
        return view('performance.rating_scales.edit', [
            'rating' => $performance_rating_scale,
        ]);
    }

    public function update(Request $request, PerformanceRatingScale $performance_rating_scale)
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('performance_rating_scales', 'code')->ignore($performance_rating_scale->id),
            ],

            'score' => [
                'required',
                'integer',
                'min:1',
                'max:100',
                Rule::unique('performance_rating_scales', 'score')->ignore($performance_rating_scale->id),
            ],

            'min_percentage' => ['required', 'numeric', 'min:0'],
            'max_percentage' => ['required', 'numeric', 'gte:min_percentage'],
            'name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($this->rangeOverlaps(
            (float) $validated['min_percentage'],
            (float) $validated['max_percentage'],
            $performance_rating_scale->id
        )) {
            return back()
                ->withInput()
                ->withErrors([
                    'min_percentage' => 'This percentage range overlaps with another rating range.',
                ]);
        }

        $performance_rating_scale->update([
            'code' => strtoupper($validated['code']),
            'score' => $validated['score'],
            'min_percentage' => $validated['min_percentage'],
            'max_percentage' => $validated['max_percentage'],
            'name' => $validated['name'] ?? null,
            'description' => $validated['description'] ?? null,
            'sort_order' => $validated['sort_order'] ?? $validated['score'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('performance-rating-scales.index')
            ->with('success', 'Performance rating updated successfully.');
    }

    public function destroy(PerformanceRatingScale $performance_rating_scale)
    {
        $performance_rating_scale->delete();

        return redirect()->route('performance-rating-scales.index')
            ->with('success', 'Performance rating deleted successfully.');
    }

    private function rangeOverlaps(float $min, float $max, ?int $ignoreId = null): bool
    {
        $query = PerformanceRatingScale::where(function ($q) use ($min, $max) {
            $q->whereBetween('min_percentage', [$min, $max])
                ->orWhereBetween('max_percentage', [$min, $max])
                ->orWhere(function ($q) use ($min, $max) {
                    $q->where('min_percentage', '<=', $min)
                        ->where('max_percentage', '>=', $max);
                });
        });

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }
}