<?php

namespace App\Notifications;

use App\Models\Performance\PerformanceAssessment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PerformanceAssessmentAssessorSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public PerformanceAssessment $assessment,
        public ?string $customMessage = null,
        public bool $openReviewerPage = true
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Performance Assessment Completed by Assessor',
            'message' => $this->customMessage ?: 'The performance assessment for ' . $this->assessment->user->fullName() . ' has been completed by the assessor and submitted for review.',
            'assessment_id' => $this->assessment->id,
            'url' => $this->openReviewerPage
                ? route('performance-assessments.reviewer', $this->assessment->id)
                : route('performance-assessments.show', $this->assessment->id),
        ];
    }
}