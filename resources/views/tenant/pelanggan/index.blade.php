@extends('layouts.app')
@section('title','Pelanggan')
@section('page-title','Pelanggan')
@section('page-subtitle','Kelola data dan riwayat pelanggan')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <div>
        <div style="background:#fff;border-radius:16px;border:1px solid #f1f5f9;padding:22px;">
            <p style="font-size:14px;font-weight:600;color:#0f172a;margin-bottom:16px;">Tambah Pelanggan</p>
            @if($errors->any())
            <div style="background:#fff1f2;border:1px solid #fecdd3;color:#be123c;font-size:13px;border-radius:10px;padding:10px 14px;margin-bottom:12px;">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ route('tenant.pelanggan.store') }}" style="display:flex;flex-direction:column;gap:12px;">
                @csrf
                @foreach([['name','Nama Lengkap *','text','Budi Santoso',true],['phone','No. HP','text','08xxxxxxxxxx',false],['email','Email','email','email@domain.com',false],['address','Alamat','text','Jl...',false],['birthday','Tanggal Lahir','date','',false]] as [$n,$l,$t,$ph,$req])
                <div>
                    <label style="display:block;font-size:12.5px;font-weight:500;color:#374151;margin-bottom:4px;">{{ $l }}</label>
                    <input type="{{ $t }}" name="{{ $n }}" value="{{ old($n) }}" {{ $req?'required':'' }} placeholder="{{ $ph }}" class="form-input">
                </div>
                @endforeach
                <button type="submit" class="btn-primary" style="justify-content:center;margin-top:4px;">Tambah Pelanggan</button>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div style="background:#fff;border-radius:16px;border:1px solid #f1f5f9;overflow:hidden;">
            <div style="padding:14px 18px;border-bottom:1px solid #f8fafc;display:flex;align-items:center;justify-content:space-between;">
                <p style="font-size:14px;font-weight:600;color:#0f172a;">Semua Pelanggan ({{ $customers->total() }})</p>
                <input type="text" id="searchCust" placeholder="Cari..." style="border:1.5px solid #e2e8f0;border-radius:10px;padding:7px 12px;font-size:13px;outline:none;width:180px;" onfocus="this.style.borderColor='#0F6E56'" onblur="this.style.borderColor='#e2e8f0'">
            </div>
            <div class="overflow-x-auto">
                <table style="width:100%;border-collapse:collapse;min-width:500px;">
                    <thead><tr style="background:#f8fafc;border-bottom:1px solid #f1f5f9;">
                        @foreach(['Pelanggan','Kontak','Total Belanja','Transaksi','Aksi'] as $h)
                        <th style="text-align:left;font-size:11px;font-weight:600;color:#64748b;padding:10px 16px;text-transform:uppercase;">{{ $h }}</th>
                        @endforeach
                    </tr></thead>
                    <tbody>
                    @forelse($customers as $c)
                    <tr class="cust-row" data-name="{{ strtolower($c->name) }}" style="border-bottom:1px solid #f8fafc;transition:background 0.1s;" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='#fff'">
                        <td style="padding:12px 16px;">
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div style="width:34px;height:34px;background:#f0fdf6;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <span style="font-size:13px;font-weight:700;color:#0F6E56;">{{ strtoupper(substr($c->name,0,2)) }}</span>
                                </div>
                                <p style="font-size:13.5px;font-weight:500;color:#0f172a;">{{ $c->name }}</p>
                            </div>
                        </td>
                        <td style="padding:12px 16px;">
                            <p style="font-size:13px;color:#374151;">{{ $c->phone ?? '—' }}</p>
                            <p style="font-size:12px;color:#94a3b8;">{{ $c->email ?? '' }}</p>
                        </td>
                        <td style="padding:12px 16px;font-size:13.5px;font-weight:700;color:#0F6E56;">Rp {{ number_format($c->total_spent,0,',','.') }}</td>
                        <td style="padding:12px 16px;font-size:13.5px;color:#374151;">{{ $c->total_transactions }}x</td>
                        <td style="padding:12px 16px;">
                            <a href="{{ route('tenant.pelanggan.show', $c) }}" style="font-size:13px;color:#0F6E56;font-weight:500;text-decoration:none;">Detail</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" style="padding:50px;text-align:center;color:#94a3b8;font-size:13.5px;">Belum ada pelanggan.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if($customers->hasPages())
            <div style="padding:12px 16px;border-top:1px solid #f8fafc;">{{ $customers->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
document.getElementById('searchCust').addEventListener('input', function() {
    var q = this.value.toLowerCase();
    document.querySelectorAll('.cust-row').forEach(r => r.style.display = r.dataset.name.includes(q)?'':'none');
});
</script>
@endpush
