<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RescueCase extends Model
{
    use HasFactory;

    // Status constants
    const STATUS_TIADA_BANTUAN = 'tiada_bantuan';
    const STATUS_MOHON_BANTUAN = 'mohon_bantuan';
    const STATUS_DALAM_TINDAKAN = 'dalam_tindakan';
    const STATUS_SEDANG_DISELAMATKAN = 'sedang_diselamatkan';
    const STATUS_BANTUAN_SELESAI = 'bantuan_selesai';
    const STATUS_TIDAK_DITEMUI = 'tidak_ditemui';
    
    // All available statuses
    const STATUSES = [
        self::STATUS_TIADA_BANTUAN,
        self::STATUS_MOHON_BANTUAN,
        self::STATUS_DALAM_TINDAKAN,
        self::STATUS_SEDANG_DISELAMATKAN,
        self::STATUS_BANTUAN_SELESAI,
        self::STATUS_TIDAK_DITEMUI,
    ];

    protected $fillable = [
        'victim_id',
        'victim_name',
        'rescuer_id',
        'rescuer_name',
        'lat',
        'lng',
        'district',
        'status',
        'rescue_started_at',
        'completed_at',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'rescue_started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function victim() {
        return $this->belongsTo(VulnerableGroup::class, 'victim_id');
    }

    public function rescuer() {
        return $this->belongsTo(User::class, 'rescuer_id');
    }
    
    /**
     * Get the status history for the rescue case.
     */
    public function statusHistory()
    {
        return $this->hasMany(RescueCaseStatusHistory::class)->latest();
    }
    
    /**
     * Update the status of the rescue case and log the change.
     *
     * @param string $status
     * @param string|null $notes
     * @param int|null $userId
     * @return $this
     */
    public function updateStatus($status, $notes = null, $userId = null)
    {
        if (!in_array($status, self::STATUSES)) {
            throw new \InvalidArgumentException("Invalid status: {$status}");
        }
        
        $this->status = $status;
        
        // Update timestamps based on status
        if ($status === self::STATUS_DALAM_TINDAKAN && !$this->rescue_started_at) {
            $this->rescue_started_at = now();
        } elseif ($status === self::STATUS_BANTUAN_SELESAI && !$this->completed_at) {
            $this->completed_at = now();
        }
        
        $this->save();

        // Log the status change
        $this->statusHistory()->create([
            'status' => $status,
            'notes' => $notes,
            'changed_by' => $userId ?? auth()->id()
        ]);

        // Trigger status updated event
        event(new \App\Events\RescueCaseStatusUpdated($this, $status));

        return $this;
    }
    
    public function chatMessages()
    {
        return $this->hasMany(ChatMessage::class);
    }
}
