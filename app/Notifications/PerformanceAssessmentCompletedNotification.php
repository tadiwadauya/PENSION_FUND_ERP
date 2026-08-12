<?php

namespace App\Notifications;

use App\Models\Performance\PerformanceAssessment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PerformanceAssessmentCompletedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public PerformanceAssessment $assessment,
        public ?string $customMessage = null
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Performance Appraisal Completed',
            'message' => $this->customMessage ?: 'Your performance appraisal for ' . ($this->assessment->period?->name ?? 'the assessment period') . ' has been completed.',
            'assessment_id' => $this->assessment->id,
            'url' => route('performance-assessments.show', $this->assessment->id),
        ];
    }
}