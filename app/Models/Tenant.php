<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $fillable = [
        'name',
        'subdomain',
        'phone',
        'logo_path',
        'address',
        'business_type',
        'owner_name',
        'owner_email',
        'status',
        'trial_ends_at',
        'approved_at',
        'rejected_at',
        'reject_reason',
        'tax_enabled',
        'tax_rate',
        'tax_name',
        'print_mode',
        'initial_capital',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'tax_enabled' => 'boolean',
        'tax_rate' => 'decimal:2',
        'initial_capital' => 'decimal:2',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    public function invoices()
    {
        return $this->hasMany(PaymentInvoice::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isActive(): bool
    {
        return $this->status === 'active' ||
            ($this->status === 'trial' && $this->trial_ends_at && $this->trial_ends_at->isFuture());
    }

    public function isTrialExpired(): bool
    {
        return $this->status === 'trial' && (!$this->trial_ends_at || $this->trial_ends_at->isPast());
    }

    /**
     * Sisa hari trial (dibulatkan ke atas). 0 jika sudah lewat / tidak ada.
     */
    public function trialDaysLeft(): int
    {
        if (!$this->trial_ends_at || $this->trial_ends_at->isPast()) {
            return 0;
        }

        return (int) ceil(now()->diffInDays($this->trial_ends_at, false));
    }

    /**
     * Label trial untuk ditampilkan, mis. "13 hari lagi" / "Trial habis".
     */
    public function trialLabel(): string
    {
        if (!$this->trial_ends_at) {
            return 'Tanpa batas';
        }

        if ($this->trial_ends_at->isPast()) {
            return 'Trial habis';
        }

        $days = $this->trialDaysLeft();

        return $days <= 0
            ? 'Berakhir hari ini'
            : $days . ' hari lagi';
    }
}
