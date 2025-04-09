<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesReport extends Model
{
    use HasFactory;
    protected $fillable = [
        'period_start',
        'period_end',
        'total_sales',
        'total_items',
        'total_transactions',
        'most_sold_product_id',
        'most_sold_quantity',
    ];

    public function mostSoldProduct()
{
    return $this->belongsTo(Product::class, 'most_sold_product_id');
}
}
