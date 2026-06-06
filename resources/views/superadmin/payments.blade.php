@extends('superadmin.layout')
@section('title','Verifikasi Pembayaran')
@section('page-title','Verifikasi Pembayaran')
@section('page-subtitle','Konfirmasi transfer langganan dari tenant')

@section('content')

{{-- Filter status --}}
@php
    $tabs = ['waiting_confirmation'=>'Menunggu Konfirmasi','paid'=>'Lunas','rejected'=>'Ditolak','all'=>'Semua'];
@endphp
<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:18px;">
    @foreach($tabs as $key => $label)
    <a href="{{ route('superadmin.payments', ['status'=>$key]) }}"
       style="font-size:13px;font-weight:500;padding:8px 16px;border-radius:99px;text-decoration:none;{{ $status===$key ? 'background:#0F6E56;color:#fff;' : 'background:#fff;color:#374151;border:1px solid #e2e8f0;' }}">
        {{ $label }}@if($key==='waiting_confirmation' && $waitingCount) ({{ $waitingCount }})@endif
    </a>
    @endforeach
</div>

<div style="background:#fff;border-radius:16px;border:1px solid #f1f5f9;overflow:hidden;">
    <div class="table-responsive overflow-x-auto">
        <table style="width:100%;border-collapse:collapse;min-width:760px;">
            <thead><tr style="background:#f8fafc;border-bottom:1px solid #f1f5f9;">
                @foreach(['Invoice','Tenant','Nominal','Durasi','Bukti','Status','Aksi'] as $h)
                <th style="text-align:left;font-size:11px;font-weight:600;color:#64748b;padding:11px 14px;text-transform:uppercase;">{{ $h }}</th>
                @endforeach
            </tr></thead>
            <tbody>
                @forelse($invoices as $inv)
                @php
                    $sc = match($inv->status){
                        'paid'=>['#f0fdf6','#15803d'], 'waiting_confirmation'=>['#eff6ff','#1d4ed8'],
                        'rejected'=>['#fef2f2','#be123c'], 'expired'=>['#f8fafc','#64748b'], default=>['#fffbeb','#92400e'],
                    };
                @endphp
                <tr style="border-bottom:1px solid #f8fafc;">
                    <td data-label="Invoice" style="padding:12px 14px;font-size:12.5px;font-weight:500;color:#0f172a;">{{ $inv->invoice_no }}<br><span style="font-size:11px;color:#94a3b8;">{{ $inv->created_at->format('d M Y H:i') }}</span></td>
                    <td data-label="Tenant" style="padding:12px 14px;font-size:13px;color:#0f172a;">{{ $inv->tenant->name ?? '-' }}<br><span style="font-size:11px;color:#94a3b8;">{{ $inv->plan->name ?? '' }}</span></td>
                    <td data-label="Nominal" style="padding:12px 14px;font-size:13px;font-weight:700;color:#0F6E56;">Rp {{ number_format($inv->total_amount,0,',','.') }}<br><span style="font-size:11px;color:#94a3b8;font-weight:400;">kode {{ $inv->unique_code }}</span></td>
                    <td data-label="Durasi" style="padding:12px 14px;font-size:12.5px;color:#374151;">{{ $inv->duration_months }} bln</td>
                    <td data-label="Bukti" style="padding:12px 14px;">
                        @if($inv->proof_path)
                        <a href="{{ Storage::url($inv->proof_path) }}" target="_blank" style="font-size:12.5px;color:#0F6E56;font-weight:600;text-decoration:none;">Lihat bukti →</a>
                        @else <span style="font-size:12px;color:#94a3b8;">—</span> @endif
                    </td>
                    <td data-label="Status" style="padding:12px 14px;"><span style="font-size:11.5px;font-weight:500;padding:3px 10px;border-radius:99px;background:{{ $sc[0] }};color:{{ $sc[1] }};">{{ $inv->statusLabel() }}</span></td>
                    <td data-label="Aksi" style="padding:12px 14px;">
                        @if($inv->status === 'waiting_confirmation')
                        <div style="display:flex;gap:6px;">
                            <form method="POST" action="{{ route('superadmin.payments.confirm', $inv) }}" onsubmit="return confirm('Konfirmasi pembayaran {{ $inv->invoice_no }}? Langganan tenant akan aktif {{ $inv->duration_months }} bulan.')">
                                @csrf @method('PUT')
                                <input type="hidden" name="action" value="approve">
                                <button type="submit" style="font-size:12px;font-weight:600;color:#fff;background:#0F6E56;border:none;border-radius:8px;padding:6px 12px;cursor:pointer;">Setujui</button>
                            </form>
                            <form method="POST" action="{{ route('superadmin.payments.confirm', $inv) }}" onsubmit="return confirm('Tolak pembayaran ini?')">
                                @csrf @method('PUT')
                                <input type="hidden" name="action" value="reject">
                                <button type="submit" style="font-size:12px;font-weight:600;color:#be123c;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:6px 12px;cursor:pointer;">Tolak</button>
                            </form>
                        </div>
                        @else
                        <span style="font-size:12px;color:#94a3b8;">{{ $inv->confirmed_at ? $inv->confirmed_at->format('d M Y') : '—' }}</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td data-empty colspan="7" style="padding:50px;text-align:center;color:#94a3b8;font-size:13.5px;">Tidak ada data pembayaran.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($invoices->hasPages())
    <div style="padding:12px 16px;border-top:1px solid #f8fafc;">{{ $invoices->links() }}</div>
    @endif
</div>

@endsection
