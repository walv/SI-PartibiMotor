<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
    ];



    public function saleServiceDetails()
{
    return $this->hasMany(SaleServiceDetail::class);
}
}
