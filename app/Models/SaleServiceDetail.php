<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleServiceDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'service_id',
        'quantity',
        'harga_satuan',
        'subtotal',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}