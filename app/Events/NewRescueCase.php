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

class NewRescueCase implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The rescue case instance.
     *
     * @var \App\Models\RescueCase
     */
    public $rescueCase;

    /**
     * Create a new event instance.
     *
     * @param  \App\Models\RescueCase  $rescueCase
     * @return void
     */
    public function __construct(RescueCase $rescueCase)
    {
        $this->rescueCase = $rescueCase;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [
            new Channel('rescue-cases'),
            new PrivateChannel('admin.dashboard'),
            new PrivateChannel('user.' . $this->rescueCase->victim_id),
            new PrivateChannel('rescue-case.' . $this->rescueCase->id)
        ];
        
        // If there are any assigned rescuers, add their private channels
        if ($this->rescueCase->relationLoaded('rescuers')) {
            foreach ($this->rescueCase->rescuers as $rescuer) {
                $channels[] = new PrivateChannel('user.' . $rescuer->user_id);
            }
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
        return 'rescue-case.created';
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
        ][$this->rescueCase->status] ?? $this->rescueCase->status;
        
        // Prepare the response data
        $data = [
            'id' => $this->rescueCase->id,
            'victim_id' => $this->rescueCase->victim_id,
            'victim_name' => $this->rescueCase->victim->name ?? $this->rescueCase->victim_name ?? 'Unknown',
            'phone' => $this->rescueCase->victim->phone_number ?? $this->rescueCase->phone ?? null,
            'status' => $this->rescueCase->status,
            'status_text' => $statusText,
            'district' => $this->rescueCase->district,
            'notes' => $this->rescueCase->notes,
            'location' => [
                'lat' => $this->rescueCase->lat,
                'lng' => $this->rescueCase->lng,
                'accuracy' => $this->rescueCase->accuracy,
                'address' => $this->rescueCase->address,
            ],
            'requested_at' => $this->rescueCase->requested_at?->toIso8601String(),
            'created_at' => $this->rescueCase->created_at->toIso8601String(),
            'updated_at' => $this->rescueCase->updated_at->toIso8601String(),
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
        if (isset($this->rescueCase->metadata) && is_array($this->rescueCase->metadata)) {
            $data['metadata'] = $this->rescueCase->metadata;
        }
        
        return $data;
    }
}
