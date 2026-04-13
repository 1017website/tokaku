@extends('layouts.app')
@section('title','Semua Riwayat Stok')
@section('page-title','Semua Riwayat Stok')
@section('page-subtitle','Log perubahan stok semua produk')

@section('header-actions')
<a href="{{ route('tenant.stok.index') }}" class="btn-secondary">&larr; Kembali</a>
@endsection

@section('content')

<div style="background:#fff;border-radius:16px;border:1px solid #f1f5f9;box-shadow:0 1px 3px rgba(0,0,0,0.04);overflow:hidden;">
    <div style="padding:14px 18px;border-bottom:1px solid #f8fafc;display:flex;flex-wrap:wrap;align-items:center;gap:8px;">
        <form method="GET" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
            <select name="type" onchange="this.form.submit()" style="border:1.5px solid #e2e8f0;border-radius:10px;padding:8px 12px;font-size:13.5px;font-family:Inter,sans-serif;outline:none;background:#fafafa;cursor:pointer;">
                <option value="">Semua Jenis</option>
                <option value="restock" {{ request('type')==='restock'?'selected':'' }}>Restock</option>
                <option value="adjustment" {{ request('type')==='adjustment'?'selected':'' }}>Penyesuaian</option>
                <option value="sale" {{ request('type')==='sale'?'selected':'' }}>Penjualan</option>
                <option value="correction" {{ request('type')==='correction'?'selected':'' }}>Koreksi</option>
            </select>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table style="width:100%;border-collapse:collapse;min-width:600px;">
            <thead>
                <tr style="background:#f8fafc;border-bottom:1px solid #f1f5f9;">
                    <th style="text-align:left;font-size:11px;font-weight:600;color:#64748b;padding:10px 18px;text-transform:uppercase;letter-spacing:0.3px;">Produk</th>
                    <th style="text-align:left;font-size:11px;font-weight:600;color:#64748b;padding:10px 14px;text-transform:uppercase;letter-spacing:0.3px;">Jenis</th>
                    <th style="text-align:center;font-size:11px;font-weight:600;color:#64748b;padding:10px 14px;text-transform:uppercase;letter-spacing:0.3px;">Sebelum</th>
                    <th style="text-align:center;font-size:11px;font-weight:600;color:#64748b;padding:10px 14px;text-transform:uppercase;letter-spacing:0.3px;">Perubahan</th>
                    <th style="text-align:center;font-size:11px;font-weight:600;color:#64748b;padding:10px 14px;text-transform:uppercase;letter-spacing:0.3px;">Sesudah</th>
                    <th style="text-align:left;font-size:11px;font-weight:600;color:#64748b;padding:10px 14px;text-transform:uppercase;letter-spacing:0.3px;">Oleh</th>
                    <th style="text-align:left;font-size:11px;font-weight:600;color:#64748b;padding:10px 18px;text-transform:uppercase;letter-spacing:0.3px;">Waktu</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr style="border-bottom:1px solid #f8fafc;transition:background 0.1s;" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='#fff'">
                    <td style="padding:12px 18px;">
                        <a href="{{ route('tenant.stok.history', $log->product) }}" style="font-size:13.5px;font-weight:500;color:#0f172a;text-decoration:none;" onmouseover="this.style.color='#0F6E56'" onmouseout="this.style.color='#0f172a'">
                            {{ $log->product->name }}
                        </a>
                    </td>
                    <td style="padding:12px 14px;">
                        @php $ts = match($log->type) { 'restock'=>'background:#f0fdf4;color:#15803d;','adjustment'=>'background:#eff6ff;color:#1d4ed8;','sale'=>'background:#fff1f2;color:#be123c;','correction'=>'background:#fffbeb;color:#b45309;',default=>'background:#f8fafc;color:#64748b;' }; @endphp
                        <span style="font-size:12px;font-weight:500;padding:3px 10px;border-radius:99px;{{ $ts }}">{{ $log->type_label }}</span>
                    </td>
                    <td style="padding:12px 14px;text-align:center;font-size:13.5px;font-weight:600;color:#64748b;">{{ $log->qty_before }}</td>
                    <td style="padding:12px 14px;text-align:center;">
                        <span style="font-size:15px;font-weight:700;color:{{ $log->qty_change>0?'#15803d':'#be123c' }};">
                            {{ $log->qty_change > 0 ? '+' . $log->qty_change : $log->qty_change }}
                        </span>
                    </td>
                    <td style="padding:12px 14px;text-align:center;font-size:14px;font-weight:700;color:#0f172a;">{{ $log->qty_after }}</td>
                    <td style="padding:12px 14px;font-size:13px;color:#374151;">{{ $log->user->name }}</td>
                    <td style="padding:12px 18px;font-size:12.5px;color:#64748b;">{{ $log->created_at->format('d M Y, H:i') }}</td>
                </tr>
                @empty
                <tr><td colspan="7" style="padding:50px;text-align:center;font-size:14px;color:#94a3b8;">Belum ada riwayat.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
    <div style="padding:14px 18px;border-top:1px solid #f8fafc;">{{ $logs->appends(request()->query())->links() }}</div>
    @endif
</div>
@endsection
