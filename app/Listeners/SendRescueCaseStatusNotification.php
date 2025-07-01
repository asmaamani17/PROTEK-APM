<?php

namespace App\Listeners;

use App\Events\RescueCaseStatusUpdated;
use App\Models\User;
use App\Notifications\RescueCaseStatusUpdated as RescueCaseStatusUpdatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class SendRescueCaseStatusNotification implements ShouldQueue
{
    /**
     * The number of times the queued listener may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 5;

    /**
     * Handle the event.
     *
     * @param  \App\Events\RescueCaseStatusUpdated  $event
     * @return void
     */
    public function handle(RescueCaseStatusUpdated $event)
    {
        $rescueCase = $event->rescueCase;
        $status = $event->status;
        
        // Get all users who should be notified
        $notifiables = collect();
        
        // Always notify the victim
        if ($rescueCase->victim) {
            $notifiables->push($rescueCase->victim);
        }
        
        // Notify the rescuer if assigned
        if ($rescueCase->rescuer) {
            $notifiables->push($rescueCase->rescuer);
        }
        
        // Notify all admins
        $admins = User::role('admin')->get();
        $notifiables = $notifiables->merge($admins);
        
        // Send notification to all relevant users
        if ($notifiables->isNotEmpty()) {
            Notification::send(
                $notifiables->unique('id'),
                new RescueCaseStatusUpdatedNotification($rescueCase, $status)
            );
        }
    }
    
    /**
     * Handle a job failure.
     *
     * @param  \App\Events\RescueCaseStatusUpdated  $event
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(RescueCaseStatusUpdated $event, $exception)
    {
        // Log the failure
        \Log::error('Failed to send status update notifications', [
            'case_id' => $event->rescueCase->id,
            'status' => $event->status,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
