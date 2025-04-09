<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseReport extends Model
{
    use HasFactory;
    protected $fillable = [
        'period_start',
        'period_end',
        'total_purchases',
        'total_items',
        'total_transactions',
        'most_purchased_product_id',
        'most_purchased_quantity',
    ];
}
