<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\VulnerableGroup;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'no_telefon',
        'password',
        'role',
        'daerah',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get all rescue cases for this user (as victim)
     */
    public function rescueCases() {
        return $this->hasMany(RescueCase::class, 'victim_id')->orderBy('created_at', 'desc');
    }
    
    /**
     * Get the currently active rescue case (if any)
     */
    public function activeRescueCase() {
        return $this->hasOne(RescueCase::class, 'victim_id')
            ->where('status', '!=', 'completed')
            ->orderBy('created_at', 'desc')
            ->with('rescuer');
    }
    
    /**
     * Get all rescue cases where this user is the rescuer
     */
    public function assignedRescues() {
        return $this->hasMany(RescueCase::class, 'rescuer_id');
    }
    
    /**
     * Get the vulnerable group record associated with the user.
     */
    public function vulnerableGroup()
    {
        return $this->hasOne(VulnerableGroup::class, 'user_id');
    }

}

