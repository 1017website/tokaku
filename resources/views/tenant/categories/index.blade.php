@extends('layouts.app')
@section('title','Kategori')
@section('page-title','Kategori')
@section('page-subtitle','Kelola kategori produk')

@section('content')
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div>
        <div style="background:#fff;border-radius:16px;border:1px solid #f1f5f9;box-shadow:0 1px 3px rgba(0,0,0,0.04);padding:22px;">
            <p style="font-size:14px;font-weight:600;color:#0f172a;margin-bottom:16px;">Tambah Kategori</p>
            <form method="POST" action="{{ route('tenant.categories.store') }}" style="display:flex;flex-direction:column;gap:12px;">
                @csrf
                @if($errors->any())
                <div style="background:#fff1f2;border:1px solid #fecdd3;color:#be123c;font-size:13px;border-radius:10px;padding:10px 14px;">{{ $errors->first() }}</div>
                @endif
                <div>
                    <label class="form-label">Nama Kategori</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="form-input" placeholder="contoh: Makanan">
                </div>
                <div>
                    <label class="form-label">Tipe Kategori</label>
                    <select name="type" id="addType" class="form-input" style="cursor:pointer;" onchange="toggleAddPeriod()">
                        <option value="regular" {{ old('type','regular')=='regular'?'selected':'' }}>Regular</option>
                        <option value="promo" {{ old('type')=='promo'?'selected':'' }}>Promo</option>
                        <option value="bundling" {{ old('type')=='bundling'?'selected':'' }}>Bundling</option>
                    </select>
                </div>
                <div id="addPeriod" style="display:none;flex-direction:column;gap:12px;">
                    <div>
                        <label class="form-label">Berlaku Dari <span style="color:#94a3b8;font-weight:400;">(opsional)</span></label>
                        <input type="datetime-local" name="starts_at" value="{{ old('starts_at') }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Berlaku Sampai <span style="color:#94a3b8;font-weight:400;">(opsional)</span></label>
                        <input type="datetime-local" name="ends_at" value="{{ old('ends_at') }}" class="form-input">
                    </div>
                    <p style="font-size:11.5px;color:#94a3b8;margin-top:-4px;">Kosongkan untuk tanpa batas waktu. Setelah lewat masa berlaku, kategori tidak muncul di kasir.</p>
                </div>
                <button type="submit" class="btn-primary" style="width:100%;justify-content:center;">Tambah</button>
            </form>
        </div>
    </div>
    <div class="sm:col-span-2">
        <div style="background:#fff;border-radius:16px;border:1px solid #f1f5f9;box-shadow:0 1px 3px rgba(0,0,0,0.04);overflow:hidden;">
            <div style="padding:16px 20px;border-bottom:1px solid #f8fafc;">
                <p style="font-size:14px;font-weight:600;color:#0f172a;margin-bottom:12px;">Daftar Kategori ({{ $categories->count() }})</p>
                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                    @php $statusTabs = ['active' => 'Aktif', 'inactive' => 'Nonaktif', 'all' => 'Semua']; @endphp
                    @foreach($statusTabs as $key => $label)
                        @php $on = ($status ?? 'active') === $key; @endphp
                        <a href="{{ route('tenant.categories.index', ['status' => $key]) }}"
                            style="border:1.5px solid {{ $on ? '#0F6E56' : '#e2e8f0' }};background:{{ $on ? '#0F6E56' : '#fff' }};color:{{ $on ? '#fff' : '#374151' }};border-radius:99px;padding:6px 16px;font-size:12.5px;font-weight:{{ $on ? '600' : '500' }};text-decoration:none;transition:all 0.15s;">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>
            <div>
                @forelse($categories as $cat)
                <div style="padding:14px 20px;border-bottom:1px solid #f8fafc;">
                    {{-- Tampilan baca --}}
                    <div id="cat-view-{{ $cat->id }}" style="display:flex;align-items:center;justify-content:space-between;">
                        <div>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <p style="font-size:14px;font-weight:500;color:#0f172a;">{{ $cat->name }}</p>
                                @if($cat->type === 'promo')
                                <span style="font-size:10.5px;font-weight:600;background:#fef3f2;color:#be123c;padding:2px 8px;border-radius:99px;">Promo</span>
                                @elseif($cat->type === 'bundling')
                                <span style="font-size:10.5px;font-weight:600;background:#eff6ff;color:#1d4ed8;padding:2px 8px;border-radius:99px;">Bundling</span>
                                @endif
                                @if($cat->is_active && $cat->type !== 'regular' && !$cat->isAvailable())
                                <span style="font-size:10.5px;font-weight:600;background:#f8fafc;color:#94a3b8;padding:2px 8px;border-radius:99px;">Kadaluarsa</span>
                                @endif
                                @unless($cat->is_active)
                                <span style="font-size:10.5px;font-weight:600;background:#fef2f2;color:#b91c1c;padding:2px 8px;border-radius:99px;">Nonaktif</span>
                                @endunless
                            </div>
                            <div style="display:flex;align-items:center;gap:10px;margin-top:3px;">
                                <p style="font-size:12px;color:#94a3b8;">{{ $cat->products_count }} produk</p>
                                @if($cat->type !== 'regular' && ($cat->starts_at || $cat->ends_at))
                                <span style="font-size:12px;color:#94a3b8;">{{ $cat->starts_at?->format('d M Y') ?? '∞' }} — {{ $cat->ends_at?->format('d M Y') ?? '∞' }}</span>
                                @endif
                            </div>
                        </div>
                        <div style="display:flex;align-items:center;gap:16px;">
                            @if($cat->is_active)
                            <button type="button" onclick="toggleCatEdit({{ $cat->id }})" style="font-size:13px;color:#0F6E56;font-weight:500;background:none;border:none;cursor:pointer;font-family:Inter,sans-serif;">Edit</button>
                            <form method="POST" action="{{ route('tenant.categories.destroy',$cat) }}" onsubmit="return confirm('Nonaktifkan kategori ini? Produk & riwayat tetap aman, kategori hanya disembunyikan dari kasir.')">
                                @csrf @method('DELETE')
                                <button type="submit" style="font-size:13px;color:#f43f5e;font-weight:500;background:none;border:none;cursor:pointer;font-family:Inter,sans-serif;">Nonaktifkan</button>
                            </form>
                            @else
                            <form method="POST" action="{{ route('tenant.categories.activate',$cat) }}">
                                @csrf @method('PUT')
                                <button type="submit" style="font-size:13px;color:#0F6E56;font-weight:600;background:none;border:none;cursor:pointer;font-family:Inter,sans-serif;">Aktifkan</button>
                            </form>
                            @endif
                        </div>
                    </div>
                    {{-- Form edit (tersembunyi) --}}
                    <form id="cat-edit-{{ $cat->id }}" method="POST" action="{{ route('tenant.categories.update',$cat) }}" style="display:none;flex-direction:column;gap:10px;margin-top:4px;">
                        @csrf @method('PUT')
                        <input type="text" name="name" value="{{ $cat->name }}" required class="form-input" placeholder="Nama kategori">
                        <select name="type" id="edit-type-{{ $cat->id }}" class="form-input" style="cursor:pointer;" onchange="toggleEditPeriod({{ $cat->id }})">
                            <option value="regular" {{ $cat->type=='regular'?'selected':'' }}>Regular</option>
                            <option value="promo" {{ $cat->type=='promo'?'selected':'' }}>Promo</option>
                            <option value="bundling" {{ $cat->type=='bundling'?'selected':'' }}>Bundling</option>
                        </select>
                        <div id="edit-period-{{ $cat->id }}" style="display:{{ $cat->type=='regular'?'none':'flex' }};flex-direction:column;gap:10px;">
                            <input type="datetime-local" name="starts_at" value="{{ $cat->starts_at?->format('Y-m-d\TH:i') }}" class="form-input" placeholder="Berlaku dari">
                            <input type="datetime-local" name="ends_at" value="{{ $cat->ends_at?->format('Y-m-d\TH:i') }}" class="form-input" placeholder="Berlaku sampai">
                        </div>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <button type="submit" class="btn-primary" style="padding:8px 16px;">Simpan</button>
                            <button type="button" onclick="toggleCatEdit({{ $cat->id }})" style="font-size:13px;color:#64748b;font-weight:500;background:none;border:none;cursor:pointer;font-family:Inter,sans-serif;">Batal</button>
                        </div>
                    </form>
                </div>
                @empty
                <div style="padding:50px 20px;text-align:center;font-size:14px;color:#94a3b8;">Belum ada kategori.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<script>
function toggleAddPeriod() {
    var t = document.getElementById('addType').value;
    document.getElementById('addPeriod').style.display = t === 'regular' ? 'none' : 'flex';
}
function toggleEditPeriod(id) {
    var t = document.getElementById('edit-type-' + id).value;
    document.getElementById('edit-period-' + id).style.display = t === 'regular' ? 'none' : 'flex';
}
function toggleCatEdit(id) {
    var view = document.getElementById('cat-view-' + id);
    var edit = document.getElementById('cat-edit-' + id);
    var isEditing = edit.style.display === 'flex';
    view.style.display = isEditing ? 'flex' : 'none';
    edit.style.display = isEditing ? 'none' : 'flex';
    if (!isEditing) edit.querySelector('input[name="name"]').focus();
}
document.addEventListener('DOMContentLoaded', toggleAddPeriod);
</script>
@endsection
