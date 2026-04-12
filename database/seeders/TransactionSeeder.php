<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Tenant;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            app()->instance('currentTenant', $tenant);

            $cashier  = User::withoutGlobalScopes()
                ->where('tenant_id', $tenant->id)
                ->where('role', 'cashier')
                ->first();

            $products = Product::withoutGlobalScopes()
                ->where('tenant_id', $tenant->id)
                ->get();

            // Buat 10 transaksi dummy per tenant
            for ($i = 0; $i < 10; $i++) {
                $selectedProducts = $products->random(rand(1, 3));

                $subtotal = 0;
                $items    = [];

                foreach ($selectedProducts as $product) {
                    $qty      = rand(1, 3);
                    $itemTotal = $product->price * $qty;
                    $subtotal += $itemTotal;

                    $items[] = [
                        'product_id'   => $product->id,
                        'product_name' => $product->name,
                        'unit_price'   => $product->price,
                        'quantity'     => $qty,
                        'subtotal'     => $itemTotal,
                    ];
                }

                $discount   = 0;
                $tax        = 0;
                $total      = $subtotal - $discount + $tax;
                $paidAmount = ceil($total / 1000) * 1000;

                $transaction = Transaction::create([
                    'tenant_id'      => $tenant->id,
                    'user_id'        => $cashier->id,
                    'invoice_no'     => Transaction::generateInvoiceNo($tenant->id),
                    'subtotal'       => $subtotal,
                    'discount'       => $discount,
                    'tax'            => $tax,
                    'total'          => $total,
                    'paid_amount'    => $paidAmount,
                    'change_amount'  => $paidAmount - $total,
                    'payment_method' => collect(['cash', 'qris', 'transfer'])->random(),
                    'notes'          => null,
                    'created_at'     => now()->subDays(rand(0, 30)),
                ]);

                foreach ($items as $item) {
                    TransactionItem::create(array_merge(
                        $item,
                        ['transaction_id' => $transaction->id]
                    ));
                }
            }
        }

        app()->forgetInstance('currentTenant');
    }
}
