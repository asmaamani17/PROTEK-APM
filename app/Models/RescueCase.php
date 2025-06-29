<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class RescueCase extends Model
{
    use HasFactory;

    protected $fillable = [
        'victim_id',
        'rescuer_id',
        'lat',
        'lng',
        'status',
        'notes',
        'completed_at'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'completed_at'
    ];

    // Status constants
    const STATUS_MOHON_BANTUAN = 'mohon_bantuan';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_RESCUED = 'rescued';
    const STATUS_NOT_FOUND = 'not_found';
    const STATUS_COMPLETED = 'completed';

    /**
     * Get the victim for this case
     */
    public function victim() {
        return $this->belongsTo(User::class, 'victim_id')->withDefault([
            'name' => 'Unknown Victim',
            'phone_number' => 'N/A'
        ]);
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
}
