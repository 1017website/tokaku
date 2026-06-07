@php $isEdit = isset($product) && $product !== null; @endphp

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
    <div style="grid-column:1/-1;">
        <label class="form-label">Nama Produk <span style="color:#f43f5e;">*</span></label>
        <input type="text" name="name" value="{{ old('name', $isEdit?$product->name:'') }}" required class="form-input" placeholder="contoh: Nasi Goreng">
        @error('name')<p style="font-size:12px;color:#f43f5e;margin-top:4px;">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="form-label">SKU / Kode</label>
        <input type="text" name="sku" value="{{ old('sku', $isEdit?$product->sku:'') }}" class="form-input" placeholder="opsional">
    </div>
    <div>
        <label class="form-label">Kategori</label>
        <select name="category_id" class="form-input" style="cursor:pointer;background:#fafafa;">
            <option value="">— Pilih —</option>
            @foreach($categories as $cat)
            <option value="{{ $cat->id }}" {{ old('category_id', $isEdit?$product->category_id:'') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="form-label">Harga Jual <span style="color:#f43f5e;">*</span></label>
        <div style="position:relative;">
            <span style="position:absolute;left:14px;top:50%;transform:translateY(-50%);font-size:13px;color:#94a3b8;">Rp</span>
            <input type="text" inputmode="numeric" name="price" value="{{ old('price', $isEdit?$product->price:'') }}" required class="form-input input-rupiah" style="padding-left:38px;" placeholder="0">
        </div>
        @error('price')<p style="font-size:12px;color:#f43f5e;margin-top:4px;">{{ $message }}</p>@enderror
    </div>
    @if(auth()->user()->isOwner())
    <div>
        <label class="form-label">Harga Modal</label>
        <div style="position:relative;">
            <span style="position:absolute;left:14px;top:50%;transform:translateY(-50%);font-size:13px;color:#94a3b8;">Rp</span>
            <input type="text" inputmode="numeric" name="cost_price" value="{{ old('cost_price', $isEdit?$product->cost_price:0) }}" class="form-input input-rupiah" style="padding-left:38px;" placeholder="0">
        </div>
    </div>
    @elseif($isEdit)
    {{-- Non-owner: jangan tampilkan modal, tapi pertahankan nilai lama agar tidak ter-reset --}}
    <input type="hidden" name="cost_price" value="{{ $product->cost_price }}">
    @endif
    <div>
        <label class="form-label">Stok <span style="color:#f43f5e;">*</span></label>
        <input type="number" name="stock" value="{{ old('stock', $isEdit?$product->stock:0) }}" min="0" required class="form-input" placeholder="0">
    </div>
    <div>
        <label class="form-label">Alert stok minimum</label>
        <input type="number" name="low_stock_alert" value="{{ old('low_stock_alert', $isEdit?$product->low_stock_alert:5) }}" min="0" class="form-input" placeholder="5">
        <p style="font-size:11.5px;color:#94a3b8;margin-top:4px;">Notifikasi jika stok di bawah angka ini</p>
    </div>
    <div style="grid-column:1/-1;">
        <label class="form-label">Foto Produk</label>
        <input type="file" name="photo" accept="image/*" class="form-input" style="padding:8px 14px;cursor:pointer;"
            onchange="if(this.files[0] && this.files[0].size > 512000){ alert('Ukuran gambar maksimal 500KB.'); this.value=''; }">
        <p style="font-size:11.5px;color:#94a3b8;margin-top:4px;">Format gambar (JPG/PNG), maksimal 500KB</p>
    </div>
    @if($isEdit)
    <div style="grid-column:1/-1;display:flex;align-items:center;gap:8px;">
        <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}
            style="width:15px;height:15px;accent-color:#0F6E56;cursor:pointer;">
        <label for="is_active" style="font-size:13.5px;color:#374151;cursor:pointer;">Produk aktif (tampil di kasir)</label>
    </div>
    @endif
</div>
