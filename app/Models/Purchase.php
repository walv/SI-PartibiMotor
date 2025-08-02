<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\PurchaseDetail;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'invoice_number',
        'supplier_name',
        'date',
        'total_price',
        'notes',          // Kolom baru untuk catatan
        'photo_struk',    // Kolom baru untuk foto struk
    ];

    // Menambahkan properti $dates untuk kolom datetime
    protected $dates = [
        'date',          // Kolom date yang merupakan tanggal
        'created_at',    // Kolom created_at
        'updated_at',    // Kolom updated_at
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function purchaseDetails()
    {
        return $this->hasMany(PurchaseDetail::class);
    }
}
