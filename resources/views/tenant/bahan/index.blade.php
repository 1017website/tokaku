@extends('layouts.app')
@section('title','Gudang Bahan Baku')
@section('page-title','Gudang Bahan Baku')
@section('page-subtitle','Pembukuan stok bahan baku toko')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
    <div style="background:#fff;border:1px solid #f1f5f9;border-radius:16px;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,.04);">
        <p style="font-size:12px;color:#64748b;font-weight:700;">Total Bahan Aktif</p>
        <p style="font-size:26px;font-weight:800;color:#0F6E56;margin-top:8px;">{{ $totalItems }}</p>
        <p style="font-size:12px;color:#94a3b8;margin-top:4px;">jenis bahan baku</p>
    </div>
    <div style="background:#fff;border:1px solid #f1f5f9;border-radius:16px;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,.04);">
        <p style="font-size:12px;color:#64748b;font-weight:700;">Stok Menipis</p>
        <p style="font-size:26px;font-weight:800;color:#be123c;margin-top:8px;">{{ $lowStock }}</p>
        <p style="font-size:12px;color:#94a3b8;margin-top:4px;">perlu segera diisi ulang</p>
    </div>
    <div style="background:#f0fdf6;border:1px solid #bbf7d2;border-radius:16px;padding:20px;">
        <p style="font-size:12.5px;color:#085041;font-weight:700;line-height:1.5;">Catatan ini murni pembukuan stok bahan. Tidak memengaruhi produk jual maupun transaksi kasir.</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    {{-- Form tambah bahan --}}
    <div style="background:#fff;border:1px solid #f1f5f9;border-radius:16px;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,.04);height:max-content;">
        <h3 style="font-size:15px;font-weight:800;color:#0f172a;margin-bottom:14px;">Tambah Bahan Baku</h3>
        <form method="POST" action="{{ route('tenant.bahan.store') }}" style="display:flex;flex-direction:column;gap:12px;">
            @csrf
            <div><label class="form-label">Nama Bahan</label><input type="text" name="name" value="{{ old('name') }}" class="form-input" required placeholder="contoh: Pentol Keju"></div>
            <div style="display:flex;gap:10px;">
                <div style="flex:1;"><label class="form-label">Stok Awal</label><input type="number" name="stock" value="{{ old('stock', 0) }}" min="0" class="form-input" required></div>
                <div style="flex:1;"><label class="form-label">Satuan</label><select name="unit" class="form-input" required>@foreach($units as $u)<option value="{{ $u }}" {{ old('unit')===$u?'selected':'' }}>{{ $u }}</option>@endforeach</select></div>
            </div>
            <div><label class="form-label">Peringatan Stok Menipis</label><input type="number" name="low_stock_alert" value="{{ old('low_stock_alert', 0) }}" min="0" class="form-input" placeholder="0 = tidak ada peringatan"></div>
            <div><label class="form-label">Catatan</label><input type="text" name="note" value="{{ old('note') }}" class="form-input" placeholder="opsional"></div>
            <button class="btn-primary" style="justify-content:center;">Simpan</button>
        </form>
    </div>

    {{-- Daftar bahan --}}
    <div class="lg:col-span-2" style="background:#fff;border:1px solid #f1f5f9;border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.04);">
        <form method="GET" style="padding:14px 18px;border-bottom:1px solid #f8fafc;display:flex;gap:8px;flex-wrap:wrap;align-items:end;">
            <div style="flex:1;min-width:160px;"><label class="form-label">Cari</label><input type="text" name="search" value="{{ request('search') }}" class="form-input" placeholder="Nama bahan..."></div>
            <div><label class="form-label">Status</label><select name="status" class="form-input"><option value="">Semua</option><option value="low" {{ request('status')==='low'?'selected':'' }}>Stok Menipis</option></select></div>
            <button class="btn-secondary">Filter</button>
        </form>
        <div class="table-responsive overflow-x-auto">
            <table style="width:100%;border-collapse:collapse;min-width:640px;">
                <thead><tr style="background:#f8fafc;">
                    <th style="text-align:left;padding:11px 18px;font-size:11px;color:#64748b;text-transform:uppercase;">Nama</th>
                    <th style="text-align:right;padding:11px 18px;font-size:11px;color:#64748b;text-transform:uppercase;">Stok</th>
                    <th style="text-align:center;padding:11px 18px;font-size:11px;color:#64748b;text-transform:uppercase;">Aksi</th>
                </tr></thead>
                <tbody>
                @forelse($materials as $m)
                    <tr style="border-bottom:1px solid #f8fafc;">
                        <td data-label="Nama" style="padding:13px 18px;">
                            <p style="font-size:13.5px;font-weight:700;color:#0f172a;">{{ $m->name }}</p>
                            @if($m->note)<p style="font-size:11.5px;color:#94a3b8;margin-top:2px;">{{ $m->note }}</p>@endif
                            @unless($m->is_active)<span style="font-size:10.5px;background:#fef2f2;color:#b91c1c;border-radius:999px;padding:2px 8px;font-weight:700;">nonaktif</span>@endunless
                        </td>
                        <td data-label="Stok" style="padding:13px 18px;text-align:right;">
                            <span style="font-size:15px;font-weight:800;color:{{ $m->isLowStock() ? '#be123c' : '#0F6E56' }};">{{ $m->stock }}</span>
                            <span style="font-size:12px;color:#94a3b8;">{{ $m->unit }}</span>
                            @if($m->isLowStock())<p style="font-size:10.5px;color:#be123c;font-weight:700;margin-top:2px;">menipis</p>@endif
                        </td>
                        <td data-label="Aksi" style="padding:13px 18px;text-align:center;white-space:nowrap;">
                            <button type="button" onclick='openAdjust(@json($m->id), @json($m->name), @json($m->unit), @json($m->stock))' style="border:none;background:#f0fdf6;color:#085041;font-weight:700;font-size:12px;cursor:pointer;border-radius:8px;padding:6px 10px;margin-right:4px;">Catat Stok</button>
                            <button type="button" onclick='openEdit(@json($m->id), @json($m->name), @json($m->unit), @json($m->low_stock_alert), @json($m->note), @json($m->is_active))' style="border:none;background:#f8fafc;color:#334155;font-weight:700;font-size:12px;cursor:pointer;border-radius:8px;padding:6px 10px;margin-right:4px;">Edit</button>
                            <a href="{{ route('tenant.bahan.history', $m) }}" style="font-size:12px;color:#0F6E56;font-weight:700;text-decoration:none;">Riwayat</a>
                        </td>
                    </tr>
                @empty
                    <tr><td data-empty colspan="3" style="padding:45px;text-align:center;color:#94a3b8;font-size:13px;">Belum ada bahan baku.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding:14px 18px;">{{ $materials->links() }}</div>
    </div>
</div>

{{-- Modal: Catat Stok (masuk/keluar/penyesuaian) --}}
<div id="adjustModal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.45);z-index:60;align-items:center;justify-content:center;padding:16px;">
    <div style="background:#fff;border-radius:18px;width:100%;max-width:420px;padding:22px;">
        <h3 style="font-size:16px;font-weight:800;color:#0f172a;margin-bottom:4px;">Catat Stok</h3>
        <p id="adjustInfo" style="font-size:12.5px;color:#64748b;margin-bottom:16px;"></p>
        <form method="POST" id="adjustForm" style="display:flex;flex-direction:column;gap:12px;">
            @csrf
            <div>
                <label class="form-label">Jenis</label>
                <select name="type" id="adjustType" class="form-input" required onchange="toggleQtyLabel()">
                    <option value="out">Stok Keluar (dipakai)</option>
                    <option value="in">Stok Masuk (tambah)</option>
                    <option value="adjustment">Penyesuaian (set jumlah akhir)</option>
                </select>
            </div>
            <div><label class="form-label" id="qtyLabel">Jumlah Keluar</label><input type="number" name="qty" min="1" class="form-input" required placeholder="contoh: 20"></div>
            <div><label class="form-label">Catatan</label><input type="text" name="note" class="form-input" placeholder="opsional, contoh: dipakai produksi"></div>
            <div style="display:flex;gap:10px;margin-top:4px;">
                <button type="button" onclick="closeAdjust()" class="btn-secondary" style="flex:1;justify-content:center;">Batal</button>
                <button type="submit" class="btn-primary" style="flex:1;justify-content:center;">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal: Edit bahan --}}
<div id="editModal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.45);z-index:60;align-items:center;justify-content:center;padding:16px;">
    <div style="background:#fff;border-radius:18px;width:100%;max-width:420px;padding:22px;">
        <h3 style="font-size:16px;font-weight:800;color:#0f172a;margin-bottom:16px;">Edit Bahan Baku</h3>
        <form method="POST" id="editForm" style="display:flex;flex-direction:column;gap:12px;">
            @csrf @method('PUT')
            <div><label class="form-label">Nama Bahan</label><input type="text" name="name" id="editName" class="form-input" required></div>
            <div style="display:flex;gap:10px;">
                <div style="flex:1;"><label class="form-label">Satuan</label><select name="unit" id="editUnit" class="form-input" required>@foreach($units as $u)<option value="{{ $u }}">{{ $u }}</option>@endforeach</select></div>
                <div style="flex:1;"><label class="form-label">Peringatan Menipis</label><input type="number" name="low_stock_alert" id="editLow" min="0" class="form-input"></div>
            </div>
            <div><label class="form-label">Catatan</label><input type="text" name="note" id="editNote" class="form-input"></div>
            <label style="display:flex;align-items:center;gap:8px;font-size:13px;color:#334155;"><input type="checkbox" name="is_active" id="editActive" value="1"> Aktif</label>
            <p style="font-size:11.5px;color:#94a3b8;">Stok tidak diubah di sini. Untuk ubah stok gunakan tombol "Catat Stok".</p>
            <div style="display:flex;gap:10px;margin-top:4px;">
                <button type="button" onclick="closeEdit()" class="btn-secondary" style="flex:1;justify-content:center;">Batal</button>
                <button type="submit" class="btn-primary" style="flex:1;justify-content:center;">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    var adjustBase = "{{ url('bahan') }}";

    function openAdjust(id, name, unit, stock) {
        var f = document.getElementById('adjustForm');
        f.action = adjustBase + '/' + id + '/adjust';
        document.getElementById('adjustInfo').textContent = name + ' — stok saat ini: ' + stock + ' ' + unit;
        document.getElementById('adjustType').value = 'out';
        toggleQtyLabel();
        document.getElementById('adjustModal').style.display = 'flex';
    }
    function closeAdjust() { document.getElementById('adjustModal').style.display = 'none'; }

    function toggleQtyLabel() {
        var t = document.getElementById('adjustType').value;
        var lbl = document.getElementById('qtyLabel');
        lbl.textContent = t === 'in' ? 'Jumlah Masuk'
                        : t === 'out' ? 'Jumlah Keluar'
                        : 'Jumlah Akhir (stok hasil hitung ulang)';
    }

    function openEdit(id, name, unit, low, note, active) {
        var f = document.getElementById('editForm');
        f.action = adjustBase + '/' + id;
        document.getElementById('editName').value = name;
        document.getElementById('editUnit').value = unit;
        document.getElementById('editLow').value = low;
        document.getElementById('editNote').value = note || '';
        document.getElementById('editActive').checked = !!active;
        document.getElementById('editModal').style.display = 'flex';
    }
    function closeEdit() { document.getElementById('editModal').style.display = 'none'; }

    // Tutup modal saat klik area gelap
    document.addEventListener('click', function (e) {
        if (e.target.id === 'adjustModal') closeAdjust();
        if (e.target.id === 'editModal') closeEdit();
    });
</script>
@endpush
