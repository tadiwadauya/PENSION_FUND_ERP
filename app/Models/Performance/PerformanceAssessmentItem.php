<?php

namespace App\Models\Performance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PerformanceAssessmentItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'performance_assessment_id',
        'performance_target_item_id',

        'section_code',
        'section_title',

        'task',

        'section_weight',
        'item_weight',

        'target_type',
        'frequency',

        'measure_target',

        'per_cycle_target_value',
        'period_target_value',

        'unit_of_measure',
        'evaluation_method',

        'employee_actual_value',
        'employee_achievement_percentage',
        'employee_rating_scale_id',
        'employee_rating_score',
        'employee_comment',
        'employee_evidence',

        'assessor_actual_value',
        'assessor_achievement_percentage',
        'assessor_rating_scale_id',
        'assessor_rating_score',
        'assessor_comment',

        'reviewer_actual_value',
        'reviewer_achievement_percentage',
        'reviewer_rating_scale_id',
        'reviewer_rating_score',
        'reviewer_comment',

        'employee_weighted_score',
        'assessor_weighted_score',
        'reviewer_weighted_score',
    ];

    protected $casts = [
        'section_weight' => 'decimal:2',
        'item_weight' => 'decimal:2',

        'per_cycle_target_value' => 'decimal:2',
        'period_target_value' => 'decimal:2',

        'employee_actual_value' => 'decimal:2',
        'employee_achievement_percentage' => 'decimal:2',

        'assessor_actual_value' => 'decimal:2',
        'assessor_achievement_percentage' => 'decimal:2',

        'reviewer_actual_value' => 'decimal:2',
        'reviewer_achievement_percentage' => 'decimal:2',
    ];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(
            PerformanceAssessment::class,
            'performance_assessment_id'
        );
    }

    public function targetItem(): BelongsTo
    {
        return $this->belongsTo(
            PerformanceTargetItem::class,
            'performance_target_item_id'
        );
    }

    public function cycles(): HasMany
    {
        return $this->hasMany(
            PerformanceAssessmentCycle::class
        )->orderBy('cycle_number');
    }

    public function employeeRating(): BelongsTo
    {
        return $this->belongsTo(
            PerformanceRatingScale::class,
            'employee_rating_scale_id'
        );
    }

    public function assessorRating(): BelongsTo
    {
        return $this->belongsTo(
            PerformanceRatingScale::class,
            'assessor_rating_scale_id'
        );
    }

    public function reviewerRating(): BelongsTo
    {
        return $this->belongsTo(
            PerformanceRatingScale::class,
            'reviewer_rating_scale_id'
        );
    }
}