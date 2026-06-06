<button data-id="{{ $product->id }}" data-name="{{ addslashes($product->name) }}" data-price="{{ $product->price }}" data-stock="{{ $product->stock }}" data-search="{{ strtolower($product->name.' '.$product->sku.' '.($product->category?->name ?? '')) }}" data-category="cat-{{ $product->category_id }}" class="product-card" {{ $product->stock <= 0 ? 'disabled' : '' }}>
    <div class="product-img">
        @if($product->photo_path)
            <img src="{{ Storage::url($product->photo_path) }}" style="width:100%;height:100%;object-fit:cover;" alt="{{ $product->name }}">
        @else
            <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="#94a3b8" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
        @endif
    </div>
    <p style="font-size:13px;font-weight:800;color:#0f172a;line-height:1.25;min-height:32px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">{{ $product->name }}</p>
    <div style="display:flex;justify-content:space-between;align-items:end;margin-top:8px;gap:6px;flex-wrap:wrap;"><b style="font-size:13px;color:#0F6E56;white-space:nowrap;">Rp {{ number_format($product->price,0,',','.') }}</b><span class="stock-label" style="font-size:11px;color:{{ $product->stock <= 0 ? '#dc2626' : '#94a3b8' }};font-weight:{{ $product->stock <= 0 ? '700' : '400' }};white-space:nowrap;">{{ $product->stock <= 0 ? 'Stok habis' : 'Stok '.$product->stock }}</span></div>
</button>
