@extends('layouts.app')
@section('title','Dashboard')
@section('page-title','Dashboard')
@section('page-subtitle','Selamat datang, ' . auth()->user()->name)

@section('header-actions')
<a href="{{ route('tenant.kasir.index') }}" class="btn-primary">Transaksi Baru</a>
@endsection

@section('content')
@php
$cards = [
    ['label'=>'Omzet hari ini','value'=>'Rp '.number_format($todayRevenue,0,',','.'),'sub'=>$todayCount.' transaksi','color'=>'#16a34a','bg'=>'#f0fdf4'],
    ['label'=>'Profit bersih hari ini','value'=>'Rp '.number_format($todayNetProfit,0,',','.'),'sub'=>'Profit kotor Rp '.number_format($todayGrossProfit,0,',','.'),'color'=>'#0F6E56','bg'=>'#f0fdf6'],
    ['label'=>'Omzet bulan ini','value'=>'Rp '.number_format($monthRevenue,0,',','.'),'sub'=>$monthCount.' transaksi','color'=>'#2563eb','bg'=>'#eff6ff'],
    ['label'=>'Profit bersih bulan ini','value'=>'Rp '.number_format($monthNetProfit,0,',','.'),'sub'=>'Pengeluaran Rp '.number_format($monthExpenses,0,',','.'),'color'=>'#7c3aed','bg'=>'#f5f3ff'],
];
@endphp

<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
@foreach($cards as $c)
    <div style="background:#fff;border-radius:16px;border:1px solid #f1f5f9;padding:18px 16px;box-shadow:0 1px 3px rgba(0,0,0,.04);">
        <div style="display:flex;justify-content:space-between;gap:10px;margin-bottom:12px;">
            <p style="font-size:12px;font-weight:600;color:#64748b;">{{ $c['label'] }}</p>
            <div style="width:32px;height:32px;border-radius:10px;background:{{ $c['bg'] }};color:{{ $c['color'] }};display:flex;align-items:center;justify-content:center;font-weight:800;">Rp</div>
        </div>
        <p style="font-size:20px;font-weight:800;color:#0f172a;line-height:1;">{{ $c['value'] }}</p>
        <p style="font-size:11.5px;color:#94a3b8;margin-top:6px;">{{ $c['sub'] }}</p>
    </div>
@endforeach
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
    <div style="background:#fff;border-radius:16px;border:1px solid #f1f5f9;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,.04);">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:14px;gap:12px;">
            <div>
                <p style="font-size:14px;font-weight:700;color:#0f172a;">Progress Balik Modal</p>
                <p style="font-size:12px;color:#94a3b8;margin-top:3px;">Profit akumulasi dibanding modal awal</p>
            </div>
            <a href="{{ route('tenant.profil') }}" style="font-size:12px;color:#0F6E56;text-decoration:none;font-weight:600;">Atur</a>
        </div>
        <div style="height:12px;background:#f1f5f9;border-radius:999px;overflow:hidden;margin-bottom:12px;">
            <div style="width:{{ $capitalProgress }}%;height:100%;background:#0F6E56;border-radius:999px;"></div>
        </div>
        <p style="font-size:22px;font-weight:800;color:#0f172a;">{{ $capitalProgress }}%</p>
        <p style="font-size:12px;color:#64748b;margin-top:4px;">Profit: Rp {{ number_format($totalNetProfit,0,',','.') }} / Target: Rp {{ number_format($initialCapital,0,',','.') }}</p>
        @if($initialCapital > 0 && $capitalProgress >= 100)
            <p style="margin-top:10px;font-size:12px;font-weight:700;color:#15803d;background:#f0fdf4;padding:8px 10px;border-radius:10px;">BEP tercapai</p>
        @endif
    </div>

    <div class="lg:col-span-2" style="background:#fff;border-radius:16px;border:1px solid #f1f5f9;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,.04);">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
            <p style="font-size:14px;font-weight:700;color:#0f172a;">Omzet & Profit 7 Hari Terakhir</p>
            <span style="font-size:12px;color:#94a3b8;">Mingguan</span>
        </div>
        <canvas id="weeklyChart" height="95"></canvas>
    </div>
</div>

{{-- Ringkasan Gudang Bahan hari ini --}}
<div style="background:#fff;border-radius:16px;border:1px solid #f1f5f9;box-shadow:0 1px 3px rgba(0,0,0,.04);padding:18px 20px;margin-bottom:24px;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
        <p style="font-size:13.5px;font-weight:700;color:#0f172a;">Gudang Bahan Hari Ini · {{ now()->translatedFormat('d M Y') }}</p>
        <a href="{{ route('tenant.bahan.index') }}" style="font-size:12px;color:#0F6E56;font-weight:600;text-decoration:none;">Buka gudang</a>
    </div>
    <div class="grid grid-cols-2 gap-3">
        <div style="border:1px solid #f1f5f9;border-left:4px solid #be123c;border-radius:12px;padding:14px 16px;">
            <p style="font-size:11.5px;color:#b91c1c;font-weight:700;">Bahan Keluar</p>
            <p style="font-size:22px;font-weight:800;color:#be123c;margin-top:6px;">{{ number_format($rawMaterialSummary['qty_out'], 0, ',', '.') }} <span style="font-size:12px;color:#94a3b8;font-weight:700;">qty</span></p>
            <p style="font-size:13px;font-weight:700;color:#334155;margin-top:2px;">{{ \App\Models\RawMaterial::rupiah($rawMaterialSummary['value_out']) }}</p>
        </div>
        <div style="border:1px solid #f1f5f9;border-left:4px solid #0F6E56;border-radius:12px;padding:14px 16px;">
            <p style="font-size:11.5px;color:#085041;font-weight:700;">Bahan Masuk</p>
            <p style="font-size:22px;font-weight:800;color:#0F6E56;margin-top:6px;">{{ number_format($rawMaterialSummary['qty_in'], 0, ',', '.') }} <span style="font-size:12px;color:#94a3b8;font-weight:700;">qty</span></p>
            <p style="font-size:13px;font-weight:700;color:#334155;margin-top:2px;">{{ \App\Models\RawMaterial::rupiah($rawMaterialSummary['value_in']) }}</p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    <div style="background:#fff;border-radius:16px;border:1px solid #f1f5f9;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.04);">
        <div style="padding:16px 20px;border-bottom:1px solid #f8fafc;display:flex;justify-content:space-between;">
            <p style="font-size:13.5px;font-weight:700;color:#0f172a;">Stok menipis</p>
            <a href="{{ route('tenant.stok.index') }}" style="font-size:12px;color:#0F6E56;font-weight:600;text-decoration:none;">Lihat semua</a>
        </div>
        @forelse($lowStockProducts as $p)
        <div style="display:flex;justify-content:space-between;padding:13px 20px;border-bottom:1px solid #f8fafc;">
            <div><p style="font-size:13.5px;font-weight:600;color:#0f172a;">{{ $p->name }}</p><p style="font-size:12px;color:#94a3b8;">{{ $p->category?->name ?? 'Tanpa kategori' }}</p></div>
            <b style="font-size:13px;color:{{ $p->stock<=0?'#be123c':'#b45309' }};">{{ $p->stock }} sisa</b>
        </div>
        @empty <div style="padding:35px;text-align:center;color:#94a3b8;font-size:13px;">Semua stok aman</div> @endforelse
    </div>

    <div style="background:#fff;border-radius:16px;border:1px solid #f1f5f9;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.04);">
        <div style="padding:16px 20px;border-bottom:1px solid #f8fafc;display:flex;justify-content:space-between;">
            <p style="font-size:13.5px;font-weight:700;color:#0f172a;">Transaksi terbaru</p>
            <a href="{{ route('tenant.laporan.index') }}" style="font-size:12px;color:#0F6E56;font-weight:600;text-decoration:none;">Lihat semua</a>
        </div>
        @forelse($recentTransactions as $t)
        <div style="display:flex;justify-content:space-between;padding:13px 20px;border-bottom:1px solid #f8fafc;">
            <div><p style="font-size:13.5px;font-weight:600;color:#0f172a;">{{ $t->invoice_no }}</p><p style="font-size:12px;color:#94a3b8;">{{ $t->created_at->diffForHumans() }} · {{ strtoupper($t->payment_method) }}</p></div>
            <b style="font-size:13.5px;color:#0f172a;">Rp {{ number_format($t->total,0,',','.') }}</b>
        </div>
        @empty <div style="padding:35px;text-align:center;color:#94a3b8;font-size:13px;">Belum ada transaksi</div> @endforelse
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('weeklyChart'), {
    type: 'line',
    data: {
        labels: @json($weeklyData->pluck('label')),
        datasets: [
            { label: 'Omzet', data: @json($weeklyData->pluck('revenue')), tension:.35 },
            { label: 'Profit', data: @json($weeklyData->pluck('profit')), tension:.35 }
        ]
    },
    options: { responsive:true, plugins:{legend:{display:true}}, scales:{y:{beginAtZero:true}} }
});
</script>
@endpush
