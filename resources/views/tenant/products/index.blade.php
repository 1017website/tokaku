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
    <div style="padding:14px 18px;border-bottom:1px solid #f8fafc;">
        <input type="text" id="searchInput" placeholder="Cari produk..."
            style="width:100%;max-width:320px;border:1.5px solid #e2e8f0;border-radius:10px;padding:9px 14px;font-size:13.5px;font-family:Inter,sans-serif;outline:none;background:#fafafa;box-sizing:border-box;transition:all 0.15s;"
            onfocus="this.style.borderColor='#0F6E56';this.style.background='#fff';"
            onblur="this.style.borderColor='#e2e8f0';this.style.background='#fafafa';">
    </div>

    {{-- Mobile card view --}}
    <div class="sm:hidden divide-y divide-gray-50" id="mobileList">
        @forelse($products as $product)
        <div class="product-row px-4 py-4" data-name="{{ strtolower($product->name) }}">
            <div class="flex items-center justify-between gap-3">
                <div class="flex-1 min-w-0">
                    <p style="font-size:14px;font-weight:600;color:#0f172a;">{{ $product->name }}</p>
                    <p style="font-size:12px;color:#94a3b8;margin-top:2px;">{{ $product->category?->name ?? '—' }} &middot; SKU: {{ $product->sku ?? '—' }}</p>
                    <div class="flex items-center gap-3 mt-2">
                        <p style="font-size:13px;font-weight:700;color:#0F6E56;">Rp {{ number_format($product->price,0,',','.') }}</p>
                        <span style="font-size:12px;color:{{ $product->stock<=0?'#be123c':($product->isLowStock()?'#b45309':'#15803d') }};font-weight:500;">Stok: {{ $product->stock }}</span>
                    </div>
                </div>
                <div class="flex flex-col items-end gap-2 flex-shrink-0">
                    @if($product->is_active)
                        <span style="font-size:11px;font-weight:500;background:#f0fdf4;color:#15803d;padding:3px 8px;border-radius:99px;">Aktif</span>
                    @else
                        <span style="font-size:11px;font-weight:500;background:#f8fafc;color:#64748b;padding:3px 8px;border-radius:99px;">Nonaktif</span>
                    @endif
                    <div class="flex items-center gap-3">
                        <a href="{{ route('tenant.products.edit',$product) }}" style="font-size:12.5px;color:#0F6E56;font-weight:500;text-decoration:none;">Edit</a>
                        <form method="POST" action="{{ route('tenant.products.destroy',$product) }}" onsubmit="return confirm('Nonaktifkan?')">
                            @csrf @method('DELETE')
                            <button type="submit" style="font-size:12.5px;color:#f43f5e;font-weight:500;background:none;border:none;cursor:pointer;font-family:Inter,sans-serif;">Nonaktifkan</button>
                        </form>
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
                    <th style="text-align:left;font-size:11.5px;font-weight:600;color:#64748b;padding:11px 20px;letter-spacing:0.3px;text-transform:uppercase;">Produk</th>
                    <th style="text-align:left;font-size:11.5px;font-weight:600;color:#64748b;padding:11px 20px;letter-spacing:0.3px;text-transform:uppercase;">Kategori</th>
                    <th style="text-align:right;font-size:11.5px;font-weight:600;color:#64748b;padding:11px 20px;letter-spacing:0.3px;text-transform:uppercase;">Harga</th>
                    <th style="text-align:right;font-size:11.5px;font-weight:600;color:#64748b;padding:11px 20px;letter-spacing:0.3px;text-transform:uppercase;">Stok</th>
                    <th style="text-align:center;font-size:11.5px;font-weight:600;color:#64748b;padding:11px 20px;letter-spacing:0.3px;text-transform:uppercase;">Status</th>
                    <th style="text-align:right;font-size:11.5px;font-weight:600;color:#64748b;padding:11px 20px;letter-spacing:0.3px;text-transform:uppercase;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr class="product-row" data-name="{{ strtolower($product->name) }}" style="border-bottom:1px solid #f8fafc;transition:background 0.1s;" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='#fff'">
                    <td style="padding:14px 20px;">
                        <p style="font-size:13.5px;font-weight:600;color:#0f172a;">{{ $product->name }}</p>
                        <p style="font-size:12px;color:#94a3b8;margin-top:2px;">{{ $product->sku ?? '—' }}</p>
                    </td>
                    <td style="padding:14px 20px;font-size:13.5px;color:#374151;">{{ $product->category?->name ?? '—' }}</td>
                    <td style="padding:14px 20px;text-align:right;">
                        <p style="font-size:13.5px;font-weight:700;color:#0f172a;">Rp {{ number_format($product->price,0,',','.') }}</p>
                        @if($product->cost_price > 0)
                        <p style="font-size:11.5px;color:#94a3b8;margin-top:1px;">Modal: Rp {{ number_format($product->cost_price,0,',','.') }}</p>
                        @endif
                    </td>
                    <td style="padding:14px 20px;text-align:right;">
                        <span style="font-size:13.5px;font-weight:700;color:{{ $product->stock<=0?'#be123c':($product->isLowStock()?'#b45309':'#0f172a') }};">{{ $product->stock }}</span>
                    </td>
                    <td style="padding:14px 20px;text-align:center;">
                        @if($product->is_active)
                            <span style="font-size:12px;font-weight:500;background:#f0fdf4;color:#15803d;padding:4px 12px;border-radius:99px;">Aktif</span>
                        @else
                            <span style="font-size:12px;font-weight:500;background:#f8fafc;color:#64748b;padding:4px 12px;border-radius:99px;">Nonaktif</span>
                        @endif
                    </td>
                    <td style="padding:14px 20px;text-align:right;">
                        <div style="display:flex;align-items:center;justify-content:flex-end;gap:12px;">
                            <a href="{{ route('tenant.products.edit',$product) }}" style="font-size:13px;color:#0F6E56;font-weight:500;text-decoration:none;">Edit</a>
                            <form method="POST" action="{{ route('tenant.products.destroy',$product) }}" onsubmit="return confirm('Nonaktifkan produk ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" style="font-size:13px;color:#f43f5e;font-weight:500;background:none;border:none;cursor:pointer;font-family:Inter,sans-serif;padding:0;">Nonaktifkan</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="padding:60px 20px;text-align:center;">
                    <p style="font-size:14px;color:#94a3b8;">Belum ada produk.</p>
                    <a href="{{ route('tenant.products.create') }}" style="font-size:13.5px;color:#0F6E56;font-weight:500;text-decoration:none;display:block;margin-top:8px;">Tambah sekarang</a>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($products->hasPages())
    <div style="padding:14px 20px;border-top:1px solid #f8fafc;">{{ $products->links() }}</div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.getElementById('searchInput').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.product-row').forEach(r => r.style.display = r.dataset.name.includes(q)?'':'none');
});
</script>
@endpush
