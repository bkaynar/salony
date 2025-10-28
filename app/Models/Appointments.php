<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointments extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'salon_id',
        'customer_id',
        'staff_id',
        'start_time',
        'end_time',
        'total_price',
        'total_duration',
        'status',
        'notes',
        'booked_by',
    ];

    protected $casts = [
        'total_price' => 'integer',
        'total_duration' => 'integer',
    ];

    // Don't cast start_time and end_time to datetime to avoid timezone conversion
    // They will be stored and retrieved as strings

    public function salon()
    {
        return $this->belongsTo(Salons::class);
    }

    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer::class);
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function services()
    {
        return $this->hasMany(AppointmentsService::class, 'appointment_id');
    }
}
