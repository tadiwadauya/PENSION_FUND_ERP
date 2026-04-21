<?php

namespace App\Notifications;

use App\Models\Performance\PerformanceTarget;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class PerformanceTargetSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public PerformanceTarget $target,
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
            'title' => 'Performance Target Submitted',
            'message' => $this->customMessage ?: $this->target->user->fullName() . ' has submitted performance targets.',
            'target_id' => $this->target->id,
            'url' => route('performance-targets.show', $this->target->id),
        ];
    }
}