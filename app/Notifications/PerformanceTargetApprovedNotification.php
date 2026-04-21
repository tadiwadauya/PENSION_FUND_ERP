<?php

namespace App\Notifications;

use App\Models\Performance\PerformanceTarget;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PerformanceTargetApprovedNotification extends Notification
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
            'title' => 'Performance Target Approved',
            'message' => 'Your performance target has been approved by the assessor.',
            'target_id' => $this->target->id,
            'url' => route('performance-targets.show', $this->target->id),
        ];
    }
}