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
        'user_id',
        'discount_amount', //tambah potongan harga/diskon
        'description' //tambah deskripsi
    ];

    // Menambahkan properti $dates untuk kolom datetime lainnya
    protected $dates = [
        'date',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'date' => 'datetime',  // Mengonversi kolom date menjadi objek datetime
        'discount_amount' => 'float' // Mengonversi diskon menjadi float
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
    public function saleServiceDetails()
    {
        return $this->hasMany(SaleServiceDetail::class);
    }

    // Hitung total harga
    public function getTotalAttribute()
    {
        $subtotal = $this->saleDetails->sum('subtotal') +
            $this->saleServiceDetails->sum('subtotal');
        return max(0, $subtotal - $this->discount_amount);
    }
    public function products()
    {
        return $this->hasMany(SaleDetail::class, 'sale_id');
    }

    public function services()
    {
        return $this->hasMany(SaleServiceDetail::class, 'sale_id');
    }
}
