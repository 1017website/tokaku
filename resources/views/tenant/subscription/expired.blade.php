@extends('layouts.app')
@section('title','Langganan Berakhir')
@section('page-title','Langganan Berakhir')

@section('content')
<div style="display:flex;align-items:center;justify-content:center;min-height:60vh;">
    <div style="text-align:center;max-width:360px;">
        <div style="width:60px;height:60px;background:#fffbeb;border-radius:20px;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
            <svg width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="#d97706" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
        <h2 style="font-size:20px;font-weight:700;color:#0f172a;letter-spacing:-0.3px;margin-bottom:8px;">Masa Berlangganan Berakhir</h2>
        <p style="font-size:14px;color:#64748b;line-height:1.6;margin-bottom:24px;">Hubungi admin Tokaku untuk memperpanjang langganan dan kembali menggunakan aplikasi.</p>
        <a href="https://wa.me/6281234567890" target="_blank" class="btn-primary" style="justify-content:center;">
            Hubungi Admin via WhatsApp
        </a>
    </div>
</div>
@endsection
