<?php

namespace App\Models\Performance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerformanceTargetItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'performance_target_id',
        'sort_order',
        'perspective',
        'target_type',
        'frequency',
        'due_day',
        'due_month',
        'due_weekday',
        'task',
        'how_to_achieve',
        'measure_target',
        'per_cycle_target_value',
        'period_target_value',
        'unit_of_measure',
        'evaluation_method',
        'target_description',
        'due_date',
        'assessor_comment',
        'hr_comment',
        'weight',
    ];

    protected $casts = [
        'due_date' => 'date',
        'per_cycle_target_value' => 'decimal:2',
        'period_target_value' => 'decimal:2',
        'weight' => 'decimal:2',
    ];

    public function performanceTarget()
    {
        return $this->belongsTo(PerformanceTarget::class);
    }

    public function recurrenceLabel(): string
    {
        if ($this->target_type === 'one_time') {
            return 'One Time';
        }

        return ucfirst($this->frequency);
    }

    public function deadlineLabel(): string
    {
        if ($this->target_type === 'one_time') {
            return $this->due_date ? $this->due_date->format('d/m/Y') : 'N/A';
        }

        if ($this->frequency === 'monthly' && $this->due_day) {
            return $this->due_day . $this->daySuffix($this->due_day) . ' of every month';
        }

        if ($this->frequency === 'weekly' && $this->due_weekday) {
            return $this->weekdayName($this->due_weekday);
        }

        if ($this->frequency === 'quarterly') {
            return $this->due_day ? 'By day ' . $this->due_day . ' of each quarter' : 'Quarterly';
        }

        if ($this->frequency === 'annual') {
            if ($this->due_day && $this->due_month) {
                return $this->due_day . $this->daySuffix($this->due_day) . ' of month ' . $this->due_month;
            }
            return 'Annual';
        }

        return ucfirst($this->frequency);
    }

    protected function daySuffix(int $day): string
    {
        if (in_array($day % 100, [11, 12, 13])) {
            return 'th';
        }

        return match ($day % 10) {
            1 => 'st',
            2 => 'nd',
            3 => 'rd',
            default => 'th',
        };
    }

    protected function weekdayName(int $day): string
    {
        return match ($day) {
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
            7 => 'Sunday',
            default => 'Unknown',
        };
    }
}