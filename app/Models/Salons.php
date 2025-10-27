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
        return $this->belongsTo(Plans::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function products()
    {
        return $this->hasMany(Products::class);
    }

    public function payments()
    {
        return $this->hasMany(Payments::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointments::class);
    }
}
