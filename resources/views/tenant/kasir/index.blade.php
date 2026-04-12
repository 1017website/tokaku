@extends('layouts.app')
@section('title','Kasir')
@section('page-title','Kasir')
@section('page-subtitle','Buat transaksi penjualan')

@section('content')

<div class="flex flex-col lg:flex-row gap-4" style="min-height:calc(100vh - 160px);">

    {{-- PRODUK --}}
    <div class="w-full lg:flex-1">
        <div style="background:#fff;border-radius:16px;border:1px solid #f1f5f9;box-shadow:0 1px 3px rgba(0,0,0,0.04);height:100%;display:flex;flex-direction:column;">
            <div style="padding:14px 16px;border-bottom:1px solid #f8fafc;">
                <input type="text" id="searchProduct" placeholder="Cari nama produk..."
                    style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:9px 14px;font-size:13.5px;font-family:Inter,sans-serif;outline:none;background:#fafafa;box-sizing:border-box;transition:all 0.15s;"
                    onfocus="this.style.borderColor='#0F6E56';this.style.boxShadow='0 0 0 3px rgba(15,110,86,0.1)';this.style.background='#fff';"
                    onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none';this.style.background='#fafafa';">
            </div>
            <div style="padding:14px;display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:10px;overflow-y:auto;flex:1;max-height:calc(100vh - 280px);" id="productGrid">
                @foreach($products as $product)
                <button onclick="addToCart({{ $product->id }},'{{ addslashes($product->name) }}',{{ $product->price }},{{ $product->stock }})"
                    class="product-card" data-name="{{ strtolower($product->name) }}"
                    {{ $product->stock <= 0 ? 'disabled' : '' }}
                    style="text-align:left;background:#fafafa;border:1.5px solid #f1f5f9;border-radius:12px;padding:12px;cursor:pointer;transition:all 0.15s;{{ $product->stock<=0?'opacity:0.4;cursor:not-allowed;':'' }}position:relative;"
                    onmouseover="{{ $product->stock>0?'this.style.borderColor=\'#0F6E56\';this.style.background=\'#f0fdf6\';':'' }}"
                    onmouseout="{{ $product->stock>0?'this.style.borderColor=\'#f1f5f9\';this.style.background=\'#fafafa\';':'' }}">
                    <div style="width:32px;height:32px;background:#fff;border-radius:8px;display:flex;align-items:center;justify-content:center;margin-bottom:10px;border:1px solid #f1f5f9;">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="#9ca3af" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <p style="font-size:12.5px;font-weight:600;color:#0f172a;line-height:1.3;margin-bottom:6px;" class="line-clamp-2">{{ $product->name }}</p>
                    <p style="font-size:12px;font-weight:700;color:#0F6E56;">Rp {{ number_format($product->price,0,',','.') }}</p>
                    <p style="font-size:11px;color:#9ca3af;margin-top:2px;">Stok: {{ $product->stock }}</p>
                </button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- KERANJANG --}}
    <div class="w-full lg:w-80 xl:w-96 flex-shrink-0">
        <div style="background:#fff;border-radius:16px;border:1px solid #f1f5f9;box-shadow:0 1px 3px rgba(0,0,0,0.04);display:flex;flex-direction:column;height:100%;max-height:calc(100vh - 160px);">

            <div style="padding:14px 18px;border-bottom:1px solid #f8fafc;display:flex;align-items:center;justify-content:space-between;">
                <p style="font-size:14px;font-weight:600;color:#0f172a;">Keranjang</p>
                <button onclick="clearCart()" style="font-size:12px;color:#f43f5e;background:none;border:none;cursor:pointer;font-family:Inter,sans-serif;font-weight:500;padding:0;">Kosongkan</button>
            </div>

            <div style="flex:1;overflow-y:auto;padding:12px 16px;" id="cartItems">
                <div id="emptyCart" style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:40px 0;text-align:center;">
                    <div style="width:44px;height:44px;background:#f8fafc;border-radius:14px;display:flex;align-items:center;justify-content:center;margin-bottom:10px;">
                        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="#d1d5db" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <p style="font-size:13.5px;color:#94a3b8;font-weight:500;">Keranjang kosong</p>
                    <p style="font-size:12px;color:#cbd5e1;margin-top:4px;">Klik produk untuk menambahkan</p>
                </div>
            </div>

            <div style="padding:14px 16px;border-top:1px solid #f8fafc;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                    <span style="font-size:13px;color:#64748b;">Subtotal</span>
                    <span style="font-size:13px;font-weight:500;color:#0f172a;" id="subtotalDisplay">Rp 0</span>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                    <span style="font-size:13px;color:#64748b;">Diskon</span>
                    <div style="display:flex;align-items:center;gap:4px;">
                        <span style="font-size:12px;color:#9ca3af;">Rp</span>
                        <input type="number" id="discountInput" value="0" min="0" onchange="recalculate()"
                            style="width:80px;text-align:right;border:1.5px solid #e2e8f0;border-radius:8px;padding:5px 8px;font-size:13px;font-family:Inter,sans-serif;outline:none;background:#fafafa;"
                            onfocus="this.style.borderColor='#0F6E56'" onblur="this.style.borderColor='#e2e8f0'">
                    </div>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 0;border-top:1.5px solid #f1f5f9;border-bottom:1.5px solid #f1f5f9;margin-bottom:12px;">
                    <span style="font-size:15px;font-weight:700;color:#0f172a;">Total</span>
                    <span style="font-size:16px;font-weight:700;color:#0F6E56;" id="totalDisplay">Rp 0</span>
                </div>

                <div style="margin-bottom:10px;">
                    <label style="display:block;font-size:12px;font-weight:500;color:#64748b;margin-bottom:5px;">Jumlah Bayar</label>
                    <input type="number" id="paidInput" placeholder="0" min="0" onchange="recalculate()"
                        style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:10px 14px;font-size:14px;font-family:Inter,sans-serif;outline:none;background:#fafafa;box-sizing:border-box;transition:all 0.15s;"
                        onfocus="this.style.borderColor='#0F6E56';this.style.boxShadow='0 0 0 3px rgba(15,110,86,0.1)';this.style.background='#fff';"
                        onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none';this.style.background='#fafafa';">
                </div>

                <div style="background:#f0fdf4;border-radius:10px;padding:10px 14px;display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                    <span style="font-size:13px;font-weight:600;color:#15803d;">Kembalian</span>
                    <span style="font-size:14px;font-weight:700;color:#15803d;" id="changeDisplay">Rp 0</span>
                </div>

                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:6px;margin-bottom:12px;">
                    @foreach(['cash'=>'Tunai','qris'=>'QRIS','transfer'=>'Transfer'] as $v=>$l)
                    <button type="button" onclick="setPayment('{{ $v }}')" id="pay-{{ $v }}"
                        style="font-size:12.5px;font-weight:500;padding:8px;border-radius:8px;border:1.5px solid #e2e8f0;cursor:pointer;font-family:Inter,sans-serif;transition:all 0.15s;background:#fff;color:#374151;{{ $loop->first?'background:#0F6E56;color:#fff;border-color:#0F6E56;':'' }}">
                        {{ $l }}
                    </button>
                    @endforeach
                </div>

                <button onclick="processTransaction()" id="btnProses"
                    style="width:100%;background:#0F6E56;color:#fff;font-family:Inter,sans-serif;font-size:14px;font-weight:600;padding:13px;border-radius:12px;border:none;cursor:pointer;transition:all 0.15s;letter-spacing:-0.2px;"
                    onmouseover="this.style.background='#085041'" onmouseout="this.style.background='#0F6E56'">
                    Proses Transaksi
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Sukses --}}
<div id="modalStruk" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);display:none;align-items:center;justify-content:center;z-index:50;padding:16px;">
    <div style="background:#fff;border-radius:20px;width:100%;max-width:360px;padding:28px;box-shadow:0 20px 60px rgba(0,0,0,0.15);">
        <div style="text-align:center;margin-bottom:20px;">
            <div style="width:52px;height:52px;background:#f0fdf4;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
                <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="#16a34a" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </div>
            <h3 style="font-size:17px;font-weight:700;color:#0f172a;letter-spacing:-0.3px;">Transaksi Berhasil!</h3>
            <p style="font-size:13px;color:#64748b;margin-top:6px;" id="modalInvoice"></p>
        </div>
        <div style="display:flex;flex-direction:column;gap:8px;">
            <a id="btnStruk" href="#" target="_blank"
                style="display:flex;align-items:center;justify-content:center;gap:6px;border:1.5px solid #e2e8f0;color:#374151;font-family:Inter,sans-serif;font-size:13.5px;font-weight:500;padding:12px;border-radius:12px;text-decoration:none;transition:all 0.15s;"
                onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#fff'">
                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak Struk
            </a>
            <button onclick="closeModal()"
                style="background:#0F6E56;color:#fff;font-family:Inter,sans-serif;font-size:13.5px;font-weight:600;padding:12px;border-radius:12px;border:none;cursor:pointer;transition:all 0.15s;"
                onmouseover="this.style.background='#085041'" onmouseout="this.style.background='#0F6E56'">
                Transaksi Baru
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let cart = {}, paymentMethod = 'cash';
const fmt = n => 'Rp ' + Math.round(n).toLocaleString('id-ID');

function addToCart(id, name, price, stock) {
    if (cart[id]) { if (cart[id].qty >= stock) { alert('Stok tidak mencukupi!'); return; } cart[id].qty++; }
    else { cart[id] = {id, name, price, stock, qty: 1}; }
    renderCart();
}
function removeFromCart(id) { delete cart[id]; renderCart(); }
function changeQty(id, d) {
    cart[id].qty += d;
    if (cart[id].qty <= 0) delete cart[id];
    else if (cart[id].qty > cart[id].stock) cart[id].qty = cart[id].stock;
    renderCart();
}
function clearCart() { cart = {}; renderCart(); }

function renderCart() {
    const c = document.getElementById('cartItems');
    const e = document.getElementById('emptyCart');
    const keys = Object.keys(cart);
    if (!keys.length) { c.innerHTML = ''; c.appendChild(e); e.style.display = 'flex'; recalculate(); return; }
    e.style.display = 'none';
    let html = '';
    keys.forEach(id => {
        const i = cart[id];
        html += `<div style="display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid #f8fafc;">
            <div style="flex:1;min-width:0;">
                <p style="font-size:13px;font-weight:500;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${i.name}</p>
                <p style="font-size:12px;color:#0F6E56;font-weight:600;margin-top:2px;">${fmt(i.price)}</p>
            </div>
            <div style="display:flex;align-items:center;gap:6px;flex-shrink:0;">
                <button onclick="changeQty(${id},-1)" style="width:26px;height:26px;border-radius:8px;border:1.5px solid #e2e8f0;background:#fff;cursor:pointer;font-size:14px;color:#374151;display:flex;align-items:center;justify-content:center;transition:all 0.15s;" onmouseover="this.style.borderColor='#0F6E56';this.style.color='#0F6E56'" onmouseout="this.style.borderColor='#e2e8f0';this.style.color='#374151'">−</button>
                <span style="font-size:13.5px;font-weight:600;color:#0f172a;min-width:20px;text-align:center;">${i.qty}</span>
                <button onclick="changeQty(${id},1)" style="width:26px;height:26px;border-radius:8px;border:1.5px solid #e2e8f0;background:#fff;cursor:pointer;font-size:14px;color:#374151;display:flex;align-items:center;justify-content:center;transition:all 0.15s;" onmouseover="this.style.borderColor='#0F6E56';this.style.color='#0F6E56'" onmouseout="this.style.borderColor='#e2e8f0';this.style.color='#374151'">+</button>
            </div>
            <button onclick="removeFromCart(${id})" style="background:none;border:none;cursor:pointer;color:#d1d5db;padding:0;flex-shrink:0;display:flex;" onmouseover="this.style.color='#f43f5e'" onmouseout="this.style.color='#d1d5db'">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>`;
    });
    c.innerHTML = html;
    recalculate();
}

function recalculate() {
    let sub = 0;
    Object.values(cart).forEach(i => sub += i.price * i.qty);
    const disc = parseFloat(document.getElementById('discountInput').value)||0;
    const total = Math.max(0, sub - disc);
    const paid = parseFloat(document.getElementById('paidInput').value)||0;
    document.getElementById('subtotalDisplay').textContent = fmt(sub);
    document.getElementById('totalDisplay').textContent = fmt(total);
    document.getElementById('changeDisplay').textContent = fmt(Math.max(0, paid - total));
}

function setPayment(m) {
    paymentMethod = m;
    ['cash','qris','transfer'].forEach(v => {
        const b = document.getElementById('pay-'+v);
        if (v===m) { b.style.background='#0F6E56'; b.style.color='#fff'; b.style.borderColor='#0F6E56'; }
        else { b.style.background='#fff'; b.style.color='#374151'; b.style.borderColor='#e2e8f0'; }
    });
}

async function processTransaction() {
    if (!Object.keys(cart).length) { alert('Keranjang masih kosong!'); return; }
    const total = parseFloat(document.getElementById('totalDisplay').textContent.replace(/[^0-9]/g,''));
    const paid = parseFloat(document.getElementById('paidInput').value)||0;
    const disc = parseFloat(document.getElementById('discountInput').value)||0;
    if (paymentMethod==='cash' && paid < total) { alert('Jumlah bayar kurang!'); return; }
    const btn = document.getElementById('btnProses');
    btn.disabled=true; btn.textContent='Memproses...'; btn.style.opacity='0.7';
    try {
        const res = await fetch("{{ route('tenant.kasir.proses') }}", {
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},
            body:JSON.stringify({items:Object.values(cart).map(i=>({id:i.id,qty:i.qty})),paid_amount:paid,payment_method:paymentMethod,discount:disc})
        });
        const data = await res.json();
        if (data.success) {
            document.getElementById('modalInvoice').textContent = 'Invoice #'+data.transaction_id;
            document.getElementById('btnStruk').href = '/kasir/'+data.transaction_id+'/struk';
            document.getElementById('modalStruk').style.display = 'flex';
            clearCart(); document.getElementById('paidInput').value=''; document.getElementById('discountInput').value=0;
        } else { alert(data.message||'Terjadi kesalahan.'); }
    } catch(e) { alert('Gagal terhubung ke server.'); }
    finally { btn.disabled=false; btn.textContent='Proses Transaksi'; btn.style.opacity='1'; }
}

function closeModal() { document.getElementById('modalStruk').style.display='none'; }

document.getElementById('searchProduct').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.product-card').forEach(c => c.style.display = c.dataset.name.includes(q)?'':'none');
});
</script>
@endpush
