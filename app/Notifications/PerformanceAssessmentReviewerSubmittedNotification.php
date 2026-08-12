<?php

namespace App\Notifications;

use App\Models\Performance\PerformanceAssessment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PerformanceAssessmentReviewerSubmittedNotification extends Notification
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
            'title' => 'Performance Assessment Reviewed',
            'message' => $this->customMessage ?: 'The performance assessment for ' . $this->assessment->user->fullName() . ' has been reviewed and is awaiting HR final confirmation.',
            'assessment_id' => $this->assessment->id,
            'url' => route('performance-assessments.show', $this->assessment->id),
        ];
    }
}