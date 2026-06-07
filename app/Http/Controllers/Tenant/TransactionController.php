<?php
namespace App\Http\Controllers\Tenant;
use App\Exports\TransactionsExport;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Debt;
use App\Models\Promo;
use App\Models\Product;
use App\Models\Shift;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Services\ReceiptEscposService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class TransactionController extends Controller {

    public function index() {
        $tenant  = app('currentTenant');
        $products = Product::active()->with('category')->orderBy('name')->get();
        $categories = Category::whereHas('products', fn($q) => $q->active())->orderBy('name')->get();
        $customers = Customer::where('tenant_id', $tenant->id)->where('is_active', true)->orderBy('name')->get();
        $topProductIds = TransactionItem::select('product_id', DB::raw('SUM(quantity) as total_qty'))
            ->whereNotNull('product_id')
            ->whereHas('transaction', fn($q) => $q->where('tenant_id', $tenant->id))
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit(12)
            ->pluck('product_id');
        $topProducts = Product::active()->with('category')->whereIn('id', $topProductIds)->get()->sortBy(function ($product) use ($topProductIds) {
            return $topProductIds->search($product->id);
        })->values();
        $promos   = Promo::where('tenant_id', $tenant->id)->where('is_active', true)->get()->filter(fn($p)=>$p->isValid());
        $activeShift = Shift::with('user')->where('tenant_id', $tenant->id)
            ->where('user_id', auth()->id())->whereNull('closed_at')->latest()->first();
        $taxEnabled  = $tenant->tax_enabled ?? false;
        $taxRate     = $tenant->tax_rate ?? 11;
        $taxName     = $tenant->tax_name ?? 'PPN';

        return view('tenant.kasir.index', compact('products','categories','customers','topProducts','promos','activeShift','taxEnabled','taxRate','taxName'));
    }

    public function proses(Request $request) {
        $request->validate([
            'items'          => 'required|array|min:1',
            'items.*.id'     => 'required|exists:products,id',
            'items.*.qty'    => 'required|integer|min:1',
            'paid_amount'    => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,qris,transfer,other',
            'payment_status' => 'nullable|in:paid,debt',
            'discount'       => 'nullable|numeric|min:0',
            'promo_id'       => 'nullable|exists:promos,id',
            'customer_id'    => 'nullable|exists:customers,id',
            'customer_name'  => 'nullable|string|max:255',  // hutang tanpa data pelanggan
            'customer_phone' => 'nullable|string|max:20',
            'tax_rate'       => 'nullable|numeric|min:0|max:100',
            'notes'          => 'nullable|string|max:255',
        ]);

        $transactionId = null;
        $invoiceNo = null;
        $stocks = [];

        DB::transaction(function () use ($request, &$transactionId, &$invoiceNo, &$stocks) {
            $tenant   = app('currentTenant');
            $subtotal = 0;
            $items    = [];

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['id']);
                if ($product->stock < $item['qty']) throw new \Exception("Stok {$product->name} tidak mencukupi.");
                $itemSubtotal = $product->price * $item['qty'];
                $subtotal    += $itemSubtotal;
                $items[] = ['product_id'=>$product->id,'product_name'=>$product->name,'unit_price'=>$product->price,'quantity'=>$item['qty'],'subtotal'=>$itemSubtotal];
                $product->decrement('stock', $item['qty']);
                $stocks[$product->id] = $product->stock; // stok terbaru setelah dikurangi
            }

            $discount      = $request->discount ?? 0;
            $taxRate       = $request->tax_rate ?? 0;
            $afterDiscount = $subtotal - $discount;
            $tax           = $taxRate > 0 ? round($afterDiscount * $taxRate / 100) : 0;
            $total         = $afterDiscount + $tax;
            $paymentStatus = $request->payment_status ?? 'paid';

            // Shift aktif
            $shift = Shift::where('tenant_id', $tenant->id)
                ->where('user_id', auth()->id())->whereNull('closed_at')->latest()->first();

            $transaction = Transaction::create([
                'tenant_id'      => $tenant->id,
                'user_id'        => auth()->id(),
                'customer_id'    => $request->customer_id,
                'shift_id'       => $shift?->id,
                'promo_id'       => $request->promo_id,
                'invoice_no'     => Transaction::generateInvoiceNo($tenant->id),
                'subtotal'       => $subtotal,
                'discount'       => $discount,
                'tax'            => $tax,
                'tax_rate'       => $taxRate,
                'total'          => $total,
                'paid_amount'    => $paymentStatus === 'debt' ? 0 : $request->paid_amount,
                'change_amount'  => $paymentStatus === 'debt' ? 0 : max(0, $request->paid_amount - $total),
                'payment_method' => $request->payment_method,
                'payment_status' => $paymentStatus,
                'notes'          => $request->notes,
            ]);

            foreach ($items as $item) {
                TransactionItem::create(array_merge($item, ['transaction_id'=>$transaction->id]));
            }

            // Update data customer
            if ($request->customer_id) {
                Customer::where('id', $request->customer_id)->increment('total_transactions');
                Customer::where('id', $request->customer_id)->increment('total_spent', $total);
            }

            // Catat hutang jika bayar nanti
            if ($paymentStatus === 'debt') {
                Debt::create([
                    'tenant_id'      => $tenant->id,
                    'transaction_id' => $transaction->id,
                    'customer_id'    => $request->customer_id,
                    'customer_name'  => $request->customer_name ?? ($request->customer_id ? Customer::find($request->customer_id)?->name : 'Pelanggan'),
                    'customer_phone' => $request->customer_phone ?? ($request->customer_id ? Customer::find($request->customer_id)?->phone : null),
                    'amount'         => $total,
                    'paid_amount'    => 0,
                    'status'         => 'unpaid',
                ]);
            }

            $transactionId = $transaction->id;
            $invoiceNo = $transaction->invoice_no;
        });

        return response()->json(['success'=>true,'transaction_id'=>$transactionId,'invoice_no'=>$invoiceNo,'stocks'=>$stocks,'message'=>'Transaksi berhasil.']);
    }

    public function struk(int $id) {
        $transaction = Transaction::with(['items','user','user.tenant','customer'])
            ->where('tenant_id', app('currentTenant')->id)->findOrFail($id);
        return view('tenant.kasir.struk', compact('transaction'));
    }

    public function strukPdf(int $id) {
        $transaction = Transaction::with(['items','user','user.tenant','customer'])
            ->where('tenant_id', app('currentTenant')->id)->findOrFail($id);
        $pdf = Pdf::loadView('tenant.kasir.struk_pdf', compact('transaction'))
            ->setPaper([0,0,164.41,800],'portrait');
        return $pdf->download("struk-{$transaction->invoice_no}.pdf");
    }

    /**
     * Perintah ESC/POS mentah (base64) untuk QZ Tray / RawBT.
     * format=raw  -> string byte langsung (untuk RawBT intent)
     * default     -> JSON { base64 } untuk QZ Tray
     */
    public function escpos(int $id, Request $request) {
        $transaction = Transaction::with(['items','user','user.tenant','customer'])
            ->where('tenant_id', app('currentTenant')->id)->findOrFail($id);

        $appSettings = ['app_name' => \App\Models\AppSetting::getValue('app_name', 'Tokaku')];

        // Buka laci default aktif; bisa dimatikan via ?drawer=0
        $openDrawer = $request->query('drawer', '1') !== '0';
        $raw = app(ReceiptEscposService::class)->build($transaction, $appSettings, $openDrawer);

        if ($request->query('format') === 'raw') {
            return response($raw, 200, ['Content-Type' => 'application/octet-stream']);
        }

        return response()->json([
            'invoice' => $transaction->invoice_no,
            'base64'  => base64_encode($raw),
        ]);
    }

    /**
     * Teks struk + nomor tujuan untuk dikirim via WhatsApp.
     */
    public function whatsapp(int $id) {
        $transaction = Transaction::with(['items','user','user.tenant','customer'])
            ->where('tenant_id', app('currentTenant')->id)->findOrFail($id);

        $appSettings = ['app_name' => \App\Models\AppSetting::getValue('app_name', 'Tokaku')];
        $text = app(ReceiptEscposService::class)->whatsappText($transaction, $appSettings);

        // Normalisasi nomor pelanggan ke format 62 (jika ada)
        $phone = $transaction->customer->phone ?? null;
        if ($phone) {
            $phone = preg_replace('/[^0-9]/', '', $phone);
            if (str_starts_with($phone, '0')) { $phone = '62' . substr($phone, 1); }
            elseif (str_starts_with($phone, '8')) { $phone = '62' . $phone; }
        }

        return response()->json([
            'invoice' => $transaction->invoice_no,
            'phone'   => $phone,   // null jika pelanggan tidak punya nomor
            'text'    => $text,
        ]);
    }

    public function laporan(Request $request) {
        $startDate = $request->start_date ?? now()->startOfMonth()->toDateString();
        $endDate   = $request->end_date   ?? now()->toDateString();
        $tenantId  = app('currentTenant')->id;

        $query = Transaction::with(['items','user','customer'])
            ->where('tenant_id', $tenantId)
            ->whereBetween('created_at',[$startDate.' 00:00:00',$endDate.' 23:59:59']);

        $transactions      = (clone $query)->orderByDesc('created_at')->paginate(20);
        $totalRevenue      = (clone $query)->sum('total');
        $totalDiscount     = (clone $query)->sum('discount');
        $totalTax          = (clone $query)->sum('tax');
        $totalTransactions = (clone $query)->count();
        $totalDebt         = (clone $query)->where('payment_status','debt')->count();

        $byPayment   = (clone $query)->selectRaw('payment_method, COUNT(*) as count, SUM(total) as total')->groupBy('payment_method')->get();
        $topProducts = TransactionItem::select('product_name',DB::raw('SUM(quantity) as total_qty'),DB::raw('SUM(subtotal) as total_revenue'))
            ->whereHas('transaction',fn($q)=>$q->where('tenant_id',$tenantId)->whereBetween('created_at',[$startDate.' 00:00:00',$endDate.' 23:59:59']))
            ->groupBy('product_name')->orderByDesc('total_qty')->limit(10)->get();
        $dailyRevenue = (clone $query)->selectRaw('DATE(created_at) as date, SUM(total) as total, COUNT(*) as count')->groupBy('date')->orderBy('date')->get();

        return view('tenant.laporan.index', compact('transactions','totalRevenue','totalDiscount','totalTax','totalTransactions','totalDebt','byPayment','topProducts','dailyRevenue','startDate','endDate'));
    }

    public function export(Request $request) {
        $startDate = $request->start_date ?? now()->startOfMonth()->toDateString();
        $endDate   = $request->end_date   ?? now()->toDateString();
        return Excel::download(new TransactionsExport($startDate,$endDate,app('currentTenant')->id), "laporan-{$startDate}-sd-{$endDate}.xlsx");
    }
}
