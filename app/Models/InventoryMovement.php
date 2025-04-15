<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    use HasFactory;

    // Kolom yang dapat diisi melalui mass assignment
    protected $fillable = [
        'product_id',
        'date',
        'movement_type',
        'quantity',
        'reference_id',
        'reference_type',
        'stock_before',
        'stock_after',
    ];

    // Menambahkan properti $dates untuk kolom datetime
    protected $dates = [
        'date',          // Kolom date yang merupakan tanggal
        'created_at',    // Kolom created_at
        'updated_at',    // Kolom updated_at
    ];

    /**
     * Relasi ke model Product.
     * Setiap InventoryMovement terkait dengan satu produk.
     */
    public function product()
    {
        return $this->belongsTo(Product::class); // Relasi ke Product
    }
    
    /**
     * Scope untuk mendapatkan pergerakan stok berdasarkan jenis pergerakan (in/out).
     *
     * @param $query
     * @param string $movementType
     * @return mixed
     */
    public function scopeMovementType($query, $movementType)
    {
        return $query->where('movement_type', $movementType);
    }

    /**
     * Menyimpan pergerakan stok ke dalam inventory.
     * 
     * @param array $data
     * @return InventoryMovement
     */
    public static function storeMovement($data)
    {
        // Pastikan untuk validasi data yang masuk
        return self::create([
            'product_id' => $data['product_id'],
            'date' => $data['date'],
            'movement_type' => $data['movement_type'],
            'quantity' => $data['quantity'],
            'reference_id' => $data['reference_id'],
            'reference_type' => $data['reference_type'],
            'stock_before' => $data['stock_before'],
            'stock_after' => $data['stock_after'],
        ]);
    }
}
