<?php

return [
    'base_domain' => env('TOKAKU_BASE_DOMAIN', 'tokaku.1017studios.id'),

    // Batas maksimal user per tenant (sudah termasuk owner).
    'max_users' => env('TOKAKU_MAX_USERS', 3),
];
