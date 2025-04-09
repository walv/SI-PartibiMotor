<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_id',
        'date',
        'movement_type',
        'quantity',
        'reference_id',
        'reference_type',
        'stock_before',
        'stock_after',
    ];

    public function product()
{
    return $this->belongsTo(Product::class);
}
}
