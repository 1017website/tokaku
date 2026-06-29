@php
    $badgeClass = $product->stock <= 0 ? 'out' : ($product->isLowStock() ? 'low' : '');
    $hasPhoto   = (bool) $product->photo_path;

    // Produk dari kategori "Menu Tetap" ikut tampil di tab kategori tertentu.
    // $pinnedProductTargets[id] berisi array category_id target (kosong = semua tab).
    $targets   = $pinnedProductTargets ?? [];
    $isPinned  = array_key_exists($product->id, $targets);
    $pinTargets = $isPinned ? $targets[$product->id] : [];
    // "all" = tampil di semua tab; daftar id = hanya tab tsb. Bukan pinned = "".
    $pinAttr   = $isPinned ? (empty($pinTargets) ? 'all' : implode(',', $pinTargets)) : '';
@endphp
<button data-id="{{ $product->id }}" data-name="{{ addslashes($product->name) }}" data-price="{{ $product->price }}" data-stock="{{ $product->stock }}" data-search="{{ strtolower($product->name.' '.$product->sku.' '.($product->category?->name ?? '')) }}" data-category="cat-{{ $product->category_id }}" data-pinned-cats="{{ $pinAttr }}" class="product-card{{ $hasPhoto ? '' : ' no-photo' }}" {{ $product->stock <= 0 ? 'disabled' : '' }}>
    <span class="stock-badge stock-label {{ $badgeClass }}">{{ $product->stock <= 0 ? 'Habis' : 'Stok '.$product->stock }}</span>
    @if($hasPhoto)
    <div class="product-img">
        <img src="{{ Storage::url($product->photo_path) }}" style="width:100%;height:100%;object-fit:cover;" alt="{{ $product->name }}">
    </div>
    <p class="pc-name" style="font-size:13px;font-weight:800;color:#0f172a;line-height:1.25;min-height:32px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">{{ $product->name }}</p>
    @else
    {{-- Tanpa gambar: tidak ada kotak kosong, nama produk tampil penuh & jadi fokus. --}}
    <p class="pc-name pc-name-only">{{ $product->name }}</p>
    @endif
    <b class="pc-price" style="font-size:13.5px;color:#0F6E56;white-space:nowrap;display:block;">Rp {{ number_format($product->price,0,',','.') }}</b>
</button>
