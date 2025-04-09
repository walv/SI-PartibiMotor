<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'category_id',
        'name',
        'brand',
        'cost_price',
        'selling_price',
        'stock', ];


    public function category()
{
    return $this->belongsTo(Category::class);
}

public function saleDetails()
{
    return $this->hasMany(SaleDetail::class);
}

public function purchaseDetails()
{
    return $this->hasMany(PurchaseDetail::class);
}

public function salesAggregates()
{
    return $this->hasMany(SalesAggregate::class);
}

public function forecastSES()
{
    return $this->hasMany(ForecastSES::class);
}

public function forecastDES()
{
    return $this->hasMany(ForecastDES::class);
}

public function forecastTES()
{
    return $this->hasMany(ForecastTES::class);
}

public function inventoryMovements()
{
    return $this->hasMany(InventoryMovement::class);
}
}
