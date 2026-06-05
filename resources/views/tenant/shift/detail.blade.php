@extends('layouts.app')
@section('title','Detail Shift')
@section('page-title','Detail Shift')
@section('page-subtitle','Rekap kas dan transaksi dalam shift')

@section('content')

@php
    $isOpen = is_null($shift->closed_at);
    $cashRevenue = $transactions->where('payment_method','cash')->sum('total');
@endphp

{{-- Ringkasan Shift --}}
<div style="background:{{ $isOpen ? '#0F6E56' : '#fff' }};border-radius:16px;{{ $isOpen ? '' : 'border:1px solid #f1f5f9;' }}padding:20px 24px;margin-bottom:20px;">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px;">
        <div>
            <p style="font-size:12px;font-weight:500;margin-bottom:4px;color:{{ $isOpen ? 'rgba(255,255,255,0.7)' : '#64748b' }};">
                {{ $isOpen ? 'SHIFT SEDANG BERJALAN' : 'SHIFT SELESAI' }}
            </p>
            <p style="font-size:16px;font-weight:700;color:{{ $isOpen ? '#fff' : '#0f172a' }};">{{ $shift->user->name }}</p>
            <p style="font-size:13px;margin-top:2px;color:{{ $isOpen ? 'rgba(255,255,255,0.7)' : '#64748b' }};">
                Dibuka {{ $shift->opened_at->format('d M Y, H:i') }}
                @if(!$isOpen) &middot; Ditutup {{ $shift->closed_at->format('d M Y, H:i') }}@endif
            </p>
        </div>
        <a href="{{ route('tenant.shift.index') }}" style="font-size:13px;font-weight:500;text-decoration:none;color:{{ $isOpen ? '#fff' : '#0F6E56' }};">&larr; Kembali</a>
    </div>
</div>

{{-- Kartu rekap kas --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;margin-bottom:20px;">
    @php
        $cards = [
            ['Kas Awal', $shift->opening_cash, '#0f172a'],
            ['Penjualan Tunai', $cashRevenue, '#0F6E56'],
            ['Total Revenue', $isOpen ? $transactions->sum('total') : $shift->total_revenue, '#0F6E56'],
        ];
        if (!$isOpen) {
            $cards[] = ['Kas Akhir', $shift->closing_cash, '#0f172a'];
            $cards[] = ['Kas Diharapkan', $shift->expected_cash, '#0f172a'];
            $diffColor = $shift->cash_difference == 0 ? '#15803d' : ($shift->cash_difference > 0 ? '#2563eb' : '#be123c');
            $cards[] = ['Selisih', $shift->cash_difference, $diffColor, true];
        }
    @endphp
    @foreach($cards as $c)
    <div style="background:#fff;border:1px solid #f1f5f9;border-radius:12px;padding:14px 16px;">
        <p style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;margin-bottom:6px;">{{ $c[0] }}</p>
        <p style="font-size:18px;font-weight:700;color:{{ $c[2] }};">
            {{ ($c[3] ?? false) && $c[1] > 0 ? '+' : '' }}Rp {{ number_format($c[1] ?? 0,0,',','.') }}
        </p>
    </div>
    @endforeach
</div>

@if($shift->notes)
<div style="background:#fffbeb;border:1px solid #fde68a;border-radius:12px;padding:12px 16px;margin-bottom:20px;">
    <p style="font-size:11px;font-weight:600;color:#92400e;text-transform:uppercase;margin-bottom:4px;">Catatan</p>
    <p style="font-size:13.5px;color:#78350f;">{{ $shift->notes }}</p>
</div>
@endif

{{-- Daftar transaksi --}}
<div style="background:#fff;border-radius:16px;border:1px solid #f1f5f9;overflow:hidden;">
    <div style="padding:14px 18px;border-bottom:1px solid #f8fafc;display:flex;align-items:center;justify-content:space-between;">
        <p style="font-size:14px;font-weight:600;color:#0f172a;">Transaksi ({{ $transactions->count() }})</p>
    </div>
    <div class="overflow-x-auto">
        <table style="width:100%;border-collapse:collapse;min-width:560px;">
            <thead><tr style="background:#f8fafc;border-bottom:1px solid #f1f5f9;">
                @foreach(['Invoice','Waktu','Kasir','Metode','Status','Total','Aksi'] as $h)
                <th style="text-align:left;font-size:11px;font-weight:600;color:#64748b;padding:10px 14px;text-transform:uppercase;">{{ $h }}</th>
                @endforeach
            </tr></thead>
            <tbody>
                @forelse($transactions as $trx)
                <tr style="border-bottom:1px solid #f8fafc;">
                    <td style="padding:12px 14px;font-size:13px;font-weight:500;color:#0f172a;">{{ $trx->invoice_no }}</td>
                    <td style="padding:12px 14px;font-size:12.5px;color:#374151;">{{ $trx->created_at->format('d M, H:i') }}</td>
                    <td style="padding:12px 14px;font-size:12.5px;color:#374151;">{{ $trx->user->name ?? '-' }}</td>
                    <td style="padding:12px 14px;font-size:12.5px;color:#374151;text-transform:capitalize;">{{ $trx->payment_method }}</td>
                    <td style="padding:12px 14px;font-size:12.5px;">
                        <span style="font-weight:500;color:{{ $trx->payment_status === 'paid' ? '#15803d' : '#be123c' }};">
                            {{ $trx->payment_status === 'paid' ? 'Lunas' : 'Hutang' }}
                        </span>
                    </td>
                    <td style="padding:12px 14px;font-size:13.5px;font-weight:700;color:#0F6E56;">Rp {{ number_format($trx->total,0,',','.') }}</td>
                    <td style="padding:12px 14px;">
                        @php
                            $trxData = [
                                'id' => $trx->id,
                                'invoice_no' => $trx->invoice_no,
                                'time' => $trx->created_at->format('d M Y, H:i'),
                                'cashier' => $trx->user->name ?? '-',
                                'method' => ucfirst($trx->payment_method),
                                'subtotal' => (float) $trx->subtotal,
                                'discount' => (float) $trx->discount,
                                'tax' => (float) $trx->tax,
                                'total' => (float) $trx->total,
                                'items' => $trx->items->map(fn($i) => ['name'=>$i->product_name,'price'=>(float)$i->unit_price,'qty'=>$i->quantity,'subtotal'=>(float)$i->subtotal]),
                            ];
                        @endphp
                        <button type="button" onclick='showTrxDetail(@json($trxData))' style="font-size:12.5px;color:#0F6E56;font-weight:600;background:none;border:none;cursor:pointer;padding:0;">Detail</button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="padding:50px;text-align:center;color:#94a3b8;font-size:13.5px;">Belum ada transaksi dalam shift ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@include('tenant.partials.trx-detail-modal')

@endsection
