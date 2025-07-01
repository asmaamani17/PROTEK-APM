<?php

namespace App\Listeners;

use App\Events\NewRescueCase;
use App\Models\User;
use App\Notifications\NewRescueCaseNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class SendNewRescueCaseNotifications implements ShouldQueue
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
     * @param  \App\Events\NewRescueCase  $event
     * @return void
     */
    public function handle(NewRescueCase $event)
    {
        $rescueCase = $event->rescueCase;
        
        // Notify all admin users
        $adminUsers = User::role('admin')->get();
        
        // Also notify any rescuers that might be assigned
        $rescuerUsers = collect();
        if ($rescueCase->relationLoaded('rescuers')) {
            $rescuerUsers = $rescueCase->rescuers->map(function($rescuer) {
                return $rescuer->user;
            })->filter();
        }
        
        $recipients = $adminUsers->merge($rescuerUsers)->unique('id');
        
        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new NewRescueCaseNotification($rescueCase));
        }
        
        // Log the notification
        \Log::info('Sent new rescue case notifications', [
            'case_id' => $rescueCase->id,
            'victim_id' => $rescueCase->victim_id,
            'recipient_count' => $recipients->count(),
        ]);
    }
    
    /**
     * Handle a job failure.
     *
     * @param  \App\Events\NewRescueCase  $event
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(NewRescueCase $event, $exception)
    {
        \Log::error('Failed to send new rescue case notifications', [
            'case_id' => $event->rescueCase->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
