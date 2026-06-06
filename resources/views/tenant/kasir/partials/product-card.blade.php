@php
    $badgeClass = $product->stock <= 0 ? 'out' : ($product->isLowStock() ? 'low' : '');
@endphp
<button data-id="{{ $product->id }}" data-name="{{ addslashes($product->name) }}" data-price="{{ $product->price }}" data-stock="{{ $product->stock }}" data-search="{{ strtolower($product->name.' '.$product->sku.' '.($product->category?->name ?? '')) }}" data-category="cat-{{ $product->category_id }}" class="product-card" {{ $product->stock <= 0 ? 'disabled' : '' }}>
    <span class="stock-badge stock-label {{ $badgeClass }}">{{ $product->stock <= 0 ? 'Habis' : 'Stok '.$product->stock }}</span>
    <div class="product-img">
        @if($product->photo_path)
            <img src="{{ Storage::url($product->photo_path) }}" style="width:100%;height:100%;object-fit:cover;" alt="{{ $product->name }}">
        @else
            <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="#94a3b8" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
        @endif
    </div>
    <p class="pc-name" style="font-size:13px;font-weight:800;color:#0f172a;line-height:1.25;min-height:32px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">{{ $product->name }}</p>
    <b class="pc-price" style="font-size:13.5px;color:#0F6E56;white-space:nowrap;display:block;">Rp {{ number_format($product->price,0,',','.') }}</b>
</button>
