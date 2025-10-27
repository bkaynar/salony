<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payments extends Model
{
    use HasFactory;

    protected $fillable = [
        'salon_id',
        'appointment_id',
        'customer_id',
        'amount',
        'method',
        'status',
        'transaction_id',
    ];

    protected $casts = [
        'amount' => 'integer',
    ];

    public function salon()
    {
        return $this->belongsTo(Salons::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointments::class);
    }

    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer::class);
    }
}
