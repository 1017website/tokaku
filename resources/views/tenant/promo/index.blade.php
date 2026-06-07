@extends('layouts.app')
@section('title','Promo & Diskon')
@section('page-title','Promo & Diskon')
@section('page-subtitle','Kelola promo dan diskon toko')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <div>
        <div style="background:#fff;border-radius:16px;border:1px solid #f1f5f9;padding:22px;">
            <p style="font-size:14px;font-weight:600;color:#0f172a;margin-bottom:16px;">Buat Promo Baru</p>
            @if($errors->any())
            <div style="background:#fff1f2;border:1px solid #fecdd3;color:#be123c;font-size:13px;border-radius:10px;padding:10px 14px;margin-bottom:12px;">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ route('tenant.promo.store') }}" style="display:flex;flex-direction:column;gap:12px;">
                @csrf
                <div>
                    <label class="form-label">Nama Promo *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="form-input" placeholder="contoh: Diskon Lebaran">
                </div>
                <div>
                    <label class="form-label">Kode Promo <span style="color:#94a3b8;font-weight:400;">(opsional)</span></label>
                    <input type="text" name="code" value="{{ old('code') }}" class="form-input" placeholder="LEBARAN20" style="text-transform:uppercase;">
                </div>
                <div>
                    <label class="form-label">Jenis Diskon *</label>
                    <select name="type" id="promoType" class="form-input" style="cursor:pointer;" onchange="updatePromoFields()">
                        <option value="percent" {{ old('type')=='percent'?'selected':'' }}>Persen (%)</option>
                        <option value="fixed" {{ old('type')=='fixed'?'selected':'' }}>Nominal (Rp)</option>
                        <option value="buyxgety" {{ old('type')=='buyxgety'?'selected':'' }}>Beli X Gratis Y (B1G1)</option>
                    </select>
                </div>
                <div id="valueWrap">
                    <label class="form-label" id="valueLabel">Nilai Diskon (%)</label>
                    <input type="number" name="value" id="valueInput" value="{{ old('value') }}" min="0" class="form-input" placeholder="10">
                </div>
                <div id="bxgyWrap" style="display:none;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <label class="form-label">Beli (qty) *</label>
                        <input type="number" name="min_qty" id="minQtyInput" value="{{ old('min_qty',1) }}" min="1" class="form-input" placeholder="1">
                    </div>
                    <div>
                        <label class="form-label">Gratis (qty) *</label>
                        <input type="number" name="free_qty" id="freeQtyInput" value="{{ old('free_qty',1) }}" min="1" class="form-input" placeholder="1">
                    </div>
                    <div style="grid-column:1/-1;">
                        <label class="form-label">Produk <span style="color:#94a3b8;font-weight:400;">(kosongkan = semua produk)</span></label>
                        <select name="product_id" class="form-input" style="cursor:pointer;">
                            <option value="">Semua produk</option>
                            @foreach($products as $p)
                                <option value="{{ $p->id }}" {{ old('product_id')==$p->id?'selected':'' }}>{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <p style="grid-column:1/-1;font-size:11.5px;color:#94a3b8;margin-top:-4px;">Contoh: Beli 1 Gratis 1 → pelanggan beli 2, bayar 1. Item gratis = produk yang sama.</p>
                </div>
                <div>
                    <label class="form-label">Min. Transaksi (Rp)</label>
                    <input type="text" inputmode="numeric" name="min_transaction" value="{{ old('min_transaction',0) }}" class="form-input input-rupiah" placeholder="0 = tidak ada minimum">
                </div>
                <div>
                    <label class="form-label">Maks. Diskon (Rp) <span style="color:#94a3b8;font-weight:400;">0 = unlimited</span></label>
                    <input type="text" inputmode="numeric" name="max_discount" value="{{ old('max_discount',0) }}" class="form-input input-rupiah">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="form-label">Berlaku Dari</label>
                        <input type="datetime-local" name="starts_at" value="{{ old('starts_at') }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Berlaku Sampai</label>
                        <input type="datetime-local" name="ends_at" value="{{ old('ends_at') }}" class="form-input">
                    </div>
                </div>
                <button type="submit" class="btn-primary" style="justify-content:center;margin-top:4px;">Buat Promo</button>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div style="background:#fff;border-radius:16px;border:1px solid #f1f5f9;overflow:hidden;">
            <div style="padding:14px 18px;border-bottom:1px solid #f8fafc;">
                <p style="font-size:14px;font-weight:600;color:#0f172a;">Daftar Promo ({{ $promos->total() }})</p>
            </div>
            <div>
                @forelse($promos as $promo)
                <div style="padding:16px 18px;border-bottom:1px solid #f8fafc;">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:10px;">
                        <div>
                            <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
                                <p style="font-size:14px;font-weight:600;color:#0f172a;">{{ $promo->name }}</p>
                                @if($promo->code)
                                <span style="font-size:11.5px;font-weight:600;background:#f0fdf6;color:#0F6E56;padding:2px 8px;border-radius:6px;font-family:monospace;">{{ $promo->code }}</span>
                                @endif
                                @if($promo->isValid())
                                <span style="font-size:11px;font-weight:500;background:#f0fdf4;color:#15803d;padding:2px 8px;border-radius:99px;">Aktif</span>
                                @else
                                <span style="font-size:11px;font-weight:500;background:#f8fafc;color:#64748b;padding:2px 8px;border-radius:99px;">Nonaktif</span>
                                @endif
                            </div>
                            <p style="font-size:13px;color:#0F6E56;font-weight:500;">{{ $promo->type_label }}</p>
                            <div style="display:flex;align-items:center;gap:12px;margin-top:5px;">
                                @if($promo->min_transaction > 0)
                                <span style="font-size:12px;color:#64748b;">Min. Rp {{ number_format($promo->min_transaction,0,',','.') }}</span>
                                @endif
                                @if($promo->starts_at || $promo->ends_at)
                                <span style="font-size:12px;color:#64748b;">
                                    {{ $promo->starts_at?->format('d M') ?? '∞' }} — {{ $promo->ends_at?->format('d M Y') ?? '∞' }}
                                </span>
                                @endif
                            </div>
                        </div>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <form method="POST" action="{{ route('tenant.promo.toggle', $promo) }}">
                                @csrf @method('PUT')
                                <button type="submit" style="font-size:12px;font-weight:500;padding:6px 12px;border-radius:8px;border:1.5px solid;cursor:pointer;font-family:Inter,sans-serif;background:#fff;{{ $promo->is_active ? 'border-color:#fecdd3;color:#be123c;' : 'border-color:#bbf7d2;color:#15803d;' }}">
                                    {{ $promo->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('tenant.promo.destroy', $promo) }}" onsubmit="return confirm('Hapus promo ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" style="font-size:12px;font-weight:500;padding:6px 12px;border-radius:8px;border:1.5px solid #e2e8f0;cursor:pointer;font-family:Inter,sans-serif;background:#fff;color:#64748b;">Hapus</button>
                            </form>
                        </div>
                    </div>
                </div>
                @empty
                <div style="padding:50px;text-align:center;color:#94a3b8;font-size:13.5px;">Belum ada promo.</div>
                @endforelse
            </div>
            @if($promos->hasPages())
            <div style="padding:12px 16px;border-top:1px solid #f8fafc;">{{ $promos->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
function updatePromoFields() {
    var t = document.getElementById('promoType').value;
    var isBxgy = t === 'buyxgety';
    var valueWrap = document.getElementById('valueWrap');
    var bxgyWrap = document.getElementById('bxgyWrap');
    var valueInput = document.getElementById('valueInput');
    var minQty = document.getElementById('minQtyInput');
    var freeQty = document.getElementById('freeQtyInput');

    valueWrap.style.display = isBxgy ? 'none' : 'block';
    bxgyWrap.style.display = isBxgy ? 'grid' : 'none';

    // value wajib hanya untuk percent/fixed; min_qty & free_qty wajib untuk bxgy
    valueInput.required = !isBxgy;
    minQty.required = isBxgy;
    freeQty.required = isBxgy;

    if (!isBxgy) {
        document.getElementById('valueLabel').textContent = t === 'percent' ? 'Nilai Diskon (%)' : 'Nilai Diskon (Rp)';
    }
}
document.addEventListener('DOMContentLoaded', updatePromoFields);
</script>
@endpush
