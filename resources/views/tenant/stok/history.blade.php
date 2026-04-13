@extends('layouts.app')
@section('title', 'Riwayat Stok — ' . $product->name)
@section('page-title', 'Riwayat Stok')
@section('page-subtitle', $product->name)

@section('header-actions')
<a href="{{ route('tenant.stok.index') }}" class="btn-secondary">&larr; Kembali</a>
@endsection

@section('content')

{{-- Info produk --}}
<div style="background:#fff;border-radius:16px;border:1px solid #f1f5f9;padding:18px 20px;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
    <div>
        <p style="font-size:16px;font-weight:700;color:#0f172a;">{{ $product->name }}</p>
        <p style="font-size:13px;color:#64748b;margin-top:2px;">{{ $product->category?->name ?? '—' }} · SKU: {{ $product->sku ?? '—' }}</p>
    </div>
    <div style="display:flex;align-items:center;gap:20px;">
        <div style="text-align:center;">
            <p style="font-size:11px;color:#64748b;margin-bottom:3px;">Stok Saat Ini</p>
            <p style="font-size:24px;font-weight:700;color:{{ $product->stock<=0?'#be123c':($product->isLowStock()?'#b45309':'#0F6E56') }};">{{ $product->stock }}</p>
        </div>
        <button onclick="openModal({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->stock }})"
            style="display:inline-flex;align-items:center;gap:6px;background:#0F6E56;color:#fff;font-family:Inter,sans-serif;font-size:13.5px;font-weight:500;padding:10px 18px;border-radius:10px;border:none;cursor:pointer;"
            onmouseover="this.style.background='#085041'" onmouseout="this.style.background='#0F6E56'">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Ubah Stok
        </button>
    </div>
</div>

{{-- Tabel riwayat --}}
<div style="background:#fff;border-radius:16px;border:1px solid #f1f5f9;box-shadow:0 1px 3px rgba(0,0,0,0.04);overflow:hidden;">
    <div style="padding:14px 18px;border-bottom:1px solid #f8fafc;">
        <p style="font-size:14px;font-weight:600;color:#0f172a;">Riwayat Perubahan Stok</p>
    </div>
    <div class="overflow-x-auto">
        <table style="width:100%;border-collapse:collapse;min-width:500px;">
            <thead>
                <tr style="background:#f8fafc;border-bottom:1px solid #f1f5f9;">
                    <th style="text-align:left;font-size:11px;font-weight:600;color:#64748b;padding:10px 18px;text-transform:uppercase;letter-spacing:0.3px;">Waktu</th>
                    <th style="text-align:left;font-size:11px;font-weight:600;color:#64748b;padding:10px 14px;text-transform:uppercase;letter-spacing:0.3px;">Jenis</th>
                    <th style="text-align:center;font-size:11px;font-weight:600;color:#64748b;padding:10px 14px;text-transform:uppercase;letter-spacing:0.3px;">Sebelum</th>
                    <th style="text-align:center;font-size:11px;font-weight:600;color:#64748b;padding:10px 14px;text-transform:uppercase;letter-spacing:0.3px;">Perubahan</th>
                    <th style="text-align:center;font-size:11px;font-weight:600;color:#64748b;padding:10px 14px;text-transform:uppercase;letter-spacing:0.3px;">Sesudah</th>
                    <th style="text-align:left;font-size:11px;font-weight:600;color:#64748b;padding:10px 14px;text-transform:uppercase;letter-spacing:0.3px;">Oleh</th>
                    <th style="text-align:left;font-size:11px;font-weight:600;color:#64748b;padding:10px 18px;text-transform:uppercase;letter-spacing:0.3px;">Catatan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr style="border-bottom:1px solid #f8fafc;transition:background 0.1s;" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='#fff'">
                    <td style="padding:12px 18px;">
                        <p style="font-size:13px;font-weight:500;color:#0f172a;">{{ $log->created_at->format('d M Y') }}</p>
                        <p style="font-size:11.5px;color:#94a3b8;">{{ $log->created_at->format('H:i') }}</p>
                    </td>
                    <td style="padding:12px 14px;">
                        @php
                        $typeStyle = match($log->type) {
                            'restock'    => 'background:#f0fdf4;color:#15803d;',
                            'adjustment' => 'background:#eff6ff;color:#1d4ed8;',
                            'sale'       => 'background:#fff1f2;color:#be123c;',
                            'correction' => 'background:#fffbeb;color:#b45309;',
                            default      => 'background:#f8fafc;color:#64748b;',
                        };
                        @endphp
                        <span style="font-size:12px;font-weight:500;padding:3px 10px;border-radius:99px;{{ $typeStyle }}">{{ $log->type_label }}</span>
                    </td>
                    <td style="padding:12px 14px;text-align:center;font-size:14px;font-weight:600;color:#64748b;">{{ $log->qty_before }}</td>
                    <td style="padding:12px 14px;text-align:center;">
                        <span style="font-size:15px;font-weight:700;color:{{ $log->qty_change>0?'#15803d':'#be123c' }};">
                            {{ $log->qty_change > 0 ? '+' . $log->qty_change : $log->qty_change }}
                        </span>
                    </td>
                    <td style="padding:12px 14px;text-align:center;font-size:14px;font-weight:700;color:#0f172a;">{{ $log->qty_after }}</td>
                    <td style="padding:12px 14px;font-size:13px;color:#374151;">{{ $log->user->name }}</td>
                    <td style="padding:12px 18px;font-size:13px;color:#64748b;">{{ $log->note ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="padding:50px;text-align:center;font-size:14px;color:#94a3b8;">Belum ada riwayat perubahan stok.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
    <div style="padding:14px 18px;border-top:1px solid #f8fafc;">{{ $logs->links() }}</div>
    @endif
</div>

{{-- Modal ubah stok (sama dengan index) --}}
<div id="stockModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);align-items:center;justify-content:center;z-index:50;padding:16px;">
    <div style="background:#fff;border-radius:20px;width:100%;max-width:420px;padding:28px;">
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
            <div>
                <label style="display:block;font-size:12.5px;font-weight:500;color:#374151;margin-bottom:6px;">Jenis</label>
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:6px;">
                    @foreach(['restock'=>['Restock','#15803d','#f0fdf4'],'adjustment'=>['Penyesuaian','#1d4ed8','#eff6ff'],'correction'=>['Koreksi','#b45309','#fffbeb']] as $val=>[$label,$clr,$bg])
                    <label style="cursor:pointer;">
                        <input type="radio" name="type" value="{{ $val }}" {{ $val==='restock'?'checked':'' }} style="display:none;" onchange="updateTypeStyle()">
                        <div id="type-{{ $val }}" style="border:1.5px solid {{ $val==='restock'?$clr:'#e2e8f0' }};background:{{ $val==='restock'?$bg:'#fff' }};border-radius:10px;padding:10px;text-align:center;">
                            <p style="font-size:12.5px;font-weight:600;color:{{ $val==='restock'?$clr:'#374151' }};">{{ $label }}</p>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>
            <div>
                <label style="display:block;font-size:12.5px;font-weight:500;color:#374151;margin-bottom:6px;">Jumlah <span style="color:#94a3b8;font-weight:400;">(positif = tambah)</span></label>
                <div style="display:flex;align-items:center;gap:10px;">
                    <button type="button" onclick="adjustQty(-1)" style="width:36px;height:36px;border-radius:10px;border:1.5px solid #e2e8f0;background:#fff;font-size:18px;cursor:pointer;display:flex;align-items:center;justify-content:center;font-family:Inter,sans-serif;">−</button>
                    <input type="number" id="qtyInput" name="qty_change" value="1" required
                        style="flex:1;border:1.5px solid #e2e8f0;border-radius:10px;padding:9px 14px;font-size:16px;font-weight:600;text-align:center;font-family:Inter,sans-serif;outline:none;"
                        onfocus="this.style.borderColor='#0F6E56'" onblur="this.style.borderColor='#e2e8f0'" oninput="updatePreview()">
                    <button type="button" onclick="adjustQty(1)" style="width:36px;height:36px;border-radius:10px;border:1.5px solid #e2e8f0;background:#fff;font-size:18px;cursor:pointer;display:flex;align-items:center;justify-content:center;font-family:Inter,sans-serif;">+</button>
                </div>
                <div style="margin-top:8px;background:#f8fafc;border-radius:10px;padding:10px 14px;display:flex;justify-content:space-between;">
                    <span style="font-size:13px;color:#64748b;">Stok setelah</span>
                    <span id="previewValue" style="font-size:15px;font-weight:700;color:#0F6E56;"></span>
                </div>
            </div>
            <div>
                <label style="display:block;font-size:12.5px;font-weight:500;color:#374151;margin-bottom:6px;">Catatan</label>
                <input type="text" name="note" placeholder="opsional"
                    style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:10px 14px;font-size:13.5px;font-family:Inter,sans-serif;outline:none;box-sizing:border-box;"
                    onfocus="this.style.borderColor='#0F6E56'" onblur="this.style.borderColor='#e2e8f0'">
            </div>
            <div style="display:flex;gap:10px;">
                <button type="button" onclick="closeModal()" style="flex:1;background:#fff;color:#374151;font-family:Inter,sans-serif;font-size:13.5px;font-weight:500;padding:12px;border-radius:12px;border:1.5px solid #e2e8f0;cursor:pointer;">Batal</button>
                <button type="submit" style="flex:1;background:#0F6E56;color:#fff;font-family:Inter,sans-serif;font-size:13.5px;font-weight:600;padding:12px;border-radius:12px;border:none;cursor:pointer;">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
var currentStock = 0;
var typeColors = { restock:{clr:'#15803d',bg:'#f0fdf4'}, adjustment:{clr:'#1d4ed8',bg:'#eff6ff'}, correction:{clr:'#b45309',bg:'#fffbeb'} };
function openModal(id, name, stock) {
    currentStock = parseInt(stock);
    document.getElementById('modalProductName').textContent  = name;
    document.getElementById('modalCurrentStock').textContent = stock;
    document.getElementById('qtyInput').value = 1;
    document.getElementById('stockForm').action = '/stok/' + id;
    document.querySelector('input[name="type"][value="restock"]').checked = true;
    updateTypeStyle(); updatePreview();
    document.getElementById('stockModal').style.display = 'flex';
}
function closeModal() { document.getElementById('stockModal').style.display = 'none'; }
function adjustQty(d) { var i = document.getElementById('qtyInput'); i.value = parseInt(i.value||0)+d; updatePreview(); }
function updatePreview() {
    var qty = parseInt(document.getElementById('qtyInput').value)||0;
    var after = currentStock + qty;
    var el = document.getElementById('previewValue');
    el.textContent = after;
    el.style.color = after < 0 ? '#be123c' : '#0F6E56';
}
function updateTypeStyle() {
    var sel = document.querySelector('input[name="type"]:checked')?.value;
    Object.keys(typeColors).forEach(function(v) {
        var div = document.getElementById('type-'+v);
        if (!div) return;
        if (v===sel) { div.style.borderColor=typeColors[v].clr; div.style.background=typeColors[v].bg; div.querySelector('p').style.color=typeColors[v].clr; }
        else { div.style.borderColor='#e2e8f0'; div.style.background='#fff'; div.querySelector('p').style.color='#374151'; }
    });
}
document.querySelectorAll('input[name="type"]').forEach(function(r){ r.addEventListener('change', updateTypeStyle); });
document.getElementById('qtyInput').addEventListener('input', updatePreview);
</script>
@endpush
