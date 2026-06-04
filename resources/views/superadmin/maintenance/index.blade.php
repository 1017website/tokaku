@extends('superadmin.layout')
@section('title','Maintenance')
@section('page-title','Maintenance Server')
@section('page-subtitle','Jalankan command Laravel dari akun administrator')

@section('content')
<div style="max-width:760px;display:flex;flex-direction:column;gap:16px;">
    @if(session('success'))
        <div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;border-radius:12px;padding:12px 14px;font-size:13px;font-weight:500;">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div style="background:#fff1f2;border:1px solid #fecdd3;color:#be123c;border-radius:12px;padding:12px 14px;font-size:13px;font-weight:500;">{{ session('error') }}</div>
    @endif

    @if(session('artisan_output'))
        <div style="background:#0f172a;color:#e2e8f0;border-radius:12px;padding:14px;overflow:auto;">
            <p style="font-size:12px;color:#94a3b8;margin-bottom:8px;font-weight:600;">Output Command</p>
            <pre style="font-size:12px;white-space:pre-wrap;font-family:monospace;">{{ session('artisan_output') }}</pre>
        </div>
    @endif

    <div style="background:#fff;border-radius:16px;border:1px solid #f1f5f9;box-shadow:0 1px 3px rgba(0,0,0,0.04);padding:22px;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap;">
            <div>
                <p style="font-size:15px;font-weight:700;color:#0f172a;margin-bottom:4px;">Database Migration</p>
                <p style="font-size:13px;color:#64748b;line-height:1.6;">Menjalankan <code>php artisan migrate --force</code> untuk membuat atau update tabel database production.</p>
            </div>
            <form method="POST" action="{{ route('superadmin.maintenance.migrate') }}">
                @csrf
                <button type="submit" class="btn-primary" onclick="return confirm('Jalankan php artisan migrate sekarang?')">Run Migrate</button>
            </form>
        </div>
    </div>

    <div style="background:#fff;border-radius:16px;border:1px solid #f1f5f9;box-shadow:0 1px 3px rgba(0,0,0,0.04);padding:22px;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap;">
            <div>
                <p style="font-size:15px;font-weight:700;color:#0f172a;margin-bottom:4px;">Storage Link</p>
                <p style="font-size:13px;color:#64748b;line-height:1.6;">Menjalankan <code>php artisan storage:link --force</code> agar logo dan file upload di storage bisa tampil di public.</p>
                <p style="font-size:12px;margin-top:8px;font-weight:600;{{ $storageLinked ? 'color:#15803d;' : 'color:#be123c;' }}">
                    Status: {{ $storageLinked ? 'public/storage sudah tersedia' : 'public/storage belum tersedia' }}
                </p>
            </div>
            <form method="POST" action="{{ route('superadmin.maintenance.storage-link') }}">
                @csrf
                <button type="submit" class="btn-primary" onclick="return confirm('Jalankan php artisan storage:link sekarang?')">Run Storage Link</button>
            </form>
        </div>
    </div>

    <div style="background:#fffbeb;border:1px solid #fde68a;color:#92400e;border-radius:14px;padding:14px;font-size:12.5px;line-height:1.6;">
        Gunakan fitur ini hanya dari akun superadmin. Untuk keamanan, tombol ini tetap membutuhkan login administrator dan role superadmin.
    </div>
</div>
@endsection
