<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\AppSetting;

return new class extends Migration
{
    public function up(): void
    {
        // Rekening tujuan transfer (1 rekening tetap, diatur superadmin di Pengaturan).
        foreach ([
            'bank_name'       => null, // mis. "BCA"
            'bank_account_no' => null, // nomor rekening
            'bank_account_name' => null, // atas nama
        ] as $key => $val) {
            AppSetting::firstOrCreate(['key' => $key], ['value' => $val]);
        }
    }

    public function down(): void
    {
        AppSetting::whereIn('key', ['bank_name','bank_account_no','bank_account_name'])->delete();
    }
};
