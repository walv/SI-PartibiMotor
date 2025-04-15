<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForecastComparison extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'period_start',
        'period_end',
        'ses_mape',
    ];

    // Relasi dengan Product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
