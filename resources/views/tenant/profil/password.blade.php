@extends('layouts.app')
@section('title','Ganti Password')
@section('page-title','Ganti Password')
@section('page-subtitle','Perbarui kata sandi akun Anda')

@section('content')
<div style="max-width:480px;">
    <div style="background:#fff;border-radius:16px;border:1px solid #f1f5f9;box-shadow:0 1px 3px rgba(0,0,0,0.04);padding:24px;">

        @if($errors->any())
        <div style="display:flex;align-items:flex-start;gap:10px;background:#fff1f2;border:1px solid #fecdd3;color:#be123c;font-size:13px;border-radius:12px;padding:12px 16px;margin-bottom:18px;">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="flex-shrink:0;margin-top:1px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ $errors->first() }}</span>
        </div>
        @endif

        <form method="POST" action="{{ route('tenant.password.update') }}" style="display:flex;flex-direction:column;gap:16px;">
            @csrf
            @method('PUT')

            <div>
                <label class="form-label">Password Saat Ini</label>
                <div style="position:relative;">
                    <input type="password" name="current_password" required class="form-input" placeholder="Masukkan password lama" id="cur" style="padding-right:42px;">
                    <button type="button" onclick="tp('cur',this)" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#9ca3af;padding:0;display:flex;" tabindex="-1">
                        <svg width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                </div>
            </div>

            <div>
                <label class="form-label">Password Baru</label>
                <div style="position:relative;">
                    <input type="password" name="password" required class="form-input" placeholder="Minimal 6 karakter" id="new" style="padding-right:42px;">
                    <button type="button" onclick="tp('new',this)" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#9ca3af;padding:0;display:flex;" tabindex="-1">
                        <svg width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                </div>
            </div>

            <div>
                <label class="form-label">Konfirmasi Password Baru</label>
                <input type="password" name="password_confirmation" required class="form-input" placeholder="Ulangi password baru">
            </div>

            <div style="display:flex;gap:10px;margin-top:4px;">
                <button type="submit" class="btn-primary" style="justify-content:center;flex:1;">Simpan Password</button>
                @if(auth()->user()->isAdmin())
                <a href="{{ route('tenant.profil') }}" class="btn-secondary" style="justify-content:center;">Batal</a>
                @endif
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function tp(id, btn){
    var el = document.getElementById(id);
    el.type = el.type === 'password' ? 'text' : 'password';
}
</script>
@endpush
