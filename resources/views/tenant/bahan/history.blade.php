@extends('layouts.app')
@section('title','Riwayat Stok — '.$bahan->name)
@section('page-title','Riwayat Stok')
@section('page-subtitle',$bahan->name)

@section('content')
<div style="margin-bottom:14px;">
    <a href="{{ route('tenant.bahan.index') }}" style="font-size:13px;color:#0F6E56;font-weight:700;text-decoration:none;">&larr; Kembali ke Gudang Bahan</a>
</div>

<div style="background:#fff;border:1px solid #f1f5f9;border-radius:16px;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,.04);margin-bottom:16px;">
    <p style="font-size:12px;color:#64748b;font-weight:700;">Stok Saat Ini</p>
    <p style="font-size:28px;font-weight:800;color:{{ $bahan->isLowStock() ? '#be123c' : '#0F6E56' }};margin-top:6px;">{{ $bahan->stock }} <span style="font-size:15px;color:#94a3b8;">{{ $bahan->unit }}</span></p>
</div>

<div style="background:#fff;border:1px solid #f1f5f9;border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.04);">
    <div class="table-responsive overflow-x-auto">
        <table style="width:100%;border-collapse:collapse;min-width:680px;">
            <thead><tr style="background:#f8fafc;">
                <th style="text-align:left;padding:11px 18px;font-size:11px;color:#64748b;text-transform:uppercase;">Waktu</th>
                <th style="text-align:left;padding:11px 18px;font-size:11px;color:#64748b;text-transform:uppercase;">Jenis</th>
                <th style="text-align:right;padding:11px 18px;font-size:11px;color:#64748b;text-transform:uppercase;">Perubahan</th>
                <th style="text-align:right;padding:11px 18px;font-size:11px;color:#64748b;text-transform:uppercase;">Sisa</th>
                <th style="text-align:left;padding:11px 18px;font-size:11px;color:#64748b;text-transform:uppercase;">Oleh</th>
                <th style="text-align:left;padding:11px 18px;font-size:11px;color:#64748b;text-transform:uppercase;">Catatan</th>
            </tr></thead>
            <tbody>
            @forelse($logs as $log)
                <tr style="border-bottom:1px solid #f8fafc;">
                    <td data-label="Waktu" style="padding:13px 18px;font-size:13px;color:#334155;">{{ $log->created_at->format('d M Y H:i') }}</td>
                    <td data-label="Jenis" style="padding:13px 18px;">
                        <span style="font-size:11.5px;border-radius:999px;padding:4px 10px;font-weight:700;
                            background:{{ $log->type==='in' ? '#f0fdf6' : ($log->type==='out' ? '#fef2f2' : '#f8fafc') }};
                            color:{{ $log->type==='in' ? '#085041' : ($log->type==='out' ? '#b91c1c' : '#475569') }};">
                            {{ $log->type_label }}
                        </span>
                    </td>
                    <td data-label="Perubahan" style="padding:13px 18px;text-align:right;font-size:13.5px;font-weight:800;color:{{ $log->qty_change >= 0 ? '#0F6E56' : '#be123c' }};">
                        {{ $log->qty_change > 0 ? '+' : '' }}{{ $log->qty_change }}
                    </td>
                    <td data-label="Sisa" style="padding:13px 18px;text-align:right;font-size:13px;color:#334155;">{{ $log->qty_after }}</td>
                    <td data-label="Oleh" style="padding:13px 18px;font-size:13px;color:#64748b;">{{ $log->user->name ?? '-' }}</td>
                    <td data-label="Catatan" style="padding:13px 18px;font-size:13px;color:#64748b;">{{ $log->note ?? '-' }}</td>
                </tr>
            @empty
                <tr><td data-empty colspan="6" style="padding:45px;text-align:center;color:#94a3b8;font-size:13px;">Belum ada riwayat.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div style="padding:14px 18px;">{{ $logs->links() }}</div>
</div>
@endsection
