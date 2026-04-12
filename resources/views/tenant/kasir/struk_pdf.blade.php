<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #000; background: #fff; width: 80mm; }
        .header { text-align: center; padding: 12px 0 8px; border-bottom: 1px dashed #000; margin-bottom: 8px; }
        .header h1 { font-size: 18px; font-weight: 700; color: #0F6E56; margin-bottom: 2px; }
        .header p { font-size: 10px; color: #555; }
        .meta { margin-bottom: 8px; border-bottom: 1px dashed #000; padding-bottom: 8px; }
        .meta-row { display: flex; justify-content: space-between; margin-bottom: 3px; }
        .meta-row .key { color: #555; }
        .items { margin-bottom: 8px; border-bottom: 1px dashed #000; padding-bottom: 8px; }
        .item { margin-bottom: 6px; }
        .item-name { font-weight: 600; }
        .item-detail { display: flex; justify-content: space-between; color: #555; margin-top: 2px; }
        .totals { margin-bottom: 8px; }
        .total-row { display: flex; justify-content: space-between; margin-bottom: 3px; }
        .total-row.grand { font-size: 13px; font-weight: 700; border-top: 1px solid #000; padding-top: 5px; margin-top: 5px; }
        .footer { text-align: center; border-top: 1px dashed #000; padding-top: 8px; color: #777; font-size: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Tokaku</h1>
        <p>{{ $transaction->user->tenant->name ?? '' }}</p>
        @if($transaction->user->tenant->address ?? false)
        <p>{{ $transaction->user->tenant->address }}</p>
        @endif
    </div>

    <div class="meta">
        <div class="meta-row">
            <span class="key">Invoice</span>
            <span>{{ $transaction->invoice_no }}</span>
        </div>
        <div class="meta-row">
            <span class="key">Kasir</span>
            <span>{{ $transaction->user->name }}</span>
        </div>
        <div class="meta-row">
            <span class="key">Waktu</span>
            <span>{{ $transaction->created_at->format('d/m/Y H:i') }}</span>
        </div>
        <div class="meta-row">
            <span class="key">Metode</span>
            <span>{{ strtoupper($transaction->payment_method) }}</span>
        </div>
    </div>

    <div class="items">
        @foreach($transaction->items as $item)
        <div class="item">
            <div class="item-name">{{ $item->product_name }}</div>
            <div class="item-detail">
                <span>{{ $item->quantity }}x Rp {{ number_format($item->unit_price,0,',','.') }}</span>
                <span>Rp {{ number_format($item->subtotal,0,',','.') }}</span>
            </div>
        </div>
        @endforeach
    </div>

    <div class="totals">
        <div class="total-row">
            <span>Subtotal</span>
            <span>Rp {{ number_format($transaction->subtotal,0,',','.') }}</span>
        </div>
        @if($transaction->discount > 0)
        <div class="total-row">
            <span>Diskon</span>
            <span>-Rp {{ number_format($transaction->discount,0,',','.') }}</span>
        </div>
        @endif
        <div class="total-row grand">
            <span>TOTAL</span>
            <span>Rp {{ number_format($transaction->total,0,',','.') }}</span>
        </div>
        <div class="total-row">
            <span>Bayar</span>
            <span>Rp {{ number_format($transaction->paid_amount,0,',','.') }}</span>
        </div>
        <div class="total-row">
            <span>Kembalian</span>
            <span>Rp {{ number_format($transaction->change_amount,0,',','.') }}</span>
        </div>
    </div>

    <div class="footer">
        <p>Terima kasih sudah berbelanja!</p>
        <p style="margin-top:4px;">Powered by Tokaku &middot; 1017studios.id</p>
    </div>
</body>
</html>
