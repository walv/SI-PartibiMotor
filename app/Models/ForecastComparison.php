<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForecastComparison extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'best_method',
        'date'
    ];

    protected $casts = [
        'date' => 'datetime'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
