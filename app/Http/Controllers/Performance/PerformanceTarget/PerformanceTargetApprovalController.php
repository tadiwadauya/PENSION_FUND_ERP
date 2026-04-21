<?php

namespace App\Http\Controllers\Performance\PerformanceTarget;

use App\Http\Controllers\Controller;
use App\Models\Performance\PerformanceTarget;
use App\Notifications\PerformanceTargetApprovedNotification;
use App\Notifications\PerformanceTargetHrReviewedNotification;
use App\Notifications\PerformanceTargetRejectedNotification;
use App\Notifications\PerformanceTargetSubmittedNotification;
use Illuminate\Http\Request;

class PerformanceTargetApprovalController extends Controller
{
    public function submit(PerformanceTarget $performance_target)
    {
        if (auth()->id() !== $performance_target->user_id) {
            abort(403);
        }

        if ($performance_target->items()->count() === 0) {
            return back()->withErrors(['items' => 'Please add at least one target item before submitting.']);
        }

        $performance_target->update([
            'status' => 'submitted',
            'submitted_at' => now(),
            'assessor_general_comment' => null,
        ]);

        if ($performance_target->assessor) {
            $performance_target->assessor->notify(
                new PerformanceTargetSubmittedNotification($performance_target)
            );
        }

        return redirect()->route('performance-targets.show', $performance_target->id)
            ->with('success', 'Performance target submitted successfully.');
    }

    public function approve(Request $request, PerformanceTarget $performance_target)
    {
        if (auth()->id() !== $performance_target->assessor_id && !auth()->user()->is_admin) {
            abort(403);
        }

        $performance_target->update([
            'status' => 'approved_by_assessor',
            'approved_at' => now(),
            'assessor_general_comment' => $request->assessor_general_comment,
        ]);

        foreach ($request->item_comments ?? [] as $itemId => $comment) {
            $item = $performance_target->items()->where('id', $itemId)->first();
            if ($item) {
                $item->update([
                    'assessor_comment' => $comment,
                ]);
            }
        }

        $performance_target->user->notify(
            new PerformanceTargetApprovedNotification($performance_target)
        );

        if ($performance_target->hrReviewer && !$performance_target->assessor?->is_ceo) {
            $performance_target->hrReviewer->notify(
                new PerformanceTargetSubmittedNotification($performance_target, 'Performance target approved by assessor and awaiting HR review.')
            );
        }

        return redirect()->route('performance-targets.show', $performance_target->id)
            ->with('success', 'Performance target approved successfully.');
    }

    public function reject(Request $request, PerformanceTarget $performance_target)
    {
        if (auth()->id() !== $performance_target->assessor_id && !auth()->user()->is_admin) {
            abort(403);
        }

        $request->validate([
            'assessor_general_comment' => ['required', 'string'],
        ]);

        $performance_target->update([
            'status' => 'rejected_by_assessor',
            'rejected_at' => now(),
            'assessor_general_comment' => $request->assessor_general_comment,
        ]);

        foreach ($request->item_comments ?? [] as $itemId => $comment) {
            $item = $performance_target->items()->where('id', $itemId)->first();
            if ($item) {
                $item->update([
                    'assessor_comment' => $comment,
                ]);
            }
        }

        $performance_target->user->notify(
            new PerformanceTargetRejectedNotification($performance_target)
        );

        return redirect()->route('performance-targets.show', $performance_target->id)
            ->with('success', 'Performance target rejected successfully.');
    }

    public function hrReview(Request $request, PerformanceTarget $performance_target)
    {
        if (
            auth()->id() !== $performance_target->hr_reviewer_id &&
            !auth()->user()->is_hr &&
            !auth()->user()->is_admin
        ) {
            abort(403);
        }

        $performance_target->update([
            'status' => 'reviewed_by_hr',
            'hr_reviewed_at' => now(),
            'hr_general_comment' => $request->hr_general_comment,
        ]);

        foreach ($request->hr_item_comments ?? [] as $itemId => $comment) {
            $item = $performance_target->items()->where('id', $itemId)->first();
            if ($item) {
                $item->update([
                    'hr_comment' => $comment,
                ]);
            }
        }

        $performance_target->user->notify(
            new PerformanceTargetHrReviewedNotification($performance_target)
        );

        return redirect()->route('performance-targets.show', $performance_target->id)
            ->with('success', 'Performance target reviewed by HR successfully.');
    }
}