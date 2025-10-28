<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expenses extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'salon_id',
        'category',
        'amount',
        'description',
        'expense_date',
    ];

    protected $casts = [
        'amount' => 'integer',
        'expense_date' => 'date',
    ];

    public function salon()
    {
        return $this->belongsTo(Salons::class);
    }
}
