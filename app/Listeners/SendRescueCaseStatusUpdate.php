<?php

namespace App\Listeners;

use App\Events\RescueCaseStatusUpdated;
use App\Models\User;
use App\Notifications\RescueCaseStatusUpdatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class SendRescueCaseStatusUpdate implements ShouldQueue
{
    use InteractsWithQueue;
    
    /**
     * The name of the queue the job should be sent to.
     *
     * @var string|null
     */
    public $queue = 'notifications';

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
        $metadata = $event->metadata ?? [];
        
        // Determine recipients based on the status update
        $recipients = collect();
        
        // Always notify the victim
        if ($rescueCase->victim) {
            $recipients->push($rescueCase->victim);
        }
        
        // Notify admins for important status changes
        if (in_array($status, ['dalam_tindakan', 'sedang_diselamatkan', 'bantuan_selesai', 'tidak_ditemui'])) {
            $admins = User::role('admin')->get();
            $recipients = $recipients->merge($admins);
        }
        
        // Notify assigned rescuers
        if ($rescueCase->relationLoaded('rescuers')) {
            $rescuers = $rescueCase->rescuers->map(function($rescuer) {
                return $rescuer->user;
            })->filter();
            
            $recipients = $recipients->merge($rescuers);
        }
        
        // Remove duplicates
        $recipients = $recipients->unique('id');
        
        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new RescueCaseStatusUpdatedNotification($rescueCase, $status, $metadata));
        }
        
        // Log the notification
        \Log::info('Sent rescue case status update notifications', [
            'case_id' => $rescueCase->id,
            'status' => $status,
            'recipient_count' => $recipients->count(),
        ]);
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
        \Log::error('Failed to send rescue case status update notifications', [
            'case_id' => $event->rescueCase->id,
            'status' => $event->status,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
