<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function index()
    {
        $products = Product::active()->with('category')->orderBy('name')->get();

        return view('tenant.kasir.index', compact('products'));
    }

    public function proses(Request $request)
    {
        $request->validate([
            'items'          => 'required|array|min:1',
            'items.*.id'     => 'required|exists:products,id',
            'items.*.qty'    => 'required|integer|min:1',
            'paid_amount'    => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,qris,transfer,other',
            'discount'       => 'nullable|numeric|min:0',
            'notes'          => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($request) {
            $tenant   = app('currentTenant');
            $subtotal = 0;
            $items    = [];

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['id']);

                if ($product->stock < $item['qty']) {
                    throw new \Exception("Stok {$product->name} tidak mencukupi.");
                }

                $itemSubtotal = $product->price * $item['qty'];
                $subtotal    += $itemSubtotal;

                $items[] = [
                    'product_id'   => $product->id,
                    'product_name' => $product->name,
                    'unit_price'   => $product->price,
                    'quantity'     => $item['qty'],
                    'subtotal'     => $itemSubtotal,
                ];

                // Kurangi stok
                $product->decrement('stock', $item['qty']);
            }

            $discount = $request->discount ?? 0;
            $tax      = 0;
            $total    = $subtotal - $discount + $tax;

            $transaction = Transaction::create([
                'tenant_id'      => $tenant->id,
                'user_id'        => auth()->id(),
                'invoice_no'     => Transaction::generateInvoiceNo($tenant->id),
                'subtotal'       => $subtotal,
                'discount'       => $discount,
                'tax'            => $tax,
                'total'          => $total,
                'paid_amount'    => $request->paid_amount,
                'change_amount'  => $request->paid_amount - $total,
                'payment_method' => $request->payment_method,
                'notes'          => $request->notes,
            ]);

            foreach ($items as $item) {
                TransactionItem::create(array_merge(
                    $item,
                    ['transaction_id' => $transaction->id]
                ));
            }

            session(['last_transaction_id' => $transaction->id]);
        });

        return response()->json([
            'success'        => true,
            'transaction_id' => session('last_transaction_id'),
            'message'        => 'Transaksi berhasil disimpan.',
        ]);
    }

    public function struk(int $id)
    {
        $transaction = Transaction::with(['items', 'user'])->findOrFail($id);

        return view('tenant.kasir.struk', compact('transaction'));
    }

    public function laporan(Request $request)
    {
        $startDate = $request->start_date ?? now()->startOfMonth()->toDateString();
        $endDate   = $request->end_date   ?? now()->toDateString();

        $transactions = Transaction::with(['items', 'user'])
            ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->orderByDesc('created_at')
            ->paginate(20);

        $totalRevenue = Transaction::whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->sum('total');

        return view('tenant.laporan.index', compact(
            'transactions',
            'totalRevenue',
            'startDate',
            'endDate',
        ));
    }

    public function export(Request $request)
    {
        // Placeholder — implementasi Laravel Excel di iterasi berikutnya
        return back()->with('info', 'Fitur export segera hadir.');
    }
}
