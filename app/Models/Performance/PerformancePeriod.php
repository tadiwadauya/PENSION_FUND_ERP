<?php

namespace App\Models\Performance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerformancePeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'year',
        'review_type',
        'start_date',
        'end_date',
        'review_start_date',
        'review_end_date',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'review_start_date' => 'date',
        'review_end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function targets()
    {
        return $this->hasMany(PerformanceTarget::class);
    }

    public function periodLabel(): string
    {
        return "{$this->name}";
    }
}