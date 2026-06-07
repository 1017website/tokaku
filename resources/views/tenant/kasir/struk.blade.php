<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk {{ $transaction->invoice_no }}</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }

        body {
            background:#e5e7eb;
            display:flex;
            justify-content:center;
            align-items:flex-start;
            padding:20px;
            min-height:100vh;
            font-family:'Consolas','Courier New',monospace;
        }

        .receipt {
            background:#fff;
            width:58mm;
            max-width:100%;
            padding:6px 4px;
            color:#000;
            font-size:12px;
            line-height:1.4;
            font-weight:700;            /* semua teks tebal -> tajam di thermal */
        }

        .center { text-align:center; }

        .store-name { font-size:16px; font-weight:700; letter-spacing:0.5px; }
        .store-sub  { font-size:11px; font-weight:700; }
        .logo       { max-width:40px; max-height:40px; object-fit:contain; margin:0 auto 4px; display:block; filter:grayscale(1) contrast(3); }

        .divider { border:none; border-top:1px dashed #000; margin:5px 0; }

        .row { display:flex; justify-content:space-between; gap:6px; }
        .row span:last-child { text-align:right; white-space:nowrap; }

        .item-name { font-weight:700; word-break:break-word; }
        .item-line { display:flex; justify-content:space-between; gap:6px; }

        .total-row { font-size:14px; font-weight:700; }

        .footer { font-size:10px; line-height:1.5; font-weight:700; }

        .actions { width:58mm; max-width:100%; margin:14px auto 0; }
        .btn {
            display:block; width:100%; text-align:center;
            padding:11px; border-radius:8px; border:none; cursor:pointer;
            font-family:system-ui, sans-serif; font-size:14px; font-weight:600;
            margin-top:8px; text-decoration:none;
        }
        .btn-print { background:#0F6E56; color:#fff; }
        .btn-pdf   { background:#f0fdf6; color:#0F6E56; border:1.5px solid #bbf7d2; }
        .btn-back  { background:#fff; color:#374151; border:1.5px solid #e2e8f0; }

        @media print {
            @page { size:58mm auto; margin:0; }
            html, body { width:58mm; margin:0; padding:0; }
            body { background:#fff; display:block; }
            .receipt {
                width:100%;
                max-width:58mm;
                padding:0;
                margin:0;
                font-size:11.5px;
                font-weight:700;
                -webkit-print-color-adjust:exact;
                print-color-adjust:exact;
            }
            .no-print { display:none !important; }
        }
    </style>
</head>
<body>
<div class="receipt">
    <div class="center">
        <div class="store-name">{{ $transaction->user->tenant->name ?? ($appSettings['app_name'] ?? 'Tokaku') }}</div>
        @if($transaction->user->tenant->address ?? false)
            <div class="store-sub">{{ $transaction->user->tenant->address }}</div>
        @endif
        @if($transaction->user->tenant->phone ?? false)
            <div class="store-sub">{{ $transaction->user->tenant->phone }}</div>
        @endif
    </div>

    <hr class="divider">

    <div class="row"><span>Invoice</span><span>{{ $transaction->invoice_no }}</span></div>
    <div class="row"><span>Kasir</span><span>{{ $transaction->user->name }}</span></div>
    <div class="row"><span>Waktu</span><span>{{ $transaction->created_at->format('d/m/y H:i') }}</span></div>

    <hr class="divider">

    @foreach($transaction->items as $item)
    <div style="margin-bottom:4px;">
        <div class="item-name">{{ $item->product_name }}</div>
        <div class="item-line">
            <span>{{ $item->quantity }} x {{ number_format($item->unit_price,0,',','.') }}</span>
            <span>{{ number_format($item->subtotal,0,',','.') }}</span>
        </div>
    </div>
    @endforeach

    <hr class="divider">

    <div class="row"><span>Subtotal</span><span>Rp {{ number_format($transaction->subtotal,0,',','.') }}</span></div>
    @if($transaction->discount > 0)
    <div class="row"><span>Diskon</span><span>-Rp {{ number_format($transaction->discount,0,',','.') }}</span></div>
    @endif
    @if($transaction->tax > 0)
    <div class="row"><span>Pajak ({{ rtrim(rtrim(number_format($transaction->tax_rate,2,',','.'),'0'),',') }}%)</span><span>Rp {{ number_format($transaction->tax,0,',','.') }}</span></div>
    @endif

    <hr class="divider">

    <div class="row total-row"><span>TOTAL</span><span>Rp {{ number_format($transaction->total,0,',','.') }}</span></div>
    <div class="row"><span>Bayar ({{ strtoupper($transaction->payment_method) }})</span><span>Rp {{ number_format($transaction->paid_amount,0,',','.') }}</span></div>
    <div class="row"><span>Kembalian</span><span>Rp {{ number_format($transaction->change_amount,0,',','.') }}</span></div>

    @if($transaction->payment_status === 'debt')
    <hr class="divider">
    <div class="center">** TRANSAKSI HUTANG **</div>
    @endif

    <hr class="divider">

    <div class="center footer">
        Terima kasih sudah berbelanja!<br>
        Powered by {{ $appSettings['app_name'] ?? 'Tokaku' }} &middot; 1017studios.id
    </div>
</div>

<div class="actions no-print">
    <button onclick="cetakLangsung()" class="btn btn-print" id="btnCetakLangsung">Cetak Langsung (Printer)</button>
    <button onclick="window.print()" class="btn btn-back">Cetak via Browser</button>
    <a href="{{ route('tenant.kasir.struk.pdf', $transaction->id) }}" class="btn btn-pdf">Download PDF</a>
    <a href="{{ route('tenant.kasir.index') }}" class="btn btn-back">Kembali ke Kasir</a>
    <p id="cetakStatus" style="text-align:center;font-size:12px;color:#64748b;margin-top:8px;font-family:system-ui,sans-serif;"></p>
</div>

<script src="https://cdn.jsdelivr.net/npm/qz-tray@2.2.4/qz-tray.js"></script>
<script>
    var ESCPOS_URL = "{{ route('tenant.kasir.escpos', $transaction->id) }}";

    // Deteksi mode printer (sama dgn halaman kasir). Tablet ber-UA desktop
    // tetap terdeteksi via dukungan layar sentuh; preferensi manual menang.
    function detectPrinterMode(){
        var storeMode = @json(optional(optional($transaction->user)->tenant)->print_mode ?? 'auto');
        if (storeMode === 'rawbt' || storeMode === 'qz') return storeMode;
        try {
            var pref = localStorage.getItem('tokaku_print_mode');
            if (pref === 'rawbt' || pref === 'qz') return pref;
        } catch(e){}
        var ua = navigator.userAgent || '';
        var isDesktopOS = /Windows NT|Macintosh|CrOS|Linux x86/i.test(ua);
        var isMobileUA  = /Android|iPhone|iPad|iPod|Mobile|Tablet|Touch/i.test(ua);
        var isTouch     = (navigator.maxTouchPoints || 0) > 0 || ('ontouchstart' in window);
        if (isMobileUA) return 'rawbt';
        if (isTouch && !isDesktopOS) return 'rawbt';
        return 'qz';
    }

    function setStatus(msg){ document.getElementById('cetakStatus').textContent = msg || ''; }

    // Auto: RawBT untuk Android/Tablet, QZ Tray untuk PC.
    function cetakLangsung(){
        if (detectPrinterMode() === 'rawbt') { cetakRawBT(); } else { cetakQZ(); }
    }

    // ---- Jalur Android: RawBT ----
    function cetakRawBT(){
        setStatus('Mengirim ke RawBT...');
        // RawBT membaca data mentah dari URL via intent rawbt:
        var url = ESCPOS_URL + '?format=raw';
        window.location.href = 'rawbt:' + new URL(url, window.location.origin).href;
        setTimeout(function(){ setStatus('Jika tidak tercetak, pastikan app RawBT terpasang & printer terhubung.'); }, 1500);
    }

    // ---- Jalur PC: QZ Tray ----
    function cetakQZ(){
        if (typeof qz === 'undefined') { setStatus('Library QZ Tray gagal dimuat.'); return; }
        setStatus('Menghubungkan ke QZ Tray...');

        var connect = qz.websocket.isActive()
            ? Promise.resolve()
            : qz.websocket.connect();

        connect.then(function(){
            return fetch(ESCPOS_URL).then(function(r){ return r.json(); });
        }).then(function(data){
            return qz.printers.getDefault().then(function(printer){
                var cfg  = qz.configs.create(printer, { encoding: 'ISO-8859-1' });
                var bytes = [{ type: 'raw', format: 'base64', data: data.base64 }];
                return qz.print(cfg, bytes);
            });
        }).then(function(){
            setStatus('Struk berhasil dikirim ke printer.');
        }).catch(function(err){
            console.error(err);
            setStatus('Gagal: pastikan aplikasi QZ Tray berjalan di PC ini.');
        });
    }
</script>
</body>
</html>
