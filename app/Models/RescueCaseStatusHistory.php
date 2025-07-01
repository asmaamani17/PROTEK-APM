<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RescueCaseStatusHistory extends Model
{
    protected $fillable = [
        'rescue_case_id',
        'status',
        'notes',
        'changed_by'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the rescue case that owns the status history.
     */
    public function rescueCase(): BelongsTo
    {
        return $this->belongsTo(RescueCase::class);
    }

    /**
     * Get the user who changed the status.
     */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(VulnerableGroup::class, 'changed_by');
    }

    /**
     * Get the label for the status.
     */
    public function getStatusLabel(): string
    {
        $labels = [
            RescueCase::STATUS_TIADA_BANTUAN => 'Tiada Bantuan',
            RescueCase::STATUS_MOHON_BANTUAN => 'Mohon Bantuan',
            RescueCase::STATUS_DALAM_TINDAKAN => 'Dalam Tindakan',
            RescueCase::STATUS_SEDANG_DISELAMATKAN => 'Sedang Diselamatkan',
            RescueCase::STATUS_BANTUAN_SELESAI => 'Bantuan Selesai',
            RescueCase::STATUS_TIDAK_DITEMUI => 'Tidak Ditemui',
        ];

        return $labels[$this->status] ?? $this->status;
    }
}
