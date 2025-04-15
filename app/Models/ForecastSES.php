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
        'period',    // Menambahkan period di fillable
        'actual',
        'forecast',
        'alpha'
    ];

    // Menambahkan properti $dates untuk kolom datetime
    protected $dates = [
        'period',        // Kolom period yang merupakan tanggal
        'created_at',    // Kolom created_at
        'updated_at',    // Kolom updated_at
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
