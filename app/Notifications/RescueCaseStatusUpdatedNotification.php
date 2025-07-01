<?php

namespace App\Notifications;

use App\Models\RescueCase;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RescueCaseStatusUpdatedNotification extends Notification implements ShouldQueue
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
     * Additional metadata about the status update.
     *
     * @var array
     */
    public $metadata;

    /**
     * Create a new notification instance.
     *
     * @param  \App\Models\RescueCase  $rescueCase
     * @param  string  $status
     * @param  array  $metadata
     * @return void
     */
    public function __construct(RescueCase $rescueCase, string $status, array $metadata = [])
    {
        $this->rescueCase = $rescueCase->loadMissing(['victim']);
        $this->status = $status;
        $this->metadata = $metadata;
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
        
        // Send email for important status updates
        if ($this->shouldSendEmail($notifiable)) {
            $channels[] = 'mail';
        }
        
        // Send SMS for important status updates if user has a phone number
        if ($notifiable->phone && $this->shouldSendSms($notifiable)) {
            $channels[] = 'sms';
        }
        
        return array_unique($channels);
    }
    
    /**
     * Determine if an email should be sent for this notification.
     *
     * @param  mixed  $notifiable
     * @return bool
     */
    /**
     * Determine if an email should be sent for this notification.
     *
     * @param  mixed  $notifiable
     * @return bool
     */
    protected function shouldSendEmail($notifiable)
    {
        // Always send email to admins for important status changes
        if ($notifiable->hasRole('admin')) {
            return in_array($this->status, [
                'dalam_tindakan', 
                'sedang_diselamatkan', 
                'bantuan_selesai', 
                'tidak_ditemui'
            ]);
        }
        
        // Send email to victim for important status changes
        if ($notifiable->id === $this->rescueCase->victim_id) {
            return in_array($this->status, [
                'dalam_tindakan', 
                'sedang_diselamatkan', 
                'bantuan_selesai', 
                'tidak_ditemui'
            ]);
        }
        
        // For rescuers, only send email for status changes that affect them directly
        if ($notifiable->hasRole('rescuer')) {
            return in_array($this->status, [
                'dalam_tindakan', 
                'sedang_diselamatkan'
            ]);
        }
        
        return false;
    }
    
    /**
     * Determine if an SMS should be sent for this notification.
     *
     * @param  mixed  $notifiable
     * @return bool
     */
    protected function shouldSendSms($notifiable)
    {
        // Only send SMS for important status changes
        $importantStatuses = [
            'dalam_tindakan', 
            'sedang_diselamatkan', 
            'bantuan_selesai', 
            'tidak_ditemui'
        ];
        
        if (!in_array($this->status, $importantStatuses)) {
            return false;
        }
        
        // Always send SMS to admins for important status changes
        if ($notifiable->hasRole('admin')) {
            return true;
        }
        
        // Send SMS to victim for important status changes
        if ($notifiable->id === $this->rescueCase->victim_id) {
            return true;
        }
        
        // For rescuers, only send SMS for status changes that affect them directly
        if ($notifiable->hasRole('rescuer')) {
            return in_array($this->status, [
                'dalam_tindakan', 
                'sedang_diselamatkan'
            ]);
        }
        
        return false;
    }
    
    /**
     * Get the SMS representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    public function toSms($notifiable)
    {
        $statusText = $this->getStatusText();
        $caseId = $this->rescueCase->id;
        $victimName = $this->rescueCase->victim->name ?? 'Mangsa';
        $location = $this->rescueCase->address ?? 'Lokasi tidak diketahui';
        
        $message = "[PROTEK-APM] Status Bantuan Diperbarui\n";
        $message .= "Kes: #{$caseId}\n";
        $message .= "Mangsa: {$victimName}\n";
        $message .= "Status: {$statusText}\n";
        $message .= "Lokasi: {$location}\n";
        $message .= "Masa: " . now()->format('d/m/Y H:i');
        
        // Add notes if available
        if (!empty($this->metadata['notes'])) {
            $message .= "\n\nCatatan: {$this->metadata['notes']}";
        }
        
        return $message;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $statusLabels = [
            'tiada_bantuan' => 'Tiada Bantuan',
            'mohon_bantuan' => 'Mohon Bantuan',
            'dalam_tindakan' => 'Dalam Tindakan',
            'sedang_diselamatkan' => 'Sedang Diselamatkan',
            'bantuan_selesai' => 'Bantuan Selesai',
            'tidak_ditemui' => 'Tidak Ditemui',
        ];
        
        $statusLabel = $statusLabels[$this->status] ?? $this->status;
        $victimName = $this->rescueCase->victim->name ?? 'Mangsa';
        $url = route('rescue-cases.show', $this->rescueCase->id);
        
        $mail = (new MailMessage)
            ->subject("[SOS] Status Bantuan Diperbarui - #{$this->rescueCase->id}")
            ->line("Status bantuan untuk kes #{$this->rescueCase->id} telah diperbarui.")
            ->line("Mangsa: {$victimName}")
            ->line("Status Baru: {$statusLabel}")
            ->line("Masa: " . now()->format('d/m/Y H:i'));
            
        // Add notes if available
        if (!empty($this->metadata['notes'])) {
            $mail->line("Catatan: {$this->metadata['notes']}");
        }
        
        // Add action button based on user role
        if ($notifiable->hasRole('admin')) {
            $mail->action('Lihat Butiran Kes', $url);
        } elseif ($notifiable->hasRole('rescuer')) {
            $mail->action('Lihat Tugasan', $url);
        } else {
            $mail->action('Lihat Status', $url);
        }
        
        return $mail->line('Terima kasih atas kerjasama anda.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'rescue_case_id' => $this->rescueCase->id,
            'status' => $this->status,
            'status_text' => $this->getStatusText(),
            'victim_id' => $this->rescueCase->victim_id,
            'victim_name' => $this->rescueCase->victim->name ?? 'Unknown',
            'updated_at' => now()->toDateTimeString(),
            'url' => route('rescue-cases.show', $this->rescueCase->id),
            'metadata' => $this->metadata,
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
            'status' => $this->status,
            'status_text' => $this->getStatusText(),
            'victim_name' => $this->rescueCase->victim->name ?? 'Unknown',
            'updated_at' => now()->toDateTimeString(),
            'url' => route('rescue-cases.show', $this->rescueCase->id),
            'message' => "Status bantuan untuk kes #{$this->rescueCase->id} telah diperbarui ke: " . $this->getStatusText(),
        ]);
    }
    
    /**
     * Get the display text for the status.
     *
     * @return string
     */
    protected function getStatusText()
    {
        $statusMap = [
            'tiada_bantuan' => 'Tiada Bantuan',
            'mohon_bantuan' => 'Mohon Bantuan',
            'dalam_tindakan' => 'Dalam Tindakan',
            'sedang_diselamatkan' => 'Sedang Diselamatkan',
            'bantuan_selesai' => 'Bantuan Selesai',
            'tidak_ditemui' => 'Tidak Ditemui',
        ];
        
        return $statusMap[$this->status] ?? $this->status;
    }
}
