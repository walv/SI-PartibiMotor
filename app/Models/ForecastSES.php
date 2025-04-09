<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForecastSes extends Model
{
    use HasFactory;

    protected $table = 'forecast_ses';

    protected $fillable = [
        'product_id',
        'year',
        'month',
        'actual',
        'forecast',
        'alpha'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
