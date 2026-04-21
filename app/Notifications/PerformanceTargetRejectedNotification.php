<?php

namespace App\Notifications;

use App\Models\Performance\PerformanceTarget;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PerformanceTargetRejectedNotification extends Notification
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
            'title' => 'Performance Target Rejected',
            'message' => 'Your performance target has been rejected by the assessor. Please review comments and resubmit.',
            'target_id' => $this->target->id,
            'url' => route('performance-targets.show', $this->target->id),
        ];
    }
}