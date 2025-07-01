<?php

namespace App\Notifications;

use App\Models\RescueCase;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class NewRescueCaseNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The rescue case instance.
     *
     * @var \App\Models\RescueCase
     */
    public $rescueCase;

    /**
     * Create a new notification instance.
     *
     * @param  \App\Models\RescueCase  $rescueCase
     * @return void
     */
    public function __construct(RescueCase $rescueCase)
    {
        $this->rescueCase = $rescueCase->loadMissing(['victim']);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $channels = ['database', 'broadcast'];
        
        // Always send email to admins for new rescue cases
        if ($notifiable->hasRole('admin')) {
            $channels[] = 'mail';
        }
        
        // Send SMS to rescuers and admins if they have a phone number
        if ($notifiable->phone) {
            if ($notifiable->hasRole(['admin', 'rescuer'])) {
                $channels[] = 'sms';
            }
        }
        
        return array_unique($channels);
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $url = route('admin.rescue-cases.show', $this->rescueCase->id);
        $victimName = $this->rescueCase->victim->name ?? 'Unknown';
        $location = $this->rescueCase->address ?? 'Lokasi tidak diketahui';
        
        return (new MailMessage)
            ->subject("[SOS] Permintaan Bantuan Baru - #{$this->rescueCase->id}")
            ->greeting('Permintaan Bantuan Baru')
            ->line("Mangsa: {$victimName}")
            ->line("Lokasi: {$location}")
            ->line("Masa: {$this->rescueCase->created_at->format('d/m/Y H:i')}")
            ->action('Lihat Butiran Kes', $url)
            ->line('Sila ambil tindakan segera untuk membantu mangsa.')
            ->salutation('Terima kasih, \nSistem PROTEK-APM');
    }

    /**
     * Get the SMS representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    public function toSms($notifiable)
    {
        $statusText = [
            'tiada_bantuan' => 'Tiada Bantuan',
            'mohon_bantuan' => 'Mohon Bantuan',
            'dalam_tindakan' => 'Dalam Tindakan',
            'sedang_diselamatkan' => 'Sedang Diselamatkan',
            'bantuan_selesai' => 'Bantuan Selesai',
            'tidak_ditemui' => 'Tidak Ditemui',
        ][$this->rescueCase->status] ?? $this->rescueCase->status;
        
        $victimName = $this->rescueCase->victim->name ?? 'Mangsa';
        $location = $this->rescueCase->address ?? 'Lokasi tidak diketahui';
        $caseId = $this->rescueCase->id;
        
        return "[PROTEK-APM] Permintaan Bantuan Baru\n" .
               "Kes: #{$caseId}\n" .
               "Mangsa: {$victimName}\n" .
               "Status: {$statusText}\n" .
               "Lokasi: {$location}\n" .
               "Masa: " . now()->format('d/m/Y H:i');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $statusText = [
            'tiada_bantuan' => 'Tiada Bantuan',
            'mohon_bantuan' => 'Mohon Bantuan',
            'dalam_tindakan' => 'Dalam Tindakan',
            'sedang_diselamatkan' => 'Sedang Diselamatkan',
            'bantuan_selesai' => 'Bantuan Selesai',
            'tidak_ditemui' => 'Tidak Ditemui',
        ][$this->rescueCase->status] ?? $this->rescueCase->status;
        
        return [
            'id' => $this->rescueCase->id,
            'victim_id' => $this->rescueCase->victim_id,
            'victim_name' => $this->rescueCase->victim->name ?? $this->rescueCase->victim_name ?? 'Unknown',
            'phone' => $this->rescueCase->victim->phone ?? $this->rescueCase->phone ?? null,
            'status' => $this->rescueCase->status,
            'status_text' => $statusText,
            'district' => $this->rescueCase->district,
            'notes' => $this->rescueCase->notes,
            'location' => [
                'lat' => $this->rescueCase->latitude,
                'lng' => $this->rescueCase->longitude,
                'accuracy' => $this->rescueCase->accuracy,
                'address' => $this->rescueCase->address,
            ],
            'requested_at' => $this->rescueCase->requested_at?->toIso8601String(),
            'created_at' => $this->rescueCase->created_at->toIso8601String(),
            'updated_at' => $this->rescueCase->updated_at->toIso8601String(),
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return BroadcastMessage
     */
    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'rescue_case_id' => $this->rescueCase->id,
            'victim_name' => $this->rescueCase->victim->name ?? 'Unknown',
            'status' => $this->rescueCase->status,
            'created_at' => $this->rescueCase->created_at->toDateTimeString(),
            'url' => route('admin.rescue-cases.show', $this->rescueCase->id),
            'message' => "Permintaan bantuan baru diterima dari {$this->rescueCase->victim->name}",
        ]);
    }
}
