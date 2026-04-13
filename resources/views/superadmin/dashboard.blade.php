@extends('superadmin.layout')
@section('title','Dashboard')
@section('page-title','Dashboard')
@section('page-subtitle','Ringkasan seluruh bisnis Tokaku')

@section('header-actions')
<a href="{{ route('superadmin.tenants') }}" class="btn-primary">
    <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
    Tambah Tenant
</a>
@endsection

@section('content')

{{-- Metric Cards --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:14px;margin-bottom:20px;">
    @foreach([
        ['Total Tenant',$totalTenants,'toko terdaftar','#0F6E56','#f0fdf6','M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
        ['Tenant Aktif',$activeTenants,'berlangganan aktif','#16a34a','#f0fdf4','M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['Masa Trial',$trialTenants,'sedang trial','#d97706','#fffbeb','M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['Total Revenue','Rp '.number_format($totalRevenue,0,',','.'),'semua waktu','#7c3aed','#f5f3ff','M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['Revenue Hari Ini','Rp '.number_format($todayRevenue,0,',','.'),$todayTransactions.' transaksi','#2563eb','#eff6ff','M13 7h8m0 0v8m0-8l-8 8-4-4-6 6'],
        ['Total Transaksi',number_format($totalTransactions),'semua tenant','#0f172a','#f8fafc','M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
    ] as [$lbl,$val,$sub,$clr,$bg,$icon])
    <div style="background:#fff;border-radius:14px;border:1px solid #f1f5f9;box-shadow:0 1px 3px rgba(0,0,0,0.04);padding:18px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
            <p style="font-size:12px;font-weight:500;color:#64748b;">{{ $lbl }}</p>
            <div style="width:30px;height:30px;background:{{ $bg }};border-radius:8px;display:flex;align-items:center;justify-content:center;">
                <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="{{ $clr }}" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
            </div>
        </div>
        <p style="font-size:20px;font-weight:700;color:#0f172a;letter-spacing:-0.5px;word-break:break-word;">{{ $val }}</p>
        <p style="font-size:11.5px;color:#94a3b8;margin-top:4px;">{{ $sub }}</p>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">

    {{-- Chart Revenue --}}
    <div class="lg:col-span-2" style="background:#fff;border-radius:14px;border:1px solid #f1f5f9;box-shadow:0 1px 3px rgba(0,0,0,0.04);padding:20px;">
        <p style="font-size:14px;font-weight:600;color:#0f172a;margin-bottom:16px;">Revenue 30 Hari Terakhir</p>
        <div style="position:relative;height:200px;">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    {{-- Tenant terbaru --}}
    <div style="background:#fff;border-radius:14px;border:1px solid #f1f5f9;box-shadow:0 1px 3px rgba(0,0,0,0.04);overflow:hidden;">
        <div style="padding:16px 20px;border-bottom:1px solid #f8fafc;display:flex;align-items:center;justify-content:space-between;">
            <p style="font-size:14px;font-weight:600;color:#0f172a;">Tenant Terbaru</p>
            <a href="{{ route('superadmin.tenants') }}" style="font-size:12px;color:#0F6E56;font-weight:500;text-decoration:none;">Semua</a>
        </div>
        @foreach($recentTenants as $t)
        <a href="{{ route('superadmin.tenants.detail', $t) }}" style="display:flex;align-items:center;gap:10px;padding:12px 16px;border-bottom:1px solid #f8fafc;text-decoration:none;transition:background 0.1s;" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='#fff'">
            <div style="width:34px;height:34px;background:#f0fdf6;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <span style="color:#0F6E56;font-size:12px;font-weight:700;">{{ strtoupper(substr($t->name,0,2)) }}</span>
            </div>
            <div style="flex:1;min-width:0;">
                <p style="font-size:13px;font-weight:500;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $t->name }}</p>
                <p style="font-size:11.5px;color:#94a3b8;">{{ $t->created_at->diffForHumans() }}</p>
            </div>
            <span style="font-size:11px;font-weight:500;padding:3px 8px;border-radius:99px;flex-shrink:0;
                {{ $t->status==='active' ? 'background:#f0fdf4;color:#15803d;' : ($t->status==='trial' ? 'background:#fffbeb;color:#b45309;' : 'background:#fff1f2;color:#be123c;') }}">
                {{ ucfirst($t->status) }}
            </span>
        </a>
        @endforeach
    </div>
</div>

{{-- Transaksi Terbaru --}}
<div style="background:#fff;border-radius:14px;border:1px solid #f1f5f9;box-shadow:0 1px 3px rgba(0,0,0,0.04);overflow:hidden;">
    <div style="padding:16px 20px;border-bottom:1px solid #f8fafc;display:flex;align-items:center;justify-content:space-between;">
        <p style="font-size:14px;font-weight:600;color:#0f172a;">Transaksi Terbaru (Semua Tenant)</p>
        <a href="{{ route('superadmin.laporan') }}" style="font-size:12px;color:#0F6E56;font-weight:500;text-decoration:none;">Lihat semua</a>
    </div>
    <div class="overflow-x-auto">
        <table style="width:100%;border-collapse:collapse;min-width:500px;">
            <thead><tr style="background:#f8fafc;border-bottom:1px solid #f1f5f9;">
                <th style="text-align:left;font-size:11px;font-weight:600;color:#64748b;padding:10px 18px;text-transform:uppercase;">Invoice</th>
                <th style="text-align:left;font-size:11px;font-weight:600;color:#64748b;padding:10px 18px;text-transform:uppercase;">Tenant</th>
                <th style="text-align:left;font-size:11px;font-weight:600;color:#64748b;padding:10px 18px;text-transform:uppercase;">Kasir</th>
                <th style="text-align:right;font-size:11px;font-weight:600;color:#64748b;padding:10px 18px;text-transform:uppercase;">Total</th>
                <th style="text-align:left;font-size:11px;font-weight:600;color:#64748b;padding:10px 18px;text-transform:uppercase;">Waktu</th>
            </tr></thead>
            <tbody>
            @foreach($recentTransactions as $t)
            <tr style="border-bottom:1px solid #f8fafc;transition:background 0.1s;" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='#fff'">
                <td style="padding:12px 18px;font-size:13px;font-weight:500;color:#0f172a;">{{ $t->invoice_no }}</td>
                <td style="padding:12px 18px;font-size:13px;color:#374151;">{{ $t->user->tenant->name ?? '—' }}</td>
                <td style="padding:12px 18px;font-size:13px;color:#64748b;">{{ $t->user->name }}</td>
                <td style="padding:12px 18px;text-align:right;font-size:13px;font-weight:700;color:#0f172a;">Rp {{ number_format($t->total,0,',','.') }}</td>
                <td style="padding:12px 18px;font-size:12.5px;color:#64748b;">{{ $t->created_at->diffForHumans() }}</td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<script>
const labels = @json($dailyRevenue->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M')));
const data   = @json($dailyRevenue->pluck('total'));
new Chart(document.getElementById('revenueChart'), {
    type: 'bar',
    data: { labels, datasets: [{ label:'Revenue', data, backgroundColor:'#0F6E56', borderRadius:5, borderSkipped:false }] },
    options: {
        responsive:true, maintainAspectRatio:false,
        plugins:{ legend:{display:false}, tooltip:{ callbacks:{ label: c=>'Rp '+Math.round(c.raw).toLocaleString('id-ID') } } },
        scales:{
            y:{ ticks:{ callback:v=>'Rp '+(v/1000).toFixed(0)+'k', font:{family:'Inter',size:11}, color:'#94a3b8' }, grid:{color:'#f1f5f9'}, border:{display:false} },
            x:{ ticks:{ font:{family:'Inter',size:11}, color:'#94a3b8' }, grid:{display:false}, border:{display:false} }
        }
    }
});
</script>
@endpush
