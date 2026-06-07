<?php

return [
    'base_domain' => env('TOKAKU_BASE_DOMAIN', 'tokaku.1017studios.id'),

    // Batas maksimal ANGGOTA TIM di luar owner (admin/kasir).
    // Total user per toko = max_users + 1 (owner). Default 3 anggota + owner = 4.
    'max_users' => env('TOKAKU_MAX_USERS', 3),

    // Durasi default masa trial (hari) saat tenant baru dibuat.
    'trial_days' => env('TOKAKU_TRIAL_DAYS', 14),
];
