<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesAggregate extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'period',      // Menyimpan periode sebagai string dalam format Y-m
        'total_sales',
        'total_price',
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
