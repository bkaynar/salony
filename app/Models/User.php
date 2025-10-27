<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles; // <-- Bunu ekleyin

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasRoles, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'salon_id',
        'name',
        'email',
        'password',
        'phone',
        'is_bookable',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'two_factor_confirmed_at' => 'datetime',
        'is_bookable' => 'boolean',
    ];

    /**
     * Relations
     */
    public function salon()
    {
        return $this->belongsTo(Salons::class);
    }

    public function staffWorkings()
    {
        return $this->hasMany(StaffWorking::class);
    }

    public function timeOffs()
    {
        return $this->hasMany(StaffTimeOffs::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointments::class, 'staff_id');
    }
}
