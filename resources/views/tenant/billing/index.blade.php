@extends('layouts.app')
@section('title','Tagihan & Langganan')
@section('page-title','Tagihan & Langganan')
@section('page-subtitle','Kelola pembayaran langganan toko Anda')

@section('content')

{{-- Status langganan ringkas --}}
@php
    $statusColor = match($tenant->status) {
        'active' => ['#f0fdf6','#bbf7d2','#15803d','Aktif'],
        'trial'  => ['#eff6ff','#bfdbfe','#1d4ed8','Masa Trial'],
        default  => ['#fef2f2','#fecaca','#be123c', ucfirst($tenant->status)],
    };
@endphp
<div style="background:{{ $statusColor[0] }};border:1px solid {{ $statusColor[1] }};border-radius:16px;padding:18px 22px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
    <div>
        <p style="font-size:12px;font-weight:600;color:{{ $statusColor[2] }};text-transform:uppercase;letter-spacing:.04em;">Status: {{ $statusColor[3] }}</p>
        @if($tenant->trial_ends_at)
        <p style="font-size:14px;color:#0f172a;margin-top:4px;">
            {{ $tenant->status === 'active' ? 'Berlaku hingga' : 'Trial berakhir' }} {{ $tenant->trial_ends_at->translatedFormat('d F Y') }}
            <span style="color:#64748b;">({{ $tenant->trialLabel() }})</span>
        </p>
        @endif
    </div>
</div>

{{-- Tagihan aktif --}}
@if($activeInvoice)
<div style="background:#fff;border-radius:16px;border:1px solid #f1f5f9;overflow:hidden;margin-bottom:20px;">
    <div style="padding:16px 20px;border-bottom:1px solid #f8fafc;background:#fffbeb;">
        <p style="font-size:14px;font-weight:700;color:#92400e;">Tagihan Belum Selesai &middot; {{ $activeInvoice->invoice_no }}</p>
    </div>
    <div style="padding:20px;">
        {{-- Nominal transfer dengan kode unik --}}
        <div style="background:#f0fdf6;border:1px dashed #0F6E56;border-radius:12px;padding:16px;text-align:center;margin-bottom:18px;">
            <p style="font-size:12px;color:#64748b;margin-bottom:4px;">Transfer TEPAT sejumlah</p>
            <p style="font-size:28px;font-weight:800;color:#0F6E56;letter-spacing:-.02em;">Rp {{ number_format($activeInvoice->total_amount,0,',','.') }}</p>
            <p style="font-size:12px;color:#64748b;margin-top:4px;">Sudah termasuk kode unik <b>{{ $activeInvoice->unique_code }}</b> — jangan dibulatkan</p>
        </div>

        {{-- Rekening tujuan --}}
        @if($bank['account'])
        <div style="border:1px solid #f1f5f9;border-radius:12px;padding:14px 16px;margin-bottom:18px;">
            <p style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;margin-bottom:8px;">Rekening Tujuan</p>
            <div style="display:flex;justify-content:space-between;font-size:13.5px;padding:3px 0;"><span style="color:#64748b;">Bank</span><b style="color:#0f172a;">{{ $bank['name'] }}</b></div>
            <div style="display:flex;justify-content:space-between;font-size:13.5px;padding:3px 0;"><span style="color:#64748b;">No. Rekening</span><b style="color:#0f172a;">{{ $bank['account'] }}</b></div>
            <div style="display:flex;justify-content:space-between;font-size:13.5px;padding:3px 0;"><span style="color:#64748b;">Atas Nama</span><b style="color:#0f172a;">{{ $bank['holder'] }}</b></div>
        </div>
        @else
        <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:12px;padding:12px 16px;margin-bottom:18px;font-size:13px;color:#be123c;">Rekening tujuan belum diatur. Silakan hubungi administrator.</div>
        @endif

        @if($activeInvoice->status === 'waiting_confirmation')
            <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:12px;padding:14px 16px;font-size:13.5px;color:#1d4ed8;display:flex;align-items:center;gap:8px;">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M12 7v5l3 3"/></svg>
                Bukti transfer sudah dikirim. Menunggu verifikasi administrator.
            </div>
            @if($activeInvoice->proof_path)
            <a href="{{ Storage::url($activeInvoice->proof_path) }}" target="_blank" style="display:inline-block;margin-top:12px;font-size:13px;color:#0F6E56;font-weight:600;text-decoration:none;">Lihat bukti yang dikirim →</a>
            @endif
        @else
            {{-- Form upload bukti --}}
            <form method="POST" action="{{ route('tenant.billing.proof', $activeInvoice) }}" enctype="multipart/form-data">
                @csrf
                <label class="form-label">Upload Bukti Transfer *</label>
                <input type="file" name="proof" accept="image/*" required class="form-input" style="padding:8px;">
                <button type="submit" class="btn-primary" style="margin-top:12px;width:100%;justify-content:center;">Kirim Bukti Transfer</button>
            </form>
        @endif
    </div>
</div>
@else
    {{-- Pilih paket --}}
    <div style="background:#fff;border-radius:16px;border:1px solid #f1f5f9;padding:20px;margin-bottom:20px;">
        <p style="font-size:14px;font-weight:600;color:#0f172a;margin-bottom:4px;">Pilih Paket Langganan</p>
        <p style="font-size:13px;color:#64748b;margin-bottom:18px;">Setelah memilih, sistem membuat tagihan dengan nominal unik untuk transfer.</p>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px;">
            @foreach($plans as $plan)
            <div style="border:{{ $plan->is_popular ? '2px solid #0F6E56' : '1px solid #e2e8f0' }};border-radius:14px;padding:18px;position:relative;">
                @if($plan->is_popular)<span style="position:absolute;top:-10px;left:18px;background:#0F6E56;color:#fff;font-size:11px;font-weight:600;padding:3px 10px;border-radius:99px;">Populer</span>@endif
                <p style="font-size:15px;font-weight:700;color:#0f172a;">{{ $plan->name }}</p>
                <p style="font-size:12px;color:#64748b;margin-bottom:10px;">{{ $plan->tagline }}</p>
                @if($plan->original_price > $plan->price)
                <p style="font-size:12px;color:#94a3b8;text-decoration:line-through;">Rp {{ number_format($plan->original_price,0,',','.') }}</p>
                @endif
                <p style="font-size:22px;font-weight:800;color:#0F6E56;">Rp {{ number_format($plan->price,0,',','.') }}</p>
                <p style="font-size:12px;color:#64748b;margin-bottom:14px;">untuk {{ $plan->duration_months }} bulan</p>
                <form method="POST" action="{{ route('tenant.billing.invoice') }}">
                    @csrf
                    <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                    <button type="submit" class="btn-primary" style="width:100%;justify-content:center;">Pilih Paket</button>
                </form>
            </div>
            @endforeach
        </div>
    </div>
@endif

{{-- Riwayat tagihan --}}
@if($invoices->count())
<div style="background:#fff;border-radius:16px;border:1px solid #f1f5f9;overflow:hidden;">
    <div style="padding:14px 18px;border-bottom:1px solid #f8fafc;"><p style="font-size:14px;font-weight:600;color:#0f172a;">Riwayat Tagihan</p></div>
    <div class="overflow-x-auto">
        <table style="width:100%;border-collapse:collapse;min-width:520px;">
            <thead><tr style="background:#f8fafc;border-bottom:1px solid #f1f5f9;">
                @foreach(['Invoice','Tanggal','Nominal','Durasi','Status'] as $h)
                <th style="text-align:left;font-size:11px;font-weight:600;color:#64748b;padding:10px 14px;text-transform:uppercase;">{{ $h }}</th>
                @endforeach
            </tr></thead>
            <tbody>
                @foreach($invoices as $inv)
                @php
                    $sc = match($inv->status){
                        'paid'=>['#f0fdf6','#15803d'], 'waiting_confirmation'=>['#eff6ff','#1d4ed8'],
                        'rejected'=>['#fef2f2','#be123c'], 'expired'=>['#f8fafc','#64748b'], default=>['#fffbeb','#92400e'],
                    };
                @endphp
                <tr style="border-bottom:1px solid #f8fafc;">
                    <td style="padding:12px 14px;font-size:13px;font-weight:500;color:#0f172a;">{{ $inv->invoice_no }}</td>
                    <td style="padding:12px 14px;font-size:12.5px;color:#374151;">{{ $inv->created_at->format('d M Y') }}</td>
                    <td style="padding:12px 14px;font-size:13px;font-weight:600;color:#0F6E56;">Rp {{ number_format($inv->total_amount,0,',','.') }}</td>
                    <td style="padding:12px 14px;font-size:12.5px;color:#374151;">{{ $inv->duration_months }} bln</td>
                    <td style="padding:12px 14px;"><span style="font-size:11.5px;font-weight:500;padding:3px 10px;border-radius:99px;background:{{ $sc[0] }};color:{{ $sc[1] }};">{{ $inv->statusLabel() }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection
