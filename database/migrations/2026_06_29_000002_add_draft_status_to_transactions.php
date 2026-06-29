<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Tambah status 'draft' untuk transaksi pesan-dulu-bayar-nanti (per nomor meja).
        // Draft TIDAK dihitung di laporan/omzet, tetapi stok sudah dikurangi saat draft dibuat.
        DB::statement("ALTER TABLE transactions MODIFY COLUMN status ENUM('completed','cancelled','draft') NOT NULL DEFAULT 'completed'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE transactions MODIFY COLUMN status ENUM('completed','cancelled') NOT NULL DEFAULT 'completed'");
    }
};
