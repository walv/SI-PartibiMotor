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
