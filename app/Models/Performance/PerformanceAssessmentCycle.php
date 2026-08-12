<?php

namespace App\Models\Performance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceAssessmentCycle extends Model
{
    use HasFactory;

    protected $fillable = [
        'performance_assessment_item_id',

        'cycle_number',
        'cycle_label',

        'cycle_start_date',
        'cycle_end_date',
        'due_date',

        'target_value',

        'employee_actual_value',
        'employee_met_target',
        'employee_comment',
        'employee_evidence',

        'assessor_actual_value',
        'assessor_met_target',
        'assessor_comment',

        'reviewer_actual_value',
        'reviewer_met_target',
        'reviewer_comment',
    ];

    protected $casts = [
        'cycle_start_date' => 'date',
        'cycle_end_date' => 'date',
        'due_date' => 'date',

        'target_value' => 'decimal:2',

        'employee_actual_value' => 'decimal:2',
        'employee_met_target' => 'boolean',

        'assessor_actual_value' => 'decimal:2',
        'assessor_met_target' => 'boolean',

        'reviewer_actual_value' => 'decimal:2',
        'reviewer_met_target' => 'boolean',
    ];

    public function assessmentItem(): BelongsTo
    {
        return $this->belongsTo(
            PerformanceAssessmentItem::class,
            'performance_assessment_item_id'
        );
    }
}