<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForecastEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'method',
        'mse',
        'rmse',
        'mae',
        'mape',
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
