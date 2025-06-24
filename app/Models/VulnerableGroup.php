<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VulnerableGroup extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'vulnerable_groups';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'serial_number',
        'name',
        'identification_number',
        'gender',
        'address',
        'district',
        'parliament',
        'dun',
        'phone_number',
        'disability_category',
        'client_type',
        'oku_status',
        'age_group',
        'parliament_dun_code',
        'prb_serial_number',
        'installation_status',
        'latitude',
        'longitude'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
    ];
    
    /**
     * Get the user that owns the vulnerable group record.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
