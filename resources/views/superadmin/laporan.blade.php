@extends('superadmin.layout')
@section('title','Laporan Global')
@section('page-title','Laporan Global')
@section('page-subtitle','Semua transaksi lintas tenant')

@section('content')

<div style="background:#fff;border-radius:14px;border:1px solid #f1f5f9;padding:16px 20px;margin-bottom:16px;">
    <form method="GET" style="display:flex;flex-wrap:wrap;align-items:flex-end;gap:10px;">
        <div>
            <label style="display:block;font-size:12px;font-weight:500;color:#64748b;margin-bottom:4px;">Dari</label>
            <input type="date" name="start_date" value="{{ $startDate }}" class="form-input" style="width:auto;">
        </div>
        <div>
            <label style="display:block;font-size:12px;font-weight:500;color:#64748b;margin-bottom:4px;">Sampai</label>
            <input type="date" name="end_date" value="{{ $endDate }}" class="form-input" style="width:auto;">
        </div>
        <div>
            <label style="display:block;font-size:12px;font-weight:500;color:#64748b;margin-bottom:4px;">Tenant</label>
            <select name="tenant_id" class="form-input" style="width:auto;cursor:pointer;">
                <option value="">Semua Tenant</option>
                @foreach($allTenants as $t)
                <option value="{{ $t->id }}" {{ $tenantId==$t->id?'selected':'' }}>{{ $t->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn-primary">Tampilkan</button>
    </form>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;margin-bottom:16px;">
    <div style="background:#0F6E56;border-radius:14px;padding:18px;">
        <p style="font-size:12px;color:rgba(255,255,255,0.7);margin-bottom:8px;">Total Revenue</p>
        <p style="font-size:20px;font-weight:700;color:#fff;">Rp {{ number_format($totalRevenue,0,',','.') }}</p>
    </div>
    <div style="background:#fff;border-radius:14px;border:1px solid #f1f5f9;padding:18px;">
        <p style="font-size:12px;font-weight:500;color:#64748b;margin-bottom:8px;">Total Transaksi</p>
        <p style="font-size:20px;font-weight:700;color:#0f172a;">{{ number_format($totalCount) }}</p>
    </div>
</div>

{{-- Revenue per tenant --}}
@if($revenueByTenant->count() > 0)
<div style="background:#fff;border-radius:14px;border:1px solid #f1f5f9;overflow:hidden;margin-bottom:16px;">
    <div style="padding:14px 18px;border-bottom:1px solid #f8fafc;">
        <p style="font-size:13.5px;font-weight:600;color:#0f172a;">Revenue per Tenant</p>
    </div>
    @foreach($revenueByTenant as $r)
    <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 18px;border-bottom:1px solid #f8fafc;">
        <div style="display:flex;align-items:center;gap:10px;">
            <div style="width:32px;height:32px;background:#f0fdf6;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <span style="color:#0F6E56;font-size:11px;font-weight:700;">{{ strtoupper(substr($r['tenant']?->name??'?',0,2)) }}</span>
            </div>
            <div>
                <p style="font-size:13px;font-weight:500;color:#0f172a;">{{ $r['tenant']?->name ?? 'Unknown' }}</p>
                <p style="font-size:12px;color:#94a3b8;">{{ $r['count'] }} transaksi</p>
            </div>
        </div>
        <p style="font-size:13.5px;font-weight:700;color:#0F6E56;">Rp {{ number_format($r['total'],0,',','.') }}</p>
    </div>
    @endforeach
</div>
@endif

{{-- Tabel transaksi --}}
<div style="background:#fff;border-radius:14px;border:1px solid #f1f5f9;overflow:hidden;">
    <div class="overflow-x-auto">
        <table style="width:100%;border-collapse:collapse;min-width:600px;">
            <thead><tr style="background:#f8fafc;border-bottom:1px solid #f1f5f9;">
                @foreach(['Invoice','Tenant','Kasir','Metode','Total','Waktu'] as $h)
                <th style="text-align:{{ $h==='Total'?'right':'left' }};font-size:11px;font-weight:600;color:#64748b;padding:10px 16px;text-transform:uppercase;">{{ $h }}</th>
                @endforeach
            </tr></thead>
            <tbody>
            @forelse($transactions as $t)
            <tr style="border-bottom:1px solid #f8fafc;transition:background 0.1s;" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='#fff'">
                <td style="padding:12px 16px;font-size:13px;font-weight:500;color:#0f172a;">{{ $t->invoice_no }}</td>
                <td style="padding:12px 16px;font-size:13px;color:#374151;">{{ $t->user?->tenant?->name ?? '—' }}</td>
                <td style="padding:12px 16px;font-size:13px;color:#64748b;">{{ $t->user?->name ?? '—' }}</td>
                <td style="padding:12px 16px;">
                    <span style="font-size:11.5px;font-weight:500;padding:3px 8px;border-radius:99px;{{ $t->payment_method==='cash'?'background:#f0fdf4;color:#15803d;':($t->payment_method==='qris'?'background:#eff6ff;color:#1d4ed8;':'background:#f5f3ff;color:#6d28d9;') }}">{{ strtoupper($t->payment_method) }}</span>
                </td>
                <td style="padding:12px 16px;text-align:right;font-size:13px;font-weight:700;color:#0f172a;">Rp {{ number_format($t->total,0,',','.') }}</td>
                <td style="padding:12px 16px;font-size:12.5px;color:#64748b;">{{ $t->created_at->format('d M Y, H:i') }}</td>
            </tr>
            @empty
            <tr><td colspan="6" style="padding:50px;text-align:center;color:#94a3b8;font-size:13.5px;">Tidak ada transaksi di periode ini.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($transactions->hasPages())
    <div style="padding:12px 16px;border-top:1px solid #f8fafc;">{{ $transactions->appends(request()->query())->links() }}</div>
    @endif
</div>
@endsection
