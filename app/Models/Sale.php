<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\SaleDetail;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'date',
        'customer_name',
        'total_price',
        'user_id'
    ];

    // Menambahkan properti $dates untuk kolom datetime lainnya
    protected $dates = [
        'date',          
        'created_at',   
        'updated_at',    
    ];

    protected $casts = [
        'date' => 'datetime',  // Mengonversi kolom date menjadi objek datetime
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function saleDetails()
    {
        return $this->hasMany(SaleDetail::class);
    }


// Relasi ke jasa
public function saleServiceDetails() {
    return $this->hasMany(SaleServiceDetail::class);
}

// Hitung total harga
public function getTotalAttribute() {
    return $this->saleDetails->sum('subtotal') + 
           $this->saleServiceDetails->sum('subtotal');
}
}
