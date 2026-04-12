@extends('superadmin.layout')
@section('title','Kelola Tenant')
@section('page-title','Kelola Tenant')
@section('page-subtitle','Semua toko yang terdaftar di Tokaku')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <div>
        <div style="background:#fff;border-radius:14px;border:1px solid #f1f5f9;box-shadow:0 1px 3px rgba(0,0,0,0.04);padding:22px;">
            <p style="font-size:14px;font-weight:600;color:#0f172a;margin-bottom:16px;">Tambah Tenant Baru</p>
            <form method="POST" action="{{ route('superadmin.tenants.store') }}" style="display:flex;flex-direction:column;gap:13px;">
                @csrf
                @if($errors->any())
                <div style="background:#fff1f2;border:1px solid #fecdd3;color:#be123c;font-size:12.5px;border-radius:10px;padding:10px 14px;">{{ $errors->first() }}</div>
                @endif
                <div>
                    <label style="display:block;font-size:12.5px;font-weight:500;color:#374151;margin-bottom:5px;">Nama Toko *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="form-input" placeholder="Warung Budi">
                </div>
                <div>
                    <label style="display:block;font-size:12.5px;font-weight:500;color:#374151;margin-bottom:5px;">Subdomain *</label>
                    <div style="display:flex;border:1.5px solid #e2e8f0;border-radius:10px;overflow:hidden;background:#fafafa;">
                        <input type="text" name="subdomain" value="{{ old('subdomain') }}" required
                            style="flex:1;border:none;padding:10px 12px;font-size:13.5px;font-family:Inter,sans-serif;outline:none;background:transparent;color:#0f172a;"
                            placeholder="warungbudi">
                        <span style="padding:10px 12px;font-size:12px;color:#94a3b8;background:#f8fafc;border-left:1.5px solid #e2e8f0;white-space:nowrap;">.tokaku.id</span>
                    </div>
                </div>
                <div>
                    <label style="display:block;font-size:12.5px;font-weight:500;color:#374151;margin-bottom:5px;">Nomor HP</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" class="form-input" placeholder="08xxxxxxxxxx">
                </div>
                <div style="padding-top:8px;border-top:1px solid #f1f5f9;">
                    <p style="font-size:12px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:0.4px;margin-bottom:10px;">Akun Owner</p>
                    <div style="display:flex;flex-direction:column;gap:10px;">
                        <div>
                            <label style="display:block;font-size:12.5px;font-weight:500;color:#374151;margin-bottom:5px;">Email *</label>
                            <input type="email" name="email" value="{{ old('email') }}" required class="form-input" placeholder="owner@email.com">
                        </div>
                        <div>
                            <label style="display:block;font-size:12.5px;font-weight:500;color:#374151;margin-bottom:5px;">Password *</label>
                            <input type="password" name="password" required class="form-input" placeholder="min. 8 karakter">
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn-primary" style="justify-content:center;margin-top:4px;">Buat Tenant</button>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div style="background:#fff;border-radius:14px;border:1px solid #f1f5f9;box-shadow:0 1px 3px rgba(0,0,0,0.04);overflow:hidden;">
            <div style="padding:16px 20px;border-bottom:1px solid #f8fafc;">
                <p style="font-size:14px;font-weight:600;color:#0f172a;">Semua Tenant ({{ $tenants->total() }})</p>
            </div>
            <div>
                @forelse($tenants as $t)
                <div style="padding:16px 20px;border-bottom:1px solid #f8fafc;">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                        <div style="display:flex;align-items:center;gap:12px;">
                            <div style="width:40px;height:40px;background:#f0fdf6;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <span style="color:#0F6E56;font-size:14px;font-weight:700;">{{ strtoupper(substr($t->name,0,2)) }}</span>
                            </div>
                            <div>
                                <p style="font-size:14px;font-weight:600;color:#0f172a;">{{ $t->name }}</p>
                                <p style="font-size:12px;font-family:monospace;color:#94a3b8;margin-top:1px;">{{ $t->subdomain }}.tokaku.id</p>
                                <p style="font-size:11.5px;color:#94a3b8;margin-top:2px;">{{ $t->users_count }} user &middot; {{ $t->created_at->format('d M Y') }}</p>
                            </div>
                        </div>
                        <div style="display:flex;align-items:center;gap:8px;flex-shrink:0;">
                            @if($t->status==='active')<span style="font-size:12px;font-weight:500;background:#f0fdf4;color:#15803d;padding:4px 10px;border-radius:99px;">Aktif</span>
                            @elseif($t->status==='trial')<span style="font-size:12px;font-weight:500;background:#fffbeb;color:#b45309;padding:4px 10px;border-radius:99px;">Trial {{ $t->trial_ends_at?'· '.$t->trial_ends_at->diffForHumans():'' }}</span>
                            @else<span style="font-size:12px;font-weight:500;background:#fff1f2;color:#be123c;padding:4px 10px;border-radius:99px;">Suspended</span>@endif
                            <form method="POST" action="{{ route('superadmin.tenants.suspend',$t) }}">
                                @csrf @method('PUT')
                                <button type="submit" onclick="return confirm('{{ $t->status==='suspended'?'Aktifkan':'Tangguhkan' }} tenant ini?')"
                                    style="font-size:12.5px;font-weight:500;padding:6px 12px;border-radius:8px;border:1.5px solid;cursor:pointer;font-family:Inter,sans-serif;background:#fff;transition:all 0.15s;{{ $t->status==='suspended'?'border-color:#bbf7d2;color:#15803d;':'border-color:#fecdd3;color:#be123c;' }}">
                                    {{ $t->status==='suspended'?'Aktifkan':'Tangguhkan' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @empty
                <div style="padding:60px 20px;text-align:center;font-size:14px;color:#94a3b8;">Belum ada tenant terdaftar.</div>
                @endforelse
            </div>
            @if($tenants->hasPages())
            <div style="padding:14px 20px;border-top:1px solid #f8fafc;">{{ $tenants->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
