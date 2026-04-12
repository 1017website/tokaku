@extends('layouts.app')
@section('title','Laporan')
@section('page-title','Laporan Penjualan')
@section('page-subtitle','Rekap transaksi, omzet, dan produk terlaris')

@section('content')

{{-- Filter --}}
<div style="background:#fff;border-radius:16px;border:1px solid #f1f5f9;box-shadow:0 1px 3px rgba(0,0,0,0.04);padding:16px 20px;margin-bottom:16px;">
    <form method="GET" style="display:flex;flex-wrap:wrap;align-items:flex-end;gap:12px;">
        <div>
            <label style="display:block;font-size:12px;font-weight:500;color:#64748b;margin-bottom:5px;">Dari Tanggal</label>
            <input type="date" name="start_date" value="{{ $startDate }}"
                style="border:1.5px solid #e2e8f0;border-radius:10px;padding:9px 14px;font-size:13.5px;font-family:Inter,sans-serif;outline:none;background:#fafafa;"
                onfocus="this.style.borderColor='#0F6E56'" onblur="this.style.borderColor='#e2e8f0'">
        </div>
        <div>
            <label style="display:block;font-size:12px;font-weight:500;color:#64748b;margin-bottom:5px;">Sampai Tanggal</label>
            <input type="date" name="end_date" value="{{ $endDate }}"
                style="border:1.5px solid #e2e8f0;border-radius:10px;padding:9px 14px;font-size:13.5px;font-family:Inter,sans-serif;outline:none;background:#fafafa;"
                onfocus="this.style.borderColor='#0F6E56'" onblur="this.style.borderColor='#e2e8f0'">
        </div>
        <button type="submit"
            style="background:#0F6E56;color:#fff;font-family:Inter,sans-serif;font-size:13.5px;font-weight:500;padding:10px 18px;border-radius:10px;border:none;cursor:pointer;"
            onmouseover="this.style.background='#085041'" onmouseout="this.style.background='#0F6E56'">
            Tampilkan
        </button>
        <a href="{{ route('tenant.laporan.export') }}?start_date={{ $startDate }}&end_date={{ $endDate }}"
            style="display:inline-flex;align-items:center;gap:6px;background:#fff;color:#374151;font-family:Inter,sans-serif;font-size:13.5px;font-weight:500;padding:10px 18px;border-radius:10px;border:1.5px solid #e2e8f0;text-decoration:none;transition:all 0.15s;"
            onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#fff'">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Export Excel
        </a>
    </form>
</div>

{{-- Summary Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
    <div style="background:#0F6E56;border-radius:14px;padding:18px;">
        <p style="font-size:12px;font-weight:500;color:rgba(255,255,255,0.7);margin-bottom:8px;">Total Omzet</p>
        <p style="font-size:20px;font-weight:700;color:#fff;letter-spacing:-0.5px;">Rp {{ number_format($totalRevenue,0,',','.') }}</p>
    </div>
    <div style="background:#fff;border-radius:14px;border:1px solid #f1f5f9;padding:18px;">
        <p style="font-size:12px;font-weight:500;color:#64748b;margin-bottom:8px;">Total Transaksi</p>
        <p style="font-size:20px;font-weight:700;color:#0f172a;letter-spacing:-0.5px;">{{ number_format($totalTransactions) }}</p>
    </div>
    <div style="background:#fff;border-radius:14px;border:1px solid #f1f5f9;padding:18px;">
        <p style="font-size:12px;font-weight:500;color:#64748b;margin-bottom:8px;">Rata-rata/Transaksi</p>
        <p style="font-size:20px;font-weight:700;color:#0f172a;letter-spacing:-0.5px;">
            Rp {{ $totalTransactions > 0 ? number_format($totalRevenue/$totalTransactions,0,',','.') : 0 }}
        </p>
    </div>
    <div style="background:#fff;border-radius:14px;border:1px solid #f1f5f9;padding:18px;">
        <p style="font-size:12px;font-weight:500;color:#64748b;margin-bottom:8px;">Total Diskon</p>
        <p style="font-size:20px;font-weight:700;color:#f43f5e;letter-spacing:-0.5px;">Rp {{ number_format($totalDiscount,0,',','.') }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">

    {{-- Chart Omzet Harian --}}
    <div class="lg:col-span-2" style="background:#fff;border-radius:16px;border:1px solid #f1f5f9;box-shadow:0 1px 3px rgba(0,0,0,0.04);padding:20px;">
        <p style="font-size:14px;font-weight:600;color:#0f172a;margin-bottom:16px;">Grafik Omzet Harian</p>
        <div style="position:relative;height:200px;">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    {{-- Summary per Metode Bayar --}}
    <div style="background:#fff;border-radius:16px;border:1px solid #f1f5f9;box-shadow:0 1px 3px rgba(0,0,0,0.04);padding:20px;">
        <p style="font-size:14px;font-weight:600;color:#0f172a;margin-bottom:16px;">Per Metode Bayar</p>
        @forelse($byPayment as $bp)
        <div style="margin-bottom:14px;">
            <div style="display:flex;justify-content:space-between;margin-bottom:5px;">
                <span style="font-size:13px;font-weight:500;color:#374151;text-transform:uppercase;">{{ $bp->payment_method }}</span>
                <span style="font-size:12px;color:#64748b;">{{ $bp->count }}x</span>
            </div>
            <div style="background:#f1f5f9;border-radius:99px;height:6px;overflow:hidden;">
                <div style="background:#0F6E56;height:100%;border-radius:99px;width:{{ $totalRevenue > 0 ? round(($bp->total/$totalRevenue)*100) : 0 }}%;"></div>
            </div>
            <p style="font-size:12px;color:#0F6E56;font-weight:600;margin-top:4px;">Rp {{ number_format($bp->total,0,',','.') }}</p>
        </div>
        @empty
        <p style="font-size:13.5px;color:#94a3b8;">Tidak ada data.</p>
        @endforelse
    </div>
</div>

{{-- Produk Terlaris --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
    <div style="background:#fff;border-radius:16px;border:1px solid #f1f5f9;box-shadow:0 1px 3px rgba(0,0,0,0.04);overflow:hidden;">
        <div style="padding:16px 20px;border-bottom:1px solid #f8fafc;">
            <p style="font-size:14px;font-weight:600;color:#0f172a;">Produk Terlaris</p>
        </div>
        <div>
            @forelse($topProducts as $i => $p)
            <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 20px;border-bottom:1px solid #f8fafc;">
                <div style="display:flex;align-items:center;gap:12px;">
                    <div style="width:26px;height:26px;background:#f0fdf6;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <span style="font-size:12px;font-weight:700;color:#0F6E56;">{{ $i+1 }}</span>
                    </div>
                    <p style="font-size:13.5px;font-weight:500;color:#0f172a;">{{ $p->product_name }}</p>
                </div>
                <div style="text-align:right;">
                    <p style="font-size:13px;font-weight:700;color:#0f172a;">{{ number_format($p->total_qty) }} pcs</p>
                    <p style="font-size:11.5px;color:#64748b;">Rp {{ number_format($p->total_revenue,0,',','.') }}</p>
                </div>
            </div>
            @empty
            <div style="padding:40px 20px;text-align:center;font-size:14px;color:#94a3b8;">Tidak ada data produk.</div>
            @endforelse
        </div>
    </div>

    {{-- Transaksi Terbaru --}}
    <div style="background:#fff;border-radius:16px;border:1px solid #f1f5f9;box-shadow:0 1px 3px rgba(0,0,0,0.04);overflow:hidden;">
        <div style="padding:16px 20px;border-bottom:1px solid #f8fafc;">
            <p style="font-size:14px;font-weight:600;color:#0f172a;">Daftar Transaksi</p>
        </div>
        <div class="overflow-x-auto">
            <table style="width:100%;border-collapse:collapse;min-width:400px;">
                <thead>
                    <tr style="background:#f8fafc;border-bottom:1px solid #f1f5f9;">
                        <th style="text-align:left;font-size:11px;font-weight:600;color:#64748b;padding:10px 16px;text-transform:uppercase;">Invoice</th>
                        <th style="text-align:left;font-size:11px;font-weight:600;color:#64748b;padding:10px 16px;text-transform:uppercase;">Metode</th>
                        <th style="text-align:right;font-size:11px;font-weight:600;color:#64748b;padding:10px 16px;text-transform:uppercase;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $t)
                    <tr style="border-bottom:1px solid #f8fafc;transition:background 0.1s;" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='#fff'">
                        <td style="padding:11px 16px;">
                            <p style="font-size:13px;font-weight:500;color:#0f172a;">{{ $t->invoice_no }}</p>
                            <p style="font-size:11.5px;color:#94a3b8;">{{ $t->created_at->format('d M, H:i') }}</p>
                        </td>
                        <td style="padding:11px 16px;">
                            <span style="font-size:11.5px;font-weight:500;padding:3px 8px;border-radius:99px;{{ $t->payment_method==='cash'?'background:#f0fdf4;color:#15803d;':($t->payment_method==='qris'?'background:#eff6ff;color:#1d4ed8;':'background:#f5f3ff;color:#6d28d9;') }}">
                                {{ strtoupper($t->payment_method) }}
                            </span>
                        </td>
                        <td style="padding:11px 16px;text-align:right;font-size:13px;font-weight:700;color:#0f172a;">Rp {{ number_format($t->total,0,',','.') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" style="padding:40px 16px;text-align:center;font-size:13.5px;color:#94a3b8;">Tidak ada transaksi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transactions->hasPages())
        <div style="padding:12px 16px;border-top:1px solid #f8fafc;">{{ $transactions->appends(request()->query())->links() }}</div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<script>
const labels = @json($dailyRevenue->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M')));
const data   = @json($dailyRevenue->pluck('total'));

const ctx = document.getElementById('revenueChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels,
        datasets: [{
            label: 'Omzet',
            data,
            backgroundColor: '#0F6E56',
            borderRadius: 6,
            borderSkipped: false,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => 'Rp ' + Math.round(ctx.raw).toLocaleString('id-ID')
                }
            }
        },
        scales: {
            y: {
                ticks: {
                    callback: v => 'Rp ' + (v/1000).toFixed(0) + 'k',
                    font: { family: 'Inter', size: 11 },
                    color: '#94a3b8',
                },
                grid: { color: '#f1f5f9' },
                border: { display: false },
            },
            x: {
                ticks: { font: { family: 'Inter', size: 11 }, color: '#94a3b8' },
                grid: { display: false },
                border: { display: false },
            }
        }
    }
});
</script>
@endpush
