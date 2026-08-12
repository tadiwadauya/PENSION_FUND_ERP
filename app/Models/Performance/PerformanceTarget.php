<?php

namespace App\Models\Performance;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PerformanceTarget extends Model
{
    use HasFactory;

    protected $fillable = [
        'performance_period_id',
        'user_id',
        'assessor_id',
        'reviewer_id',
        'hr_reviewer_id',
        'title',
        'status',
        'assessor_general_comment',
        'hr_general_comment',
        'submitted_at',
        'approved_at',
        'rejected_at',
        'hr_reviewed_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'hr_reviewed_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Performance Period
    |--------------------------------------------------------------------------
    */

    public function period(): BelongsTo
    {
        return $this->belongsTo(
            PerformancePeriod::class,
            'performance_period_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Employee / Staff Member
    |--------------------------------------------------------------------------
    */

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'user_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Assessor
    |--------------------------------------------------------------------------
    */

    public function assessor(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'assessor_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Reviewer
    |--------------------------------------------------------------------------
    */

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'reviewer_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | HR Reviewer
    |--------------------------------------------------------------------------
    */

    public function hrReviewer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'hr_reviewer_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Performance Target Items
    |--------------------------------------------------------------------------
    */

    public function items(): HasMany
    {
        return $this->hasMany(
            PerformanceTargetItem::class,
            'performance_target_id'
        )->orderBy('sort_order');
    }

    /*
    |--------------------------------------------------------------------------
    | Performance Target Sections
    |--------------------------------------------------------------------------
    */

    public function sections(): HasMany
    {
        return $this->hasMany(
            PerformanceTargetSection::class,
            'performance_target_id'
        )->orderBy('sort_order');
    }

    /*
    |--------------------------------------------------------------------------
    | Performance Assessment
    |--------------------------------------------------------------------------
    |
    | One approved performance target produces one performance assessment.
    |
    */

    public function assessment(): HasOne
    {
        return $this->hasOne(
            PerformanceAssessment::class,
            'performance_target_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Editable Status
    |--------------------------------------------------------------------------
    */

    public function isEditable(): bool
    {
        return in_array($this->status, [
            'not_submitted',
            'rejected_by_assessor',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Status Label
    |--------------------------------------------------------------------------
    */

    public function statusLabel(): string
    {
        return ucwords(
            str_replace('_', ' ', $this->status)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Is Fully Approved?
    |--------------------------------------------------------------------------
    |
    | Normal staff:
    | reviewed_by_hr
    |
    | Staff reporting directly to CEO:
    | approved_by_assessor
    |
    */

    public function isFullyApproved(): bool
    {
        if ($this->status === 'reviewed_by_hr') {
            return true;
        }

        if (
            $this->status === 'approved_by_assessor' &&
            $this->assessor?->is_ceo
        ) {
            return true;
        }

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | Can Start Assessment?
    |--------------------------------------------------------------------------
    */

    public function canStartAssessment(): bool
    {
        return $this->isFullyApproved() &&
            !$this->assessment()->exists();
    }
}