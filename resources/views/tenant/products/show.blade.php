@extends('layouts.app')
@section('title', $product->name)
@section('page-title', $product->name)
@section('page-subtitle', 'Detail dan riwayat transaksi produk')

@section('header-actions')
<a href="{{ route('tenant.products.edit', $product) }}" class="btn-primary">Edit Produk</a>
<a href="{{ route('tenant.products.index') }}" class="btn-secondary">&larr; Kembali</a>
@endsection

@section('content')

{{-- Info Produk --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
    <div style="background:#fff;border-radius:14px;border:1px solid #f1f5f9;padding:18px;">
        <p style="font-size:12px;font-weight:500;color:#64748b;margin-bottom:8px;">Harga Jual</p>
        <p style="font-size:20px;font-weight:700;color:#0F6E56;letter-spacing:-0.5px;">Rp {{ number_format($product->price,0,',','.') }}</p>
    </div>
    <div style="background:#fff;border-radius:14px;border:1px solid #f1f5f9;padding:18px;">
        <p style="font-size:12px;font-weight:500;color:#64748b;margin-bottom:8px;">Stok Saat Ini</p>
        <p style="font-size:20px;font-weight:700;letter-spacing:-0.5px;color:{{ $product->stock<=0?'#be123c':($product->isLowStock()?'#b45309':'#0f172a') }};">
            {{ $product->stock }}
        </p>
    </div>
    <div style="background:#fff;border-radius:14px;border:1px solid #f1f5f9;padding:18px;">
        <p style="font-size:12px;font-weight:500;color:#64748b;margin-bottom:8px;">Total Terjual</p>
        <p style="font-size:20px;font-weight:700;color:#0f172a;letter-spacing:-0.5px;">{{ number_format($totalTerjual) }} pcs</p>
    </div>
    <div style="background:#fff;border-radius:14px;border:1px solid #f1f5f9;padding:18px;">
        <p style="font-size:12px;font-weight:500;color:#64748b;margin-bottom:8px;">Total Omzet</p>
        <p style="font-size:20px;font-weight:700;color:#0f172a;letter-spacing:-0.5px;">Rp {{ number_format($totalOmzet,0,',','.') }}</p>
    </div>
</div>

@if($totalLaba !== null)
<div style="background:#f0fdf6;border:1px solid #bbf7d2;border-radius:14px;padding:16px 20px;margin-bottom:20px;display:flex;align-items:center;gap:10px;">
    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#15803d" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
    <p style="font-size:13.5px;color:#15803d;font-weight:500;">Estimasi total laba: <strong>Rp {{ number_format($totalLaba,0,',','.') }}</strong></p>
</div>
@endif

{{-- Riwayat Transaksi --}}
<div style="background:#fff;border-radius:16px;border:1px solid #f1f5f9;box-shadow:0 1px 3px rgba(0,0,0,0.04);overflow:hidden;">
    <div style="padding:16px 20px;border-bottom:1px solid #f8fafc;">
        <p style="font-size:14px;font-weight:600;color:#0f172a;">Riwayat Penjualan</p>
    </div>
    <div class="overflow-x-auto">
        <table style="width:100%;border-collapse:collapse;min-width:500px;">
            <thead>
                <tr style="background:#f8fafc;border-bottom:1px solid #f1f5f9;">
                    <th style="text-align:left;font-size:11px;font-weight:600;color:#64748b;padding:10px 18px;text-transform:uppercase;letter-spacing:0.3px;">Invoice</th>
                    <th style="text-align:left;font-size:11px;font-weight:600;color:#64748b;padding:10px 18px;text-transform:uppercase;letter-spacing:0.3px;">Kasir</th>
                    <th style="text-align:center;font-size:11px;font-weight:600;color:#64748b;padding:10px 18px;text-transform:uppercase;letter-spacing:0.3px;">Qty</th>
                    <th style="text-align:right;font-size:11px;font-weight:600;color:#64748b;padding:10px 18px;text-transform:uppercase;letter-spacing:0.3px;">Subtotal</th>
                    <th style="text-align:left;font-size:11px;font-weight:600;color:#64748b;padding:10px 18px;text-transform:uppercase;letter-spacing:0.3px;">Waktu</th>
                </tr>
            </thead>
            <tbody>
                @forelse($history as $item)
                <tr style="border-bottom:1px solid #f8fafc;transition:background 0.1s;" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='#fff'">
                    <td style="padding:12px 18px;font-size:13px;font-weight:500;color:#0f172a;">{{ $item->transaction->invoice_no }}</td>
                    <td style="padding:12px 18px;font-size:13px;color:#374151;">{{ $item->transaction->user->name }}</td>
                    <td style="padding:12px 18px;text-align:center;font-size:13px;font-weight:600;color:#0f172a;">{{ $item->quantity }}</td>
                    <td style="padding:12px 18px;text-align:right;font-size:13px;font-weight:600;color:#0f172a;">Rp {{ number_format($item->subtotal,0,',','.') }}</td>
                    <td style="padding:12px 18px;font-size:12.5px;color:#64748b;">{{ $item->transaction->created_at->format('d M Y, H:i') }}</td>
                </tr>
                @empty
                <tr><td colspan="5" style="padding:50px 20px;text-align:center;font-size:14px;color:#94a3b8;">Produk ini belum pernah terjual.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($history->hasPages())
    <div style="padding:14px 18px;border-top:1px solid #f8fafc;">{{ $history->links() }}</div>
    @endif
</div>

@endsection
