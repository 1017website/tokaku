<?php

namespace App\Services;

use App\Models\Transaction;

/**
 * Membangun perintah ESC/POS mentah untuk struk thermal 58mm (32 karakter / baris).
 * Output berupa string byte, dipakai QZ Tray (PC) maupun RawBT (Android).
 */
class ReceiptEscposService
{
    /** Lebar karakter untuk kertas 58mm font A. */
    private const WIDTH = 32;

    // ESC/POS control codes
    private const ESC = "\x1B";
    private const GS  = "\x1D";

    /**
     * Perintah buka laci kasir (drawer kick): ESC p m t1 t2.
     * m=0 -> pin 2 (paling umum). t1=25, t2=250 -> durasi pulse aman.
     * Dikirim di awal cetak agar laci terbuka bersamaan keluarnya struk.
     */
    private const DRAWER_KICK = self::ESC . "p\x00\x19\xFA";

    public function build(Transaction $transaction, array $appSettings = [], bool $openDrawer = false): string
    {
        $t      = $transaction;
        $tenant = $t->user->tenant ?? null;
        $appName = $appSettings['app_name'] ?? 'Tokaku';

        $out  = self::ESC . "@";          // init printer

        // Buka laci hanya untuk pembayaran tunai (lunas).
        if ($openDrawer && $t->payment_method === 'cash' && $t->payment_status !== 'debt') {
            $out .= self::DRAWER_KICK;
        }

        $out .= self::ESC . "t\x00";      // charset PC437 (aman untuk ASCII)

        // ---- Logo dinonaktifkan (uncomment untuk mengaktifkan kembali) ----
        // $logoRaster = $this->logoRaster($tenant);
        // if ($logoRaster !== '') {
        //     $out .= self::align('center') . $logoRaster;
        // }

        // ---- Header (center) ----
        $out .= self::align('center');
        $out .= self::boldDouble(($tenant->name ?? $appName)) . "\n";
        $out .= self::bold(false);

        if (!empty($tenant->address)) {
            foreach ($this->wrap($tenant->address, self::WIDTH) as $line) {
                $out .= $line . "\n";
            }
        }
        if (!empty($tenant->phone)) {
            $out .= $tenant->phone . "\n";
        }

        $out .= self::align('left');
        $out .= $this->line();

        // ---- Meta ----
        $out .= $this->twoCol('Invoice', $t->invoice_no);
        if (!empty($t->table_no)) { $out .= $this->twoCol('No. Meja', $t->table_no); }
        $out .= $this->twoCol('Kasir', $t->user->name ?? '-');
        $out .= $this->twoCol('Waktu', $t->created_at->format('d/m/y H:i'));
        $out .= $this->line();

        // ---- Items ----
        foreach ($t->items as $item) {
            $out .= self::bold(true) . $this->cut($item->product_name, self::WIDTH) . "\n" . self::bold(false);
            $qtyText = $item->quantity . ' x ' . $this->rupiah($item->unit_price, false);
            $out .= $this->twoCol($qtyText, $this->rupiah($item->subtotal, false));
        }
        $out .= $this->line();

        // ---- Totals ----
        $out .= $this->twoCol('Subtotal', 'Rp ' . $this->rupiah($t->subtotal, false));
        if ($t->discount > 0) {
            $out .= $this->twoCol('Diskon', '-Rp ' . $this->rupiah($t->discount, false));
        }
        if ($t->tax > 0) {
            $out .= $this->twoCol('Pajak (' . rtrim(rtrim(number_format($t->tax_rate, 2, ',', '.'), '0'), ',') . '%)', 'Rp ' . $this->rupiah($t->tax, false));
        }
        $out .= $this->line();

        $out .= self::bold(true);
        $out .= $this->twoCol('TOTAL', 'Rp ' . $this->rupiah($t->total, false));
        $out .= self::bold(false);
        $out .= $this->twoCol('Bayar (' . strtoupper($t->payment_method) . ')', 'Rp ' . $this->rupiah($t->paid_amount, false));
        $out .= $this->twoCol('Kembalian', 'Rp ' . $this->rupiah($t->change_amount, false));

        if ($t->payment_status === 'debt') {
            $out .= $this->line();
            $out .= self::align('center') . self::bold(true) . "** TRANSAKSI HUTANG **\n" . self::bold(false) . self::align('left');
        }

        $out .= $this->line();

        // ---- Footer ----
        $out .= self::align('center');
        $out .= "Terima kasih sudah berbelanja!\n";
        $out .= $appName . " - 1017studios.id\n";
        $out .= self::align('left');

        // Feed + potong kertas
        $out .= "\n\n\n";
        $out .= self::GS . "V\x01";  // partial cut

        return $out;
    }

    // ===== Helpers =====

    /**
     * Konversi logo toko ke raster ESC/POS (GS v 0), 1-bit hitam-putih.
     * Mengembalikan '' jika tidak ada logo / GD tidak tersedia.
     */
    private function logoRaster($tenant): string
    {
        if (empty($tenant->logo_path) || !function_exists('imagecreatefromstring')) {
            return '';
        }

        $path = storage_path('app/public/' . $tenant->logo_path);
        if (!is_file($path)) {
            return '';
        }

        $src = @imagecreatefromstring(@file_get_contents($path));
        if ($src === false) {
            return '';
        }

        // Resize ke lebar maks 200px (kelipatan 8), tinggi proporsional
        $maxW = 200;
        $ow = imagesx($src);
        $oh = imagesy($src);
        $w  = min($maxW, $ow);
        $w  = (int) (floor($w / 8) * 8);
        if ($w < 8) { $w = 8; }
        $h  = (int) round($oh * ($w / $ow));

        $img = imagecreatetruecolor($w, $h);
        $white = imagecolorallocate($img, 255, 255, 255);
        imagefilledrectangle($img, 0, 0, $w, $h, $white);
        imagecopyresampled($img, $src, 0, 0, 0, 0, $w, $h, $ow, $oh);
        imagedestroy($src);

        // Auto-crop baris putih di atas & bawah (hilangkan padding kosong dari file logo)
        $isDarkRow = function ($img, $w, $y) {
            for ($x = 0; $x < $w; $x++) {
                $rgb = imagecolorat($img, $x, $y);
                $lum = ((($rgb >> 16) & 0xFF) * 0.299) + ((($rgb >> 8) & 0xFF) * 0.587) + (($rgb & 0xFF) * 0.114);
                if ($lum < 128) { return true; }
            }
            return false;
        };
        $top = 0; $bottom = $h - 1;
        while ($top < $h && !$isDarkRow($img, $w, $top)) { $top++; }
        while ($bottom > $top && !$isDarkRow($img, $w, $bottom)) { $bottom--; }
        if ($top < $bottom) {
            $ch = $bottom - $top + 1;
            $cropped = imagecreatetruecolor($w, $ch);
            imagefilledrectangle($cropped, 0, 0, $w, $ch, $white);
            imagecopy($cropped, $img, 0, 0, 0, $top, $w, $ch);
            imagedestroy($img);
            $img = $cropped;
            $h   = $ch;
        }

        // 1-bit threshold -> bitmap
        $bytesPerRow = (int) ceil($w / 8);
        $raster = '';
        for ($y = 0; $y < $h; $y++) {
            for ($b = 0; $b < $bytesPerRow; $b++) {
                $byte = 0;
                for ($bit = 0; $bit < 8; $bit++) {
                    $x = $b * 8 + $bit;
                    if ($x < $w) {
                        $rgb = imagecolorat($img, $x, $y);
                        $r = ($rgb >> 16) & 0xFF;
                        $g = ($rgb >> 8) & 0xFF;
                        $bl = $rgb & 0xFF;
                        $lum = ($r * 0.299 + $g * 0.587 + $bl * 0.114);
                        if ($lum < 128) {           // gelap -> titik hitam
                            $byte |= (0x80 >> $bit);
                        }
                    }
                }
                $raster .= chr($byte);
            }
        }
        imagedestroy($img);

        $xL = $bytesPerRow & 0xFF;
        $xH = ($bytesPerRow >> 8) & 0xFF;
        $yL = $h & 0xFF;
        $yH = ($h >> 8) & 0xFF;

        // GS v 0 m xL xH yL yH [data]
        return self::GS . "v0" . chr(0) . chr($xL) . chr($xH) . chr($yL) . chr($yH) . $raster;
    }

    private function rupiah($value, bool $prefix = true): string
    {
        return ($prefix ? 'Rp ' : '') . number_format((float) $value, 0, ',', '.');
    }

    /**
     * Versi teks struk untuk dikirim via WhatsApp (plain text, tanpa kode ESC/POS).
     */
    public function whatsappText(Transaction $transaction, array $appSettings = []): string
    {
        $t      = $transaction;
        $tenant = $t->user->tenant ?? null;
        $appName = $appSettings['app_name'] ?? 'Tokaku';

        $lines = [];
        $lines[] = '*' . ($tenant->name ?? $appName) . '*';
        if (!empty($tenant->address)) { $lines[] = $tenant->address; }
        if (!empty($tenant->phone))   { $lines[] = $tenant->phone; }
        $lines[] = '--------------------------------';
        $lines[] = 'Invoice : ' . $t->invoice_no;
        if (!empty($t->table_no)) { $lines[] = 'Meja    : ' . $t->table_no; }
        $lines[] = 'Kasir   : ' . ($t->user->name ?? '-');
        $lines[] = 'Waktu   : ' . $t->created_at->format('d/m/y H:i');
        $lines[] = '--------------------------------';
        foreach ($t->items as $item) {
            $lines[] = $item->product_name;
            $lines[] = '  ' . $item->quantity . ' x ' . $this->rupiah($item->unit_price, false)
                     . ' = ' . $this->rupiah($item->subtotal, false);
        }
        $lines[] = '--------------------------------';
        $lines[] = 'Subtotal : Rp ' . $this->rupiah($t->subtotal, false);
        if ($t->discount > 0) { $lines[] = 'Diskon   : -Rp ' . $this->rupiah($t->discount, false); }
        if ($t->tax > 0)      { $lines[] = 'Pajak    : Rp ' . $this->rupiah($t->tax, false); }
        $lines[] = '*TOTAL    : Rp ' . $this->rupiah($t->total, false) . '*';
        $lines[] = 'Bayar (' . strtoupper($t->payment_method) . ') : Rp ' . $this->rupiah($t->paid_amount, false);
        $lines[] = 'Kembalian : Rp ' . $this->rupiah($t->change_amount, false);
        if ($t->payment_status === 'debt') { $lines[] = '_** TRANSAKSI HUTANG **_'; }
        $lines[] = '--------------------------------';
        $lines[] = 'Terima kasih sudah berbelanja!';
        $lines[] = $appName . ' - 1017studios.id';

        return implode("\n", $lines);
    }

    /** Baris kiri-kanan dalam 32 kolom. */
    private function twoCol(string $left, string $right): string
    {
        $space = self::WIDTH - mb_strlen($left) - mb_strlen($right);
        if ($space < 1) {
            // potong kiri jika kepanjangan
            $left  = $this->cut($left, self::WIDTH - mb_strlen($right) - 1);
            $space = self::WIDTH - mb_strlen($left) - mb_strlen($right);
            $space = max($space, 1);
        }
        return $left . str_repeat(' ', $space) . $right . "\n";
    }

    private function line(): string
    {
        return str_repeat('-', self::WIDTH) . "\n";
    }

    private function cut(string $text, int $max): string
    {
        return mb_strlen($text) > $max ? mb_substr($text, 0, $max) : $text;
    }

    /** Bungkus teks panjang menjadi beberapa baris. */
    private function wrap(string $text, int $max): array
    {
        return explode("\n", wordwrap($text, $max, "\n", true));
    }

    private static function align(string $mode): string
    {
        $map = ['left' => "\x00", 'center' => "\x01", 'right' => "\x02"];
        return self::ESC . "a" . ($map[$mode] ?? "\x00");
    }

    private static function bold(bool $on): string
    {
        return self::ESC . "E" . ($on ? "\x01" : "\x00");
    }

    /** Bold + double height untuk nama toko. */
    private static function boldDouble(string $text): string
    {
        return self::ESC . "E\x01" . self::GS . "!\x01" . $text . self::GS . "!\x00";
    }
}
