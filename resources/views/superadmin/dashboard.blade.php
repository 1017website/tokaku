@extends('superadmin.layout')
@section('title','Dashboard')
@section('page-title','Dashboard')
@section('page-subtitle','Ringkasan seluruh tenant Tokaku')
@section('header-actions')
<a href="{{ route('superadmin.tenants') }}" class="btn-primary">
    <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
    Tambah Tenant
</a>
@endsection

@section('content')
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;margin-bottom:20px;">
    @foreach([['Total Tenant',$totalTenants,'toko terdaftar','#0F6E56','#f0fdf6','M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],['Tenant Aktif',$activeTenants,'berlangganan aktif','#16a34a','#f0fdf4','M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],['Masa Trial',$trialTenants,'sedang trial','#d97706','#fffbeb','M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z']] as [$lbl,$val,$sub,$clr,$bg,$icon])
    <div style="background:#fff;border-radius:14px;border:1px solid #f1f5f9;box-shadow:0 1px 3px rgba(0,0,0,0.04);padding:18px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
            <p style="font-size:12px;font-weight:500;color:#64748b;">{{ $lbl }}</p>
            <div style="width:30px;height:30px;background:{{ $bg }};border-radius:8px;display:flex;align-items:center;justify-content:center;">
                <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="{{ $clr }}" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
            </div>
        </div>
        <p style="font-size:24px;font-weight:700;color:#0f172a;letter-spacing:-0.5px;">{{ $val }}</p>
        <p style="font-size:11.5px;color:#94a3b8;margin-top:4px;">{{ $sub }}</p>
    </div>
    @endforeach
</div>

<div style="background:#fff;border-radius:14px;border:1px solid #f1f5f9;box-shadow:0 1px 3px rgba(0,0,0,0.04);overflow:hidden;">
    <div style="padding:16px 20px;border-bottom:1px solid #f8fafc;display:flex;align-items:center;justify-content:space-between;">
        <p style="font-size:14px;font-weight:600;color:#0f172a;">Tenant Terbaru</p>
        <a href="{{ route('superadmin.tenants') }}" style="font-size:12px;color:#0F6E56;font-weight:500;text-decoration:none;">Lihat semua</a>
    </div>
    <div class="overflow-x-auto">
        <table style="width:100%;border-collapse:collapse;min-width:400px;">
            <thead><tr style="background:#f8fafc;border-bottom:1px solid #f1f5f9;">
                @foreach(['Nama Toko','Subdomain','Status','Terdaftar'] as $h)
                <th style="text-align:left;font-size:11px;font-weight:600;color:#64748b;padding:10px 18px;text-transform:uppercase;letter-spacing:0.3px;">{{ $h }}</th>
                @endforeach
            </tr></thead>
            <tbody>
            @foreach(\App\Models\Tenant::latest()->limit(5)->get() as $t)
            <tr style="border-bottom:1px solid #f8fafc;transition:background 0.1s;" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='#fff'">
                <td style="padding:13px 18px;font-size:13.5px;font-weight:500;color:#0f172a;">{{ $t->name }}</td>
                <td style="padding:13px 18px;font-family:monospace;font-size:12.5px;color:#64748b;">{{ $t->subdomain }}</td>
                <td style="padding:13px 18px;">
                    @if($t->status==='active')<span style="font-size:12px;font-weight:500;background:#f0fdf4;color:#15803d;padding:3px 10px;border-radius:99px;">Aktif</span>
                    @elseif($t->status==='trial')<span style="font-size:12px;font-weight:500;background:#fffbeb;color:#b45309;padding:3px 10px;border-radius:99px;">Trial</span>
                    @else<span style="font-size:12px;font-weight:500;background:#fff1f2;color:#be123c;padding:3px 10px;border-radius:99px;">Suspended</span>@endif
                </td>
                <td style="padding:13px 18px;font-size:13px;color:#64748b;">{{ $t->created_at->format('d M Y') }}</td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
