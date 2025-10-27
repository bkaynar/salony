<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffWorking extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'day_of_week',
        'start_time',
        'end_time',
        'is_off',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'is_off' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
