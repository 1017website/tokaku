<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class RawMaterialLog extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'raw_material_id',
        'user_id',
        'qty_before',
        'qty_change',
        'qty_after',
        'price',
        'type',
        'note',
    ];

    protected $casts = [
        'qty_before' => 'integer',
        'qty_change' => 'integer',
        'qty_after'  => 'integer',
        'price'      => 'decimal:2',
    ];

    /** Nilai pergerakan (qty x harga satuan saat itu), selalu positif. */
    public function getValueAttribute(): float
    {
        return abs((float) $this->qty_change) * (float) $this->price;
    }

    public function rawMaterial()
    {
        return $this->belongsTo(RawMaterial::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'in'         => 'Masuk',
            'out'        => 'Keluar',
            'adjustment' => 'Penyesuaian',
            default      => ucfirst($this->type),
        };
    }
}
