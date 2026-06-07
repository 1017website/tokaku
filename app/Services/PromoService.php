<?php

namespace App\Services;

use App\Models\Promo;
use Illuminate\Support\Collection;

/**
 * Menghitung promo OTOMATIS berdasarkan isi keranjang.
 *
 * Server adalah sumber kebenaran: kasir hanya mengirim daftar item
 * (id + qty + harga), service ini yang menentukan promo mana yang
 * berlaku dan berapa total diskonnya. Dengan begitu nilai diskon
 * tidak bisa dimanipulasi dari sisi client.
 *
 * Aturan main saat ini (Tahap 1):
 * - Hanya SATU promo terbaik yang dipakai per transaksi (diskon terbesar),
 *   supaya tidak tumpang tindih dan mudah dipahami pelanggan.
 * - buyxgety : per produk tertentu (atau semua produk bila product_id null).
 * - percent  : diskon % dari subtotal, dibatasi max_discount.
 * - fixed    : potongan nominal tetap.
 * - min_transaction selalu dicek terhadap subtotal keranjang.
 */
class PromoService
{
    /**
     * @param  array  $items  Array of ['id'=>int, 'qty'=>int, 'price'=>float]
     * @return array{discount:int, promo_id:?int, promo_name:?string, label:?string}
     */
    public function bestPromo(array $items, int $tenantId): array
    {
        $subtotal = 0;
        foreach ($items as $i) {
            $subtotal += ((float) ($i['price'] ?? 0)) * ((int) ($i['qty'] ?? 0));
        }
        $subtotal = (int) round($subtotal);

        $promos = Promo::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get()
            ->filter(fn (Promo $p) => $p->isValid());

        $best = ['discount' => 0, 'promo_id' => null, 'promo_name' => null, 'label' => null];

        foreach ($promos as $promo) {
            if ($subtotal < (int) $promo->min_transaction) {
                continue;
            }

            $disc = $this->discountFor($promo, $items, $subtotal);

            if ($disc > $best['discount']) {
                $best = [
                    'discount'   => $disc,
                    'promo_id'   => $promo->id,
                    'promo_name' => $promo->name,
                    'label'      => $promo->type_label,
                ];
            }
        }

        return $best;
    }

    /**
     * Hitung diskon satu promo terhadap keranjang.
     */
    public function discountFor(Promo $promo, array $items, int $subtotal): int
    {
        return match ($promo->type) {
            'buyxgety' => $this->buyXGetY($promo, $items),
            'percent', 'fixed' => $promo->calculateDiscount($subtotal),
            default => 0,
        };
    }

    /**
     * Beli X Gratis Y.
     *
     * Untuk tiap produk yang memenuhi syarat, hitung berapa unit gratis
     * berdasarkan kelipatan (min_qty + free_qty), lalu kalikan harga
     * satuan produk tersebut. Item gratis = unit termurah (sama produk,
     * jadi harga satuan produk itu sendiri).
     *
     * Jika promo->product_id diisi → hanya berlaku untuk produk itu.
     * Jika null → berlaku untuk setiap produk di keranjang (per produk).
     */
    protected function buyXGetY(Promo $promo, array $items): int
    {
        $buy  = max(1, (int) $promo->min_qty);
        $free = max(1, (int) $promo->free_qty);
        $group = $buy + $free;

        $total = 0;

        foreach ($items as $i) {
            $pid = (int) ($i['id'] ?? 0);
            $qty = (int) ($i['qty'] ?? 0);
            $price = (float) ($i['price'] ?? 0);

            if ($promo->product_id && $pid !== (int) $promo->product_id) {
                continue;
            }
            if ($qty < $group) {
                continue;
            }

            $freeUnits = intdiv($qty, $group) * $free;
            $total += (int) round($freeUnits * $price);
        }

        return $total;
    }
}
