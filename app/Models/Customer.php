<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'salon_id',
        'name',
        'phone',
        'email',
        'notes',
    ];

    public function salon()
    {
        return $this->belongsTo(Salons::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointments::class);
    }
}
