@extends('layouts.app')
@section('title','Hutang Piutang')
@section('page-title','Hutang Piutang')
@section('page-subtitle','Catat dan pantau hutang pelanggan')

@section('header-actions')
<a href="{{ route('tenant.hutang.history') }}" class="btn-secondary">Riwayat Lunas</a>
@endsection

@section('content')
<div class="grid grid-cols-2 lg:grid-cols-3 gap-3 mb-4">
    <div style="background:#fff1f2;border:1px solid #fecdd3;border-radius:14px;padding:18px;">
        <p style="font-size:12px;font-weight:500;color:#be123c;margin-bottom:6px;">Total Hutang Aktif</p>
        <p style="font-size:22px;font-weight:700;color:#be123c;">Rp {{ number_format($totalDebt,0,',','.') }}</p>
    </div>
    <div style="background:#f0fdf4;border:1px solid #bbf7d2;border-radius:14px;padding:18px;">
        <p style="font-size:12px;font-weight:500;color:#15803d;margin-bottom:6px;">Dibayar Hari Ini</p>
        <p style="font-size:22px;font-weight:700;color:#15803d;">Rp {{ number_format($paidToday,0,',','.') }}</p>
    </div>
    <div class="col-span-2 lg:col-span-1" style="background:#fff;border:1px solid #f1f5f9;border-radius:14px;padding:18px;">
        <p style="font-size:12px;font-weight:500;color:#64748b;margin-bottom:6px;">Total Catatan Hutang</p>
        <p style="font-size:22px;font-weight:700;color:#0f172a;">{{ $debts->total() }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <div>
        <div style="background:#fff;border-radius:16px;border:1px solid #f1f5f9;padding:22px;">
            <p style="font-size:14px;font-weight:600;color:#0f172a;margin-bottom:16px;">Catat Hutang Baru</p>
            <form method="POST" action="{{ route('tenant.hutang.store') }}" style="display:flex;flex-direction:column;gap:12px;">
                @csrf
                <div>
                    <label class="form-label">Nama Pelanggan *</label>
                    <input type="text" name="customer_name" value="{{ old('customer_name') }}" required class="form-input" placeholder="Nama pelanggan">
                </div>
                <div>
                    <label class="form-label">No. HP</label>
                    <input type="text" name="customer_phone" value="{{ old('customer_phone') }}" class="form-input" placeholder="08xxxxxxxxxx">
                </div>
                <div>
                    <label class="form-label">Jumlah Hutang (Rp) *</label>
                    <input type="number" name="amount" value="{{ old('amount') }}" required min="1" class="form-input" placeholder="0">
                </div>
                <div>
                    <label class="form-label">Jatuh Tempo</label>
                    <input type="date" name="due_date" value="{{ old('due_date') }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Catatan</label>
                    <input type="text" name="notes" value="{{ old('notes') }}" class="form-input" placeholder="opsional">
                </div>
                <button type="submit" class="btn-primary" style="justify-content:center;">Catat Hutang</button>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div style="background:#fff;border-radius:16px;border:1px solid #f1f5f9;overflow:hidden;">
            <div style="padding:14px 18px;border-bottom:1px solid #f8fafc;">
                <p style="font-size:14px;font-weight:600;color:#0f172a;">Hutang Belum Lunas</p>
            </div>
            <div class="overflow-x-auto">
                <table style="width:100%;border-collapse:collapse;min-width:500px;">
                    <thead><tr style="background:#f8fafc;border-bottom:1px solid #f1f5f9;">
                        @foreach(['Pelanggan','Hutang','Sudah Bayar','Sisa','Jatuh Tempo','Aksi'] as $h)
                        <th style="text-align:left;font-size:11px;font-weight:600;color:#64748b;padding:10px 14px;text-transform:uppercase;">{{ $h }}</th>
                        @endforeach
                    </tr></thead>
                    <tbody>
                    @forelse($debts as $debt)
                    <tr style="border-bottom:1px solid #f8fafc;{{ $debt->isOverdue()?'background:#fff8f8;':'' }}">
                        <td style="padding:12px 14px;">
                            <p style="font-size:13.5px;font-weight:500;color:#0f172a;">{{ $debt->customer_name }}</p>
                            <p style="font-size:12px;color:#94a3b8;">{{ $debt->customer_phone ?? '—' }}</p>
                        </td>
                        <td style="padding:12px 14px;font-size:13.5px;font-weight:600;color:#0f172a;">Rp {{ number_format($debt->amount,0,',','.') }}</td>
                        <td style="padding:12px 14px;font-size:13px;color:#15803d;">Rp {{ number_format($debt->paid_amount,0,',','.') }}</td>
                        <td style="padding:12px 14px;font-size:14px;font-weight:700;color:#be123c;">Rp {{ number_format($debt->remaining,0,',','.') }}</td>
                        <td style="padding:12px 14px;">
                            @if($debt->due_date)
                            <span style="font-size:12.5px;{{ $debt->isOverdue()?'color:#be123c;font-weight:600;':'color:#64748b;' }}">
                                {{ $debt->isOverdue() ? '⚠ ' : '' }}{{ $debt->due_date->format('d M Y') }}
                            </span>
                            @else<span style="color:#94a3b8;font-size:13px;">—</span>@endif
                        </td>
                        <td style="padding:12px 14px;">
                            <button onclick="openPayModal({{ $debt->id }},'{{ addslashes($debt->customer_name) }}',{{ $debt->remaining }})"
                                style="font-size:12.5px;font-weight:500;padding:6px 12px;border-radius:8px;background:#0F6E56;color:#fff;border:none;cursor:pointer;">
                                Bayar
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" style="padding:50px;text-align:center;color:#94a3b8;font-size:13.5px;">Tidak ada hutang aktif. 🎉</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if($debts->hasPages())<div style="padding:12px 16px;border-top:1px solid #f8fafc;">{{ $debts->links() }}</div>@endif
        </div>
    </div>
</div>

{{-- Modal Bayar Hutang --}}
<div id="payModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);align-items:center;justify-content:center;z-index:50;padding:16px;">
    <div style="background:#fff;border-radius:20px;width:100%;max-width:380px;padding:28px;">
        <h3 style="font-size:16px;font-weight:700;color:#0f172a;margin-bottom:4px;">Bayar Hutang</h3>
        <p id="payModalName" style="font-size:13px;color:#64748b;margin-bottom:16px;"></p>
        <form id="payForm" method="POST" style="display:flex;flex-direction:column;gap:14px;">
            @csrf
            <div>
                <label class="form-label">Jumlah Bayar (Rp) *</label>
                <input type="number" name="amount" id="payAmount" required min="1" class="form-input" placeholder="0">
                <p id="payMax" style="font-size:12px;color:#64748b;margin-top:4px;"></p>
            </div>
            <div>
                <label class="form-label">Catatan</label>
                <input type="text" name="note" class="form-input" placeholder="opsional">
            </div>
            <div style="display:flex;gap:10px;">
                <button type="button" onclick="closePayModal()" class="btn-secondary" style="flex:1;justify-content:center;">Batal</button>
                <button type="submit" class="btn-primary" style="flex:1;justify-content:center;">Konfirmasi</button>
            </div>
        </form>
    </div>
</div>
@endsection
@push('scripts')
<script>
function openPayModal(id, name, remaining) {
    document.getElementById('payModalName').textContent = name;
    document.getElementById('payMax').textContent = 'Sisa hutang: Rp ' + remaining.toLocaleString('id-ID');
    document.getElementById('payAmount').max = remaining;
    document.getElementById('payAmount').value = remaining;
    document.getElementById('payForm').action = '/hutang/' + id + '/bayar';
    document.getElementById('payModal').style.display = 'flex';
}
function closePayModal() { document.getElementById('payModal').style.display = 'none'; }
</script>
@endpush
