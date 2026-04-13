@extends('superadmin.layout')
@section('title', $tenant->name)
@section('page-title', $tenant->name)
@section('page-subtitle', 'Detail dan statistik tenant')

@section('header-actions')
<a href="{{ route('superadmin.tenants') }}" class="btn-secondary">&larr; Kembali</a>
@endsection

@section('content')

{{-- Status Bar --}}
<div style="background:#fff;border-radius:14px;border:1px solid #f1f5f9;padding:20px;margin-bottom:16px;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;">
    <div style="display:flex;align-items:center;gap:14px;">
        <div style="width:48px;height:48px;background:#f0fdf6;border-radius:14px;display:flex;align-items:center;justify-content:center;">
            <span style="color:#0F6E56;font-size:18px;font-weight:700;">{{ strtoupper(substr($tenant->name,0,2)) }}</span>
        </div>
        <div>
            <p style="font-size:17px;font-weight:700;color:#0f172a;">{{ $tenant->name }}</p>
            <p style="font-size:12.5px;color:#64748b;font-family:monospace;">{{ $tenant->subdomain }}.tokaku.id</p>
        </div>
    </div>
    <div style="display:flex;align-items:center;gap:8px;">
        <span style="font-size:13px;font-weight:500;padding:6px 14px;border-radius:99px;
            {{ $tenant->status==='active'?'background:#f0fdf4;color:#15803d;':($tenant->status==='trial'?'background:#fffbeb;color:#b45309;':'background:#fff1f2;color:#be123c;') }}">
            {{ ucfirst($tenant->status) }}
            @if($tenant->status==='trial' && $tenant->trial_ends_at)
                · {{ $tenant->trial_ends_at->diffForHumans() }}
            @endif
        </span>
        <form method="POST" action="{{ route('superadmin.tenants.suspend', $tenant) }}">
            @csrf @method('PUT')
            <button type="submit" class="btn-secondary" onclick="return confirm('{{ $tenant->status==='suspended'?'Aktifkan':'Tangguhkan' }} tenant ini?')"
                style="{{ $tenant->status==='suspended'?'color:#15803d;border-color:#bbf7d2;':'color:#be123c;border-color:#fecdd3;' }}">
                {{ $tenant->status==='suspended' ? 'Aktifkan' : 'Tangguhkan' }}
            </button>
        </form>
    </div>
</div>

{{-- Update Status --}}
<div style="background:#fff;border-radius:14px;border:1px solid #f1f5f9;padding:20px;margin-bottom:16px;">
    <p style="font-size:13.5px;font-weight:600;color:#0f172a;margin-bottom:12px;">Ubah Status & Tanggal Trial</p>
    <form method="POST" action="{{ route('superadmin.tenants.status', $tenant) }}" style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;">
        @csrf @method('PUT')
        <div>
            <label style="display:block;font-size:12px;font-weight:500;color:#64748b;margin-bottom:4px;">Status</label>
            <select name="status" class="form-input" style="width:auto;cursor:pointer;">
                <option value="trial" {{ $tenant->status==='trial'?'selected':'' }}>Trial</option>
                <option value="active" {{ $tenant->status==='active'?'selected':'' }}>Aktif</option>
                <option value="suspended" {{ $tenant->status==='suspended'?'selected':'' }}>Suspended</option>
            </select>
        </div>
        <div>
            <label style="display:block;font-size:12px;font-weight:500;color:#64748b;margin-bottom:4px;">Trial Berakhir</label>
            <input type="date" name="trial_ends_at" value="{{ $tenant->trial_ends_at?->format('Y-m-d') }}" class="form-input" style="width:auto;">
        </div>
        <button type="submit" class="btn-primary">Simpan</button>
    </form>
</div>

{{-- Stats --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px;margin-bottom:16px;">
    @foreach([
        ['Total Transaksi',$tenant->transactions_count,'transaksi'],
        ['Total Revenue','Rp '.number_format($tenant->transactions_sum_total??0,0,',','.'),'semua waktu'],
        ['Total Produk',$tenant->products_count,'produk'],
        ['Total User',$tenant->users_count,'user aktif'],
    ] as [$lbl,$val,$sub])
    <div style="background:#fff;border-radius:12px;border:1px solid #f1f5f9;padding:16px;">
        <p style="font-size:11.5px;font-weight:500;color:#64748b;margin-bottom:6px;">{{ $lbl }}</p>
        <p style="font-size:18px;font-weight:700;color:#0f172a;">{{ $val }}</p>
        <p style="font-size:11px;color:#94a3b8;margin-top:3px;">{{ $sub }}</p>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">

    {{-- Users --}}
    <div style="background:#fff;border-radius:14px;border:1px solid #f1f5f9;overflow:hidden;">
        <div style="padding:14px 18px;border-bottom:1px solid #f8fafc;">
            <p style="font-size:13.5px;font-weight:600;color:#0f172a;">Tim Toko ({{ $users->count() }})</p>
        </div>
        @foreach($users as $u)
        <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 18px;border-bottom:1px solid #f8fafc;">
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:32px;height:32px;background:#f0fdf6;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <span style="color:#0F6E56;font-size:11px;font-weight:700;">{{ strtoupper(substr($u->name,0,2)) }}</span>
                </div>
                <div>
                    <p style="font-size:13px;font-weight:500;color:#0f172a;">{{ $u->name }}</p>
                    <p style="font-size:12px;color:#94a3b8;">{{ $u->email }}</p>
                </div>
            </div>
            <span style="font-size:11.5px;font-weight:500;padding:3px 9px;border-radius:99px;
                {{ $u->role==='owner'?'background:#f0fdf4;color:#15803d;':($u->role==='admin'?'background:#eff6ff;color:#1d4ed8;':'background:#f8fafc;color:#64748b;') }}">
                {{ ucfirst($u->role) }}
            </span>
        </div>
        @endforeach
    </div>

    {{-- Revenue Bulanan --}}
    <div style="background:#fff;border-radius:14px;border:1px solid #f1f5f9;overflow:hidden;">
        <div style="padding:14px 18px;border-bottom:1px solid #f8fafc;">
            <p style="font-size:13.5px;font-weight:600;color:#0f172a;">Revenue per Bulan</p>
        </div>
        @forelse($monthlyRevenue as $m)
        <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 18px;border-bottom:1px solid #f8fafc;">
            <div>
                <p style="font-size:13px;font-weight:500;color:#0f172a;">{{ \Carbon\Carbon::parse($m->month.'-01')->format('F Y') }}</p>
                <p style="font-size:12px;color:#94a3b8;">{{ $m->count }} transaksi</p>
            </div>
            <p style="font-size:13.5px;font-weight:700;color:#0F6E56;">Rp {{ number_format($m->total,0,',','.') }}</p>
        </div>
        @empty
        <div style="padding:30px;text-align:center;color:#94a3b8;font-size:13.5px;">Belum ada transaksi.</div>
        @endforelse
    </div>
</div>

{{-- Produk Terlaris --}}
<div style="background:#fff;border-radius:14px;border:1px solid #f1f5f9;overflow:hidden;margin-bottom:16px;">
    <div style="padding:14px 18px;border-bottom:1px solid #f8fafc;">
        <p style="font-size:13.5px;font-weight:600;color:#0f172a;">Produk Terlaris</p>
    </div>
    @forelse($topProducts as $i => $p)
    <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 18px;border-bottom:1px solid #f8fafc;">
        <div style="display:flex;align-items:center;gap:10px;">
            <div style="width:24px;height:24px;background:#f0fdf6;border-radius:6px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <span style="font-size:11px;font-weight:700;color:#0F6E56;">{{ $i+1 }}</span>
            </div>
            <p style="font-size:13px;font-weight:500;color:#0f172a;">{{ $p->product_name }}</p>
        </div>
        <div style="text-align:right;">
            <p style="font-size:13px;font-weight:700;color:#0f172a;">{{ number_format($p->total_qty) }} pcs</p>
            <p style="font-size:11.5px;color:#64748b;">Rp {{ number_format($p->total_revenue,0,',','.') }}</p>
        </div>
    </div>
    @empty
    <div style="padding:30px;text-align:center;color:#94a3b8;font-size:13.5px;">Belum ada data produk.</div>
    @endforelse
</div>

{{-- Transaksi Terbaru --}}
<div style="background:#fff;border-radius:14px;border:1px solid #f1f5f9;overflow:hidden;">
    <div style="padding:14px 18px;border-bottom:1px solid #f8fafc;">
        <p style="font-size:13.5px;font-weight:600;color:#0f172a;">Transaksi Terbaru</p>
    </div>
    <div class="overflow-x-auto">
        <table style="width:100%;border-collapse:collapse;min-width:400px;">
            <thead><tr style="background:#f8fafc;border-bottom:1px solid #f1f5f9;">
                <th style="text-align:left;font-size:11px;font-weight:600;color:#64748b;padding:10px 16px;text-transform:uppercase;">Invoice</th>
                <th style="text-align:left;font-size:11px;font-weight:600;color:#64748b;padding:10px 16px;text-transform:uppercase;">Kasir</th>
                <th style="text-align:left;font-size:11px;font-weight:600;color:#64748b;padding:10px 16px;text-transform:uppercase;">Metode</th>
                <th style="text-align:right;font-size:11px;font-weight:600;color:#64748b;padding:10px 16px;text-transform:uppercase;">Total</th>
                <th style="text-align:left;font-size:11px;font-weight:600;color:#64748b;padding:10px 16px;text-transform:uppercase;">Waktu</th>
            </tr></thead>
            <tbody>
            @forelse($recentTransactions as $t)
            <tr style="border-bottom:1px solid #f8fafc;">
                <td style="padding:11px 16px;font-size:13px;font-weight:500;color:#0f172a;">{{ $t->invoice_no }}</td>
                <td style="padding:11px 16px;font-size:13px;color:#374151;">{{ $t->user->name }}</td>
                <td style="padding:11px 16px;">
                    <span style="font-size:11.5px;font-weight:500;padding:3px 8px;border-radius:99px;{{ $t->payment_method==='cash'?'background:#f0fdf4;color:#15803d;':($t->payment_method==='qris'?'background:#eff6ff;color:#1d4ed8;':'background:#f5f3ff;color:#6d28d9;') }}">{{ strtoupper($t->payment_method) }}</span>
                </td>
                <td style="padding:11px 16px;text-align:right;font-size:13px;font-weight:700;color:#0f172a;">Rp {{ number_format($t->total,0,',','.') }}</td>
                <td style="padding:11px 16px;font-size:12.5px;color:#64748b;">{{ $t->created_at->format('d M Y, H:i') }}</td>
            </tr>
            @empty
            <tr><td colspan="5" style="padding:40px;text-align:center;color:#94a3b8;font-size:13.5px;">Belum ada transaksi.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
