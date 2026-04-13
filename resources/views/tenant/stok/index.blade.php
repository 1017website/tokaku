@extends('layouts.app')
@section('title','Manajemen Stok')
@section('page-title','Manajemen Stok')
@section('page-subtitle','Tambah, kurangi, dan pantau stok produk')

@section('header-actions')
<a href="{{ route('tenant.stok.history.all') }}" class="btn-secondary">
    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    Riwayat Semua Stok
</a>
@endsection

@section('content')

<div style="background:#fff;border-radius:16px;border:1px solid #f1f5f9;box-shadow:0 1px 3px rgba(0,0,0,0.04);overflow:hidden;">
    <div style="padding:14px 18px;border-bottom:1px solid #f8fafc;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
        <p style="font-size:13.5px;color:#64748b;">Diurutkan dari stok terkecil. Klik <strong style="color:#0f172a;">Kelola</strong> untuk ubah stok.</p>
        <div style="display:flex;align-items:center;gap:8px;">
            <div style="width:10px;height:10px;background:#f0fdf4;border:1.5px solid #86efad;border-radius:3px;"></div><span style="font-size:12px;color:#64748b;">Aman</span>
            <div style="width:10px;height:10px;background:#fffbeb;border:1.5px solid #fcd34d;border-radius:3px;margin-left:6px;"></div><span style="font-size:12px;color:#64748b;">Menipis</span>
            <div style="width:10px;height:10px;background:#fff1f2;border:1.5px solid #fca5a5;border-radius:3px;margin-left:6px;"></div><span style="font-size:12px;color:#64748b;">Habis</span>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table style="width:100%;border-collapse:collapse;min-width:500px;">
            <thead>
                <tr style="background:#f8fafc;border-bottom:1px solid #f1f5f9;">
                    <th style="text-align:left;font-size:11px;font-weight:600;color:#64748b;padding:10px 20px;text-transform:uppercase;letter-spacing:0.3px;">Produk</th>
                    <th style="text-align:left;font-size:11px;font-weight:600;color:#64748b;padding:10px 14px;text-transform:uppercase;letter-spacing:0.3px;" class="hidden sm:table-cell">Kategori</th>
                    <th style="text-align:center;font-size:11px;font-weight:600;color:#64748b;padding:10px 14px;text-transform:uppercase;letter-spacing:0.3px;">Stok</th>
                    <th style="text-align:center;font-size:11px;font-weight:600;color:#64748b;padding:10px 14px;text-transform:uppercase;letter-spacing:0.3px;">Min. Alert</th>
                    <th style="text-align:center;font-size:11px;font-weight:600;color:#64748b;padding:10px 20px;text-transform:uppercase;letter-spacing:0.3px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                @php
                    $rowBg = $product->stock <= 0
                        ? 'background:#fff8f8;'
                        : ($product->isLowStock() ? 'background:#fffdf0;' : '');
                @endphp
                <tr style="border-bottom:1px solid #f8fafc;{{ $rowBg }}transition:opacity 0.1s;">

                    <td style="padding:13px 20px;">
                        <p style="font-size:13.5px;font-weight:600;color:#0f172a;">{{ $product->name }}</p>
                        <p style="font-size:12px;color:#94a3b8;margin-top:1px;">{{ $product->sku ?? '—' }}</p>
                    </td>

                    <td style="padding:13px 14px;font-size:13px;color:#374151;" class="hidden sm:table-cell">
                        {{ $product->category?->name ?? '—' }}
                    </td>

                    <td style="padding:13px 14px;text-align:center;">
                        <span style="font-size:18px;font-weight:700;color:{{ $product->stock<=0?'#be123c':($product->isLowStock()?'#b45309':'#15803d') }};">
                            {{ $product->stock }}
                        </span>
                    </td>

                    <td style="padding:13px 14px;text-align:center;">
                        <span style="font-size:13px;color:#94a3b8;">{{ $product->low_stock_alert }}</span>
                    </td>

                    <td style="padding:13px 20px;text-align:center;">
                        <div style="display:flex;align-items:center;justify-content:center;gap:6px;">
                            <button
                                onclick="openModal({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->stock }})"
                                style="display:inline-flex;align-items:center;gap:5px;background:#0F6E56;color:#fff;font-family:Inter,sans-serif;font-size:12.5px;font-weight:500;padding:7px 14px;border-radius:8px;border:none;cursor:pointer;transition:all 0.15s;"
                                onmouseover="this.style.background='#085041'" onmouseout="this.style.background='#0F6E56'">
                                <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                Kelola
                            </button>
                            <a href="{{ route('tenant.stok.history', $product) }}"
                                title="Riwayat stok"
                                style="width:32px;height:32px;border-radius:8px;border:1.5px solid #e2e8f0;background:#fff;display:inline-flex;align-items:center;justify-content:center;text-decoration:none;transition:all 0.15s;"
                                onmouseover="this.style.borderColor='#0F6E56';this.style.background='#f0fdf6';" onmouseout="this.style.borderColor='#e2e8f0';this.style.background='#fff';">
                                <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="#94a3b8" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="padding:60px 20px;text-align:center;font-size:14px;color:#94a3b8;">Belum ada produk.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($products->hasPages())
    <div style="padding:14px 20px;border-top:1px solid #f8fafc;">{{ $products->links() }}</div>
    @endif
</div>

{{-- Modal Ubah Stok --}}
<div id="stockModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);align-items:center;justify-content:center;z-index:50;padding:16px;">
    <div style="background:#fff;border-radius:20px;width:100%;max-width:420px;padding:28px;box-shadow:0 20px 60px rgba(0,0,0,0.15);">

        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
            <div>
                <h3 style="font-size:16px;font-weight:700;color:#0f172a;">Kelola Stok</h3>
                <p id="modalProductName" style="font-size:13px;color:#64748b;margin-top:2px;"></p>
            </div>
            <div style="text-align:center;">
                <p style="font-size:11px;color:#64748b;margin-bottom:2px;">Stok saat ini</p>
                <p id="modalCurrentStock" style="font-size:22px;font-weight:700;color:#0F6E56;"></p>
            </div>
        </div>

        <form id="stockForm" method="POST" style="display:flex;flex-direction:column;gap:14px;">
            @csrf @method('PUT')

            {{-- Tipe --}}
            <div>
                <label style="display:block;font-size:12.5px;font-weight:500;color:#374151;margin-bottom:6px;">Jenis Perubahan</label>
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:6px;">
                    @foreach(['restock'=>['Restock','tambah dari supplier','#15803d','#f0fdf4'],'adjustment'=>['Penyesuaian','koreksi stok fisik','#1d4ed8','#eff6ff'],'correction'=>['Koreksi','kesalahan input','#b45309','#fffbeb']] as $val=>[$label,$desc,$clr,$bg])
                    <label style="cursor:pointer;">
                        <input type="radio" name="type" value="{{ $val }}" {{ $val==='restock'?'checked':'' }} style="display:none;" onchange="updateTypeStyle()">
                        <div id="type-{{ $val }}" style="border:1.5px solid {{ $val==='restock'?$clr:'#e2e8f0' }};background:{{ $val==='restock'?$bg:'#fff' }};border-radius:10px;padding:10px;text-align:center;transition:all 0.15s;">
                            <p style="font-size:12.5px;font-weight:600;color:{{ $val==='restock'?$clr:'#374151' }};">{{ $label }}</p>
                            <p style="font-size:10.5px;color:{{ $val==='restock'?$clr:'#94a3b8' }};margin-top:2px;">{{ $desc }}</p>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- Jumlah --}}
            <div>
                <label style="display:block;font-size:12.5px;font-weight:500;color:#374151;margin-bottom:6px;">
                    Jumlah <span style="font-size:11.5px;color:#94a3b8;">(positif = tambah, negatif = kurang)</span>
                </label>
                <div style="display:flex;align-items:center;gap:10px;">
                    <button type="button" onclick="adjustQty(-1)"
                        style="width:36px;height:36px;border-radius:10px;border:1.5px solid #e2e8f0;background:#fff;font-size:18px;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-family:Inter,sans-serif;">−</button>
                    <input type="number" id="qtyInput" name="qty_change" value="1" required
                        style="flex:1;border:1.5px solid #e2e8f0;border-radius:10px;padding:9px 14px;font-size:16px;font-weight:600;text-align:center;font-family:Inter,sans-serif;outline:none;"
                        onfocus="this.style.borderColor='#0F6E56'" onblur="this.style.borderColor='#e2e8f0'"
                        oninput="updatePreview()">
                    <button type="button" onclick="adjustQty(1)"
                        style="width:36px;height:36px;border-radius:10px;border:1.5px solid #e2e8f0;background:#fff;font-size:18px;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-family:Inter,sans-serif;">+</button>
                </div>
                {{-- Preview --}}
                <div id="stockPreview" style="margin-top:8px;background:#f8fafc;border-radius:10px;padding:10px 14px;display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:13px;color:#64748b;">Stok setelah perubahan</span>
                    <span id="previewValue" style="font-size:15px;font-weight:700;color:#0F6E56;"></span>
                </div>
            </div>

            {{-- Catatan --}}
            <div>
                <label style="display:block;font-size:12.5px;font-weight:500;color:#374151;margin-bottom:6px;">Catatan <span style="color:#94a3b8;font-weight:400;">(opsional)</span></label>
                <input type="text" name="note" placeholder="contoh: Belanja dari Toko Maju 50 pcs"
                    style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:10px 14px;font-size:13.5px;font-family:Inter,sans-serif;outline:none;box-sizing:border-box;"
                    onfocus="this.style.borderColor='#0F6E56'" onblur="this.style.borderColor='#e2e8f0'">
            </div>

            <div style="display:flex;gap:10px;margin-top:4px;">
                <button type="button" onclick="closeModal()"
                    style="flex:1;background:#fff;color:#374151;font-family:Inter,sans-serif;font-size:13.5px;font-weight:500;padding:12px;border-radius:12px;border:1.5px solid #e2e8f0;cursor:pointer;">
                    Batal
                </button>
                <button type="submit"
                    style="flex:1;background:#0F6E56;color:#fff;font-family:Inter,sans-serif;font-size:13.5px;font-weight:600;padding:12px;border-radius:12px;border:none;cursor:pointer;"
                    onmouseover="this.style.background='#085041'" onmouseout="this.style.background='#0F6E56'">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
var currentStock = 0;
var typeColors = {
    restock:    { clr:'#15803d', bg:'#f0fdf4' },
    adjustment: { clr:'#1d4ed8', bg:'#eff6ff' },
    correction: { clr:'#b45309', bg:'#fffbeb' },
};

function openModal(id, name, stock) {
    currentStock = parseInt(stock);
    document.getElementById('modalProductName').textContent  = name;
    document.getElementById('modalCurrentStock').textContent = stock;
    document.getElementById('qtyInput').value = 1;
    document.getElementById('stockForm').action = '/stok/' + id;
    document.querySelector('input[name="type"][value="restock"]').checked = true;
    updateTypeStyle();
    updatePreview();
    document.getElementById('stockModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('stockModal').style.display = 'none';
}

function adjustQty(delta) {
    var input = document.getElementById('qtyInput');
    input.value = parseInt(input.value || 0) + delta;
    updatePreview();
}

function updatePreview() {
    var qty    = parseInt(document.getElementById('qtyInput').value) || 0;
    var after  = currentStock + qty;
    var el     = document.getElementById('previewValue');
    el.textContent = after;
    el.style.color = after < 0 ? '#be123c' : (after === 0 ? '#b45309' : '#0F6E56');
}

function updateTypeStyle() {
    var selected = document.querySelector('input[name="type"]:checked')?.value;
    Object.keys(typeColors).forEach(function(v) {
        var div = document.getElementById('type-' + v);
        if (!div) return;
        if (v === selected) {
            div.style.borderColor = typeColors[v].clr;
            div.style.background  = typeColors[v].bg;
            div.querySelector('p').style.color = typeColors[v].clr;
            div.querySelectorAll('p')[1].style.color = typeColors[v].clr;
        } else {
            div.style.borderColor = '#e2e8f0';
            div.style.background  = '#fff';
            div.querySelector('p').style.color = '#374151';
            div.querySelectorAll('p')[1].style.color = '#94a3b8';
        }
    });
}

document.querySelectorAll('input[name="type"]').forEach(function(r) {
    r.addEventListener('change', updateTypeStyle);
});
document.getElementById('qtyInput').addEventListener('input', updatePreview);
</script>
@endpush
