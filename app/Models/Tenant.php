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
        'status',
        'trial_ends_at',
        'tax_enabled',
        'tax_rate',
        'tax_name',
        'initial_capital',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
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

    public function isActive(): bool
    {
        return $this->status === 'active' ||
            ($this->status === 'trial' && $this->trial_ends_at && $this->trial_ends_at->isFuture());
    }

    public function isTrialExpired(): bool
    {
        return $this->status === 'trial' && (!$this->trial_ends_at || $this->trial_ends_at->isPast());
    }
}
