<?php

namespace App\Notifications\Channels;

use App\Services\SimpleSmsService;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class SmsChannel
{
    /**
     * The SMS service instance.
     *
     * @var \App\Services\SimpleSmsService
     */
    protected $smsService;

    /**
     * Create a new SMS channel instance.
     *
     * @param  \App\Services\SimpleSmsService  $smsService
     * @return void
     */
    public function __construct(SimpleSmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return bool
     */
    public function send($notifiable, Notification $notification)
    {
        if (!method_exists($notification, 'toSms')) {
            throw new \RuntimeException(
                'Notification is missing toSms method.'
            );
        }

        $message = $notification->toSms($notifiable);
        
        // Get the recipient's phone number
        $to = $notifiable->routeNotificationFor('sms', $notification) ?: $notifiable->no_telefon;
        
        if (empty($to)) {
            Log::warning('No phone number provided for SMS notification', [
                'notifiable_id' => $notifiable->id,
                'notifiable_type' => get_class($notifiable),
                'notification' => get_class($notification)
            ]);
            return false;
        }
        
        // Send the SMS using our SimpleSmsService
        return $this->smsService->send($to, $message);
    }
}
