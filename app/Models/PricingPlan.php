<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PricingPlan extends Model
{
    protected $fillable = [
        'name',
        'tagline',
        'duration_months',
        'original_price',
        'price',
        'is_popular',
        'is_active',
        'sort_order',
        'cta_label',
    ];

    protected $casts = [
        'original_price'  => 'decimal:2',
        'price'           => 'decimal:2',
        'is_popular'      => 'boolean',
        'is_active'       => 'boolean',
        'duration_months' => 'integer',
        'sort_order'      => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('duration_months');
    }

    /** Persentase diskon (0–100). 0 jika tidak ada potongan. */
    public function discountPercent(): int
    {
        if ($this->original_price <= 0 || $this->price >= $this->original_price) {
            return 0;
        }

        return (int) round(($this->original_price - $this->price) / $this->original_price * 100);
    }

    public function hasDiscount(): bool
    {
        return $this->original_price > 0 && $this->price < $this->original_price;
    }

    /** Harga efektif per bulan (setelah diskon). */
    public function pricePerMonth(): float
    {
        if ($this->duration_months <= 0) {
            return (float) $this->price;
        }

        return (float) $this->price / $this->duration_months;
    }

    /** Format ke Rupiah ringkas, mis. 1800000 -> "Rp 1.800.000". */
    public static function rupiah($value): string
    {
        return 'Rp ' . number_format((float) $value, 0, ',', '.');
    }

    public function priceLabel(): string
    {
        return self::rupiah($this->price);
    }

    public function originalPriceLabel(): string
    {
        return self::rupiah($this->original_price);
    }

    public function perMonthLabel(): string
    {
        return self::rupiah(round($this->pricePerMonth()));
    }

    /** Label periode, mis. "/bulan", "/6 bulan", "/tahun". */
    public function periodLabel(): string
    {
        return match ((int) $this->duration_months) {
            1       => '/bulan',
            12      => '/tahun',
            default => '/' . $this->duration_months . ' bulan',
        };
    }
}
