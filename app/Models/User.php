<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'password',
        'role',
        'permissions',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'is_active'         => 'boolean',
        'permissions'       => 'array',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['owner', 'admin']);
    }

    public function isCashier(): bool
    {
        return $this->role === 'cashier';
    }

    /**
     * Cek apakah user punya akses ke sebuah modul.
     * Owner & superadmin selalu full akses.
     */
    public function hasAccess(string $module): bool
    {
        if (in_array($this->role, ['owner', 'superadmin'], true)) {
            return true;
        }

        return in_array($module, (array) $this->permissions, true);
    }

    /**
     * Rute landing page sesuai akses user setelah login.
     * Owner → dashboard. Lainnya → modul pertama yang boleh diakses.
     */
    public function homeRoute(): string
    {
        if (in_array($this->role, ['owner', 'superadmin'], true)) {
            return route('tenant.dashboard');
        }

        // Urutan prioritas modul → nama route index-nya
        $map = [
            'kasir'       => 'tenant.kasir.index',
            'produk'      => 'tenant.products.index',
            'kategori'    => 'tenant.categories.index',
            'laporan'     => 'tenant.laporan.index',
            'stok'        => 'tenant.stok.index',
            'pelanggan'   => 'tenant.pelanggan.index',
            'promo'       => 'tenant.promo.index',
            'pengeluaran' => 'tenant.expenses.index',
            'hutang'      => 'tenant.hutang.index',
            'shift'       => 'tenant.shift.index',
            'bahan'       => 'tenant.bahan.index',
        ];

        foreach ($map as $module => $routeName) {
            if ($this->hasAccess($module)) {
                return route($routeName);
            }
        }

        // Tidak punya akses modul apapun
        return route('tenant.no-access');
    }
}
