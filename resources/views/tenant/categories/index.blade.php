@extends('layouts.app')
@section('title','Kategori')
@section('page-title','Kategori')
@section('page-subtitle','Kelola kategori produk')

@php
    $hariList = [1=>'Senin',2=>'Selasa',3=>'Rabu',4=>'Kamis',5=>'Jumat',6=>'Sabtu',7=>'Minggu'];
@endphp

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
                    <input type="text" name="name" value="{{ old('name') }}" required class="form-input" placeholder="contoh: Menu Senin">
                </div>
                <div>
                    <label class="form-label">Urutan Tampil <span style="color:#94a3b8;font-weight:400;">(kecil = duluan)</span></label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" max="9999" class="form-input" placeholder="0">
                </div>
                <div>
                    <label class="form-label">Tipe Kategori</label>
                    <select name="type" id="addType" class="form-input" style="cursor:pointer;" onchange="toggleAddPeriod()">
                        <option value="regular" {{ old('type','regular')=='regular'?'selected':'' }}>Regular</option>
                        <option value="promo" {{ old('type')=='promo'?'selected':'' }}>Promo</option>
                        <option value="bundling" {{ old('type')=='bundling'?'selected':'' }}>Bundling</option>
                    </select>
                </div>
                <label style="display:flex;align-items:flex-start;gap:9px;background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:11px 12px;cursor:pointer;">
                    <input type="checkbox" name="is_pinned" value="1" id="addPinned" {{ old('is_pinned') ? 'checked' : '' }} onchange="toggleAddSchedule()" style="margin-top:2px;">
                    <span style="font-size:12.5px;color:#92400e;line-height:1.4;"><b>Menu Tetap</b><br><span style="color:#b45309;font-weight:400;">Produk kategori ini ikut tampil di tab kategori lain (mis. Nasi Putih). Mengabaikan jadwal hari.</span></span>
                </label>
                <div id="addPinnedTargets" style="display:none;background:#fff;border:1px solid #fde68a;border-radius:10px;padding:11px 12px;">
                    <label class="form-label" style="font-size:11.5px;">Tampil di Kategori <span style="color:#94a3b8;font-weight:400;">(kosong = semua kategori)</span></label>
                    <div style="display:flex;flex-wrap:wrap;gap:6px;">
                        @forelse($targetOptions as $opt)
                        <label style="font-size:12px;display:inline-flex;align-items:center;gap:5px;border:1.5px solid #e2e8f0;border-radius:8px;padding:6px 10px;cursor:pointer;">
                            <input type="checkbox" name="pinned_targets[]" value="{{ $opt->id }}" {{ collect(old('pinned_targets',[]))->contains($opt->id) ? 'checked' : '' }}> {{ $opt->name }}
                        </label>
                        @empty
                        <span style="font-size:12px;color:#94a3b8;">Belum ada kategori lain.</span>
                        @endforelse
                    </div>
                </div>
                <div id="addSchedule">
                    <label class="form-label">Jadwal Hari Tampil <span style="color:#94a3b8;font-weight:400;">(kosongkan = setiap hari)</span></label>
                    <div style="display:flex;flex-wrap:wrap;gap:6px;">
                        @foreach($hariList as $num => $nama)
                        <label style="font-size:12px;display:inline-flex;align-items:center;gap:5px;border:1.5px solid #e2e8f0;border-radius:8px;padding:6px 10px;cursor:pointer;">
                            <input type="checkbox" name="schedule_days[]" value="{{ $num }}" {{ collect(old('schedule_days',[]))->contains($num) ? 'checked' : '' }}> {{ $nama }}
                        </label>
                        @endforeach
                    </div>
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
                            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                                <span style="font-size:11px;font-weight:700;color:#64748b;background:#f1f5f9;border-radius:6px;padding:2px 7px;">#{{ $cat->sort_order }}</span>
                                <p style="font-size:14px;font-weight:500;color:#0f172a;">{{ $cat->name }}</p>
                                @if($cat->is_pinned)
                                <span style="font-size:10.5px;font-weight:700;background:#fffbeb;color:#b45309;border:1px solid #fde68a;padding:2px 8px;border-radius:99px;">Menu Tetap</span>
                                @endif
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
                            <div style="display:flex;align-items:center;gap:10px;margin-top:3px;flex-wrap:wrap;">
                                <p style="font-size:12px;color:#94a3b8;">{{ $cat->products_count }} produk</p>
                                @if(!$cat->is_pinned && !empty($cat->schedule_days))
                                <span style="font-size:12px;color:#0F6E56;font-weight:600;">{{ collect($cat->schedule_days)->map(fn($d)=>$hariList[$d] ?? $d)->join(', ') }}</span>
                                @elseif(!$cat->is_pinned)
                                <span style="font-size:12px;color:#94a3b8;">Setiap hari</span>
                                @endif
                                @if($cat->is_pinned)
                                <span style="font-size:12px;color:#b45309;font-weight:600;">Tampil di: {{ empty($cat->pinned_targets) ? 'Semua kategori' : $targetOptions->whereIn('id', $cat->pinned_targets)->pluck('name')->join(', ') }}</span>
                                @endif
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
                    <form id="cat-edit-{{ $cat->id }}" method="POST" action="{{ route('tenant.categories.update',$cat) }}" style="display:none;flex-direction:column;gap:10px;margin-top:8px;">
                        @csrf @method('PUT')
                        <input type="text" name="name" value="{{ $cat->name }}" required class="form-input" placeholder="Nama kategori">
                        <div style="display:flex;gap:10px;">
                            <div style="flex:1;">
                                <label class="form-label" style="font-size:11px;">Urutan</label>
                                <input type="number" name="sort_order" value="{{ $cat->sort_order }}" min="0" max="9999" class="form-input">
                            </div>
                            <div style="flex:1;">
                                <label class="form-label" style="font-size:11px;">Tipe</label>
                                <select name="type" id="edit-type-{{ $cat->id }}" class="form-input" style="cursor:pointer;" onchange="toggleEditPeriod({{ $cat->id }})">
                                    <option value="regular" {{ $cat->type=='regular'?'selected':'' }}>Regular</option>
                                    <option value="promo" {{ $cat->type=='promo'?'selected':'' }}>Promo</option>
                                    <option value="bundling" {{ $cat->type=='bundling'?'selected':'' }}>Bundling</option>
                                </select>
                            </div>
                        </div>
                        <label style="display:flex;align-items:flex-start;gap:9px;background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:10px 12px;cursor:pointer;">
                            <input type="checkbox" name="is_pinned" value="1" id="edit-pinned-{{ $cat->id }}" {{ $cat->is_pinned ? 'checked' : '' }} onchange="toggleEditSchedule({{ $cat->id }})" style="margin-top:2px;">
                            <span style="font-size:12.5px;color:#92400e;line-height:1.4;"><b>Menu Tetap</b> <span style="color:#b45309;font-weight:400;">— tampil di kategori lain, abaikan jadwal hari.</span></span>
                        </label>
                        <div id="edit-targets-{{ $cat->id }}" style="display:{{ $cat->is_pinned ? 'block' : 'none' }};background:#fff;border:1px solid #fde68a;border-radius:10px;padding:10px 12px;">
                            <label class="form-label" style="font-size:11px;">Tampil di Kategori <span style="color:#94a3b8;font-weight:400;">(kosong = semua)</span></label>
                            <div style="display:flex;flex-wrap:wrap;gap:6px;">
                                @forelse($targetOptions->where('id','!=',$cat->id) as $opt)
                                <label style="font-size:12px;display:inline-flex;align-items:center;gap:5px;border:1.5px solid #e2e8f0;border-radius:8px;padding:6px 10px;cursor:pointer;">
                                    <input type="checkbox" name="pinned_targets[]" value="{{ $opt->id }}" {{ collect($cat->pinned_targets ?? [])->contains($opt->id) ? 'checked' : '' }}> {{ $opt->name }}
                                </label>
                                @empty
                                <span style="font-size:12px;color:#94a3b8;">Belum ada kategori lain.</span>
                                @endforelse
                            </div>
                        </div>
                        <div id="edit-schedule-{{ $cat->id }}" style="display:{{ $cat->is_pinned ? 'none' : 'none' }};">
                            <label class="form-label" style="font-size:11px;">Jadwal Hari <span style="color:#94a3b8;font-weight:400;">(kosong = setiap hari)</span></label>
                            <div style="display:flex;flex-wrap:wrap;gap:6px;">
                                @foreach($hariList as $num => $nama)
                                <label style="font-size:12px;display:inline-flex;align-items:center;gap:5px;border:1.5px solid #e2e8f0;border-radius:8px;padding:6px 10px;cursor:pointer;">
                                    <input type="checkbox" name="schedule_days[]" value="{{ $num }}" {{ collect($cat->schedule_days ?? [])->contains($num) ? 'checked' : '' }}> {{ $nama }}
                                </label>
                                @endforeach
                            </div>
                        </div>
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
function toggleAddSchedule() {
    var pinned = document.getElementById('addPinned').checked;
    document.getElementById('addSchedule').style.display = pinned ? 'none' : 'none';
    document.getElementById('addPinnedTargets').style.display = pinned ? 'block' : 'none';
}
function toggleEditPeriod(id) {
    var t = document.getElementById('edit-type-' + id).value;
    document.getElementById('edit-period-' + id).style.display = t === 'regular' ? 'none' : 'flex';
}
function toggleEditSchedule(id) {
    var pinned = document.getElementById('edit-pinned-' + id).checked;
    document.getElementById('edit-schedule-' + id).style.display = pinned ? 'none' : 'none';
    var t = document.getElementById('edit-targets-' + id);
    if (t) t.style.display = pinned ? 'block' : 'none';
}
function toggleCatEdit(id) {
    var view = document.getElementById('cat-view-' + id);
    var edit = document.getElementById('cat-edit-' + id);
    var isEditing = edit.style.display === 'flex';
    view.style.display = isEditing ? 'flex' : 'none';
    edit.style.display = isEditing ? 'none' : 'flex';
    if (!isEditing) edit.querySelector('input[name="name"]').focus();
}
document.addEventListener('DOMContentLoaded', function(){ toggleAddPeriod(); toggleAddSchedule(); });
</script>
@endsection
