<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForecastTes extends Model
{
    use HasFactory;

    protected $table = 'forecast_tes';

    protected $fillable = [
        'product_id',
        'year',
        'month',
        'actual',
        'forecast',
        'level',
        'trend',
        'seasonal',
        'alpha',
        'beta',
        'gamma'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
