<?php

namespace App\Models\Performance;

use Illuminate\Database\Eloquent\Model;

class PerformanceRatingScale extends Model
{
    protected $fillable = [
        'code',
        'score',
        'min_percentage',
        'max_percentage',
        'name',
        'description',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'score' => 'integer',
        'min_percentage' => 'decimal:2',
        'max_percentage' => 'decimal:2',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public static function ratingForPercentage(float $percentage): ?self
    {
        return static::where('is_active', true)
            ->where('min_percentage', '<=', $percentage)
            ->where('max_percentage', '>=', $percentage)
            ->orderByDesc('score')
            ->first();
    }
}