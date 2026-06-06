@extends('layouts.app')
@section('title','Produk')
@section('page-title','Produk')
@section('page-subtitle','Kelola daftar produk toko Anda')

@section('header-actions')
<a href="{{ route('tenant.products.create') }}" class="btn-primary">
    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
    <span class="hidden sm:inline">Tambah Produk</span>
    <span class="sm:hidden">Tambah</span>
</a>
@endsection

@section('content')
<div style="background:#fff;border-radius:16px;border:1px solid #f1f5f9;box-shadow:0 1px 3px rgba(0,0,0,0.04);overflow:hidden;">

    {{-- Search --}}
    <div style="padding:14px 18px;border-bottom:1px solid #f8fafc;">
        <input type="text" id="searchInput" placeholder="Cari produk..."
            style="width:100%;max-width:320px;border:1.5px solid #e2e8f0;border-radius:10px;padding:9px 14px;font-size:13.5px;font-family:Inter,sans-serif;outline:none;background:#fafafa;box-sizing:border-box;"
            onfocus="this.style.borderColor='#0F6E56';this.style.background='#fff';"
            onblur="this.style.borderColor='#e2e8f0';this.style.background='#fafafa';">
    </div>

    {{-- Filter status --}}
    <div style="padding:12px 18px;border-bottom:1px solid #f8fafc;display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
        @php
            $statusTabs = ['active' => 'Aktif', 'inactive' => 'Nonaktif', 'all' => 'Semua'];
        @endphp
        @foreach($statusTabs as $key => $label)
            @php $on = ($status ?? 'active') === $key; @endphp
            <a href="{{ route('tenant.products.index', ['status' => $key]) }}"
                style="border:1.5px solid {{ $on ? '#0F6E56' : '#e2e8f0' }};background:{{ $on ? '#0F6E56' : '#fff' }};color:{{ $on ? '#fff' : '#374151' }};border-radius:99px;padding:6px 16px;font-size:12.5px;font-weight:{{ $on ? '600' : '500' }};text-decoration:none;transition:all 0.15s;">
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- Filter kategori --}}
    @if($categories->isNotEmpty())
    <div style="padding:12px 18px;border-bottom:1px solid #f8fafc;display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
        <button type="button" class="cat-pill cat-active" data-cat="all"
            style="border:1.5px solid #0F6E56;background:#0F6E56;color:#fff;border-radius:99px;padding:6px 16px;font-size:12.5px;font-weight:600;font-family:Inter,sans-serif;cursor:pointer;transition:all 0.15s;">
            Semua Produk
        </button>
        @foreach($categories as $cat)
        <button type="button" class="cat-pill" data-cat="{{ $cat->id }}"
            style="border:1.5px solid #e2e8f0;background:#fff;color:#374151;border-radius:99px;padding:6px 16px;font-size:12.5px;font-weight:500;font-family:Inter,sans-serif;cursor:pointer;transition:all 0.15s;">
            {{ $cat->name }}
        </button>
        @endforeach
    </div>
    @endif

    {{-- Mobile card view --}}
    <div class="sm:hidden" id="mobileList">
        @forelse($products as $product)
        <div class="product-row" data-name="{{ strtolower($product->name) }}" data-category="{{ $product->category_id ?? '' }}" style="padding:14px 16px;border-bottom:1px solid #f8fafc;">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;">
                <div style="flex:1;min-width:0;">
                    <p style="font-size:14px;font-weight:600;color:#0f172a;">{{ $product->name }}</p>
                    <p style="font-size:12px;color:#94a3b8;margin-top:2px;">{{ $product->category?->name ?? '—' }}{{ $product->sku ? ' · '.$product->sku : '' }}</p>
                    <div style="display:flex;align-items:center;gap:12px;margin-top:6px;">
                        <p style="font-size:13px;font-weight:700;color:#0F6E56;">Rp {{ number_format($product->price,0,',','.') }}</p>
                        @if(auth()->user()->isOwner() && $product->cost_price > 0)
                        <span style="font-size:11.5px;color:#94a3b8;">Modal: Rp {{ number_format($product->cost_price,0,',','.') }}</span>
                        @endif
                        <span style="font-size:12px;font-weight:500;color:{{ $product->stock<=0?'#be123c':($product->isLowStock()?'#b45309':'#64748b') }};">Stok: {{ $product->stock }}</span>
                    </div>
                </div>
                <div style="display:flex;flex-direction:column;align-items:flex-end;gap:8px;">
                    @if($product->is_active)
                        <span style="font-size:11px;font-weight:500;background:#f0fdf4;color:#15803d;padding:3px 8px;border-radius:99px;">Aktif</span>
                    @else
                        <span style="font-size:11px;font-weight:500;background:#f8fafc;color:#64748b;padding:3px 8px;border-radius:99px;">Nonaktif</span>
                    @endif
                    <div style="display:flex;align-items:center;gap:4px;">
                        {{-- Detail --}}
                        <a href="{{ route('tenant.products.show', $product) }}" title="Riwayat"
                            style="width:32px;height:32px;border-radius:8px;border:1.5px solid #e2e8f0;background:#fff;display:flex;align-items:center;justify-content:center;text-decoration:none;transition:all 0.15s;"
                            onmouseover="this.style.borderColor='#0F6E56';this.style.background='#f0fdf6';" onmouseout="this.style.borderColor='#e2e8f0';this.style.background='#fff';">
                            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="#64748b" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        </a>
                        {{-- Edit --}}
                        <a href="{{ route('tenant.products.edit', $product) }}" title="Edit"
                            style="width:32px;height:32px;border-radius:8px;border:1.5px solid #e2e8f0;background:#fff;display:flex;align-items:center;justify-content:center;text-decoration:none;transition:all 0.15s;"
                            onmouseover="this.style.borderColor='#0F6E56';this.style.background='#f0fdf6';" onmouseout="this.style.borderColor='#e2e8f0';this.style.background='#fff';">
                            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="#64748b" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </a>
                        {{-- Nonaktifkan / Aktifkan --}}
                        @if($product->is_active)
                        <form method="POST" action="{{ route('tenant.products.destroy', $product) }}" onsubmit="return confirm('Nonaktifkan produk ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" title="Nonaktifkan"
                                style="width:32px;height:32px;border-radius:8px;border:1.5px solid #e2e8f0;background:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all 0.15s;"
                                onmouseover="this.style.borderColor='#fecdd3';this.style.background='#fff1f2';" onmouseout="this.style.borderColor='#e2e8f0';this.style.background='#fff';">
                                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="#94a3b8" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                            </button>
                        </form>
                        @else
                        <form method="POST" action="{{ route('tenant.products.activate', $product) }}" onsubmit="return confirm('Aktifkan kembali produk ini?')">
                            @csrf @method('PUT')
                            <button type="submit" title="Aktifkan kembali"
                                style="width:32px;height:32px;border-radius:8px;border:1.5px solid #e2e8f0;background:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all 0.15s;"
                                onmouseover="this.style.borderColor='#0F6E56';this.style.background='#f0fdf6';" onmouseout="this.style.borderColor='#e2e8f0';this.style.background='#fff';">
                                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="#94a3b8" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div style="padding:60px 20px;text-align:center;">
            <p style="font-size:14px;color:#94a3b8;">Belum ada produk.</p>
            <a href="{{ route('tenant.products.create') }}" style="font-size:13.5px;color:#0F6E56;font-weight:500;text-decoration:none;display:block;margin-top:8px;">Tambah sekarang</a>
        </div>
        @endforelse
    </div>

    {{-- Desktop table --}}
    <div class="hidden sm:block overflow-x-auto">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:#f8fafc;border-bottom:1px solid #f1f5f9;">
                    <th style="text-align:left;font-size:11px;font-weight:600;color:#64748b;padding:11px 20px;letter-spacing:0.3px;text-transform:uppercase;">Produk</th>
                    <th style="text-align:left;font-size:11px;font-weight:600;color:#64748b;padding:11px 16px;letter-spacing:0.3px;text-transform:uppercase;">Kategori</th>
                    <th style="text-align:right;font-size:11px;font-weight:600;color:#64748b;padding:11px 16px;letter-spacing:0.3px;text-transform:uppercase;">Harga</th>
                    <th style="text-align:center;font-size:11px;font-weight:600;color:#64748b;padding:11px 16px;letter-spacing:0.3px;text-transform:uppercase;">Stok</th>
                    <th style="text-align:center;font-size:11px;font-weight:600;color:#64748b;padding:11px 16px;letter-spacing:0.3px;text-transform:uppercase;">Status</th>
                    <th style="text-align:center;font-size:11px;font-weight:600;color:#64748b;padding:11px 20px;letter-spacing:0.3px;text-transform:uppercase;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr class="product-row" data-name="{{ strtolower($product->name) }}" data-category="{{ $product->category_id ?? '' }}"
                    style="border-bottom:1px solid #f8fafc;transition:background 0.1s;"
                    onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='#fff'">

                    <td style="padding:14px 20px;">
                        <p style="font-size:13.5px;font-weight:600;color:#0f172a;">{{ $product->name }}</p>
                        <p style="font-size:12px;color:#94a3b8;margin-top:2px;">{{ $product->sku ?? '—' }}</p>
                    </td>

                    <td style="padding:14px 16px;font-size:13.5px;color:#374151;">
                        {{ $product->category?->name ?? '—' }}
                    </td>

                    <td style="padding:14px 16px;text-align:right;">
                        <p style="font-size:13.5px;font-weight:700;color:#0f172a;">Rp {{ number_format($product->price,0,',','.') }}</p>
                        @if(auth()->user()->isOwner() && $product->cost_price > 0)
                        <p style="font-size:11.5px;color:#94a3b8;margin-top:1px;">Modal: Rp {{ number_format($product->cost_price,0,',','.') }}</p>
                        @endif
                    </td>

                    <td style="padding:14px 16px;text-align:center;">
                        <span style="font-size:14px;font-weight:700;color:{{ $product->stock<=0?'#be123c':($product->isLowStock()?'#b45309':'#0f172a') }};">
                            {{ $product->stock }}
                        </span>
                        @if($product->isLowStock() && $product->stock > 0)
                        <p style="font-size:10.5px;color:#b45309;margin-top:1px;">Menipis</p>
                        @elseif($product->stock <= 0)
                        <p style="font-size:10.5px;color:#be123c;margin-top:1px;">Habis</p>
                        @endif
                    </td>

                    <td style="padding:14px 16px;text-align:center;">
                        @if($product->is_active)
                            <span style="font-size:12px;font-weight:500;background:#f0fdf4;color:#15803d;padding:4px 12px;border-radius:99px;">Aktif</span>
                        @else
                            <span style="font-size:12px;font-weight:500;background:#f8fafc;color:#64748b;padding:4px 12px;border-radius:99px;">Nonaktif</span>
                        @endif
                    </td>

                    <td style="padding:14px 20px;">
                        <div style="display:flex;align-items:center;justify-content:center;gap:4px;">

                            {{-- Detail / Riwayat --}}
                            <a href="{{ route('tenant.products.show', $product) }}"
                                title="Lihat riwayat"
                                style="width:32px;height:32px;border-radius:8px;border:1.5px solid #e2e8f0;background:#fff;display:inline-flex;align-items:center;justify-content:center;text-decoration:none;transition:all 0.15s;"
                                onmouseover="this.style.borderColor='#0F6E56';this.style.background='#f0fdf6';this.querySelector('svg').style.stroke='#0F6E56';"
                                onmouseout="this.style.borderColor='#e2e8f0';this.style.background='#fff';this.querySelector('svg').style.stroke='#94a3b8';">
                                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="#94a3b8" stroke-width="2" style="transition:stroke 0.15s;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </a>

                            {{-- Edit --}}
                            <a href="{{ route('tenant.products.edit', $product) }}"
                                title="Edit produk"
                                style="width:32px;height:32px;border-radius:8px;border:1.5px solid #e2e8f0;background:#fff;display:inline-flex;align-items:center;justify-content:center;text-decoration:none;transition:all 0.15s;"
                                onmouseover="this.style.borderColor='#0F6E56';this.style.background='#f0fdf6';this.querySelector('svg').style.stroke='#0F6E56';"
                                onmouseout="this.style.borderColor='#e2e8f0';this.style.background='#fff';this.querySelector('svg').style.stroke='#94a3b8';">
                                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="#94a3b8" stroke-width="2" style="transition:stroke 0.15s;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>

                            {{-- Nonaktifkan / Aktifkan --}}
                            @if($product->is_active)
                            <form method="POST" action="{{ route('tenant.products.destroy', $product) }}"
                                onsubmit="return confirm('Nonaktifkan produk {{ addslashes($product->name) }}?')"
                                style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    title="Nonaktifkan produk"
                                    style="width:32px;height:32px;border-radius:8px;border:1.5px solid #e2e8f0;background:#fff;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;transition:all 0.15s;padding:0;"
                                    onmouseover="this.style.borderColor='#fecdd3';this.style.background='#fff1f2';this.querySelector('svg').style.stroke='#be123c';"
                                    onmouseout="this.style.borderColor='#e2e8f0';this.style.background='#fff';this.querySelector('svg').style.stroke='#94a3b8';">
                                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="#94a3b8" stroke-width="2" style="transition:stroke 0.15s;">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                    </svg>
                                </button>
                            </form>
                            @else
                            <form method="POST" action="{{ route('tenant.products.activate', $product) }}"
                                onsubmit="return confirm('Aktifkan kembali produk {{ addslashes($product->name) }}?')"
                                style="display:inline;">
                                @csrf @method('PUT')
                                <button type="submit"
                                    title="Aktifkan kembali"
                                    style="width:32px;height:32px;border-radius:8px;border:1.5px solid #e2e8f0;background:#fff;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;transition:all 0.15s;padding:0;"
                                    onmouseover="this.style.borderColor='#0F6E56';this.style.background='#f0fdf6';this.querySelector('svg').style.stroke='#0F6E56';"
                                    onmouseout="this.style.borderColor='#e2e8f0';this.style.background='#fff';this.querySelector('svg').style.stroke='#94a3b8';">
                                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="#94a3b8" stroke-width="2" style="transition:stroke 0.15s;">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </button>
                            </form>
                            @endif

                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="padding:60px 20px;text-align:center;">
                        <p style="font-size:14px;color:#94a3b8;">Belum ada produk.</p>
                        <a href="{{ route('tenant.products.create') }}" style="font-size:13.5px;color:#0F6E56;font-weight:500;text-decoration:none;display:block;margin-top:8px;">Tambah sekarang</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Tooltip legend --}}
<div style="display:flex;align-items:center;gap:16px;margin-top:12px;padding:0 4px;">
    <div style="display:flex;align-items:center;gap:6px;">
        <div style="width:28px;height:28px;border-radius:7px;border:1.5px solid #e2e8f0;background:#fff;display:flex;align-items:center;justify-content:center;">
            <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="#94a3b8" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        </div>
        <span style="font-size:12px;color:#64748b;">Riwayat</span>
    </div>
    <div style="display:flex;align-items:center;gap:6px;">
        <div style="width:28px;height:28px;border-radius:7px;border:1.5px solid #e2e8f0;background:#fff;display:flex;align-items:center;justify-content:center;">
            <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="#94a3b8" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
        </div>
        <span style="font-size:12px;color:#64748b;">Edit</span>
    </div>
    <div style="display:flex;align-items:center;gap:6px;">
        <div style="width:28px;height:28px;border-radius:7px;border:1.5px solid #e2e8f0;background:#fff;display:flex;align-items:center;justify-content:center;">
            <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="#94a3b8" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
        </div>
        <span style="font-size:12px;color:#64748b;">Nonaktifkan</span>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    var search = document.getElementById('searchInput');
    var pills  = document.querySelectorAll('.cat-pill');
    var activeCat = 'all';

    function applyFilter() {
        var q = (search.value || '').toLowerCase();
        document.querySelectorAll('.product-row').forEach(function (row) {
            var matchName = row.dataset.name.includes(q);
            var matchCat  = activeCat === 'all' || row.dataset.category === activeCat;
            row.style.display = (matchName && matchCat) ? '' : 'none';
        });
    }

    search.addEventListener('input', applyFilter);

    pills.forEach(function (pill) {
        pill.addEventListener('click', function () {
            activeCat = pill.dataset.cat;
            pills.forEach(function (p) {
                p.style.border = '1.5px solid #e2e8f0';
                p.style.background = '#fff';
                p.style.color = '#374151';
                p.style.fontWeight = '500';
            });
            pill.style.border = '1.5px solid #0F6E56';
            pill.style.background = '#0F6E56';
            pill.style.color = '#fff';
            pill.style.fontWeight = '600';
            applyFilter();
        });
    });
})();
</script>
@endpush
