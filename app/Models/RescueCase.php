<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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
    ];

    public function victim() {
        return $this->belongsTo(User::class, 'victim_id');
    }

    /**
     * Get the rescuer assigned to this case (if any)
     */
    public function rescuer() {
        return $this->belongsTo(User::class, 'rescuer_id')->withDefault([
            'name' => 'Belum Ditugaskan',
            'phone_number' => 'N/A'
        ]);
    }

    /**
     * Scope a query to only include active cases
     */
    public function scopeActive($query) {
        return $query->where('status', '!=', self::STATUS_COMPLETED);
    }

    /**
     * Scope a query to only include completed cases
     */
    public function scopeCompleted($query) {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Check if the case is active
     */
    public function isActive() {
        return $this->status !== self::STATUS_COMPLETED;
    }

    /**
     * Get the status display text
     */
    public function getStatusTextAttribute() {
        $statuses = [
            self::STATUS_MOHON_BANTUAN => 'Mohon Bantuan',
            self::STATUS_IN_PROGRESS => 'Dalam Tindakan',
            self::STATUS_RESCUED => 'Telah Diselamatkan',
            self::STATUS_NOT_FOUND => 'Tidak Ditemui',
            self::STATUS_COMPLETED => 'Selesai',
        ];

        return $statuses[$this->status] ?? ucfirst(str_replace('_', ' ', $this->status));
    }

    /**
     * Get the status badge HTML
     */
    public function getStatusBadgeAttribute() {
        $statusClasses = [
            self::STATUS_MOHON_BANTUAN => 'danger',
            self::STATUS_IN_PROGRESS => 'primary',
            self::STATUS_RESCUED => 'success',
            self::STATUS_NOT_FOUND => 'warning',
            self::STATUS_COMPLETED => 'dark',
        ];

        $statusIcons = [
            self::STATUS_MOHON_BANTUAN => 'exclamation-triangle',
            self::STATUS_IN_PROGRESS => 'people-carry',
            self::STATUS_RESCUED => 'user-shield',
            self::STATUS_NOT_FOUND => 'user-slash',
            self::STATUS_COMPLETED => 'check-circle',
        ];

        $class = $statusClasses[$this->status] ?? 'secondary';
        $icon = $statusIcons[$this->status] ?? 'info-circle';

        return sprintf(
            '<span class="badge bg-%s"><i class="fas fa-%s me-1"></i> %s</span>',
            $class,
            $icon,
            $this->status_text
        );
    }

    /**
     * Mark the case as completed
     */
    public function markAsCompleted() {
        $this->status = self::STATUS_COMPLETED;
        $this->completed_at = now();
        return $this->save();
    }

    /**
     * Get the duration of the rescue operation
     */
    public function getDurationAttribute() {
        if (!$this->completed_at) {
            return null;
        }

        return $this->created_at->diffForHumans($this->completed_at, true);
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
