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
use App\Models\StockLog;
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

        // Kategori yang sedang berlaku: lolos is_active + masa berlaku (SQL),
        // lalu disaring jadwal hari di PHP (schedule_days disimpan JSON).
        // Kategori pinned ("tetap") selalu lolos jadwal hari.
        $availableCategories = Category::available()->get()
            ->filter(fn($c) => $c->isAvailable());
        $availableCategoryIds = $availableCategories->pluck('id');

        // ID kategori pinned ("Menu Tetap") yang berlaku.
        $pinnedCategories = $availableCategories->where('is_pinned', true);

        // Produk yang boleh dijual: tanpa kategori, ATAU kategorinya sedang berlaku.
        $products = Product::active()->with('category')
            ->where(fn($q) => $q->whereNull('category_id')->orWhereIn('category_id', $availableCategoryIds))
            ->orderBy('name')->get();

        // Peta produk pinned -> daftar ID kategori tab tempat ia ikut tampil.
        // Diambil dari pinned_targets kategori tetap (kosong = tampil di semua tab).
        // key = product_id, value = array category_id target (kosong = semua).
        $pinnedProductTargets = [];
        foreach ($products as $p) {
            $cat = $pinnedCategories->firstWhere('id', $p->category_id);
            if ($cat) {
                $pinnedProductTargets[$p->id] = array_map('intval', $cat->pinned_targets ?? []);
            }
        }

        // Tab kategori: hanya yang berlaku & punya produk aktif, diurutkan sesuai sort_order.
        // Kategori berbasis jadwal hari (punya schedule_days) TIDAK ditampilkan sebagai tab;
        // produknya tetap dapat diakses lewat tab "Semua Produk".
        $categories = $availableCategories
            ->filter(fn($c) => empty($c->schedule_days))
            ->filter(fn($c) => $c->products()->active()->exists())
            ->sortBy([['sort_order', 'asc'], ['name', 'asc']])
            ->values();
        $customers = Customer::where('tenant_id', $tenant->id)->where('is_active', true)->orderBy('name')->get();
        $topProductIds = TransactionItem::select('product_id', DB::raw('SUM(quantity) as total_qty'))
            ->whereNotNull('product_id')
            ->whereHas('transaction', fn($q) => $q->where('tenant_id', $tenant->id))
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit(12)
            ->pluck('product_id');
        $topProducts = Product::active()->with('category')
            ->whereIn('id', $topProductIds)
            ->where(fn($q) => $q->whereNull('category_id')->orWhereIn('category_id', $availableCategoryIds))
            ->get()->sortBy(function ($product) use ($topProductIds) {
            return $topProductIds->search($product->id);
        })->values();
        $promos   = Promo::where('tenant_id', $tenant->id)->where('is_active', true)->get()->filter(fn($p)=>$p->isValid());
        $activeShift = Shift::with('user')->where('tenant_id', $tenant->id)
            ->where('user_id', auth()->id())->whereNull('closed_at')->latest()->first();
        $taxEnabled  = $tenant->tax_enabled ?? false;
        $taxRate     = $tenant->tax_rate ?? 11;
        $taxName     = $tenant->tax_name ?? 'PPN';

        // Draft tersimpan (pesan belum dibayar), dikelompokkan per nomor meja.
        $drafts = Transaction::with('items')
            ->where('tenant_id', $tenant->id)
            ->draft()
            ->orderBy('table_no')
            ->orderByDesc('created_at')
            ->get();

        // Bentuk ringkas untuk JS (hindari closure di dalam @json pada Blade).
        $draftsJson = $drafts->map(fn($d) => [
            'id'       => $d->id,
            'table_no' => $d->table_no,
            'total'    => (float) $d->subtotal,
            'count'    => (int) $d->items->sum('quantity'),
            'created'  => $d->created_at->format('H:i'),
            'items'    => $d->items->map(fn($it) => [
                'name'  => $it->product_name,
                'qty'   => (int) $it->quantity,
                'price' => (float) $it->unit_price,
            ])->values(),
        ])->values();

        return view('tenant.kasir.index', compact('products','categories','customers','topProducts','promos','activeShift','taxEnabled','taxRate','taxName','pinnedProductTargets','drafts','draftsJson'));
    }

    public function proses(Request $request) {
        $this->validateTransaction($request);

        try {
            [$transactionId, $invoiceNo, $stocks] = $this->persistTransaction($request, 'completed');
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json(['success'=>true,'transaction_id'=>$transactionId,'invoice_no'=>$invoiceNo,'stocks'=>$stocks,'message'=>'Transaksi berhasil.']);
    }

    /**
     * Aturan validasi bersama untuk proses transaksi maupun simpan draft.
     */
    private function validateTransaction(Request $request): void
    {
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
            'customer_name'  => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'table_no'       => 'nullable|string|max:20',
            'tax_rate'       => 'nullable|numeric|min:0|max:100',
            'notes'          => 'nullable|string|max:255',
        ]);
    }

    /**
     * Inti penyimpanan transaksi (dipakai untuk completed maupun draft).
     * Stok DIKURANGI di sini untuk kedua status — draft pun mengurangi stok.
     * Untuk draft, pembayaran/hutang TIDAK dicatat (paid_amount = 0, tanpa Debt).
     *
     * @return array{0:int,1:?string,2:array}  [transactionId, invoiceNo, stocks]
     */
    private function persistTransaction(Request $request, string $status): array
    {
        $isDraft = $status === 'draft';
        $transactionId = null;
        $invoiceNo = null;
        $stocks = [];

        // Retry ringan untuk duplikasi invoice_no (race condition unique tenant+invoice).
        $attempt = 0;
        do {
            $attempt++;
            $stocks = [];
            try {
                DB::transaction(function () use ($request, $status, $isDraft, &$transactionId, &$invoiceNo, &$stocks) {
                    $tenant   = app('currentTenant');
                    $subtotal = 0;
                    $items    = [];

                    foreach ($request->items as $item) {
                        $product = Product::with('category')->findOrFail($item['id']);
                        if ($product->category && !$product->category->isAvailable()) {
                            throw new \Exception("Kategori \"{$product->category->name}\" sudah tidak berlaku. Muat ulang halaman kasir.");
                        }
                        if ($product->stock < $item['qty']) throw new \Exception("Stok {$product->name} tidak mencukupi.");
                        $itemSubtotal = $product->price * $item['qty'];
                        $subtotal    += $itemSubtotal;
                        $items[] = ['product_id'=>$product->id,'product_name'=>$product->name,'unit_price'=>$product->price,'quantity'=>$item['qty'],'subtotal'=>$itemSubtotal];
                        $product->decrement('stock', $item['qty']);
                        $stocks[$product->id] = $product->stock;
                    }

                    $discount = $request->discount ?? 0;
                    $promoId  = null;

                    // Promo otomatis dihitung server (untuk draft sekalipun, agar konsisten).
                    $promoItems = array_map(function ($it) {
                        return ['id' => (int) $it['product_id'], 'qty' => (int) $it['quantity'], 'price' => (float) $it['unit_price']];
                    }, $items);

                    $best = app(\App\Services\PromoService::class)->bestPromo($promoItems, $tenant->id);
                    if ($best['discount'] > 0 && $best['discount'] >= $discount) {
                        $discount = $best['discount'];
                        $promoId  = $best['promo_id'];
                    }

                    $taxRate       = $request->tax_rate ?? 0;
                    $afterDiscount = max(0, $subtotal - $discount);
                    $tax           = $taxRate > 0 ? round($afterDiscount * $taxRate / 100) : 0;
                    $total         = $afterDiscount + $tax;
                    $paymentStatus = $isDraft ? 'paid' : ($request->payment_status ?? 'paid');

                    $shift = Shift::where('tenant_id', $tenant->id)
                        ->where('user_id', auth()->id())->whereNull('closed_at')->latest()->first();

                    $transaction = Transaction::create([
                        'tenant_id'      => $tenant->id,
                        'user_id'        => auth()->id(),
                        'customer_id'    => $request->customer_id,
                        'shift_id'       => $shift?->id,
                        'promo_id'       => $promoId,
                        'invoice_no'     => Transaction::generateInvoiceNo($tenant->id),
                        'table_no'       => $request->table_no ?: null,
                        'subtotal'       => $subtotal,
                        'discount'       => $discount,
                        'tax'            => $tax,
                        'tax_rate'       => $taxRate,
                        'total'          => $total,
                        'paid_amount'    => $isDraft ? 0 : ($paymentStatus === 'debt' ? 0 : $request->paid_amount),
                        'change_amount'  => $isDraft ? 0 : ($paymentStatus === 'debt' ? 0 : max(0, $request->paid_amount - $total)),
                        'payment_method' => $request->payment_method,
                        'payment_status' => $paymentStatus,
                        'status'         => $status,
                        'notes'          => $request->notes,
                    ]);

                    foreach ($items as $item) {
                        TransactionItem::create(array_merge($item, ['transaction_id'=>$transaction->id]));
                    }

                    // Statistik pelanggan, hutang — HANYA untuk transaksi final, bukan draft.
                    if (!$isDraft) {
                        if ($request->customer_id) {
                            Customer::where('id', $request->customer_id)->increment('total_transactions');
                            Customer::where('id', $request->customer_id)->increment('total_spent', $total);
                        }

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
                    }

                    $transactionId = $transaction->id;
                    $invoiceNo = $transaction->invoice_no;
                });

                break;
            } catch (\Illuminate\Database\QueryException $e) {
                if ($e->getCode() === '23000' && $attempt < 5) {
                    continue;
                }
                throw $e;
            }
        } while ($attempt < 5);

        return [$transactionId, $invoiceNo, $stocks];
    }

    /**
     * Simpan pesanan sebagai DRAFT (pesan dulu, bayar nanti). No. meja wajib.
     * Stok langsung dikurangi. Bisa banyak draft per meja.
     */
    public function draftStore(Request $request) {
        $request->merge(['payment_status' => null]);
        $this->validateTransaction($request);
        $request->validate([
            'table_no' => 'required|string|max:20',
        ], [
            'table_no.required' => 'Nomor meja wajib diisi untuk menyimpan draft.',
        ]);

        try {
            [$transactionId, $invoiceNo] = $this->persistTransaction($request, 'draft');
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json(['success'=>true,'transaction_id'=>$transactionId,'invoice_no'=>$invoiceNo,'message'=>'Pesanan disimpan sebagai draft.']);
    }

    /**
     * Ambil isi sebuah draft untuk dimuat ke keranjang (boleh diedit dulu).
     */
    public function draftShow(int $id) {
        $draft = Transaction::with('items')
            ->where('tenant_id', app('currentTenant')->id)
            ->draft()->findOrFail($id);

        // Stok terkini tiap produk = stok di DB + qty yang sedang "tertahan" di draft ini,
        // supaya saat dimuat ke keranjang, batas qty tetap benar.
        $items = $draft->items->map(function ($it) {
            $product = $it->product_id ? Product::find($it->product_id) : null;
            $heldStock = $product ? ($product->stock + $it->quantity) : $it->quantity;
            return [
                'id'    => $it->product_id,
                'name'  => $it->product_name,
                'price' => (float) $it->unit_price,
                'qty'   => $it->quantity,
                'stock' => $heldStock,
            ];
        })->filter(fn($i) => $i['id'])->values();

        return response()->json([
            'success'  => true,
            'draft_id' => $draft->id,
            'table_no' => $draft->table_no,
            'customer_id' => $draft->customer_id,
            'notes'    => $draft->notes,
            'items'    => $items,
        ]);
    }

    /**
     * Proses draft menjadi transaksi final.
     * Caranya: kembalikan stok draft lama -> hapus draft -> jalankan persist 'completed'
     * dengan item final (yang mungkin sudah diedit di kasir). Dengan begitu perubahan
     * qty/item ter-rekalkulasi otomatis dan stok selalu konsisten.
     */
    public function draftCheckout(int $id, Request $request) {
        $this->validateTransaction($request);

        $draft = Transaction::with('items')
            ->where('tenant_id', app('currentTenant')->id)
            ->draft()->findOrFail($id);

        try {
            // 1) Kembalikan stok yang ditahan draft, lalu hapus draft.
            DB::transaction(function () use ($draft) {
                foreach ($draft->items as $item) {
                    if ($item->product_id) {
                        Product::where('id', $item->product_id)->increment('stock', $item->quantity);
                    }
                }
                $draft->items()->delete();
                $draft->delete();
            });

            // 2) Buat transaksi final dari item terkini.
            [$transactionId, $invoiceNo, $stocks] = $this->persistTransaction($request, 'completed');
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json(['success'=>true,'transaction_id'=>$transactionId,'invoice_no'=>$invoiceNo,'stocks'=>$stocks,'message'=>'Transaksi berhasil.']);
    }

    /**
     * Batalkan/hapus draft tanpa jadi transaksi. Stok dikembalikan.
     */
    public function draftDestroy(int $id) {
        $draft = Transaction::with('items')
            ->where('tenant_id', app('currentTenant')->id)
            ->draft()->findOrFail($id);

        DB::transaction(function () use ($draft) {
            foreach ($draft->items as $item) {
                if ($item->product_id) {
                    Product::where('id', $item->product_id)->increment('stock', $item->quantity);
                }
            }
            $draft->items()->delete();
            $draft->delete();
        });

        return response()->json(['success' => true, 'message' => 'Draft dihapus & stok dikembalikan.']);
    }

    /**
     * Batalkan transaksi. Hanya owner & admin.
     * Mengembalikan stok produk, mencatat StockLog, membatalkan hutang terkait,
     * dan mengoreksi statistik pelanggan. Transaksi yang dibatalkan dikeluarkan
     * dari laporan & omzet.
     */
    public function cancel(int $id, Request $request) {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Hanya owner & admin yang dapat membatalkan transaksi.');
        }

        $request->validate([
            'reason' => 'nullable|string|max:255',
        ]);

        $transaction = Transaction::with('items')
            ->where('tenant_id', app('currentTenant')->id)->findOrFail($id);

        if ($transaction->isCancelled()) {
            return back()->with('error', 'Transaksi ini sudah dibatalkan sebelumnya.');
        }

        DB::transaction(function () use ($transaction, $request) {
            // Kembalikan stok tiap produk + catat jejak.
            foreach ($transaction->items as $item) {
                if (!$item->product_id) continue;

                $product = Product::withoutGlobalScopes()->find($item->product_id);
                if (!$product) continue;

                $qtyBefore = $product->stock;
                $product->increment('stock', $item->quantity);

                StockLog::create([
                    'product_id' => $product->id,
                    'user_id'    => auth()->id(),
                    'qty_before' => $qtyBefore,
                    'qty_change' => $item->quantity,
                    'qty_after'  => $qtyBefore + $item->quantity,
                    'type'       => 'cancel',
                    'note'       => "Pembatalan transaksi {$transaction->invoice_no}",
                ]);
            }

            // Koreksi statistik pelanggan.
            if ($transaction->customer_id) {
                $customer = Customer::find($transaction->customer_id);
                if ($customer) {
                    $customer->decrement('total_transactions');
                    $customer->decrement('total_spent', $transaction->total);
                }
            }

            // Hapus hutang terkait (beserta riwayat pembayarannya via cascade).
            if ($debt = $transaction->debt) {
                $debt->delete();
            }

            $transaction->update([
                'status'        => 'cancelled',
                'cancelled_at'  => now(),
                'cancelled_by'  => auth()->id(),
                'cancel_reason' => $request->reason,
            ]);
        });

        return back()->with('success', "Transaksi {$transaction->invoice_no} berhasil dibatalkan & stok dikembalikan.");
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
        $search    = trim((string) $request->q);
        $tenantId  = app('currentTenant')->id;

        $query = Transaction::with(['items','user','customer','canceller'])
            ->where('tenant_id', $tenantId)
            ->where('status', '!=', 'draft')
            ->whereBetween('created_at',[$startDate.' 00:00:00',$endDate.' 23:59:59']);

        // List transaksi — boleh difilter pencarian (invoice / nama pelanggan).
        $listQuery = (clone $query);
        if ($search !== '') {
            $listQuery->where(function ($q) use ($search) {
                $q->where('invoice_no', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn($c) => $c->where('name', 'like', "%{$search}%"));
            });
        }
        $transactions = $listQuery->orderByDesc('created_at')->paginate(20)->appends($request->query());

        // Agregat omzet/laporan HANYA dari transaksi yang tidak dibatalkan.
        $valid = (clone $query)->notCancelled();
        $totalRevenue      = (clone $valid)->sum('total');
        $totalDiscount     = (clone $valid)->sum('discount');
        $totalTax          = (clone $valid)->sum('tax');
        $totalTransactions = (clone $valid)->count();
        $totalDebt         = (clone $valid)->where('payment_status','debt')->count();

        $byPayment   = (clone $valid)->selectRaw('payment_method, COUNT(*) as count, SUM(total) as total')->groupBy('payment_method')->get();
        $topProducts = TransactionItem::select('product_name',DB::raw('SUM(quantity) as total_qty'),DB::raw('SUM(subtotal) as total_revenue'))
            ->whereHas('transaction',fn($q)=>$q->where('tenant_id',$tenantId)->where('status','completed')->whereBetween('created_at',[$startDate.' 00:00:00',$endDate.' 23:59:59']))
            ->groupBy('product_name')->orderByDesc('total_qty')->limit(10)->get();

        // ---- Semua Produk Terjual (periode) + filter kategori & sort ----
        $catFilter = $request->cat;                       // id kategori | '' (semua) | 'none' (tanpa kategori)
        $sort      = $request->sort ?: 'qty_desc';        // qty_desc|qty_asc|omzet_desc|omzet_asc|name_asc
        $sortMap   = [
            'qty_desc'   => ['total_qty', 'desc'],
            'qty_asc'    => ['total_qty', 'asc'],
            'omzet_desc' => ['total_revenue', 'desc'],
            'omzet_asc'  => ['total_revenue', 'asc'],
            'name_asc'   => ['product_name', 'asc'],
        ];
        [$sortCol, $sortDir] = $sortMap[$sort] ?? $sortMap['qty_desc'];

        $allQuery = TransactionItem::query()
            ->leftJoin('products', 'transaction_items.product_id', '=', 'products.id')
            ->whereHas('transaction', fn($q)=>$q->where('tenant_id',$tenantId)->where('status','completed')->whereBetween('created_at',[$startDate.' 00:00:00',$endDate.' 23:59:59']))
            ->select(
                'transaction_items.product_name',
                DB::raw('SUM(transaction_items.quantity) as total_qty'),
                DB::raw('SUM(transaction_items.subtotal) as total_revenue'),
                DB::raw('MAX(products.category_id) as category_id')
            )
            ->groupBy('transaction_items.product_name');

        if ($catFilter === 'none') {
            $allQuery->whereNull('products.category_id');
        } elseif ($catFilter !== null && $catFilter !== '') {
            $allQuery->where('products.category_id', $catFilter);
        }

        $allProducts  = $allQuery->orderBy($sortCol, $sortDir)->get();
        $allCategories = Category::orderBy('name')->get(['id','name']);

        $dailyRevenue = (clone $valid)->selectRaw('DATE(created_at) as date, SUM(total) as total, COUNT(*) as count')->groupBy('date')->orderBy('date')->get();

        return view('tenant.laporan.index', compact('transactions','totalRevenue','totalDiscount','totalTax','totalTransactions','totalDebt','byPayment','topProducts','allProducts','allCategories','catFilter','sort','dailyRevenue','startDate','endDate','search'));
    }

    public function export(Request $request) {
        $startDate = $request->start_date ?? now()->startOfMonth()->toDateString();
        $endDate   = $request->end_date   ?? now()->toDateString();
        return Excel::download(new TransactionsExport($startDate,$endDate,app('currentTenant')->id), "laporan-{$startDate}-sd-{$endDate}.xlsx");
    }
}
