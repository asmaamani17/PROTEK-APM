<?php

namespace App\Events;

use App\Models\RescueCase;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RescueCaseStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

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
     * The timestamp of the status update.
     *
     * @var \Illuminate\Support\Carbon
     */
    public $timestamp;
    
    /**
     * Additional metadata about the status update.
     *
     * @var array
     */
    public $metadata;

    /**
     * Create a new event instance.
     *
     * @param  \App\Models\RescueCase  $rescueCase
     * @param  string  $status
     * @param  array  $metadata
     * @return void
     */
    public function __construct(RescueCase $rescueCase, string $status, array $metadata = [])
    {
        $this->rescueCase = $rescueCase->loadMissing(['victim', 'rescuers.user']);
        $this->status = $status;
        $this->timestamp = now();
        $this->metadata = $metadata;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('rescue-case.' . $this->rescueCase->id),
            new PrivateChannel('user.' . $this->rescueCase->victim_id),
            new PrivateChannel('private-victim.' . $this->rescueCase->victim_id),
            new Channel('rescue-cases')
        ];
        
        // Add channels for all assigned rescuers
        foreach ($this->rescueCase->rescuers as $rescuer) {
            $channels[] = new PrivateChannel('user.' . $rescuer->user_id);
        }
        
        return $channels;
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'rescue-case.status-updated';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        // Get the status text based on the status value
        $statusText = [
            'tiada_bantuan' => 'Tiada Bantuan',
            'mohon_bantuan' => 'Mohon Bantuan',
            'dalam_tindakan' => 'Dalam Tindakan',
            'sedang_diselamatkan' => 'Sedang Diselamatkan',
            'bantuan_selesai' => 'Bantuan Selesai',
            'tidak_ditemui' => 'Tidak Ditemui',
        ][$this->status] ?? $this->status;
        
        // Prepare the response data
        $data = [
            'id' => $this->rescueCase->id,
            'status' => $this->status,
            'status_text' => $statusText,
            'victim_id' => $this->rescueCase->victim_id,
            'victim_name' => $this->rescueCase->victim->name ?? 'Unknown',
            'location' => [
                'lat' => $this->rescueCase->latitude,
                'lng' => $this->rescueCase->longitude,
                'accuracy' => $this->rescueCase->accuracy,
                'address' => $this->rescueCase->address,
            ],
            'requested_at' => $this->rescueCase->requested_at?->toIso8601String(),
            'updated_at' => $this->rescueCase->updated_at->toIso8601String(),
            'timestamp' => $this->timestamp->toIso8601String(),
        ];
        
        // Add assigned rescuers if any
        if ($this->rescueCase->relationLoaded('rescuers') && $this->rescueCase->rescuers->isNotEmpty()) {
            $data['assigned_rescuers'] = $this->rescueCase->rescuers->map(function($rescuer) {
                return [
                    'id' => $rescuer->id,
                    'user_id' => $rescuer->user_id,
                    'name' => $rescuer->user->name ?? 'Unknown',
                    'phone' => $rescuer->user->phone ?? null,
                    'status' => $rescuer->status,
                    'assigned_at' => $rescuer->created_at->toIso8601String(),
                ];
            });
        }
        
        // Add any additional metadata
        if (!empty($this->metadata)) {
            $data['metadata'] = $this->metadata;
        }
        
        return $data;
    }
}
