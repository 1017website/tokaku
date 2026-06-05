@extends('superadmin.layout')
@section('title','Pengaturan Aplikasi')
@section('page-title','Pengaturan Aplikasi')
@section('page-subtitle','Kelola identitas brand, SEO, dan integrasi iklan aplikasi')

@php
    $card  = 'background:#fff;border-radius:16px;border:1px solid #f1f5f9;box-shadow:0 1px 3px rgba(0,0,0,0.04);padding:24px;margin-bottom:20px;';
    $label = 'display:block;font-size:13px;font-weight:500;color:#374151;margin-bottom:6px;';
    $hint  = 'font-size:12px;color:#94a3b8;margin-top:6px;';
    $secTitle = 'font-size:15px;font-weight:700;color:#0f172a;margin-bottom:4px;';
    $secSub   = 'font-size:12.5px;color:#94a3b8;margin-bottom:18px;';
    $err = fn($m) => "<p style='font-size:12px;color:#e11d48;margin-top:6px;'>$m</p>";
@endphp

@section('content')
<div style="max-width:640px;">

    @if(session('success'))
        <div style="background:#ecfdf5;border:1px solid #a7f3d0;color:#047857;border-radius:12px;padding:12px 16px;font-size:13px;margin-bottom:20px;">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('superadmin.settings.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- ════════ IDENTITAS APLIKASI ════════ --}}
        <div style="{{ $card }}">
            <p style="{{ $secTitle }}">Identitas Aplikasi</p>
            <p style="{{ $secSub }}">Nama, logo, dan favicon yang tampil di seluruh aplikasi.</p>

            <div style="display:flex;flex-direction:column;gap:18px;">
                <div>
                    <label style="{{ $label }}">Nama Aplikasi <span style="color:#f43f5e;">*</span></label>
                    <input type="text" name="app_name" value="{{ old('app_name', $settings['app_name'] ?? 'Tokaku') }}" required class="form-input" placeholder="Tokaku">
                    @error('app_name') {!! $err($message) !!} @enderror
                </div>

                {{-- Logo ikon --}}
                <div>
                    <label style="{{ $label }}">Logo Ikon (kotak)</label>
                    <div style="display:flex;align-items:center;gap:14px;margin-bottom:10px;">
                        <div style="width:64px;height:64px;border-radius:16px;border:1px solid #e2e8f0;background:#f8fafc;display:flex;align-items:center;justify-content:center;overflow:hidden;">
                            @if(!empty($settings['app_logo_path']))
                                <img src="{{ Storage::url($settings['app_logo_path']) }}" alt="Logo" style="width:100%;height:100%;object-fit:cover;">
                            @else
                                <span style="width:36px;height:36px;border-radius:12px;background:#0F6E56;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;">T</span>
                            @endif
                        </div>
                        <p style="{{ $hint }}">Dipakai di sidebar & avatar. Rasio 1:1. jpg, png, webp, svg. Maks 2MB.</p>
                    </div>
                    <input type="file" name="app_logo" accept="image/*" class="form-input" style="padding:8px 14px;cursor:pointer;">
                    @error('app_logo') {!! $err($message) !!} @enderror
                </div>

                {{-- Logo full --}}
                <div>
                    <label style="{{ $label }}">Logo Full (dengan teks)</label>
                    <div style="display:flex;align-items:center;gap:14px;margin-bottom:10px;">
                        <div style="height:48px;min-width:120px;padding:0 12px;border-radius:12px;border:1px solid #e2e8f0;background:#f8fafc;display:flex;align-items:center;justify-content:center;overflow:hidden;">
                            @if(!empty($settings['app_logo_full']))
                                <img src="{{ Storage::url($settings['app_logo_full']) }}" alt="Logo Full" style="max-height:100%;max-width:200px;object-fit:contain;">
                            @else
                                <span style="font-size:12px;color:#cbd5e1;">Belum ada</span>
                            @endif
                        </div>
                        <p style="{{ $hint }}">Logo horizontal (gambar + teks brand). Untuk header/halaman publik. Maks 2MB.</p>
                    </div>
                    <input type="file" name="app_logo_full_file" accept="image/*" class="form-input" style="padding:8px 14px;cursor:pointer;">
                    @error('app_logo_full_file') {!! $err($message) !!} @enderror
                </div>

                {{-- Favicon --}}
                <div>
                    <label style="{{ $label }}">Favicon</label>
                    <div style="display:flex;align-items:center;gap:14px;margin-bottom:10px;">
                        <div style="width:40px;height:40px;border-radius:8px;border:1px solid #e2e8f0;background:#f8fafc;display:flex;align-items:center;justify-content:center;overflow:hidden;">
                            @if(!empty($settings['app_favicon']))
                                <img src="{{ Storage::url($settings['app_favicon']) }}" alt="Favicon" style="width:100%;height:100%;object-fit:contain;">
                            @else
                                <span style="font-size:11px;color:#cbd5e1;">—</span>
                            @endif
                        </div>
                        <p style="{{ $hint }}">Ikon tab browser. Ukuran ideal 512×512. png, ico, svg. Maks 1MB.</p>
                    </div>
                    <input type="file" name="app_favicon_file" accept=".png,.ico,.svg,image/*" class="form-input" style="padding:8px 14px;cursor:pointer;">
                    @error('app_favicon_file') {!! $err($message) !!} @enderror
                </div>
            </div>
        </div>

        {{-- ════════ SEO ════════ --}}
        <div style="{{ $card }}">
            <p style="{{ $secTitle }}">SEO</p>
            <p style="{{ $secSub }}">Metadata untuk hasil pencarian Google & preview saat dibagikan.</p>

            <div style="display:flex;flex-direction:column;gap:18px;">
                <div>
                    <label style="{{ $label }}">Meta Title</label>
                    <input type="text" name="seo_title" value="{{ old('seo_title', $settings['seo_title'] ?? '') }}" class="form-input" maxlength="70" placeholder="Tokaku — Aplikasi Kasir UMKM">
                    <p style="{{ $hint }}">Maks 70 karakter. Kosongkan untuk pakai nama aplikasi.</p>
                    @error('seo_title') {!! $err($message) !!} @enderror
                </div>
                <div>
                    <label style="{{ $label }}">Meta Description</label>
                    <textarea name="seo_description" class="form-input" rows="3" maxlength="300" placeholder="Deskripsi singkat aplikasi untuk mesin pencari.">{{ old('seo_description', $settings['seo_description'] ?? '') }}</textarea>
                    <p style="{{ $hint }}">Idealnya 150–160 karakter.</p>
                    @error('seo_description') {!! $err($message) !!} @enderror
                </div>
                <div>
                    <label style="{{ $label }}">Meta Keywords</label>
                    <input type="text" name="seo_keywords" value="{{ old('seo_keywords', $settings['seo_keywords'] ?? '') }}" class="form-input" placeholder="kasir, pos, umkm, aplikasi toko">
                    <p style="{{ $hint }}">Pisahkan dengan koma.</p>
                    @error('seo_keywords') {!! $err($message) !!} @enderror
                </div>
                <div>
                    <label style="{{ $label }}">OG Image (preview share)</label>
                    <div style="display:flex;align-items:center;gap:14px;margin-bottom:10px;">
                        <div style="width:120px;height:63px;border-radius:10px;border:1px solid #e2e8f0;background:#f8fafc;display:flex;align-items:center;justify-content:center;overflow:hidden;">
                            @if(!empty($settings['seo_og_image']))
                                <img src="{{ Storage::url($settings['seo_og_image']) }}" alt="OG" style="width:100%;height:100%;object-fit:cover;">
                            @else
                                <span style="font-size:11px;color:#cbd5e1;">1200×630</span>
                            @endif
                        </div>
                        <p style="{{ $hint }}">Gambar saat link dibagikan ke WA/FB/Twitter. Ideal 1200×630. Maks 2MB.</p>
                    </div>
                    <input type="file" name="seo_og_image_file" accept="image/*" class="form-input" style="padding:8px 14px;cursor:pointer;">
                    @error('seo_og_image_file') {!! $err($message) !!} @enderror
                </div>
            </div>
        </div>

        {{-- ════════ IKLAN & TRACKING ════════ --}}
        <div style="{{ $card }}">
            <p style="{{ $secTitle }}">Iklan & Tracking</p>
            <p style="{{ $secSub }}">Pasang ID untuk konversi & analitik. Skrip otomatis dimuat di semua halaman.</p>

            <div style="display:flex;flex-direction:column;gap:18px;">
                <div>
                    <label style="{{ $label }}">Google Ads ID</label>
                    <input type="text" name="google_ads_id" value="{{ old('google_ads_id', $settings['google_ads_id'] ?? '') }}" class="form-input" placeholder="AW-123456789">
                    <p style="{{ $hint }}">Format: AW-XXXXXXXXX (Google Ads conversion).</p>
                    @error('google_ads_id') {!! $err($message) !!} @enderror
                </div>
                <div>
                    <label style="{{ $label }}">Google Analytics 4 ID</label>
                    <input type="text" name="google_analytics_id" value="{{ old('google_analytics_id', $settings['google_analytics_id'] ?? '') }}" class="form-input" placeholder="G-XXXXXXXXXX">
                    <p style="{{ $hint }}">Format: G-XXXXXXXXXX.</p>
                    @error('google_analytics_id') {!! $err($message) !!} @enderror
                </div>
                <div>
                    <label style="{{ $label }}">Google Tag Manager ID</label>
                    <input type="text" name="gtm_id" value="{{ old('gtm_id', $settings['gtm_id'] ?? '') }}" class="form-input" placeholder="GTM-XXXXXXX">
                    <p style="{{ $hint }}">Format: GTM-XXXXXXX. Opsional (alternatif memuat tag terpusat).</p>
                    @error('gtm_id') {!! $err($message) !!} @enderror
                </div>
                <div>
                    <label style="{{ $label }}">Meta (Facebook) Pixel ID</label>
                    <input type="text" name="meta_pixel_id" value="{{ old('meta_pixel_id', $settings['meta_pixel_id'] ?? '') }}" class="form-input" placeholder="1234567890123456">
                    <p style="{{ $hint }}">Hanya angka. Untuk Meta Ads (Facebook & Instagram).</p>
                    @error('meta_pixel_id') {!! $err($message) !!} @enderror
                </div>
            </div>
        </div>

        {{-- ════════ Rekening Pembayaran ════════ --}}
        <div style="background:#fff;border-radius:14px;border:1px solid #f1f5f9;padding:20px;margin-bottom:16px;">
            <p style="{{ $secTitle }}">Rekening Pembayaran</p>
            <p style="font-size:12.5px;color:#64748b;margin:-6px 0 14px;">Rekening tujuan transfer yang ditampilkan ke tenant saat membayar langganan.</p>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px;">
                <div>
                    <label class="form-label">Nama Bank</label>
                    <input type="text" name="bank_name" value="{{ old('bank_name', $settings['bank_name'] ?? '') }}" class="form-input" placeholder="mis. BCA">
                </div>
                <div>
                    <label class="form-label">Nomor Rekening</label>
                    <input type="text" name="bank_account_no" value="{{ old('bank_account_no', $settings['bank_account_no'] ?? '') }}" class="form-input" placeholder="1234567890">
                </div>
                <div>
                    <label class="form-label">Atas Nama</label>
                    <input type="text" name="bank_account_name" value="{{ old('bank_account_name', $settings['bank_account_name'] ?? '') }}" class="form-input" placeholder="Nama pemilik rekening">
                </div>
            </div>
        </div>

        <div style="display:flex;justify-content:flex-end;padding-bottom:20px;">
            <button type="submit" class="btn-primary">Simpan Perubahan</button>
        </div>
    </form>
</div>
@endsection
