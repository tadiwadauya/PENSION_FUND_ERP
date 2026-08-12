<?php

namespace App\Models\Performance;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PerformanceAssessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'performance_target_id',
        'performance_period_id',
        'user_id',
        'assessor_id',
        'reviewer_id',
        'hr_reviewer_id',
        'title',
        'status',
        'employee_general_comment',
        'assessor_general_comment',
        'reviewer_general_comment',
        'hr_general_comment',
        'employee_submitted_at',
        'assessor_assessed_at',
        'reviewer_reviewed_at',
        'completed_at',
    ];

    protected $casts = [
        'employee_submitted_at' => 'datetime',
        'assessor_assessed_at' => 'datetime',
        'reviewer_reviewed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function performanceTarget(): BelongsTo
    {
        return $this->belongsTo(PerformanceTarget::class);
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(
            PerformancePeriod::class,
            'performance_period_id'
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assessor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assessor_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function hrReviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hr_reviewer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PerformanceAssessmentItem::class);
    }

    public function isEmployeeEditable(): bool
    {
        return in_array($this->status, [
            'not_started',
            'self_assessment_in_progress',
            'rejected_by_assessor',
        ]);
    }
}