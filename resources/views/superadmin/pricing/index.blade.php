@extends('superadmin.layout')
@section('title','Kelola Harga')
@section('page-title','Master Harga')
@section('page-subtitle','Kelola paket langganan yang tampil di halaman utama')

@php
    $rp = fn($v) => 'Rp ' . number_format((float)$v, 0, ',', '.');
@endphp

@section('content')
<div style="max-width:920px;">

    @if(session('success'))
        <div style="background:#ecfdf5;border:1px solid #a7f3d0;color:#047857;border-radius:12px;padding:12px 16px;font-size:13px;margin-bottom:20px;">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div style="background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;border-radius:12px;padding:12px 16px;font-size:13px;margin-bottom:20px;">
            {{ $errors->first() }}
        </div>
    @endif

    <div style="display:flex;justify-content:flex-end;margin-bottom:16px;">
        <button class="btn-primary" onclick="openPlanModal()">+ Tambah Paket</button>
    </div>

    {{-- Tabel paket --}}
    <div style="background:#fff;border-radius:16px;border:1px solid #f1f5f9;box-shadow:0 1px 3px rgba(0,0,0,0.04);overflow:hidden;">
        <div class="table-responsive">
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="background:#f8fafc;text-align:left;color:#64748b;">
                    <th style="padding:12px 16px;font-weight:600;">Paket</th>
                    <th style="padding:12px 16px;font-weight:600;">Durasi</th>
                    <th style="padding:12px 16px;font-weight:600;">Harga Asli</th>
                    <th style="padding:12px 16px;font-weight:600;">Harga Diskon</th>
                    <th style="padding:12px 16px;font-weight:600;">Diskon</th>
                    <th style="padding:12px 16px;font-weight:600;">/bulan</th>
                    <th style="padding:12px 16px;font-weight:600;">Status</th>
                    <th style="padding:12px 16px;font-weight:600;text-align:right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($plans as $plan)
                <tr style="border-top:1px solid #f1f5f9;">
                    <td data-label="Paket" style="padding:12px 16px;">
                        <span style="font-weight:600;color:#0f172a;">{{ $plan->name }}</span>
                        @if($plan->is_popular)
                            <span style="display:inline-block;margin-left:6px;font-size:10px;font-weight:700;color:#0F6E56;background:#dcfce9;padding:2px 7px;border-radius:6px;">POPULER</span>
                        @endif
                        <div style="font-size:11.5px;color:#94a3b8;">{{ $plan->tagline }}</div>
                    </td>
                    <td data-label="Durasi" style="padding:12px 16px;color:#475569;">{{ $plan->duration_months }} bln</td>
                    <td data-label="Harga Asli" style="padding:12px 16px;color:#94a3b8;text-decoration:line-through;">{{ $rp($plan->original_price) }}</td>
                    <td data-label="Harga Diskon" style="padding:12px 16px;font-weight:600;color:#0f172a;">{{ $rp($plan->price) }}</td>
                    <td data-label="Diskon" style="padding:12px 16px;color:#e11d48;font-weight:600;">{{ $plan->discountPercent() }}%</td>
                    <td data-label="/bulan" style="padding:12px 16px;color:#475569;">{{ $rp(round($plan->pricePerMonth())) }}</td>
                    <td data-label="Status" style="padding:12px 16px;">
                        <form method="POST" action="{{ route('superadmin.pricing.toggle', $plan) }}" style="display:inline;">
                            @csrf @method('PUT')
                            <button type="submit" style="border:none;cursor:pointer;font-size:11px;font-weight:600;padding:3px 10px;border-radius:20px;{{ $plan->is_active ? 'background:#dcfce9;color:#047857;' : 'background:#f1f5f9;color:#94a3b8;' }}">
                                {{ $plan->is_active ? 'Aktif' : 'Nonaktif' }}
                            </button>
                        </form>
                    </td>
                    <td data-label="Aksi" style="padding:12px 16px;text-align:right;white-space:nowrap;">
                        <button class="btn-secondary" style="padding:5px 12px;font-size:12px;"
                            onclick='editPlan(@json($plan))'>Edit</button>
                        <form method="POST" action="{{ route('superadmin.pricing.destroy', $plan) }}" style="display:inline;" onsubmit="return confirm('Hapus paket {{ $plan->name }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-secondary" style="padding:5px 12px;font-size:12px;color:#e11d48;border-color:#fecaca;">Hapus</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td data-empty colspan="8" style="padding:24px;text-align:center;color:#94a3b8;">Belum ada paket harga.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    <p style="font-size:12px;color:#94a3b8;margin-top:14px;">
        Hanya paket berstatus <b>Aktif</b> yang tampil di halaman utama. Paket bertanda <b>POPULER</b> akan disorot (hanya satu).
    </p>
</div>

{{-- ════════ MODAL ════════ --}}
<div id="planOverlay" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.45);z-index:60;align-items:flex-start;justify-content:center;padding:40px 16px;overflow-y:auto;">
    <div style="background:#fff;border-radius:16px;width:100%;max-width:480px;padding:24px;box-shadow:0 20px 60px rgba(0,0,0,0.2);">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
            <h3 id="planModalTitle" style="font-size:16px;font-weight:700;color:#0f172a;">Tambah Paket</h3>
            <button onclick="closePlanModal()" style="background:none;border:none;cursor:pointer;color:#94a3b8;font-size:20px;line-height:1;">&times;</button>
        </div>

        <form id="planForm" method="POST" action="{{ route('superadmin.pricing.store') }}">
            @csrf
            <input type="hidden" name="_method" id="planMethod" value="POST">

            <div style="display:flex;flex-direction:column;gap:14px;">
                <div>
                    <label style="display:block;font-size:13px;font-weight:500;color:#374151;margin-bottom:6px;">Nama Paket *</label>
                    <input type="text" name="name" id="f_name" class="form-input" required placeholder="6 Bulan">
                </div>
                <div>
                    <label style="display:block;font-size:13px;font-weight:500;color:#374151;margin-bottom:6px;">Tagline</label>
                    <input type="text" name="tagline" id="f_tagline" class="form-input" placeholder="Paling populer & hemat">
                </div>
                <div style="display:flex;gap:12px;">
                    <div style="flex:1;">
                        <label style="display:block;font-size:13px;font-weight:500;color:#374151;margin-bottom:6px;">Durasi (bulan) *</label>
                        <input type="number" name="duration_months" id="f_duration" class="form-input" required min="1" placeholder="6">
                    </div>
                    <div style="flex:1;">
                        <label style="display:block;font-size:13px;font-weight:500;color:#374151;margin-bottom:6px;">Urutan</label>
                        <input type="number" name="sort_order" id="f_sort" class="form-input" min="0" placeholder="2">
                    </div>
                </div>
                <div style="display:flex;gap:12px;">
                    <div style="flex:1;">
                        <label style="display:block;font-size:13px;font-weight:500;color:#374151;margin-bottom:6px;">Harga Asli *</label>
                        <input type="number" name="original_price" id="f_original" class="form-input" required min="0" step="1000" placeholder="1800000">
                    </div>
                    <div style="flex:1;">
                        <label style="display:block;font-size:13px;font-weight:500;color:#374151;margin-bottom:6px;">Harga Diskon *</label>
                        <input type="number" name="price" id="f_price" class="form-input" required min="0" step="1000" placeholder="900000">
                    </div>
                </div>
                <div>
                    <label style="display:block;font-size:13px;font-weight:500;color:#374151;margin-bottom:6px;">Label Tombol (CTA)</label>
                    <input type="text" name="cta_label" id="f_cta" class="form-input" placeholder="Mulai Sekarang">
                </div>
                <div style="display:flex;gap:20px;padding-top:4px;">
                    <label style="display:flex;align-items:center;gap:8px;font-size:13px;color:#374151;cursor:pointer;">
                        <input type="checkbox" name="is_popular" id="f_popular" value="1"> Paket Populer
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;font-size:13px;color:#374151;cursor:pointer;">
                        <input type="checkbox" name="is_active" id="f_active" value="1" checked> Aktif
                    </label>
                </div>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:10px;padding-top:20px;border-top:1px solid #f1f5f9;margin-top:20px;">
                <button type="button" class="btn-secondary" onclick="closePlanModal()">Batal</button>
                <button type="submit" class="btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<script>
    const storeUrl = "{{ route('superadmin.pricing.store') }}";
    const updateBase = "{{ url('superadmin/harga') }}";

    function openPlanModal() {
        document.getElementById('planModalTitle').textContent = 'Tambah Paket';
        document.getElementById('planForm').action = storeUrl;
        document.getElementById('planMethod').value = 'POST';
        document.getElementById('planForm').reset();
        document.getElementById('f_active').checked = true;
        document.getElementById('planOverlay').style.display = 'flex';
    }

    function editPlan(p) {
        document.getElementById('planModalTitle').textContent = 'Edit Paket';
        document.getElementById('planForm').action = updateBase + '/' + p.id;
        document.getElementById('planMethod').value = 'PUT';
        document.getElementById('f_name').value = p.name ?? '';
        document.getElementById('f_tagline').value = p.tagline ?? '';
        document.getElementById('f_duration').value = p.duration_months ?? '';
        document.getElementById('f_sort').value = p.sort_order ?? '';
        document.getElementById('f_original').value = parseFloat(p.original_price) || '';
        document.getElementById('f_price').value = parseFloat(p.price) || '';
        document.getElementById('f_cta').value = p.cta_label ?? '';
        document.getElementById('f_popular').checked = !!p.is_popular;
        document.getElementById('f_active').checked = !!p.is_active;
        document.getElementById('planOverlay').style.display = 'flex';
    }

    function closePlanModal() {
        document.getElementById('planOverlay').style.display = 'none';
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('planOverlay').addEventListener('click', function(e) {
            if (e.target === this) closePlanModal();
        });
    });
</script>
@endpush
@endsection
