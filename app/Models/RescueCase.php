<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RescueCase extends Model
{
    use HasFactory;

    protected $fillable = [
        'victim_id',
        'rescuer_id',
        'lat',
        'lng',
        'status',
    ];

    public function victim() {
        return $this->belongsTo(User::class, 'victim_id');
    }

    public function rescuer() {
        return $this->belongsTo(User::class, 'rescuer_id');
    }
}
