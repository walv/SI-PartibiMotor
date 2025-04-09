<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForecastDes extends Model
{
    use HasFactory;

    protected $table = 'forecast_des';

    protected $fillable = [
        'product_id',
        'year',
        'month',
        'actual',
        'forecast',
        'level',
        'trend',
        'alpha',
        'beta'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
