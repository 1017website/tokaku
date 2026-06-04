@extends('superadmin.layout')
@section('title','Pengaturan Aplikasi')
@section('page-title','Pengaturan Aplikasi')
@section('page-subtitle','Kelola logo dan nama aplikasi Tokaku')

@section('content')
<div style="max-width:560px;">
    <div style="background:#fff;border-radius:16px;border:1px solid #f1f5f9;box-shadow:0 1px 3px rgba(0,0,0,0.04);padding:24px;">
        <form method="POST" action="{{ route('superadmin.settings.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div style="display:flex;flex-direction:column;gap:16px;">
                <div>
                    <label style="display:block;font-size:13px;font-weight:500;color:#374151;margin-bottom:6px;">Nama Aplikasi <span style="color:#f43f5e;">*</span></label>
                    <input type="text" name="app_name" value="{{ old('app_name', $settings['app_name'] ?? 'Tokaku') }}" required class="form-input" placeholder="Tokaku">
                    @error('app_name') <p style="font-size:12px;color:#e11d48;margin-top:6px;">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label style="display:block;font-size:13px;font-weight:500;color:#374151;margin-bottom:6px;">Logo Tokaku</label>
                    <div style="display:flex;align-items:center;gap:14px;margin-bottom:10px;">
                        <div style="width:64px;height:64px;border-radius:16px;border:1px solid #e2e8f0;background:#f8fafc;display:flex;align-items:center;justify-content:center;overflow:hidden;">
                            @if(!empty($settings['app_logo_path']))
                                <img src="{{ Storage::url($settings['app_logo_path']) }}" alt="Logo Tokaku" style="width:100%;height:100%;object-fit:cover;">
                            @else
                                <span style="width:36px;height:36px;border-radius:12px;background:#0F6E56;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;">T</span>
                            @endif
                        </div>
                        <div>
                            <p style="font-size:13px;font-weight:600;color:#0f172a;margin-bottom:2px;">Preview Logo</p>
                            <p style="font-size:12px;color:#94a3b8;">Format jpg, png, webp, svg. Maksimal 2MB.</p>
                        </div>
                    </div>
                    <input type="file" name="app_logo" accept="image/*" class="form-input" style="padding:8px 14px;cursor:pointer;">
                    @error('app_logo') <p style="font-size:12px;color:#e11d48;margin-top:6px;">{{ $message }}</p> @enderror
                </div>
            </div>

            <div style="display:flex;justify-content:flex-end;padding-top:20px;border-top:1px solid #f8fafc;margin-top:20px;">
                <button type="submit" class="btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection
