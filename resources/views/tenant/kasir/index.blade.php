@extends('layouts.app')
@section('title','Kasir')
@section('page-title','Kasir')
@section('page-subtitle','Buat transaksi penjualan')

@section('content')

<div class="flex flex-col lg:flex-row gap-4" style="min-height:calc(100vh - 160px);">

    {{-- PRODUK --}}
    <div class="w-full lg:flex-1">
        <div style="background:#fff;border-radius:16px;border:1px solid #f1f5f9;box-shadow:0 1px 3px rgba(0,0,0,0.04);display:flex;flex-direction:column;height:100%;">
            <div style="padding:14px 16px;border-bottom:1px solid #f8fafc;">
                <input type="text" id="searchProduct" placeholder="Cari nama produk..."
                    style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:9px 14px;font-size:13.5px;font-family:Inter,sans-serif;outline:none;background:#fafafa;box-sizing:border-box;"
                    onfocus="this.style.borderColor='#0F6E56';this.style.background='#fff';"
                    onblur="this.style.borderColor='#e2e8f0';this.style.background='#fafafa';">
            </div>
            <div id="productGrid" style="padding:14px;display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:10px;overflow-y:auto;flex:1;max-height:calc(100vh - 260px);">
                @forelse($products as $product)
                <button
                    data-id="{{ $product->id }}"
                    data-name="{{ addslashes($product->name) }}"
                    data-price="{{ $product->price }}"
                    data-stock="{{ $product->stock }}"
                    data-search="{{ strtolower($product->name) }}"
                    class="product-card"
                    {{ $product->stock <= 0 ? 'disabled' : '' }}
                    style="text-align:left;background:#fafafa;border:1.5px solid #f1f5f9;border-radius:12px;padding:12px;cursor:pointer;transition:all 0.15s;{{ $product->stock<=0?'opacity:0.4;cursor:not-allowed;':'' }}">
                    <div style="width:32px;height:32px;background:#fff;border-radius:8px;display:flex;align-items:center;justify-content:center;margin-bottom:10px;border:1px solid #f1f5f9;">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="#9ca3af" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <p style="font-size:12.5px;font-weight:600;color:#0f172a;line-height:1.3;margin-bottom:6px;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;">{{ $product->name }}</p>
                    <p style="font-size:12px;font-weight:700;color:#0F6E56;">Rp {{ number_format($product->price,0,',','.') }}</p>
                    <p style="font-size:11px;color:#9ca3af;margin-top:2px;">Stok: {{ $product->stock }}</p>
                </button>
                @empty
                <div style="grid-column:1/-1;padding:40px;text-align:center;">
                    <p style="font-size:14px;color:#94a3b8;">Belum ada produk aktif.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- KERANJANG --}}
    <div class="w-full lg:w-80 xl:w-96 flex-shrink-0">
        <div style="background:#fff;border-radius:16px;border:1px solid #f1f5f9;box-shadow:0 1px 3px rgba(0,0,0,0.04);display:flex;flex-direction:column;max-height:calc(100vh - 140px);">

            <div style="padding:14px 18px;border-bottom:1px solid #f8fafc;display:flex;align-items:center;justify-content:space-between;">
                <div style="display:flex;align-items:center;gap:8px;">
                    <p style="font-size:14px;font-weight:600;color:#0f172a;">Keranjang</p>
                    <span id="cartBadge" style="display:none;background:#0F6E56;color:#fff;font-size:11px;font-weight:600;padding:2px 7px;border-radius:99px;"></span>
                </div>
                <button id="btnClear" onclick="clearCart()" style="display:none;font-size:12px;color:#f43f5e;background:none;border:none;cursor:pointer;font-family:Inter,sans-serif;font-weight:500;padding:0;">Kosongkan</button>
            </div>

            <div id="cartItems" style="flex:1;overflow-y:auto;padding:0 16px;min-height:80px;">
                {{-- diisi JS --}}
            </div>

            <div style="padding:14px 16px;border-top:1px solid #f8fafc;">
                <div style="display:flex;justify-content:space-between;margin-bottom:7px;">
                    <span style="font-size:13px;color:#64748b;">Subtotal</span>
                    <span id="subtotalDisplay" style="font-size:13px;font-weight:500;color:#0f172a;">Rp 0</span>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                    <span style="font-size:13px;color:#64748b;">Diskon</span>
                    <div style="display:flex;align-items:center;gap:4px;">
                        <span style="font-size:12px;color:#9ca3af;">Rp</span>
                        <input type="number" id="discountInput" value="0" min="0"
                            oninput="recalculate()"
                            style="width:80px;text-align:right;border:1.5px solid #e2e8f0;border-radius:8px;padding:5px 8px;font-size:13px;font-family:Inter,sans-serif;outline:none;"
                            onfocus="this.style.borderColor='#0F6E56'" onblur="this.style.borderColor='#e2e8f0'">
                    </div>
                </div>
                <div style="display:flex;justify-content:space-between;padding:10px 0;border-top:1.5px solid #f1f5f9;border-bottom:1.5px solid #f1f5f9;margin-bottom:12px;">
                    <span style="font-size:15px;font-weight:700;color:#0f172a;">Total</span>
                    <span id="totalDisplay" style="font-size:16px;font-weight:700;color:#0F6E56;">Rp 0</span>
                </div>
                <div style="margin-bottom:10px;">
                    <label style="display:block;font-size:12px;font-weight:500;color:#64748b;margin-bottom:5px;">Jumlah Bayar</label>
                    <input type="number" id="paidInput" placeholder="0" min="0"
                        oninput="recalculate()"
                        style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:10px 14px;font-size:14px;font-family:Inter,sans-serif;outline:none;background:#fafafa;box-sizing:border-box;"
                        onfocus="this.style.borderColor='#0F6E56';this.style.background='#fff';"
                        onblur="this.style.borderColor='#e2e8f0';this.style.background='#fafafa';">
                </div>
                <div style="background:#f0fdf4;border-radius:10px;padding:10px 14px;display:flex;justify-content:space-between;margin-bottom:12px;">
                    <span style="font-size:13px;font-weight:600;color:#15803d;">Kembalian</span>
                    <span id="changeDisplay" style="font-size:14px;font-weight:700;color:#15803d;">Rp 0</span>
                </div>
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:6px;margin-bottom:12px;">
                    @foreach(['cash'=>'Tunai','qris'=>'QRIS','transfer'=>'Transfer'] as $v=>$l)
                    <button id="pay-{{ $v }}" onclick="setPayment('{{ $v }}')"
                        style="font-size:12.5px;font-weight:500;padding:8px;border-radius:8px;border:1.5px solid;cursor:pointer;font-family:Inter,sans-serif;transition:all 0.15s;{{ $loop->first?'background:#0F6E56;color:#fff;border-color:#0F6E56;':'background:#fff;color:#374151;border-color:#e2e8f0;' }}">
                        {{ $l }}
                    </button>
                    @endforeach
                </div>
                <button id="btnProses" onclick="processTransaction()"
                    style="width:100%;background:#0F6E56;color:#fff;font-family:Inter,sans-serif;font-size:14px;font-weight:600;padding:13px;border-radius:12px;border:none;cursor:pointer;transition:all 0.15s;"
                    onmouseover="this.style.background='#085041'" onmouseout="this.style.background='#0F6E56'">
                    Proses Transaksi
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal --}}
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
            <a id="btnStruk" href="#" target="_blank"
                style="display:flex;align-items:center;justify-content:center;gap:6px;border:1.5px solid #e2e8f0;color:#374151;font-family:Inter,sans-serif;font-size:13.5px;font-weight:500;padding:12px;border-radius:12px;text-decoration:none;">
                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak Struk
            </a>
            <a id="btnPdf" href="#" target="_blank"
                style="display:flex;align-items:center;justify-content:center;gap:6px;background:#f0fdf6;color:#0F6E56;font-family:Inter,sans-serif;font-size:13.5px;font-weight:500;padding:12px;border-radius:12px;text-decoration:none;border:1.5px solid #bbf7d2;">
                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Download PDF
            </a>
            <button onclick="closeModal()"
                style="background:#0F6E56;color:#fff;font-family:Inter,sans-serif;font-size:13.5px;font-weight:600;padding:12px;border-radius:12px;border:none;cursor:pointer;"
                onmouseover="this.style.background='#085041'" onmouseout="this.style.background='#0F6E56'">
                Transaksi Baru
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
var cart = {};
var paymentMethod = 'cash';

function fmt(n) {
    return 'Rp ' + Math.round(n).toLocaleString('id-ID');
}

// ── CART OPERATIONS ──────────────────────────────────────────
function addToCart(id, name, price, stock) {
    id = String(id);
    price = parseFloat(price);
    stock = parseInt(stock);
    if (cart[id]) {
        if (cart[id].qty >= cart[id].stock) {
            alert('Stok ' + name + ' tidak mencukupi!');
            return;
        }
        cart[id].qty++;
    } else {
        cart[id] = { id: id, name: name, price: price, stock: stock, qty: 1 };
    }
    renderCart();
}

function changeQty(id, delta) {
    id = String(id);
    if (!cart[id]) return;
    cart[id].qty += delta;
    if (cart[id].qty <= 0) {
        delete cart[id];
    } else if (cart[id].qty > cart[id].stock) {
        cart[id].qty = cart[id].stock;
    }
    renderCart();
}

function removeItem(id) {
    id = String(id);
    delete cart[id];
    renderCart();
}

function clearCart() {
    cart = {};
    renderCart();
}

// ── RENDER ───────────────────────────────────────────────────
function renderCart() {
    var container = document.getElementById('cartItems');
    var badge     = document.getElementById('cartBadge');
    var clearBtn  = document.getElementById('btnClear');
    var keys      = Object.keys(cart);

    if (keys.length === 0) {
        container.innerHTML =
            '<div style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:40px 0;text-align:center;">' +
            '<div style="width:44px;height:44px;background:#f8fafc;border-radius:14px;display:flex;align-items:center;justify-content:center;margin-bottom:10px;">' +
            '<svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="#d1d5db" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>' +
            '</div>' +
            '<p style="font-size:13.5px;color:#94a3b8;font-weight:500;">Keranjang kosong</p>' +
            '<p style="font-size:12px;color:#cbd5e1;margin-top:4px;">Klik produk untuk menambahkan</p>' +
            '</div>';
        badge.style.display    = 'none';
        clearBtn.style.display = 'none';
        recalculate();
        return;
    }

    badge.textContent       = keys.length;
    badge.style.display     = 'inline';
    clearBtn.style.display  = 'block';

    var html = '';
    keys.forEach(function(id) {
        var item = cart[id];
        html +=
            '<div style="display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid #f8fafc;">' +
            '<div style="flex:1;min-width:0;">' +
            '<p style="font-size:13px;font-weight:500;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + item.name + '</p>' +
            '<p style="font-size:12px;color:#0F6E56;font-weight:600;margin-top:2px;">' + fmt(item.price) + '</p>' +
            '</div>' +
            '<div style="display:flex;align-items:center;gap:5px;flex-shrink:0;">' +
            '<button onclick="changeQty(\'' + id + '\',-1)" style="width:26px;height:26px;border-radius:8px;border:1.5px solid #e2e8f0;background:#fff;cursor:pointer;font-size:15px;color:#374151;line-height:1;font-family:Inter,sans-serif;display:flex;align-items:center;justify-content:center;">−</button>' +
            '<span style="font-size:13.5px;font-weight:600;color:#0f172a;min-width:22px;text-align:center;">' + item.qty + '</span>' +
            '<button onclick="changeQty(\'' + id + '\',1)" style="width:26px;height:26px;border-radius:8px;border:1.5px solid #e2e8f0;background:#fff;cursor:pointer;font-size:15px;color:#374151;line-height:1;font-family:Inter,sans-serif;display:flex;align-items:center;justify-content:center;">+</button>' +
            '</div>' +
            '<button onclick="removeItem(\'' + id + '\')" style="background:none;border:none;cursor:pointer;color:#d1d5db;padding:2px;flex-shrink:0;display:flex;" onmouseover="this.style.color=\'#f43f5e\'" onmouseout="this.style.color=\'#d1d5db\'">' +
            '<svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>' +
            '</button>' +
            '</div>';
    });

    container.innerHTML = html;
    recalculate();
}

// ── RECALCULATE ──────────────────────────────────────────────
function recalculate() {
    var sub  = 0;
    Object.values(cart).forEach(function(i) { sub += i.price * i.qty; });
    var disc = parseFloat(document.getElementById('discountInput').value) || 0;
    var tot  = Math.max(0, sub - disc);
    var paid = parseFloat(document.getElementById('paidInput').value) || 0;

    document.getElementById('subtotalDisplay').textContent = fmt(sub);
    document.getElementById('totalDisplay').textContent    = fmt(tot);
    document.getElementById('changeDisplay').textContent   = fmt(Math.max(0, paid - tot));
}

// ── PAYMENT METHOD ───────────────────────────────────────────
function setPayment(method) {
    paymentMethod = method;
    ['cash','qris','transfer'].forEach(function(v) {
        var btn = document.getElementById('pay-' + v);
        if (v === method) {
            btn.style.background  = '#0F6E56';
            btn.style.color       = '#fff';
            btn.style.borderColor = '#0F6E56';
        } else {
            btn.style.background  = '#fff';
            btn.style.color       = '#374151';
            btn.style.borderColor = '#e2e8f0';
        }
    });
}

// ── PROCESS ──────────────────────────────────────────────────
async function processTransaction() {
    if (Object.keys(cart).length === 0) { alert('Keranjang masih kosong!'); return; }

    var subtotalText = document.getElementById('totalDisplay').textContent;
    var total = parseFloat(subtotalText.replace(/[^0-9]/g, ''));
    var paid  = parseFloat(document.getElementById('paidInput').value) || 0;
    var disc  = parseFloat(document.getElementById('discountInput').value) || 0;

    if (paymentMethod === 'cash' && paid < total) { alert('Jumlah bayar kurang!'); return; }

    var btn = document.getElementById('btnProses');
    btn.disabled    = true;
    btn.textContent = 'Memproses...';
    btn.style.opacity = '0.7';

    var items = Object.values(cart).map(function(i) {
        return { id: parseInt(i.id), qty: i.qty };
    });

    try {
        var res = await fetch('{{ route("tenant.kasir.proses") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                items:          items,
                paid_amount:    paid,
                payment_method: paymentMethod,
                discount:       disc
            })
        });

        var data = await res.json();

        if (data.success) {
            document.getElementById('modalInvoice').textContent = 'Invoice #' + data.transaction_id;
            document.getElementById('btnStruk').href = '/kasir/' + data.transaction_id + '/struk';
            document.getElementById('btnPdf').href   = '/kasir/' + data.transaction_id + '/struk-pdf';
            document.getElementById('modalStruk').style.display = 'flex';
            clearCart();
            document.getElementById('paidInput').value    = '';
            document.getElementById('discountInput').value = '0';
            recalculate();
        } else {
            alert(data.message || 'Terjadi kesalahan.');
        }
    } catch (e) {
        alert('Gagal terhubung ke server.');
        console.error(e);
    } finally {
        btn.disabled    = false;
        btn.textContent = 'Proses Transaksi';
        btn.style.opacity = '1';
    }
}

// ── MODAL ────────────────────────────────────────────────────
function closeModal() {
    document.getElementById('modalStruk').style.display = 'none';
}

// ── SEARCH ───────────────────────────────────────────────────
document.getElementById('searchProduct').addEventListener('input', function() {
    var q = this.value.toLowerCase().trim();
    document.querySelectorAll('.product-card').forEach(function(card) {
        card.style.display = card.dataset.search.includes(q) ? '' : 'none';
    });
});

// ── PRODUCT CARD CLICK ───────────────────────────────────────
document.getElementById('productGrid').addEventListener('click', function(e) {
    var card = e.target.closest('.product-card');
    if (card && !card.disabled) {
        addToCart(card.dataset.id, card.dataset.name, card.dataset.price, card.dataset.stock);
    }
});

// ── INIT ─────────────────────────────────────────────────────
renderCart();
</script>
@endpush
