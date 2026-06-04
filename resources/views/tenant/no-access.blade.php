@extends('layouts.app')
@section('title','Tidak Ada Akses')
@section('page-title','Tidak Ada Akses')
@section('page-subtitle','Hubungi pemilik toko')

@section('content')
<div style="max-width:480px;margin:40px auto;background:#fff;border-radius:16px;border:1px solid #f1f5f9;box-shadow:0 1px 3px rgba(0,0,0,0.04);padding:40px 32px;text-align:center;">
    <div style="width:56px;height:56px;background:#fffbeb;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 18px;">
        <svg width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="#d97706" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M5 19h14a2 2 0 001.84-2.75L13.74 4a2 2 0 00-3.5 0L3.18 16.25A2 2 0 005 19z" />
        </svg>
    </div>
    <p style="font-size:16px;font-weight:700;color:#0f172a;margin-bottom:6px;">Belum ada akses modul</p>
    <p style="font-size:13.5px;color:#64748b;line-height:1.6;">
        Akun Anda belum diberi akses ke modul manapun. Silakan hubungi pemilik toko untuk mengatur akses melalui menu Tim Toko.
    </p>
</div>
@endsection
