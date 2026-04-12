<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk {{ $transaction->invoice_no }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin:0;padding:0;box-sizing:border-box;-webkit-font-smoothing:antialiased; }
        body { font-family:'Inter',sans-serif;background:#f8fafc;display:flex;justify-content:center;padding:24px;min-height:100vh;align-items:flex-start; }
        .receipt { background:#fff;width:100%;max-width:320px;border-radius:16px;border:1px solid #f1f5f9;box-shadow:0 4px 20px rgba(0,0,0,0.06);overflow:hidden; }
        .receipt-header { background:#0F6E56;padding:20px;text-align:center; }
        .receipt-body { padding:20px; }
        .divider { border:none;border-top:1.5px dashed #e2e8f0;margin:14px 0; }
        .row { display:flex;justify-content:space-between;margin:5px 0;font-size:13px; }
        .row .label { color:#64748b; }
        .row .value { font-weight:500;color:#0f172a; }
        .total-row { font-size:15px;font-weight:700; }
        .btn { display:block;text-align:center;padding:12px;border-radius:12px;font-family:'Inter',sans-serif;font-size:14px;font-weight:600;cursor:pointer;border:none;margin-top:12px;transition:all 0.15s; }
        .btn-print { background:#0F6E56;color:#fff; }
        .btn-print:hover { background:#085041; }
        .btn-back { background:#fff;color:#374151;border:1.5px solid #e2e8f0;text-decoration:none;display:block; }
        @media print { body{background:#fff;padding:0;} .receipt{box-shadow:none;border:none;max-width:100%;border-radius:0;} .no-print{display:none;} }
    </style>
</head>
<body>
<div class="receipt">
    <div class="receipt-header">
        <div style="display:inline-flex;align-items:center;justify-content:center;width:40px;height:40px;background:rgba(255,255,255,0.15);border-radius:12px;margin-bottom:8px;">
            <svg width="20" height="20" viewBox="0 0 18 18" fill="none"><path d="M3 5h12M3 9h8M3 13h5" stroke="white" stroke-width="2" stroke-linecap="round"/></svg>
        </div>
        <p style="color:#fff;font-size:18px;font-weight:700;letter-spacing:-0.3px;">Tokaku</p>
        <p style="color:rgba(255,255,255,0.75);font-size:12px;margin-top:3px;">{{ $transaction->user->tenant->name ?? '' }}</p>
    </div>
    <div class="receipt-body">
        <div class="row"><span class="label">Invoice</span><span class="value" style="font-family:monospace;font-size:12px;">{{ $transaction->invoice_no }}</span></div>
        <div class="row"><span class="label">Kasir</span><span class="value">{{ $transaction->user->name }}</span></div>
        <div class="row"><span class="label">Waktu</span><span class="value">{{ $transaction->created_at->format('d/m/Y H:i') }}</span></div>
        <hr class="divider">
        @foreach($transaction->items as $item)
        <div style="margin-bottom:10px;">
            <p style="font-size:13.5px;font-weight:500;color:#0f172a;margin-bottom:3px;">{{ $item->product_name }}</p>
            <div class="row">
                <span style="font-size:12.5px;color:#64748b;">{{ $item->quantity }}x Rp {{ number_format($item->unit_price,0,',','.') }}</span>
                <span style="font-size:13px;font-weight:600;color:#0f172a;">Rp {{ number_format($item->subtotal,0,',','.') }}</span>
            </div>
        </div>
        @endforeach
        <hr class="divider">
        <div class="row"><span class="label">Subtotal</span><span class="value">Rp {{ number_format($transaction->subtotal,0,',','.') }}</span></div>
        @if($transaction->discount > 0)
        <div class="row"><span class="label">Diskon</span><span class="value" style="color:#f43f5e;">-Rp {{ number_format($transaction->discount,0,',','.') }}</span></div>
        @endif
        <div class="row total-row" style="margin-top:8px;"><span>TOTAL</span><span style="color:#0F6E56;">Rp {{ number_format($transaction->total,0,',','.') }}</span></div>
        <div class="row" style="margin-top:6px;"><span class="label">Bayar ({{ strtoupper($transaction->payment_method) }})</span><span class="value">Rp {{ number_format($transaction->paid_amount,0,',','.') }}</span></div>
        <div class="row"><span class="label">Kembalian</span><span class="value">Rp {{ number_format($transaction->change_amount,0,',','.') }}</span></div>
        <hr class="divider">
        <p style="text-align:center;font-size:12.5px;color:#94a3b8;line-height:1.6;">Terima kasih sudah berbelanja!<br><span style="font-size:11.5px;">Powered by Tokaku · 1017studios.id</span></p>
        <button onclick="window.print()" class="btn btn-print no-print">Cetak Struk</button>
        <a href="{{ route('tenant.kasir.index') }}" class="btn btn-back no-print">Kembali ke Kasir</a>
    </div>
</div>
</body>
</html>
