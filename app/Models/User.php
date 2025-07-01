<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\VulnerableGroup;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

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

    public function rescueCases() {
        return $this->hasMany(RescueCase::class, 'rescuer_id');
    }
    
    public function vulnerableGroups() {
        return $this->hasMany(VulnerableGroup::class);
    }
    
    /**
     * Route notifications for the SMS channel.
     *
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return string
     */
    public function routeNotificationForSms($notification)
    {
        // Return the phone number for SMS notifications
        return $this->no_telefon;
    }

    public function vulnerableGroup()
    {
        return $this->hasOne(VulnerableGroup::class, 'user_id');
    }

}

