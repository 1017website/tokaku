<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class RawMaterial extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'unit',
        'price',
        'stock',
        'low_stock_alert',
        'note',
        'is_active',
    ];

    protected $casts = [
        'price'           => 'decimal:2',
        'stock'           => 'integer',
        'low_stock_alert' => 'integer',
        'is_active'       => 'boolean',
    ];

    public function logs()
    {
        return $this->hasMany(RawMaterialLog::class);
    }

    /** Nilai persediaan saat ini (stok x harga satuan). */
    public function stockValue(): float
    {
        return (float) $this->stock * (float) $this->price;
    }

    public static function rupiah($value): string
    {
        return 'Rp ' . number_format((float) $value, 0, ',', '.');
    }

    public function isLowStock(): bool
    {
        return $this->low_stock_alert > 0 && $this->stock <= $this->low_stock_alert;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /** Daftar satuan umum untuk dropdown. */
    public static function units(): array
    {
        return ['pcs', 'kg', 'gram', 'liter', 'ml', 'pack', 'box', 'lusin', 'ikat', 'porsi'];
    }
}
