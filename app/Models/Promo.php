<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;
use Carbon\Carbon;

class Promo extends Model {
    use BelongsToTenant;
    protected $fillable = ['tenant_id','name','code','type','value','min_qty','free_qty','min_transaction','max_discount','product_id','starts_at','ends_at','is_active'];
    protected $casts = ['value'=>'float','min_transaction'=>'integer','max_discount'=>'integer','starts_at'=>'datetime','ends_at'=>'datetime','is_active'=>'boolean'];

    public function product() { return $this->belongsTo(Product::class); }

    public function isValid(): bool {
        if (!$this->is_active) return false;
        if ($this->starts_at && now()->lt($this->starts_at)) return false;
        if ($this->ends_at && now()->gt($this->ends_at)) return false;
        return true;
    }

    // Hitung diskon berdasarkan subtotal
    public function calculateDiscount(int $subtotal): int {
        if ($subtotal < $this->min_transaction) return 0;
        $disc = $this->type === 'percent'
            ? round($subtotal * $this->value / 100)
            : (int) $this->value;
        if ($this->max_discount > 0) $disc = min($disc, $this->max_discount);
        return min($disc, $subtotal);
    }

    public function getTypeLabelAttribute(): string {
        return match($this->type) {
            'percent'  => "Diskon {$this->value}%",
            'fixed'    => 'Diskon Rp '.number_format($this->value,0,',','.'),
            'buyxgety' => "Beli {$this->min_qty} Gratis {$this->free_qty}",
            default    => $this->type,
        };
    }
}
