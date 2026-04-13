<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockLog extends Model
{
    protected $fillable = [
        'product_id', 'user_id',
        'qty_before', 'qty_change', 'qty_after',
        'type', 'note',
    ];

    protected $casts = [
        'qty_before' => 'integer',
        'qty_change' => 'integer',
        'qty_after'  => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'restock'     => 'Restock',
            'adjustment'  => 'Penyesuaian',
            'sale'        => 'Penjualan',
            'correction'  => 'Koreksi',
            default       => ucfirst($this->type),
        };
    }
}
