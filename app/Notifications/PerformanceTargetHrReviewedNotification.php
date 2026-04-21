<?php

namespace App\Notifications;

use App\Models\Performance\PerformanceTarget;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PerformanceTargetHrReviewedNotification extends Notification
{
    use Queueable;

    public function __construct(public PerformanceTarget $target)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Performance Target Reviewed by HR',
            'message' => 'Your performance target has been reviewed by Human Resources.',
            'target_id' => $this->target->id,
            'url' => route('performance-targets.show', $this->target->id),
        ];
    }
}