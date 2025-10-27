<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plans extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price_monthly',
        'price_yearly',
        'max_staff_count',
        'allow_online_booking',
        'allow_sms_notifications',
    ];

    protected $casts = [
        'price_monthly' => 'float',
        'price_yearly' => 'float',
        'max_staff_count' => 'integer',
        'allow_online_booking' => 'boolean',
        'allow_sms_notifications' => 'boolean',
    ];

    public function salons()
    {
        return $this->hasMany(Salons::class);
    }
}
