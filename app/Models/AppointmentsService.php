<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppointmentsService extends Model
{
    use HasFactory;

    protected $table = 'appointments_services';

    public $timestamps = false;

    protected $fillable = [
        'appointment_id',
        'service_id',
        'price',
        'duration_minutes',
    ];

    protected $casts = [
        'price' => 'integer',
        'duration_minutes' => 'integer',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointments::class);
    }

    public function service()
    {
        return $this->belongsTo(\App\Models\Service::class);
    }
}
