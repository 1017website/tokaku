@extends('layouts.app')
@section('title','Dashboard')
@section('page-title','Dashboard')
@section('page-subtitle','Selamat datang, ' . auth()->user()->name)

@section('header-actions')
<a href="{{ route('tenant.kasir.index') }}" class="btn-primary">
    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
    <span class="hidden sm:inline">Transaksi Baru</span>
    <span class="sm:hidden">Baru</span>
</a>
@endsection

@section('content')

{{-- Metric Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">

    @php
    $cards = [
        ['label'=>'Omzet hari ini','value'=>'Rp '.number_format($todayRevenue,0,',','.'),'sub'=>$todayCount.' transaksi','icon'=>'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z','color'=>'#16a34a','bg'=>'#f0fdf4'],
        ['label'=>'Omzet bulan ini','value'=>'Rp '.number_format($monthRevenue,0,',','.'),'sub'=>$monthCount.' transaksi','icon'=>'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z','color'=>'#2563eb','bg'=>'#eff6ff'],
        ['label'=>'Total produk aktif','value'=>\App\Models\Product::active()->count(),'sub'=>'produk tersedia','icon'=>'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4','color'=>'#7c3aed','bg'=>'#f5f3ff'],
        ['label'=>'Stok menipis','value'=>$lowStockProducts->count(),'sub'=>'perlu restock','icon'=>'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z','color'=>'#d97706','bg'=>'#fffbeb'],
    ];
    @endphp

    @foreach($cards as $c)
    <div style="background:#fff;border-radius:16px;border:1px solid #f1f5f9;box-shadow:0 1px 3px rgba(0,0,0,0.04);padding:18px 16px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
            <p style="font-size:12px;font-weight:500;color:#64748b;">{{ $c['label'] }}</p>
            <div style="width:32px;height:32px;background:{{ $c['bg'] }};border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="{{ $c['color'] }}" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $c['icon'] }}"/></svg>
            </div>
        </div>
        <p style="font-size:20px;font-weight:700;color:#0f172a;letter-spacing:-0.5px;line-height:1;">{{ $c['value'] }}</p>
        <p style="font-size:11.5px;color:#94a3b8;margin-top:5px;">{{ $c['sub'] }}</p>
    </div>
    @endforeach
</div>

{{-- Bottom --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

    {{-- Stok menipis --}}
    <div style="background:#fff;border-radius:16px;border:1px solid #f1f5f9;box-shadow:0 1px 3px rgba(0,0,0,0.04);overflow:hidden;">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #f8fafc;">
            <p style="font-size:13.5px;font-weight:600;color:#0f172a;">Stok menipis</p>
            <a href="{{ route('tenant.products.index') }}" style="font-size:12px;color:#0F6E56;font-weight:500;text-decoration:none;">Lihat semua</a>
        </div>
        @forelse($lowStockProducts as $p)
        <div style="display:flex;align-items:center;justify-content:space-between;padding:13px 20px;border-bottom:1px solid #f8fafc;">
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:34px;height:34px;background:#f8fafc;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="#9ca3af" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <div>
                    <p style="font-size:13.5px;font-weight:500;color:#0f172a;">{{ $p->name }}</p>
                    <p style="font-size:12px;color:#94a3b8;">{{ $p->category?->name ?? 'Tanpa kategori' }}</p>
                </div>
            </div>
            <span style="font-size:13px;font-weight:600;padding:4px 10px;border-radius:99px;{{ $p->stock<=0 ? 'background:#fff1f2;color:#be123c;' : 'background:#fffbeb;color:#b45309;' }}">
                {{ $p->stock }} sisa
            </span>
        </div>
        @empty
        <div style="padding:40px 20px;text-align:center;">
            <div style="width:36px;height:36px;background:#f0fdf4;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 8px;">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#16a34a" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </div>
            <p style="font-size:13.5px;color:#94a3b8;">Semua stok aman</p>
        </div>
        @endforelse
    </div>

    {{-- Transaksi terbaru --}}
    <div style="background:#fff;border-radius:16px;border:1px solid #f1f5f9;box-shadow:0 1px 3px rgba(0,0,0,0.04);overflow:hidden;">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #f8fafc;">
            <p style="font-size:13.5px;font-weight:600;color:#0f172a;">Transaksi terbaru</p>
            <a href="{{ route('tenant.laporan.index') }}" style="font-size:12px;color:#0F6E56;font-weight:500;text-decoration:none;">Lihat semua</a>
        </div>
        @forelse($recentTransactions as $t)
        <div style="display:flex;align-items:center;justify-content:space-between;padding:13px 20px;border-bottom:1px solid #f8fafc;">
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:34px;height:34px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;{{ $t->payment_method==='cash'?'background:#f0fdf4;':($t->payment_method==='qris'?'background:#eff6ff;':'background:#f5f3ff;') }}">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="{{ $t->payment_method==='cash'?'#16a34a':($t->payment_method==='qris'?'#2563eb':'#7c3aed') }}" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <div>
                    <p style="font-size:13.5px;font-weight:500;color:#0f172a;">{{ $t->invoice_no }}</p>
                    <p style="font-size:12px;color:#94a3b8;">{{ $t->created_at->diffForHumans() }} &middot; {{ strtoupper($t->payment_method) }}</p>
                </div>
            </div>
            <p style="font-size:13.5px;font-weight:600;color:#0f172a;">Rp {{ number_format($t->total,0,',','.') }}</p>
        </div>
        @empty
        <div style="padding:40px 20px;text-align:center;">
            <p style="font-size:13.5px;color:#94a3b8;">Belum ada transaksi</p>
        </div>
        @endforelse
    </div>
</div>

{{-- Quick nav mobile --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-4 lg:hidden">
    @foreach([['Kasir','tenant.kasir.index','M9 7H6a2 2 0 00-2 2v9a2 2 0 002 2h9a2 2 0 002-2v-3M9 7V5a2 2 0 012-2h2a2 2 0 012 2v2M9 7h6m-3 4v4m-2-2h4'],['Produk','tenant.products.index','M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],['Laporan','tenant.laporan.index','M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],['Kategori','tenant.categories.index','M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z']] as [$lbl,$route,$icon])
    <a href="{{ route($route) }}" style="background:#fff;border:1px solid #f1f5f9;border-radius:14px;padding:16px;text-align:center;text-decoration:none;display:flex;flex-direction:column;align-items:center;gap:8px;transition:all 0.15s;" onmouseover="this.style.borderColor='#0F6E56'" onmouseout="this.style.borderColor='#f1f5f9'">
        <div style="width:36px;height:36px;background:#f0fdf6;border-radius:10px;display:flex;align-items:center;justify-content:center;">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#0F6E56" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
        </div>
        <p style="font-size:13px;font-weight:500;color:#374151;">{{ $lbl }}</p>
    </a>
    @endforeach
</div>

@endsection
