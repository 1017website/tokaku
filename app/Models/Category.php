<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'name', 'is_active', 'type', 'starts_at', 'ends_at', 'sort_order', 'is_pinned', 'schedule_days', 'pinned_targets'];

    protected $casts = [
        'is_active'      => 'boolean',
        'is_pinned'      => 'boolean',
        'schedule_days'  => 'array',
        'pinned_targets' => 'array',
        'starts_at'      => 'datetime',
        'ends_at'        => 'datetime',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Urutkan kategori sesuai sort_order (kecil duluan), lalu nama.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Apakah kategori dijadwalkan tampil pada hari tertentu (mis. hari ini)?
     * schedule_days kosong/null = tampil setiap hari.
     *
     * @param  int|null  $isoDay  1=Senin ... 7=Minggu. Default: hari ini.
     */
    public function isScheduledOn(?int $isoDay = null): bool
    {
        $days = $this->schedule_days;

        if (empty($days)) {
            return true; // tanpa jadwal = selalu tampil
        }

        $isoDay = $isoDay ?? (int) now()->isoWeekday();

        return in_array($isoDay, array_map('intval', $days), true);
    }

    /**
     * Apakah kategori sedang berlaku (bisa dipilih di kasir).
     * - regular: selalu berlaku (selama lolos jadwal hari).
     * - promo/bundling: cek masa berlaku. Null = tanpa batas.
     * Kategori pinned ("tetap") mengabaikan jadwal hari — selalu tampil.
     */
    public function isAvailable(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        // Jadwal hari hanya membatasi kategori non-pinned.
        if (!$this->is_pinned && !$this->isScheduledOn()) {
            return false;
        }

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
     * Scope: hanya kategori yang sedang berlaku (cek is_active + masa berlaku promo/bundling).
     * Jadwal hari (schedule_days) TIDAK difilter di SQL — disimpan sebagai JSON,
     * jadi difilter di level PHP via ->filter(fn => $c->isAvailable()).
     */
    public function scopeAvailable($query)
    {
        $now = now();

        return $query->where('is_active', true)->where(function ($q) use ($now) {
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
