<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Service;

class SaleDetail extends Model
{
    use HasFactory;


    protected $fillable = [
        'sale_id',
        'product_id',
        'service_id',
        'quantity',
        'price',
        'subtotal'
    ];

    //
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    // // Relasi ke Product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    // Relasi ke Service
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
