<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Products extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'salon_id',
        'name',
        'sku',
        'stock_level',
        'price',
        'cost',
    ];

    protected $casts = [
        'stock_level' => 'integer',
        'price' => 'integer',
        'cost' => 'integer',
    ];

    public function salon()
    {
        return $this->belongsTo(Salons::class);
    }
}
