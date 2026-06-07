<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'name', 'type', 'starts_at', 'ends_at'];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at'   => 'datetime',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Apakah kategori sedang berlaku (bisa dipilih di kasir).
     * - regular: selalu berlaku.
     * - promo/bundling: cek masa berlaku. Null = tanpa batas.
     */
    public function isAvailable(): bool
    {
        if ($this->type === 'regular') {
            return true;
        }

        if ($this->starts_at && now()->lt($this->starts_at)) {
            return false;
        }
        if ($this->ends_at && now()->gt($this->ends_at)) {
            return false;
        }

        return true;
    }

    /**
     * Scope: hanya kategori yang sedang berlaku.
     * Regular selalu lolos; promo/bundling dicek terhadap masa berlaku.
     */
    public function scopeAvailable($query)
    {
        $now = now();

        return $query->where(function ($q) use ($now) {
            $q->where('type', 'regular')
                ->orWhere(function ($q2) use ($now) {
                    $q2->whereIn('type', ['promo', 'bundling'])
                        ->where(fn ($s) => $s->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
                        ->where(fn ($e) => $e->whereNull('ends_at')->orWhere('ends_at', '>=', $now));
                });
        });
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'promo'    => 'Promo',
            'bundling' => 'Bundling',
            default    => 'Regular',
        };
    }
}
