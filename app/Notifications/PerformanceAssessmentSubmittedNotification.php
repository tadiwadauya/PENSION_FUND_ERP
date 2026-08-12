<?php

namespace App\Notifications;

use App\Models\Performance\PerformanceAssessment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PerformanceAssessmentSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(public PerformanceAssessment $assessment)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Performance Assessment Submitted',
            'message' => $this->assessment->user->fullName() . ' has submitted a performance self-assessment for your assessment.',
            'assessment_id' => $this->assessment->id,
            'url' => route('performance-assessments.assessor', $this->assessment->id),
        ];
    }
}