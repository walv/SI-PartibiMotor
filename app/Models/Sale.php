<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\SaleDetail;

class Sale extends Model
{
    use HasFactory;

    // 
    protected $fillable = [
        'invoice_number',
        'date',
        'customer_name',
        'service_price',
        'total_price',
        'user_id'
    ];

    protected $casts = [
        'date' => 'datetime',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

  
    public function saleDetails()
    {
        return $this->hasMany(SaleDetail::class);
    }
}
