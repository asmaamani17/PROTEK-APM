<?php

namespace App\Notifications;

use App\Models\RescueCase;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class RescueCaseStatusUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The rescue case instance.
     *
     * @var \App\Models\RescueCase
     */
    public $rescueCase;

    /**
     * The new status.
     *
     * @var string
     */
    public $status;

    /**
     * Create a new notification instance.
     *
     * @param  \App\Models\RescueCase  $rescueCase
     * @param  string  $status
     * @return void
     */
    public function __construct(RescueCase $rescueCase, string $status)
    {
        $this->rescueCase = $rescueCase;
        $this->status = $status;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array<string>
     */
    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array<string, mixed>
     */
    public function toArray($notifiable): array
    {
        $statusLabels = [
            RescueCase::STATUS_MOHON_BANTUAN => 'Mohon Bantuan',
            RescueCase::STATUS_DALAM_TINDAKAN => 'Dalam Tindakan',
            RescueCase::STATUS_SEDANG_DISELAMATKAN => 'Sedang Diselamatkan',
            RescueCase::STATUS_BANTUAN_SELESAI => 'Bantuan Selesai',
            RescueCase::STATUS_TIDAK_DITEMUI => 'Tidak Ditemui',
        ];

        $statusLabel = $statusLabels[$this->status] ?? $this->status;

        return [
            'title' => 'Status Bantuan Dikemaskini',
            'message' => "Status bantuan telah dikemaskini kepada: {$statusLabel}",
            'case_id' => $this->rescueCase->id,
            'status' => $this->status,
            'status_label' => $statusLabel,
            'timestamp' => now()->toDateTimeString(),
            'url' => $this->getNotificationUrl($notifiable),
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\BroadcastMessage
     */
    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }

    /**
     * Get the notification URL based on user role.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function getNotificationUrl($notifiable): string
    {
        if ($notifiable->hasRole('admin')) {
            return route('admin.dashboard');
        }

        if ($notifiable->hasRole('rescuer')) {
            return route('rescuer.dashboard');
        }

        return route('victim.dashboard');
    }
}
