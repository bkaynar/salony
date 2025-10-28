<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salons extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'subdomain',
        'phone',
        'address',
        'settings',
        'plan_id',
        'subscription_ends_at',
    ];

    protected $casts = [
        'settings' => 'array',
        'subscription_ends_at' => 'datetime',
    ];

    public function plan()
    {
        return $this->belongsTo(Plans::class, 'plan_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'salon_id');
    }

    public function products()
    {
        return $this->hasMany(Products::class, 'salon_id');
    }

    public function payments()
    {
        return $this->hasMany(Payments::class, 'salon_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointments::class, 'salon_id');
    }

    public function services()
    {
        return $this->hasMany(Service::class, 'salon_id');
    }

    /**
     * Use `subdomain` column for implicit route model binding and URL generation.
     */
    public function getRouteKeyName()
    {
        return 'subdomain';
    }
}
