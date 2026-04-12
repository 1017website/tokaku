<?php

namespace App\Http\Controllers\Tenant;

use App\Exports\TransactionsExport;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

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

        $transactionId = null;

        DB::transaction(function () use ($request, &$transactionId) {
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

                $product->decrement('stock', $item['qty']);
            }

            $discount = $request->discount ?? 0;
            $total    = $subtotal - $discount;

            $transaction = Transaction::create([
                'tenant_id'      => $tenant->id,
                'user_id'        => auth()->id(),
                'invoice_no'     => Transaction::generateInvoiceNo($tenant->id),
                'subtotal'       => $subtotal,
                'discount'       => $discount,
                'tax'            => 0,
                'total'          => $total,
                'paid_amount'    => $request->paid_amount,
                'change_amount'  => $request->paid_amount - $total,
                'payment_method' => $request->payment_method,
                'notes'          => $request->notes,
            ]);

            foreach ($items as $item) {
                TransactionItem::create(array_merge($item, [
                    'transaction_id' => $transaction->id,
                ]));
            }

            $transactionId = $transaction->id;
        });

        return response()->json([
            'success'        => true,
            'transaction_id' => $transactionId,
            'message'        => 'Transaksi berhasil disimpan.',
        ]);
    }

    public function struk(int $id)
    {
        $transaction = Transaction::with(['items', 'user', 'user.tenant'])
            ->where('tenant_id', app('currentTenant')->id)
            ->findOrFail($id);

        return view('tenant.kasir.struk', compact('transaction'));
    }

    public function strukPdf(int $id)
    {
        $transaction = Transaction::with(['items', 'user', 'user.tenant'])
            ->where('tenant_id', app('currentTenant')->id)
            ->findOrFail($id);

        $pdf = Pdf::loadView('tenant.kasir.struk_pdf', compact('transaction'))
            ->setPaper([0, 0, 226.77, 500], 'portrait'); // 80mm thermal

        return $pdf->download("struk-{$transaction->invoice_no}.pdf");
    }

    public function laporan(Request $request)
    {
        $startDate = $request->start_date ?? now()->startOfMonth()->toDateString();
        $endDate   = $request->end_date   ?? now()->toDateString();
        $tenantId  = app('currentTenant')->id;

        $query = Transaction::with(['items', 'user'])
            ->where('tenant_id', $tenantId)
            ->whereBetween('created_at', [
                $startDate . ' 00:00:00',
                $endDate   . ' 23:59:59',
            ]);

        $transactions = (clone $query)->orderByDesc('created_at')->paginate(20);
        $totalRevenue = (clone $query)->sum('total');
        $totalDiscount = (clone $query)->sum('discount');
        $totalTransactions = (clone $query)->count();

        // Summary per metode bayar
        $byPayment = (clone $query)
            ->selectRaw('payment_method, COUNT(*) as count, SUM(total) as total')
            ->groupBy('payment_method')
            ->get();

        // Produk terlaris
        $topProducts = TransactionItem::query()
            ->select('product_name', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_revenue'))
            ->whereHas('transaction', function ($q) use ($tenantId, $startDate, $endDate) {
                $q->where('tenant_id', $tenantId)
                  ->whereBetween('created_at', [
                      $startDate . ' 00:00:00',
                      $endDate   . ' 23:59:59',
                  ]);
            })
            ->groupBy('product_name')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();

        // Omzet per hari (untuk chart)
        $dailyRevenue = (clone $query)
            ->selectRaw('DATE(created_at) as date, SUM(total) as total, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('tenant.laporan.index', compact(
            'transactions',
            'totalRevenue',
            'totalDiscount',
            'totalTransactions',
            'byPayment',
            'topProducts',
            'dailyRevenue',
            'startDate',
            'endDate',
        ));
    }

    public function export(Request $request)
    {
        $startDate = $request->start_date ?? now()->startOfMonth()->toDateString();
        $endDate   = $request->end_date   ?? now()->toDateString();
        $tenantId  = app('currentTenant')->id;

        $filename = "laporan-{$startDate}-sd-{$endDate}.xlsx";

        return Excel::download(
            new TransactionsExport($startDate, $endDate, $tenantId),
            $filename
        );
    }
}
