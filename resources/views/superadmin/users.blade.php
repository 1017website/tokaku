@extends('superadmin.layout')
@section('title','Semua User')
@section('page-title','Semua User')
@section('page-subtitle','Daftar seluruh user di semua tenant')

@section('content')
<div style="background:#fff;border-radius:16px;border:1px solid #f1f5f9;box-shadow:0 1px 3px rgba(0,0,0,0.04);overflow:hidden;">
    <div style="padding:14px 18px;border-bottom:1px solid #f8fafc;display:flex;align-items:center;justify-content:space-between;">
        <p style="font-size:14px;font-weight:600;color:#0f172a;">Total: {{ $users->total() }} user</p>
        <form method="GET">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / email..."
                style="border:1.5px solid #e2e8f0;border-radius:10px;padding:8px 14px;font-size:13.5px;font-family:Inter,sans-serif;outline:none;background:#fafafa;width:220px;"
                onfocus="this.style.borderColor='#0F6E56'" onblur="this.style.borderColor='#e2e8f0'">
        </form>
    </div>
    <div class="overflow-x-auto">
        <table style="width:100%;border-collapse:collapse;min-width:500px;">
            <thead><tr style="background:#f8fafc;border-bottom:1px solid #f1f5f9;">
                <th style="text-align:left;font-size:11px;font-weight:600;color:#64748b;padding:10px 18px;text-transform:uppercase;">User</th>
                <th style="text-align:left;font-size:11px;font-weight:600;color:#64748b;padding:10px 18px;text-transform:uppercase;">Tenant</th>
                <th style="text-align:left;font-size:11px;font-weight:600;color:#64748b;padding:10px 18px;text-transform:uppercase;">Role</th>
                <th style="text-align:center;font-size:11px;font-weight:600;color:#64748b;padding:10px 18px;text-transform:uppercase;">Status</th>
                <th style="text-align:left;font-size:11px;font-weight:600;color:#64748b;padding:10px 18px;text-transform:uppercase;">Bergabung</th>
            </tr></thead>
            <tbody>
            @forelse($users as $u)
            <tr style="border-bottom:1px solid #f8fafc;transition:background 0.1s;" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='#fff'">
                <td style="padding:13px 18px;">
                    <p style="font-size:13.5px;font-weight:500;color:#0f172a;">{{ $u->name }}</p>
                    <p style="font-size:12px;color:#94a3b8;">{{ $u->email }}</p>
                </td>
                <td style="padding:13px 18px;">
                    @if($u->tenant)
                    <a href="{{ route('superadmin.tenants.detail', $u->tenant) }}" style="font-size:13px;color:#0F6E56;font-weight:500;text-decoration:none;">{{ $u->tenant->name }}</a>
                    @else
                    <span style="font-size:13px;color:#94a3b8;">—</span>
                    @endif
                </td>
                <td style="padding:13px 18px;">
                    <span style="font-size:12px;font-weight:500;padding:3px 10px;border-radius:99px;
                        {{ $u->role==='superadmin'?'background:#f5f3ff;color:#6d28d9;':($u->role==='owner'?'background:#f0fdf4;color:#15803d;':($u->role==='admin'?'background:#eff6ff;color:#1d4ed8;':'background:#f8fafc;color:#475569;')) }}">
                        {{ ucfirst($u->role) }}
                    </span>
                </td>
                <td style="padding:13px 18px;text-align:center;">
                    @if($u->is_active)
                    <span style="font-size:12px;font-weight:500;background:#f0fdf4;color:#15803d;padding:3px 10px;border-radius:99px;">Aktif</span>
                    @else
                    <span style="font-size:12px;font-weight:500;background:#fff1f2;color:#be123c;padding:3px 10px;border-radius:99px;">Nonaktif</span>
                    @endif
                </td>
                <td style="padding:13px 18px;font-size:13px;color:#64748b;">{{ $u->created_at->format('d M Y') }}</td>
            </tr>
            @empty
            <tr><td colspan="5" style="padding:50px;text-align:center;color:#94a3b8;font-size:13.5px;">Tidak ada user.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
    <div style="padding:12px 18px;border-top:1px solid #f8fafc;">{{ $users->appends(request()->query())->links() }}</div>
    @endif
</div>
@endsection
