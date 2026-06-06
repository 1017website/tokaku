@extends('layouts.app')
@section('title','Shift Kasir')
@section('page-title','Shift Kasir')
@section('page-subtitle','Kelola shift kerja dan rekap kas')

@section('content')

{{-- Status Shift Aktif --}}
@if($activeShift)
<div style="background:#0F6E56;border-radius:16px;padding:20px 24px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
    <div>
        <p style="color:rgba(255,255,255,0.7);font-size:12px;font-weight:500;margin-bottom:4px;">SHIFT SEDANG BERJALAN</p>
        <p style="color:#fff;font-size:16px;font-weight:700;">Dibuka {{ $activeShift->opened_at->format('d M Y, H:i') }}</p>
        <p style="color:rgba(255,255,255,0.7);font-size:13px;margin-top:2px;">Kas awal: Rp {{ number_format($activeShift->opening_cash,0,',','.') }}</p>
    </div>
    <button onclick="document.getElementById('closeModal').style.display='flex'"
        style="background:rgba(255,255,255,0.15);color:#fff;font-family:Inter,sans-serif;font-size:13.5px;font-weight:600;padding:10px 20px;border-radius:10px;border:1.5px solid rgba(255,255,255,0.3);cursor:pointer;">
        Tutup Shift
    </button>
</div>
@else
<div style="background:#fff;border-radius:16px;border:1px solid #f1f5f9;padding:20px 24px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
    <div>
        <p style="font-size:14px;font-weight:600;color:#0f172a;">Tidak ada shift aktif</p>
        <p style="font-size:13px;color:#64748b;margin-top:3px;">Buka shift baru untuk mulai bekerja.</p>
    </div>
    <button onclick="document.getElementById('openModal').style.display='flex'"
        class="btn-primary">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        Buka Shift
    </button>
</div>
@endif

{{-- Riwayat Shift --}}
<div style="background:#fff;border-radius:16px;border:1px solid #f1f5f9;overflow:hidden;">
    <div style="padding:14px 18px;border-bottom:1px solid #f8fafc;">
        <p style="font-size:14px;font-weight:600;color:#0f172a;">Riwayat Shift</p>
    </div>
    <div class="table-responsive overflow-x-auto">
        <table style="width:100%;border-collapse:collapse;min-width:600px;">
            <thead><tr style="background:#f8fafc;border-bottom:1px solid #f1f5f9;">
                @foreach(['Kasir','Buka','Tutup','Kas Awal','Total Transaksi','Revenue','Selisih','Aksi'] as $h)
                <th style="text-align:left;font-size:11px;font-weight:600;color:#64748b;padding:10px 14px;text-transform:uppercase;">{{ $h }}</th>
                @endforeach
            </tr></thead>
            <tbody>
            @forelse($shifts as $shift)
            <tr style="border-bottom:1px solid #f8fafc;transition:background 0.1s;" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='#fff'">
                <td data-label="Kasir" style="padding:12px 14px;font-size:13.5px;font-weight:500;color:#0f172a;">{{ $shift->user->name }}</td>
                <td data-label="Buka" style="padding:12px 14px;font-size:12.5px;color:#374151;">{{ $shift->opened_at->format('d M, H:i') }}</td>
                <td data-label="Tutup" style="padding:12px 14px;font-size:12.5px;color:#374151;">
                    @if($shift->closed_at){{ $shift->closed_at->format('d M, H:i') }}@else<span style="color:#f59e0b;font-weight:500;">Berjalan</span>@endif
                </td>
                <td data-label="Kas Awal" style="padding:12px 14px;font-size:13px;color:#374151;">Rp {{ number_format($shift->opening_cash,0,',','.') }}</td>
                @php
                    $isRunning = is_null($shift->closed_at);
                    $trxCount  = $isRunning ? ($shift->transactions_count ?? 0) : $shift->total_transactions;
                    $trxRev    = $isRunning ? ($shift->transactions_sum_total ?? 0) : $shift->total_revenue;
                @endphp
                <td data-label="Total Transaksi" style="padding:12px 14px;font-size:13.5px;font-weight:600;color:#0f172a;">{{ $trxCount }}</td>
                <td data-label="Revenue" style="padding:12px 14px;font-size:13.5px;font-weight:700;color:#0F6E56;">Rp {{ number_format($trxRev,0,',','.') }}</td>
                <td data-label="Selisih" style="padding:12px 14px;">
                    @if(!is_null($shift->cash_difference))
                    <span style="font-size:13px;font-weight:600;color:{{ $shift->cash_difference==0?'#15803d':($shift->cash_difference>0?'#2563eb':'#be123c') }};">
                        {{ $shift->cash_difference>0?'+':'' }}Rp {{ number_format($shift->cash_difference,0,',','.') }}
                    </span>
                    @else<span style="color:#94a3b8;">—</span>@endif
                </td>
                <td data-label="Aksi" style="padding:12px 14px;">
                    <a href="{{ route('tenant.shift.show', $shift) }}" style="font-size:13px;color:#0F6E56;font-weight:500;text-decoration:none;">Detail</a>
                </td>
            </tr>
            @empty
            <tr><td data-empty colspan="8" style="padding:50px;text-align:center;color:#94a3b8;font-size:13.5px;">Belum ada riwayat shift.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($shifts->hasPages())<div style="padding:12px 16px;border-top:1px solid #f8fafc;">{{ $shifts->links() }}</div>@endif
</div>

{{-- Modal Buka Shift --}}
<div id="openModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);align-items:center;justify-content:center;z-index:50;padding:16px;">
    <div style="background:#fff;border-radius:20px;width:100%;max-width:380px;padding:28px;">
        <h3 style="font-size:16px;font-weight:700;color:#0f172a;margin-bottom:4px;">Buka Shift</h3>
        <p style="font-size:13px;color:#64748b;margin-bottom:20px;">Masukkan jumlah kas awal di laci kasir.</p>
        <form method="POST" action="{{ route('tenant.shift.open') }}" style="display:flex;flex-direction:column;gap:14px;">
            @csrf
            <div>
                <label class="form-label">Kas Awal (Rp) *</label>
                <input type="text" inputmode="numeric" name="opening_cash" required class="form-input input-rupiah" placeholder="500000">
            </div>
            <div style="display:flex;gap:10px;">
                <button type="button" onclick="document.getElementById('openModal').style.display='none'" class="btn-secondary" style="flex:1;justify-content:center;">Batal</button>
                <button type="submit" class="btn-primary" style="flex:1;justify-content:center;">Buka Shift</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Tutup Shift --}}
@if($activeShift)
<div id="closeModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);align-items:center;justify-content:center;z-index:50;padding:16px;">
    <div style="background:#fff;border-radius:20px;width:100%;max-width:380px;padding:28px;">
        <h3 style="font-size:16px;font-weight:700;color:#0f172a;margin-bottom:4px;">Tutup Shift</h3>
        <p style="font-size:13px;color:#64748b;margin-bottom:16px;">Hitung uang tunai di laci kasir, lalu masukkan jumlahnya.</p>

        @if($cashSummary)
        <div style="background:#f8fafc;border:1px solid #f1f5f9;border-radius:12px;padding:12px 14px;margin-bottom:16px;display:flex;flex-direction:column;gap:7px;">
            <div style="display:flex;justify-content:space-between;font-size:12.5px;color:#64748b;"><span>Kas awal</span><b style="color:#0f172a;">Rp {{ number_format($cashSummary['opening'],0,',','.') }}</b></div>
            <div style="display:flex;justify-content:space-between;font-size:12.5px;color:#64748b;"><span>Penjualan tunai</span><b style="color:#0f172a;">Rp {{ number_format($cashSummary['cash'],0,',','.') }}</b></div>
            <div style="display:flex;justify-content:space-between;font-size:13px;border-top:1px dashed #e2e8f0;padding-top:7px;"><b style="color:#0f172a;">Kas seharusnya di laci</b><b style="color:#0F6E56;">Rp {{ number_format($cashSummary['expected'],0,',','.') }}</b></div>
            @if($cashSummary['noncash'] > 0)
            <div style="display:flex;justify-content:space-between;font-size:12px;color:#94a3b8;margin-top:2px;"><span>Non-tunai (QRIS/transfer)</span><span>Rp {{ number_format($cashSummary['noncash'],0,',','.') }}</span></div>
            <p style="font-size:11px;color:#94a3b8;line-height:1.4;">Pemasukan non-tunai tidak masuk laci, jadi tidak dihitung di kas akhir.</p>
            @endif
        </div>
        @endif
        <form method="POST" action="{{ route('tenant.shift.close', $activeShift) }}" style="display:flex;flex-direction:column;gap:14px;">
            @csrf
            <div>
                <label class="form-label">Kas Akhir (Rp) *</label>
                <input type="text" inputmode="numeric" name="closing_cash" required class="form-input input-rupiah" placeholder="0">
            </div>
            <div>
                <label class="form-label">Catatan</label>
                <input type="text" name="notes" class="form-input" placeholder="opsional">
            </div>
            <div style="display:flex;gap:10px;">
                <button type="button" onclick="document.getElementById('closeModal').style.display='none'" class="btn-secondary" style="flex:1;justify-content:center;">Batal</button>
                <button type="submit" class="btn-primary" style="flex:1;justify-content:center;background:#f43f5e;" onmouseover="this.style.background='#be123c'" onmouseout="this.style.background='#f43f5e'">Tutup Shift</button>
            </div>
        </form>
    </div>
</div>
@endif

@endsection
