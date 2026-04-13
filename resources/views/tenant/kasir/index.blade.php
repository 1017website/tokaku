@extends('layouts.app')
@section('title','Kasir')
@section('page-title','Kasir')
@section('page-subtitle','Buat transaksi penjualan')

@section('content')

@if($activeShift)
<div style="background:#f0fdf6;border:1px solid #bbf7d2;border-radius:12px;padding:10px 16px;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
    <div style="display:flex;align-items:center;gap:8px;">
        <div style="width:8px;height:8px;background:#16a34a;border-radius:50%;"></div>
        <span style="font-size:13px;font-weight:500;color:#15803d;">Shift aktif sejak {{ $activeShift->opened_at->format('H:i') }}</span>
    </div>
    <a href="{{ route('tenant.shift.index') }}" style="font-size:12.5px;color:#15803d;font-weight:500;text-decoration:none;">Tutup Shift</a>
</div>
@else
<div style="background:#fffbeb;border:1px solid #fcd34d;border-radius:12px;padding:10px 16px;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
    <span style="font-size:13px;color:#b45309;font-weight:500;">⚠ Tidak ada shift aktif</span>
    <a href="{{ route('tenant.shift.index') }}" style="font-size:12.5px;color:#b45309;font-weight:500;text-decoration:none;">Buka Shift</a>
</div>
@endif

<div class="flex flex-col lg:flex-row gap-4" style="min-height:calc(100vh - 220px);">

    {{-- PRODUK --}}
    <div class="w-full lg:flex-1">
        <div style="background:#fff;border-radius:16px;border:1px solid #f1f5f9;box-shadow:0 1px 3px rgba(0,0,0,0.04);display:flex;flex-direction:column;height:100%;">
            <div style="padding:14px 16px;border-bottom:1px solid #f8fafc;">
                <input type="text" id="searchProduct" placeholder="Cari nama produk..."
                    style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:9px 14px;font-size:13.5px;font-family:Inter,sans-serif;outline:none;background:#fafafa;box-sizing:border-box;"
                    onfocus="this.style.borderColor='#0F6E56';this.style.background='#fff';"
                    onblur="this.style.borderColor='#e2e8f0';this.style.background='#fafafa';">
            </div>
            <div id="productGrid" style="padding:14px;display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:10px;overflow-y:auto;flex:1;max-height:calc(100vh - 300px);">
                @forelse($products as $product)
                <button data-id="{{ $product->id }}" data-name="{{ addslashes($product->name) }}" data-price="{{ $product->price }}" data-stock="{{ $product->stock }}" data-search="{{ strtolower($product->name) }}"
                    class="product-card" {{ $product->stock <= 0 ? 'disabled' : '' }}
                    style="text-align:left;background:#fafafa;border:1.5px solid #f1f5f9;border-radius:12px;padding:12px;cursor:pointer;transition:all 0.15s;{{ $product->stock<=0?'opacity:0.4;cursor:not-allowed;':'' }}">
                    <div style="width:32px;height:32px;background:#fff;border-radius:8px;display:flex;align-items:center;justify-content:center;margin-bottom:10px;border:1px solid #f1f5f9;">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="#9ca3af" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <p style="font-size:12.5px;font-weight:600;color:#0f172a;line-height:1.3;margin-bottom:6px;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;">{{ $product->name }}</p>
                    <p style="font-size:12px;font-weight:700;color:#0F6E56;">Rp {{ number_format($product->price,0,',','.') }}</p>
                    <p style="font-size:11px;color:#9ca3af;margin-top:2px;">Stok: {{ $product->stock }}</p>
                </button>
                @empty
                <div style="grid-column:1/-1;padding:40px;text-align:center;"><p style="font-size:14px;color:#94a3b8;">Belum ada produk aktif.</p></div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- KERANJANG --}}
    <div class="w-full lg:w-80 xl:w-96 flex-shrink-0">
        <div style="background:#fff;border-radius:16px;border:1px solid #f1f5f9;box-shadow:0 1px 3px rgba(0,0,0,0.04);display:flex;flex-direction:column;max-height:calc(100vh - 180px);">

            <div style="padding:12px 16px;border-bottom:1px solid #f8fafc;display:flex;align-items:center;justify-content:space-between;">
                <div style="display:flex;align-items:center;gap:8px;">
                    <p style="font-size:14px;font-weight:600;color:#0f172a;">Keranjang</p>
                    <span id="cartBadge" style="display:none;background:#0F6E56;color:#fff;font-size:11px;font-weight:600;padding:2px 7px;border-radius:99px;"></span>
                </div>
                <button id="btnClear" onclick="clearCart()" style="display:none;font-size:12px;color:#f43f5e;background:none;border:none;cursor:pointer;font-family:Inter,sans-serif;font-weight:500;">Kosongkan</button>
            </div>

            <div id="cartItems" style="flex:1;overflow-y:auto;padding:0 16px;min-height:60px;"></div>

            <div style="padding:12px 16px;border-top:1px solid #f8fafc;display:flex;flex-direction:column;gap:8px;">

                {{-- Pelanggan --}}
                <div style="position:relative;">
                    <input type="text" id="customerSearch" placeholder="🔍 Cari pelanggan (opsional)..."
                        style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:9px 14px;font-size:13px;font-family:Inter,sans-serif;outline:none;background:#fafafa;box-sizing:border-box;"
                        onfocus="this.style.borderColor='#0F6E56'" onblur="setTimeout(()=>document.getElementById('custDropdown').style.display='none',200)"
                        oninput="searchCustomer(this.value)">
                    <div id="custDropdown" style="display:none;position:absolute;bottom:100%;left:0;right:0;background:#fff;border:1.5px solid #e2e8f0;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,0.1);z-index:20;max-height:160px;overflow-y:auto;margin-bottom:4px;"></div>
                </div>
                <div id="selectedCustomer" style="display:none;background:#f0fdf6;border-radius:10px;padding:8px 12px;display:flex;align-items:center;justify-content:space-between;">
                    <span id="selectedCustomerName" style="font-size:13px;font-weight:500;color:#0F6E56;"></span>
                    <button onclick="clearCustomer()" style="background:none;border:none;cursor:pointer;color:#94a3b8;font-size:14px;padding:0;">×</button>
                </div>

                {{-- Promo --}}
                @if($promos->count() > 0)
                <div style="display:flex;gap:6px;">
                    <input type="text" id="promoCode" placeholder="Kode promo..."
                        style="flex:1;border:1.5px solid #e2e8f0;border-radius:10px;padding:9px 12px;font-size:13px;font-family:Inter,sans-serif;outline:none;background:#fafafa;text-transform:uppercase;"
                        onfocus="this.style.borderColor='#0F6E56'" onblur="this.style.borderColor='#e2e8f0'">
                    <button onclick="applyPromo()" style="padding:9px 14px;background:#f0fdf6;color:#0F6E56;border:1.5px solid #bbf7d2;border-radius:10px;font-family:Inter,sans-serif;font-size:13px;font-weight:500;cursor:pointer;white-space:nowrap;">Terapkan</button>
                </div>
                <div id="promoInfo" style="display:none;background:#f0fdf6;border-radius:10px;padding:8px 12px;display:flex;align-items:center;justify-content:space-between;">
                    <span id="promoText" style="font-size:13px;font-weight:500;color:#15803d;"></span>
                    <button onclick="clearPromo()" style="background:none;border:none;cursor:pointer;color:#94a3b8;font-size:14px;padding:0;">×</button>
                </div>
                @endif

                {{-- Summary --}}
                <div style="display:flex;justify-content:space-between;"><span style="font-size:13px;color:#64748b;">Subtotal</span><span id="subtotalDisplay" style="font-size:13px;font-weight:500;color:#0f172a;">Rp 0</span></div>
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:13px;color:#64748b;">Diskon</span>
                    <div style="display:flex;align-items:center;gap:4px;">
                        <span style="font-size:12px;color:#9ca3af;">Rp</span>
                        <input type="number" id="discountInput" value="0" min="0" oninput="recalculate()"
                            style="width:80px;text-align:right;border:1.5px solid #e2e8f0;border-radius:8px;padding:5px 8px;font-size:13px;font-family:Inter,sans-serif;outline:none;"
                            onfocus="this.style.borderColor='#0F6E56'" onblur="this.style.borderColor='#e2e8f0'">
                    </div>
                </div>

                @if($taxEnabled)
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <div style="display:flex;align-items:center;gap:6px;">
                        <span style="font-size:13px;color:#64748b;">{{ $taxName }} ({{ $taxRate }}%)</span>
                        <label style="display:flex;align-items:center;gap:4px;cursor:pointer;">
                            <input type="checkbox" id="taxToggle" checked onchange="recalculate()"
                                style="width:14px;height:14px;accent-color:#0F6E56;">
                        </label>
                    </div>
                    <span id="taxDisplay" style="font-size:13px;font-weight:500;color:#374151;">Rp 0</span>
                </div>
                @endif

                <div style="display:flex;justify-content:space-between;padding:10px 0;border-top:1.5px solid #f1f5f9;border-bottom:1.5px solid #f1f5f9;">
                    <span style="font-size:15px;font-weight:700;color:#0f172a;">Total</span>
                    <span id="totalDisplay" style="font-size:16px;font-weight:700;color:#0F6E56;">Rp 0</span>
                </div>

                {{-- Metode Bayar --}}
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:5px;">
                    @foreach(['cash'=>'Tunai','qris'=>'QRIS','transfer'=>'Transfer'] as $v=>$l)
                    <button id="pay-{{ $v }}" onclick="setPayment('{{ $v }}')"
                        style="font-size:12px;font-weight:500;padding:8px;border-radius:8px;border:1.5px solid;cursor:pointer;font-family:Inter,sans-serif;transition:all 0.15s;{{ $loop->first?'background:#0F6E56;color:#fff;border-color:#0F6E56;':'background:#fff;color:#374151;border-color:#e2e8f0;' }}">
                        {{ $l }}
                    </button>
                    @endforeach
                </div>

                {{-- Bayar / Hutang toggle --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:5px;">
                    <button id="status-paid" onclick="setPaymentStatus('paid')" style="font-size:12px;font-weight:500;padding:7px;border-radius:8px;border:1.5px solid #0F6E56;cursor:pointer;background:#0F6E56;color:#fff;font-family:Inter,sans-serif;">Bayar Sekarang</button>
                    <button id="status-debt" onclick="setPaymentStatus('debt')" style="font-size:12px;font-weight:500;padding:7px;border-radius:8px;border:1.5px solid #e2e8f0;cursor:pointer;background:#fff;color:#374151;font-family:Inter,sans-serif;">Bayar Nanti (Hutang)</button>
                </div>

                <div id="paidSection">
                    <label style="display:block;font-size:12px;font-weight:500;color:#64748b;margin-bottom:4px;">Jumlah Bayar</label>
                    <input type="number" id="paidInput" placeholder="0" min="0" oninput="recalculate()"
                        style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:10px 14px;font-size:14px;font-family:Inter,sans-serif;outline:none;background:#fafafa;box-sizing:border-box;"
                        onfocus="this.style.borderColor='#0F6E56';this.style.background='#fff';"
                        onblur="this.style.borderColor='#e2e8f0';this.style.background='#fafafa';">
                    <div style="background:#f0fdf4;border-radius:10px;padding:9px 14px;display:flex;justify-content:space-between;margin-top:6px;">
                        <span style="font-size:13px;font-weight:600;color:#15803d;">Kembalian</span>
                        <span id="changeDisplay" style="font-size:14px;font-weight:700;color:#15803d;">Rp 0</span>
                    </div>
                </div>
                <div id="debtSection" style="display:none;">
                    <div style="background:#fff1f2;border-radius:10px;padding:10px 14px;font-size:13px;color:#be123c;font-weight:500;">
                        ⚠ Transaksi ini akan dicatat sebagai hutang pelanggan.
                    </div>
                    <input type="text" id="debtCustomerName" placeholder="Nama pelanggan *" style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:9px 14px;font-size:13px;font-family:Inter,sans-serif;outline:none;box-sizing:border-box;margin-top:6px;" onfocus="this.style.borderColor='#0F6E56'" onblur="this.style.borderColor='#e2e8f0'">
                </div>

                <button id="btnProses" onclick="processTransaction()"
                    style="width:100%;background:#0F6E56;color:#fff;font-family:Inter,sans-serif;font-size:14px;font-weight:600;padding:13px;border-radius:12px;border:none;cursor:pointer;"
                    onmouseover="this.style.background='#085041'" onmouseout="this.style.background='#0F6E56'">
                    Proses Transaksi
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Sukses --}}
<div id="modalStruk" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);align-items:center;justify-content:center;z-index:50;padding:16px;">
    <div style="background:#fff;border-radius:20px;width:100%;max-width:360px;padding:28px;">
        <div style="text-align:center;margin-bottom:20px;">
            <div style="width:52px;height:52px;background:#f0fdf4;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
                <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="#16a34a" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </div>
            <h3 style="font-size:17px;font-weight:700;color:#0f172a;">Transaksi Berhasil!</h3>
            <p id="modalInvoice" style="font-size:13px;color:#64748b;margin-top:6px;"></p>
        </div>
        <div style="display:flex;flex-direction:column;gap:8px;">
            <a id="btnStruk" href="#" target="_blank" style="display:flex;align-items:center;justify-content:center;gap:6px;border:1.5px solid #e2e8f0;color:#374151;font-family:Inter,sans-serif;font-size:13.5px;font-weight:500;padding:12px;border-radius:12px;text-decoration:none;">
                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak Struk
            </a>
            <a id="btnPdf" href="#" target="_blank" style="display:flex;align-items:center;justify-content:center;gap:6px;background:#f0fdf6;color:#0F6E56;font-family:Inter,sans-serif;font-size:13.5px;font-weight:500;padding:12px;border-radius:12px;text-decoration:none;border:1.5px solid #bbf7d2;">
                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Download PDF
            </a>
            <button onclick="closeModal()" style="background:#0F6E56;color:#fff;font-family:Inter,sans-serif;font-size:13.5px;font-weight:600;padding:12px;border-radius:12px;border:none;cursor:pointer;" onmouseover="this.style.background='#085041'" onmouseout="this.style.background='#0F6E56'">
                Transaksi Baru
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var cart = {};
var paymentMethod  = 'cash';
var paymentStatus  = 'paid';
var selectedCustomerId   = null;
var selectedPromoId      = null;
var selectedPromoDiscount = 0;
var TAX_ENABLED = {{ $taxEnabled ? 'true' : 'false' }};
var TAX_RATE    = {{ $taxRate }};

function fmt(n) { return 'Rp ' + Math.round(n).toLocaleString('id-ID'); }

// ── CART ─────────────────────────────────────────────────────
function addToCart(id, name, price, stock) {
    id = String(id); price = parseFloat(price); stock = parseInt(stock);
    if (cart[id]) { if (cart[id].qty >= stock) { alert('Stok tidak cukup!'); return; } cart[id].qty++; }
    else { cart[id] = {id,name,price,stock,qty:1}; }
    renderCart();
}
function changeQty(id, d) {
    id=String(id); if(!cart[id]) return;
    cart[id].qty += d;
    if (cart[id].qty <= 0) delete cart[id];
    else if (cart[id].qty > cart[id].stock) cart[id].qty = cart[id].stock;
    renderCart();
}
function removeItem(id) { delete cart[String(id)]; renderCart(); }
function clearCart()    { cart={}; renderCart(); }

function renderCart() {
    var c     = document.getElementById('cartItems');
    var badge = document.getElementById('cartBadge');
    var clrBtn= document.getElementById('btnClear');
    var keys  = Object.keys(cart);

    if (!keys.length) {
        c.innerHTML = '<div style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:32px 0;text-align:center;"><svg width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="#d1d5db" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg><p style="font-size:13px;color:#94a3b8;margin-top:8px;">Keranjang kosong</p></div>';
        badge.style.display = 'none'; clrBtn.style.display = 'none';
        recalculate(); return;
    }
    badge.textContent = keys.length; badge.style.display='inline'; clrBtn.style.display='block';
    var html = '';
    keys.forEach(function(id) {
        var i = cart[id];
        html += '<div style="display:flex;align-items:center;gap:8px;padding:8px 0;border-bottom:1px solid #f8fafc;">'
            + '<div style="flex:1;min-width:0;"><p style="font-size:13px;font-weight:500;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+i.name+'</p>'
            + '<p style="font-size:12px;color:#0F6E56;font-weight:600;margin-top:1px;">'+fmt(i.price)+'</p></div>'
            + '<div style="display:flex;align-items:center;gap:5px;flex-shrink:0;">'
            + '<button onclick="changeQty(\''+id+'\',-1)" style="width:24px;height:24px;border-radius:7px;border:1.5px solid #e2e8f0;background:#fff;cursor:pointer;font-size:14px;color:#374151;display:flex;align-items:center;justify-content:center;font-family:Inter,sans-serif;">−</button>'
            + '<span style="font-size:13.5px;font-weight:600;color:#0f172a;min-width:20px;text-align:center;">'+i.qty+'</span>'
            + '<button onclick="changeQty(\''+id+'\',1)" style="width:24px;height:24px;border-radius:7px;border:1.5px solid #e2e8f0;background:#fff;cursor:pointer;font-size:14px;color:#374151;display:flex;align-items:center;justify-content:center;font-family:Inter,sans-serif;">+</button>'
            + '</div><button onclick="removeItem(\''+id+'\')" style="background:none;border:none;cursor:pointer;color:#d1d5db;padding:2px;" onmouseover="this.style.color=\'#f43f5e\'" onmouseout="this.style.color=\'#d1d5db\'">'
            + '<svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button></div>';
    });
    c.innerHTML = html;
    recalculate();
}

function recalculate() {
    var sub  = 0; Object.values(cart).forEach(i => sub += i.price * i.qty);
    var disc = parseFloat(document.getElementById('discountInput').value)||0;
    disc = Math.max(disc, selectedPromoDiscount);
    document.getElementById('discountInput').value = disc;
    var afterDisc = Math.max(0, sub - disc);
    var tax = 0;
    if (TAX_ENABLED) {
        var taxToggle = document.getElementById('taxToggle');
        if (taxToggle && taxToggle.checked) {
            tax = Math.round(afterDisc * TAX_RATE / 100);
            document.getElementById('taxDisplay').textContent = fmt(tax);
        } else if (taxToggle) {
            document.getElementById('taxDisplay').textContent = fmt(0);
        }
    }
    var total = afterDisc + tax;
    var paid  = parseFloat(document.getElementById('paidInput').value)||0;
    document.getElementById('subtotalDisplay').textContent = fmt(sub);
    document.getElementById('totalDisplay').textContent    = fmt(total);
    document.getElementById('changeDisplay').textContent   = fmt(Math.max(0, paid - total));
}

// ── PAYMENT ──────────────────────────────────────────────────
function setPayment(m) {
    paymentMethod = m;
    ['cash','qris','transfer'].forEach(v => {
        var b = document.getElementById('pay-'+v);
        b.style.background  = v===m ? '#0F6E56' : '#fff';
        b.style.color       = v===m ? '#fff' : '#374151';
        b.style.borderColor = v===m ? '#0F6E56' : '#e2e8f0';
    });
}
function setPaymentStatus(s) {
    paymentStatus = s;
    document.getElementById('paidSection').style.display = s==='paid' ? 'block' : 'none';
    document.getElementById('debtSection').style.display = s==='debt' ? 'block' : 'none';
    document.getElementById('status-paid').style.background  = s==='paid' ? '#0F6E56' : '#fff';
    document.getElementById('status-paid').style.color       = s==='paid' ? '#fff' : '#374151';
    document.getElementById('status-paid').style.borderColor = s==='paid' ? '#0F6E56' : '#e2e8f0';
    document.getElementById('status-debt').style.background  = s==='debt' ? '#f43f5e' : '#fff';
    document.getElementById('status-debt').style.color       = s==='debt' ? '#fff' : '#374151';
    document.getElementById('status-debt').style.borderColor = s==='debt' ? '#f43f5e' : '#e2e8f0';
}

// ── CUSTOMER SEARCH ──────────────────────────────────────────
async function searchCustomer(q) {
    if (!q || q.length < 2) { document.getElementById('custDropdown').style.display='none'; return; }
    var res  = await fetch('/pelanggan/search?q='+encodeURIComponent(q));
    var data = await res.json();
    var dd   = document.getElementById('custDropdown');
    if (!data.length) { dd.style.display='none'; return; }
    dd.innerHTML = data.map(c => '<div onclick="selectCustomer('+c.id+',\''+c.name+'\')" style="padding:10px 14px;cursor:pointer;font-size:13.5px;border-bottom:1px solid #f8fafc;transition:background 0.1s;" onmouseover="this.style.background=\'#f8fafc\'" onmouseout="this.style.background=\'#fff\'">'+c.name+'<span style="font-size:12px;color:#94a3b8;margin-left:8px;">'+( c.phone||'' )+'</span></div>').join('');
    dd.style.display = 'block';
}
function selectCustomer(id, name) {
    selectedCustomerId = id;
    document.getElementById('customerSearch').value = name;
    document.getElementById('custDropdown').style.display = 'none';
    document.getElementById('selectedCustomerName').textContent = '👤 ' + name;
    document.getElementById('selectedCustomer').style.display = 'flex';
    document.getElementById('debtCustomerName').value = name;
}
function clearCustomer() {
    selectedCustomerId = null;
    document.getElementById('customerSearch').value = '';
    document.getElementById('selectedCustomer').style.display = 'none';
}

// ── PROMO ────────────────────────────────────────────────────
async function applyPromo() {
    var code = document.getElementById('promoCode').value.trim();
    var sub  = Object.values(cart).reduce((s,i)=>s+i.price*i.qty, 0);
    if (!code) { alert('Masukkan kode promo.'); return; }
    var res  = await fetch('/promo/calculate', {
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},
        body: JSON.stringify({code, subtotal:sub})
    });
    var data = await res.json();
    if (data.discount > 0) {
        selectedPromoId       = data.promo_id;
        selectedPromoDiscount = data.discount;
        document.getElementById('promoText').textContent = '🎉 ' + data.message + ' (-' + fmt(data.discount) + ')';
        document.getElementById('promoInfo').style.display = 'flex';
        document.getElementById('discountInput').value = data.discount;
        recalculate();
    } else { alert(data.message); }
}
function clearPromo() {
    selectedPromoId = null; selectedPromoDiscount = 0;
    document.getElementById('promoInfo').style.display = 'none';
    document.getElementById('promoCode').value = '';
    document.getElementById('discountInput').value = 0;
    recalculate();
}

// ── PROCESS ──────────────────────────────────────────────────
async function processTransaction() {
    if (!Object.keys(cart).length) { alert('Keranjang kosong!'); return; }

    var sub   = Object.values(cart).reduce((s,i)=>s+i.price*i.qty, 0);
    var disc  = parseFloat(document.getElementById('discountInput').value)||0;
    var taxToggle = document.getElementById('taxToggle');
    var taxRate   = (TAX_ENABLED && taxToggle && taxToggle.checked) ? TAX_RATE : 0;
    var afterDisc = Math.max(0, sub - disc);
    var tax       = Math.round(afterDisc * taxRate / 100);
    var total     = afterDisc + tax;
    var paid      = parseFloat(document.getElementById('paidInput').value)||0;

    if (paymentStatus==='paid' && paymentMethod==='cash' && paid < total) { alert('Jumlah bayar kurang!'); return; }
    if (paymentStatus==='debt') {
        var dn = document.getElementById('debtCustomerName').value.trim();
        if (!dn && !selectedCustomerId) { alert('Masukkan nama pelanggan untuk hutang.'); return; }
    }

    var btn = document.getElementById('btnProses');
    btn.disabled=true; btn.textContent='Memproses...'; btn.style.opacity='0.7';

    try {
        var res = await fetch('{{ route("tenant.kasir.proses") }}', {
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},
            body: JSON.stringify({
                items:          Object.values(cart).map(i=>({id:parseInt(i.id),qty:i.qty})),
                paid_amount:    paid,
                payment_method: paymentMethod,
                payment_status: paymentStatus,
                discount:       disc,
                promo_id:       selectedPromoId,
                customer_id:    selectedCustomerId,
                customer_name:  document.getElementById('debtCustomerName')?.value || document.getElementById('customerSearch').value,
                tax_rate:       taxRate,
            })
        });
        var data = await res.json();
        if (data.success) {
            document.getElementById('modalInvoice').textContent = 'Invoice #'+data.transaction_id;
            document.getElementById('btnStruk').href = '/kasir/'+data.transaction_id+'/struk';
            document.getElementById('btnPdf').href   = '/kasir/'+data.transaction_id+'/struk-pdf';
            document.getElementById('modalStruk').style.display = 'flex';
            clearCart(); clearCustomer(); clearPromo();
            document.getElementById('paidInput').value = '';
            document.getElementById('discountInput').value = 0;
            recalculate();
        } else { alert(data.message||'Terjadi kesalahan.'); }
    } catch(e) { alert('Gagal terhubung ke server.'); console.error(e); }
    finally { btn.disabled=false; btn.textContent='Proses Transaksi'; btn.style.opacity='1'; }
}

function closeModal() { document.getElementById('modalStruk').style.display='none'; }

// ── SEARCH PRODUK ────────────────────────────────────────────
document.getElementById('searchProduct').addEventListener('input', function() {
    var q = this.value.toLowerCase();
    document.querySelectorAll('.product-card').forEach(c => c.style.display = c.dataset.search.includes(q)?'':'none');
});

// ── PRODUCT CLICK ────────────────────────────────────────────
document.getElementById('productGrid').addEventListener('click', function(e) {
    var c = e.target.closest('.product-card');
    if (c && !c.disabled) addToCart(c.dataset.id, c.dataset.name, c.dataset.price, c.dataset.stock);
});

// ── INIT ─────────────────────────────────────────────────────
renderCart();
</script>
@endpush
