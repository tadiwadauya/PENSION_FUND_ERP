<?php

namespace App\Models\Performance;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function period()
    {
        return $this->belongsTo(PerformancePeriod::class, 'performance_period_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assessor()
    {
        return $this->belongsTo(User::class, 'assessor_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function hrReviewer()
    {
        return $this->belongsTo(User::class, 'hr_reviewer_id');
    }

    public function items()
    {
        return $this->hasMany(PerformanceTargetItem::class)->orderBy('sort_order');
    }

    public function isEditable(): bool
    {
        return in_array($this->status, ['not_submitted', 'rejected_by_assessor']);
    }

    public function requiresHrReview(): bool
    {
        return !optional($this->assessor)->is_ceo;
    }
}