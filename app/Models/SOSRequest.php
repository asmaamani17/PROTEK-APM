<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SOSRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'latitude',
        'longitude',
        'additional_info',
        'requested_at',
        'responded_at',
        'responded_by'
    ];

    protected $dates = [
        'requested_at',
        'responded_at',
        'created_at',
        'updated_at'
    ];

    // Status constants
    const STATUS_REQUESTED = 'requested_help';
    const STATUS_IN_ACTION = 'dalam_tindakan';        // Dalam Tindakan (In Action)
    const STATUS_RESCUING = 'sedang_diselamatkan';    // Sedang Diselamatkan (Being Rescued)
    const STATUS_COMPLETED = 'bantauan_selesai';      // Bantauan Selesai (Rescue Completed)
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Get the user who requested help
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who responded to the request
     */
    public function responder()
    {
        return $this->belongsTo(User::class, 'responded_by');
    }

    /**
     * Scope for active requests
     */
    public function scopeActive($query)
    {
        return $query->where('status', '!=', self::STATUS_COMPLETED)
                    ->where('status', '!=', self::STATUS_CANCELLED);
    }
}
