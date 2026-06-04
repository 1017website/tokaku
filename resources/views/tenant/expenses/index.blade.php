@extends('layouts.app')
@section('title','Pengeluaran')
@section('page-title','Pengeluaran')
@section('page-subtitle','Catat biaya operasional toko')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
    <div style="background:#fff;border:1px solid #f1f5f9;border-radius:16px;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,.04);">
        <p style="font-size:12px;color:#64748b;font-weight:700;">Total Pengeluaran</p>
        <p style="font-size:26px;font-weight:800;color:#be123c;margin-top:8px;">Rp {{ number_format($totalExpenses,0,',','.') }}</p>
        <p style="font-size:12px;color:#94a3b8;margin-top:4px;">Periode {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
    </div>
    <div class="lg:col-span-2" style="background:#fff;border:1px solid #f1f5f9;border-radius:16px;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,.04);">
        <p style="font-size:13.5px;font-weight:800;color:#0f172a;margin-bottom:10px;">Ringkasan Kategori</p>
        <div style="display:flex;flex-wrap:wrap;gap:8px;">
        @forelse($byCategory as $row)
            <span style="font-size:12px;background:#f8fafc;border:1px solid #eef2f7;border-radius:999px;padding:7px 11px;color:#334155;"><b>{{ $row->category }}</b> · Rp {{ number_format($row->total,0,',','.') }}</span>
        @empty
            <span style="font-size:13px;color:#94a3b8;">Belum ada data.</span>
        @endforelse
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <div style="background:#fff;border:1px solid #f1f5f9;border-radius:16px;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,.04);height:max-content;">
        <h3 style="font-size:15px;font-weight:800;color:#0f172a;margin-bottom:14px;">Tambah Pengeluaran</h3>
        <form method="POST" action="{{ route('tenant.expenses.store') }}" style="display:flex;flex-direction:column;gap:12px;">
            @csrf
            <div><label class="form-label">Tanggal</label><input type="date" name="expense_date" value="{{ old('expense_date', now()->toDateString()) }}" class="form-input" required></div>
            <div><label class="form-label">Kategori</label><select name="category" class="form-input" required>@foreach($categories as $cat)<option value="{{ $cat }}">{{ $cat }}</option>@endforeach</select></div>
            <div><label class="form-label">Nominal</label><input type="number" name="amount" min="0" step="100" class="form-input" required placeholder="0"></div>
            <div><label class="form-label">Keterangan</label><input type="text" name="description" class="form-input" placeholder="contoh: Bayar listrik bulan ini"></div>
            <button class="btn-primary" style="justify-content:center;">Simpan</button>
        </form>
    </div>

    <div class="lg:col-span-2" style="background:#fff;border:1px solid #f1f5f9;border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.04);">
        <form method="GET" style="padding:14px 18px;border-bottom:1px solid #f8fafc;display:flex;gap:8px;flex-wrap:wrap;align-items:end;">
            <div><label class="form-label">Dari</label><input type="date" name="start_date" value="{{ $startDate }}" class="form-input"></div>
            <div><label class="form-label">Sampai</label><input type="date" name="end_date" value="{{ $endDate }}" class="form-input"></div>
            <div><label class="form-label">Kategori</label><select name="category" class="form-input"><option value="">Semua</option>@foreach($categories as $cat)<option value="{{ $cat }}" {{ request('category')===$cat?'selected':'' }}>{{ $cat }}</option>@endforeach</select></div>
            <button class="btn-secondary">Filter</button>
        </form>
        <div class="overflow-x-auto">
            <table style="width:100%;border-collapse:collapse;min-width:640px;">
                <thead><tr style="background:#f8fafc;"><th style="text-align:left;padding:11px 18px;font-size:11px;color:#64748b;text-transform:uppercase;">Tanggal</th><th style="text-align:left;padding:11px 18px;font-size:11px;color:#64748b;text-transform:uppercase;">Kategori</th><th style="text-align:left;padding:11px 18px;font-size:11px;color:#64748b;text-transform:uppercase;">Keterangan</th><th style="text-align:right;padding:11px 18px;font-size:11px;color:#64748b;text-transform:uppercase;">Nominal</th><th style="text-align:center;padding:11px 18px;font-size:11px;color:#64748b;text-transform:uppercase;">Aksi</th></tr></thead>
                <tbody>
                @forelse($expenses as $expense)
                    <tr style="border-bottom:1px solid #f8fafc;">
                        <td style="padding:13px 18px;font-size:13px;color:#334155;">{{ $expense->expense_date->format('d M Y') }}</td>
                        <td style="padding:13px 18px;"><span style="font-size:12px;background:#f8fafc;border-radius:999px;padding:5px 9px;color:#334155;font-weight:700;">{{ $expense->category }}</span></td>
                        <td style="padding:13px 18px;font-size:13px;color:#64748b;">{{ $expense->description ?? '-' }}</td>
                        <td style="padding:13px 18px;text-align:right;font-size:13.5px;font-weight:800;color:#be123c;">Rp {{ number_format($expense->amount,0,',','.') }}</td>
                        <td style="padding:13px 18px;text-align:center;"><form method="POST" action="{{ route('tenant.expenses.destroy', $expense) }}" onsubmit="return confirm('Hapus pengeluaran ini?')">@csrf @method('DELETE')<button style="border:none;background:none;color:#f43f5e;font-weight:700;font-size:12px;cursor:pointer;">Hapus</button></form></td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="padding:45px;text-align:center;color:#94a3b8;font-size:13px;">Belum ada pengeluaran.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($expenses->hasPages())<div style="padding:14px 18px;border-top:1px solid #f8fafc;">{{ $expenses->links() }}</div>@endif
    </div>
</div>
@endsection
