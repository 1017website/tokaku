@extends('layouts.app')
@section('title','Detail Stok')
@section('page-title','Detail Stok')
@section('page-subtitle',$product->name)
@section('header-actions')
<a href="{{ route('tenant.stok.index') }}" class="btn-secondary">Kembali</a>
<a href="{{ route('tenant.stok.history', $product) }}" class="btn-primary">Riwayat Lengkap</a>
@endsection
@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <div style="background:#fff;border:1px solid #f1f5f9;border-radius:16px;padding:22px;box-shadow:0 1px 3px rgba(0,0,0,.04);">
        <p style="font-size:13px;color:#64748b;">Produk</p>
        <h3 style="font-size:20px;font-weight:800;color:#0f172a;margin-top:4px;">{{ $product->name }}</h3>
        <p style="font-size:13px;color:#94a3b8;margin-top:4px;">{{ $product->category?->name ?? 'Tanpa kategori' }} · {{ $product->sku ?? 'Tanpa SKU' }}</p>
        <div style="margin-top:20px;display:grid;grid-template-columns:1fr 1fr;gap:10px;">
            <div style="background:#f8fafc;border-radius:12px;padding:14px;"><p style="font-size:12px;color:#64748b;">Stok</p><b style="font-size:28px;color:#0F6E56;">{{ $product->stock }}</b></div>
            <div style="background:#f8fafc;border-radius:12px;padding:14px;"><p style="font-size:12px;color:#64748b;">Min. Alert</p><b style="font-size:28px;color:#0f172a;">{{ $product->low_stock_alert }}</b></div>
        </div>
        <form method="POST" action="{{ route('tenant.stok.update', $product) }}" style="margin-top:18px;display:flex;flex-direction:column;gap:12px;">
            @csrf @method('PUT')
            <div><label class="form-label">Jenis Perubahan</label><select name="type" class="form-input"><option value="restock">Restock</option><option value="adjustment">Penyesuaian</option><option value="correction">Koreksi</option></select></div>
            <div><label class="form-label">Jumlah (+ tambah, - kurang)</label><input type="number" name="qty_change" value="1" class="form-input" required></div>
            <div><label class="form-label">Catatan</label><input type="text" name="note" class="form-input" placeholder="Opsional"></div>
            <button class="btn-primary" style="justify-content:center;">Simpan Stok</button>
        </form>
    </div>
    <div class="lg:col-span-2" style="background:#fff;border:1px solid #f1f5f9;border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.04);">
        <div style="padding:16px 20px;border-bottom:1px solid #f8fafc;"><b style="font-size:14px;color:#0f172a;">Riwayat Stok Terbaru</b></div>
        @forelse($logs as $log)
        <div style="display:flex;justify-content:space-between;gap:12px;padding:14px 20px;border-bottom:1px solid #f8fafc;">
            <div><p style="font-size:13.5px;font-weight:700;color:#0f172a;">{{ ucfirst($log->type) }}</p><p style="font-size:12px;color:#94a3b8;">{{ $log->created_at->format('d M Y H:i') }} · {{ $log->user?->name ?? '-' }}</p><p style="font-size:12px;color:#64748b;margin-top:2px;">{{ $log->note ?? '-' }}</p></div>
            <div style="text-align:right;"><b style="font-size:14px;color:{{ $log->qty_change >= 0 ? '#15803d' : '#be123c' }};">{{ $log->qty_change >= 0 ? '+' : '' }}{{ $log->qty_change }}</b><p style="font-size:12px;color:#64748b;">{{ $log->qty_before }} → {{ $log->qty_after }}</p></div>
        </div>
        @empty <div style="padding:40px;text-align:center;color:#94a3b8;font-size:13px;">Belum ada riwayat stok.</div> @endforelse
    </div>
</div>
@endsection
