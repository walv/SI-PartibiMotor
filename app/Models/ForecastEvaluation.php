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

    // Menambahkan properti $dates untuk kolom datetime lainnya
    protected $dates = [
        'date',          // Kolom date yang merupakan tanggal
        'created_at',    // Kolom created_at
        'updated_at',    // Kolom updated_at
    ];

    protected $casts = [
        'date' => 'datetime'  // Mengonversi kolom date menjadi objek datetime
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
