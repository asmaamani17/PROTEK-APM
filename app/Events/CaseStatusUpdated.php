<?php

namespace App\Events;

use App\Models\EmergencyCase;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CaseStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $case;

    /**
     * Create a new event instance.
     *
     * @param  \App\Models\EmergencyCase  $case
     * @return void
     */
    public function __construct(EmergencyCase $case)
    {
        $this->case = $case;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return [
            new PrivateChannel('case.' . $this->case->id),
            new PrivateChannel('admin.dashboard'),
            new PrivateChannel('rescuer.dashboard'),
            new PrivateChannel('victim.' . $this->case->victim_id),
        ];
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'case.status.updated';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'case' => [
                'id' => $this->case->id,
                'status' => $this->case->status,
                'status_text' => $this->getStatusText($this->case->status),
                'updated_at' => $this->case->updated_at->toDateTimeString(),
                'rescue_started_at' => $this->case->rescue_started_at?->toDateTimeString(),
                'rescue_completed_at' => $this->case->rescue_completed_at?->toDateTimeString(),
            ]
        ];
    }

    /**
     * Get the status text for the given status.
     *
     * @param  string  $status
     * @return string
     */
    protected function getStatusText($status)
    {
        $statuses = [
            'dalam_tindakan' => 'Dalam Tindakan',
            'sedang_diselamatkan' => 'Sedang Diselamatkan',
            'bantuan_selesai' => 'Bantuan Selesai',
            'tidak_ditemui' => 'Tidak Ditemui',
        ];

        return $statuses[$status] ?? 'Unknown';
    }
}
