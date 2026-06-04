<?php

/**
 * Daftar modul yang bisa diberi akses ke user (kasir/admin).
 * key = nama permission yang disimpan di kolom users.permissions
 * Dipakai untuk: form checkbox, middleware permission, dan filter sidebar.
 *
 * Owner SELALU punya akses ke semua modul (di-bypass di User::hasAccess).
 */

return [
    'modules' => [
        'kasir'      => 'Kasir',
        'produk'     => 'Produk',
        'kategori'   => 'Kategori',
        'laporan'    => 'Laporan',
        'stok'       => 'Stok',
        'pelanggan'  => 'Pelanggan',
        'promo'      => 'Promo & Diskon',
        'pengeluaran'=> 'Pengeluaran',
        'hutang'     => 'Hutang Piutang',
        'shift'      => 'Shift Kasir',
    ],

    // Default permission saat membuat user baru per role bawaan.
    'defaults' => [
        'admin'   => ['kasir', 'produk', 'kategori', 'laporan', 'stok', 'pelanggan', 'promo', 'pengeluaran', 'hutang', 'shift'],
        'cashier' => ['kasir', 'pelanggan', 'shift'],
    ],
];
