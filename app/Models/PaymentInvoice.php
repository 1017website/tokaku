<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentInvoice extends Model
{
    protected $fillable = [
        'tenant_id',
        'pricing_plan_id',
        'invoice_no',
        'base_amount',
        'unique_code',
        'total_amount',
        'duration_months',
        'status',
        'proof_path',
        'paid_at',
        'confirmed_at',
        'confirmed_by',
        'note',
    ];

    protected $casts = [
        'base_amount'    => 'integer',
        'unique_code'    => 'integer',
        'total_amount'   => 'integer',
        'duration_months'=> 'integer',
        'paid_at'        => 'datetime',
        'confirmed_at'   => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan()
    {
        return $this->belongsTo(PricingPlan::class, 'pricing_plan_id');
    }

    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    /**
     * Buat invoice baru untuk tenant + paket harga.
     * Kode unik 3 digit (1-999) digenerate sedemikian rupa agar total_amount
     * unik di antara invoice tenant yang masih menunggu pembayaran, supaya
     * superadmin mudah mencocokkan mutasi transfer.
     */
    public static function createForPlan(Tenant $tenant, PricingPlan $plan): self
    {
        $base = (int) round($plan->price);

        // Hindari tabrakan total_amount dengan invoice yang belum lunas.
        $existingTotals = self::where('tenant_id', $tenant->id)
            ->whereIn('status', ['unpaid', 'waiting_confirmation'])
            ->pluck('total_amount')
            ->all();

        do {
            $code  = random_int(1, 999);
            $total = $base + $code;
        } while (in_array($total, $existingTotals, true));

        return self::create([
            'tenant_id'       => $tenant->id,
            'pricing_plan_id' => $plan->id,
            'invoice_no'      => self::generateInvoiceNo(),
            'base_amount'     => $base,
            'unique_code'     => $code,
            'total_amount'    => $total,
            'duration_months' => $plan->duration_months,
            'status'          => 'unpaid',
        ]);
    }

    public static function generateInvoiceNo(): string
    {
        $prefix = 'INV-PAY-' . now()->format('Ymd') . '-';
        $last = self::where('invoice_no', 'like', $prefix . '%')
            ->orderByDesc('invoice_no')
            ->value('invoice_no');
        $number = $last ? (int) substr($last, -4) + 1 : 1;
        return $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'unpaid'               => 'Belum dibayar',
            'waiting_confirmation' => 'Menunggu konfirmasi',
            'paid'                 => 'Lunas',
            'rejected'             => 'Ditolak',
            'expired'              => 'Kedaluwarsa',
            default                => $this->status,
        };
    }
}
