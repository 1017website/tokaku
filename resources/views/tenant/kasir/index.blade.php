@extends('layouts.app')
@section('title','Kasir')
@section('page-title','Kasir')
@section('page-subtitle','Buat transaksi penjualan')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
.pos-wrap{display:grid;grid-template-columns:minmax(0,1fr) 360px;gap:18px;min-height:calc(100vh - 170px)}
.pos-panel{background:#fff;border:1px solid #eef2f7;border-radius:18px;box-shadow:0 1px 3px rgba(15,23,42,.04)}
.category-tabs{display:flex;gap:10px;overflow-x:auto;padding:12px 2px 2px;scrollbar-width:thin}
.cat-btn{white-space:nowrap;border:1.5px solid #e2e8f0;background:#fff;color:#334155;padding:9px 18px;border-radius:999px;font-size:13px;font-weight:700;cursor:pointer;font-family:Inter,sans-serif}
.cat-btn.active{background:#0F6E56;border-color:#0F6E56;color:#fff}
.product-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:12px;overflow:auto;padding:16px;max-height:calc(100vh - 285px)}
.product-card{background:#fff;border:1.5px solid #edf2f7;border-radius:16px;padding:12px;text-align:left;cursor:pointer;min-height:142px;transition:.15s;font-family:Inter,sans-serif}
.product-card:hover{border-color:#0F6E56;box-shadow:0 8px 24px rgba(15,110,86,.08);transform:translateY(-1px)}
.product-card:disabled{opacity:.45;cursor:not-allowed;transform:none;box-shadow:none}
.product-img{width:100%;height:66px;background:#f8fafc;border-radius:12px;display:flex;align-items:center;justify-content:center;margin-bottom:10px;overflow:hidden}
.cart-sticky{position:sticky;top:0;max-height:calc(100vh - 120px);display:flex;flex-direction:column}
.select-customer{width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:9px 12px;font-size:13px;font-family:Inter,sans-serif;background:#fafafa;outline:none}
@media(max-width:1024px){.pos-wrap{grid-template-columns:1fr}.cart-sticky{position:relative;max-height:none}.product-grid{max-height:none;grid-template-columns:repeat(auto-fill,minmax(135px,1fr))}}
@media(max-width:640px){.product-grid{grid-template-columns:repeat(2,1fr);padding:12px}.pos-wrap{gap:12px}.cat-btn{padding:8px 14px}}
</style>
@endpush

@section('content')
@if(isset($activeShift) && $activeShift)
<div style="background:#f0fdf6;border:1px solid #bbf7d2;border-radius:12px;padding:10px 16px;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;gap:8px;">
    <span style="font-size:13px;font-weight:600;color:#15803d;">Shift aktif sejak {{ $activeShift->opened_at->format('H:i') }}</span>
    <a href="{{ route('tenant.shift.index') }}" style="font-size:12.5px;color:#15803d;font-weight:600;text-decoration:none;">Tutup Shift</a>
</div>
@endif

<div class="pos-wrap">
    <div class="pos-panel" style="display:flex;flex-direction:column;overflow:hidden;">
        <div style="padding:16px 18px;border-bottom:1px solid #f8fafc;">
            <input type="text" id="searchProduct" placeholder="Cari nama produk..." class="form-input">
            <div class="category-tabs" id="categoryTabs">
                <button type="button" class="cat-btn active" data-category="all">Semua Produk</button>
                @if(isset($topProducts) && $topProducts->count())
                    <button type="button" class="cat-btn" data-category="top">Top Order</button>
                @endif
                @foreach($categories as $category)
                    <button type="button" class="cat-btn" data-category="cat-{{ $category->id }}">{{ $category->name }}</button>
                @endforeach
            </div>
        </div>

        @if(isset($topProducts) && $topProducts->count())
        <div class="product-grid product-section" data-section="top" style="display:none;">
            @foreach($topProducts as $product)
                @include('tenant.kasir.partials.product-card', ['product' => $product])
            @endforeach
        </div>
        @endif

        <div class="product-grid product-section" data-section="all">
            @forelse($products as $product)
                @include('tenant.kasir.partials.product-card', ['product' => $product])
            @empty
                <div style="grid-column:1/-1;text-align:center;padding:60px 20px;color:#94a3b8;font-size:14px;">Belum ada produk aktif.</div>
            @endforelse
        </div>
    </div>

    <div class="pos-panel cart-sticky">
        <div style="padding:14px 18px;border-bottom:1px solid #f8fafc;display:flex;justify-content:space-between;align-items:center;">
            <div style="display:flex;align-items:center;gap:8px;"><b style="font-size:14px;color:#0f172a;">Keranjang</b><span id="cartBadge" style="display:none;background:#0F6E56;color:#fff;font-size:11px;font-weight:700;padding:2px 7px;border-radius:99px;"></span></div>
            <button id="btnClear" onclick="clearCart()" style="display:none;font-size:12px;color:#f43f5e;background:none;border:none;cursor:pointer;font-weight:600;">Kosongkan</button>
        </div>

        <div style="padding:12px 16px;border-bottom:1px solid #f8fafc;">
            <label style="font-size:12px;font-weight:700;color:#64748b;margin-bottom:6px;display:block;">Pelanggan</label>
            <select id="customerSelect" class="select-customer">
                <option value="">Umum</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->name }}{{ $customer->phone ? ' - '.$customer->phone : '' }}</option>
                @endforeach
            </select>
        </div>

        <div id="cartItems" style="flex:1;overflow-y:auto;padding:0 16px;min-height:90px;"></div>

        <div style="padding:14px 16px;border-top:1px solid #f8fafc;display:flex;flex-direction:column;gap:9px;">
            <div style="display:flex;justify-content:space-between;"><span style="font-size:13px;color:#64748b;">Subtotal</span><b id="subtotalDisplay" style="font-size:13px;color:#0f172a;">Rp 0</b></div>
            <div style="display:flex;justify-content:space-between;align-items:center;"><span style="font-size:13px;color:#64748b;">Diskon</span><div style="display:flex;align-items:center;gap:5px;"><span style="font-size:12px;color:#9ca3af;">Rp</span><input type="number" id="discountInput" value="0" min="0" oninput="recalculate()" style="width:92px;text-align:right;border:1.5px solid #e2e8f0;border-radius:8px;padding:6px 8px;font-size:13px;"></div></div>
            <div style="display:flex;justify-content:space-between;padding:11px 0;border-top:1.5px solid #f1f5f9;border-bottom:1.5px solid #f1f5f9;"><b style="font-size:15px;color:#0f172a;">Total</b><b id="totalDisplay" style="font-size:17px;color:#0F6E56;">Rp 0</b></div>
            <div><label style="display:block;font-size:12px;font-weight:700;color:#64748b;margin-bottom:5px;">Jumlah Bayar</label><input type="number" id="paidInput" placeholder="0" min="0" oninput="recalculate()" class="form-input"></div>
            <div style="background:#f0fdf4;border-radius:10px;padding:9px 12px;display:flex;justify-content:space-between;"><b style="font-size:13px;color:#15803d;">Kembalian</b><b id="changeDisplay" style="font-size:14px;color:#15803d;">Rp 0</b></div>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:6px;">
                @foreach(['cash'=>'Tunai','qris'=>'QRIS','transfer'=>'Transfer'] as $v=>$l)
                <button id="pay-{{ $v }}" onclick="setPayment('{{ $v }}')" style="font-size:12.5px;font-weight:700;padding:9px;border-radius:9px;border:1.5px solid;cursor:pointer;font-family:Inter,sans-serif;{{ $loop->first?'background:#0F6E56;color:#fff;border-color:#0F6E56;':'background:#fff;color:#374151;border-color:#e2e8f0;' }}">{{ $l }}</button>
                @endforeach
            </div>
            <button onclick="showConfirmModal()" class="btn-primary" style="width:100%;justify-content:center;padding:13px;border-radius:12px;">Proses Transaksi</button>
        </div>
    </div>
</div>

<div id="modalKonfirmasi" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);align-items:center;justify-content:center;z-index:50;padding:16px;">
    <div style="background:#fff;border-radius:20px;width:100%;max-width:440px;max-height:90vh;display:flex;flex-direction:column;box-shadow:0 24px 80px rgba(0,0,0,.18);">
        <div style="padding:20px 24px 16px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;"><div><b style="font-size:16px;color:#0f172a;">Konfirmasi Pesanan</b><p style="font-size:12px;color:#94a3b8;margin-top:2px;">Pastikan semua item sudah benar</p></div><button onclick="closeConfirmModal()" style="background:none;border:none;cursor:pointer;color:#94a3b8;font-size:22px;">×</button></div>
        <div style="overflow-y:auto;flex:1;padding:0 24px;"><div id="confirmItemList" style="padding:12px 0;"></div></div>
        <div style="padding:16px 24px;background:#f8fafc;border-top:1px solid #f1f5f9;border-radius:0 0 20px 20px;"><div id="confirmSummary" style="display:flex;flex-direction:column;gap:7px;margin-bottom:14px;"></div><div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;"><button onclick="closeConfirmModal()" class="btn-secondary" style="justify-content:center;">Edit</button><button id="btnKonfirmasi" onclick="processTransaction()" class="btn-primary" style="justify-content:center;">Konfirmasi & Bayar</button></div></div>
    </div>
</div>

<div id="modalStruk" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);align-items:center;justify-content:center;z-index:60;padding:16px;">
    <div style="background:#fff;border-radius:20px;width:100%;max-width:360px;padding:28px;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,.15);">
        <h3 style="font-size:17px;font-weight:800;color:#0f172a;">Transaksi Berhasil!</h3><p id="modalInvoice" style="font-size:13px;color:#64748b;margin:8px 0 18px;"></p>
        <div style="display:flex;flex-direction:column;gap:8px;"><button id="btnCetakLangsung" onclick="cetakLangsungKasir()" class="btn-primary" style="justify-content:center;">Cetak Struk</button><button onclick="kirimWaKasir()" class="btn-secondary" style="justify-content:center;background:#dcfce7;color:#15803d;border-color:#bbf7d0;">Kirim Struk ke WhatsApp</button><a id="btnStruk" target="_blank" class="btn-secondary" style="justify-content:center;">Buka Struk (Browser)</a><a id="btnPdf" target="_blank" class="btn-secondary" style="justify-content:center;">Download PDF</a><button onclick="closeModal()" class="btn-secondary" style="justify-content:center;">Tutup</button><p id="cetakStatusKasir" style="font-size:12px;color:#64748b;margin-top:4px;"></p></div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
var cart = {}; var paymentMethod = 'cash';
function fmt(n){ return 'Rp ' + Math.round(n).toLocaleString('id-ID'); }
function escapeHtml(v){ return String(v).replace(/[&<>'"]/g, function(c){ return {'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[c]; }); }
function addToCart(id,name,price,stock){ id=String(id); price=parseFloat(price); stock=parseInt(stock); if(cart[id]){ if(cart[id].qty>=stock){alert('Stok '+name+' tidak mencukupi!');return;} cart[id].qty++; } else cart[id]={id:id,name:name,price:price,stock:stock,qty:1}; renderCart(); }
function changeQty(id,d){ id=String(id); if(!cart[id]) return; cart[id].qty+=d; if(cart[id].qty<=0) delete cart[id]; else if(cart[id].qty>cart[id].stock) cart[id].qty=cart[id].stock; renderCart(); }
function removeItem(id){ delete cart[String(id)]; renderCart(); }
function clearCart(){ cart={}; renderCart(); }
function renderCart(){ var c=document.getElementById('cartItems'), badge=document.getElementById('cartBadge'), clr=document.getElementById('btnClear'), keys=Object.keys(cart); if(!keys.length){ c.innerHTML='<div style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:34px 0;text-align:center;color:#94a3b8;font-size:13px;">Keranjang kosong<br><span style="font-size:12px;color:#cbd5e1;margin-top:4px;">Klik produk untuk menambahkan</span></div>'; badge.style.display='none'; clr.style.display='none'; recalculate(); return;} badge.textContent=keys.length; badge.style.display='inline'; clr.style.display='block'; var html=''; keys.forEach(function(id){var i=cart[id]; html+='<div style="display:flex;align-items:center;gap:8px;padding:10px 0;border-bottom:1px solid #f8fafc;"><div style="flex:1;min-width:0;"><p style="font-size:13px;font-weight:700;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+escapeHtml(i.name)+'</p><p style="font-size:12px;color:#0F6E56;font-weight:700;">'+fmt(i.price)+'</p></div><div style="display:flex;align-items:center;gap:5px;"><button onclick="changeQty(\''+id+'\',-1)" style="width:25px;height:25px;border-radius:7px;border:1.5px solid #e2e8f0;background:#fff;cursor:pointer;">−</button><b style="font-size:13px;min-width:20px;text-align:center;">'+i.qty+'</b><button onclick="changeQty(\''+id+'\',1)" style="width:25px;height:25px;border-radius:7px;border:1.5px solid #e2e8f0;background:#fff;cursor:pointer;">+</button></div><button onclick="removeItem(\''+id+'\')" style="background:none;border:none;color:#f43f5e;cursor:pointer;font-weight:800;">×</button></div>';}); c.innerHTML=html; recalculate(); }
function recalculate(){ var sub=0; Object.values(cart).forEach(function(i){sub+=i.price*i.qty;}); var disc=parseFloat(document.getElementById('discountInput').value)||0; var tot=Math.max(0,sub-disc); var paid=parseFloat(document.getElementById('paidInput').value)||0; document.getElementById('subtotalDisplay').textContent=fmt(sub); document.getElementById('totalDisplay').textContent=fmt(tot); document.getElementById('changeDisplay').textContent=fmt(Math.max(0,paid-tot)); }
function setPayment(m){ paymentMethod=m; ['cash','qris','transfer'].forEach(function(v){var b=document.getElementById('pay-'+v); b.style.background=v===m?'#0F6E56':'#fff'; b.style.color=v===m?'#fff':'#374151'; b.style.borderColor=v===m?'#0F6E56':'#e2e8f0';}); }
function showConfirmModal(){ if(!Object.keys(cart).length){alert('Keranjang masih kosong!');return;} var sub=0; Object.values(cart).forEach(function(i){sub+=i.price*i.qty;}); var disc=parseFloat(document.getElementById('discountInput').value)||0, total=Math.max(0,sub-disc), paid=parseFloat(document.getElementById('paidInput').value)||0; if(paymentMethod==='cash' && paid<total){alert('Jumlah bayar kurang dari total!');return;} var html=''; Object.values(cart).forEach(function(i){html+='<div style="display:flex;justify-content:space-between;gap:10px;padding:10px 0;border-bottom:1px solid #f1f5f9;"><div><b style="font-size:13.5px;color:#0f172a;">'+escapeHtml(i.name)+'</b><p style="font-size:12px;color:#64748b;">'+fmt(i.price)+' × '+i.qty+'</p></div><b style="font-size:13px;color:#0f172a;">'+fmt(i.price*i.qty)+'</b></div>';}); document.getElementById('confirmItemList').innerHTML=html; document.getElementById('confirmSummary').innerHTML='<div style="display:flex;justify-content:space-between;"><span>Subtotal</span><b>'+fmt(sub)+'</b></div><div style="display:flex;justify-content:space-between;"><span>Diskon</span><b>'+fmt(disc)+'</b></div><div style="display:flex;justify-content:space-between;border-top:1px solid #e2e8f0;padding-top:8px;"><b>Total</b><b style="color:#0F6E56;">'+fmt(total)+'</b></div>'; document.getElementById('modalKonfirmasi').style.display='flex'; }
function closeConfirmModal(){ document.getElementById('modalKonfirmasi').style.display='none'; }
async function processTransaction(){ var btn=document.getElementById('btnKonfirmasi'), disc=parseFloat(document.getElementById('discountInput').value)||0, paid=parseFloat(document.getElementById('paidInput').value)||0; btn.disabled=true; btn.textContent='Memproses...'; try{ var res=await fetch('{{ route("tenant.kasir.proses") }}',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').getAttribute('content')},body:JSON.stringify({items:Object.values(cart).map(function(i){return {id:parseInt(i.id),qty:i.qty};}),paid_amount:paid,payment_method:paymentMethod,discount:disc,customer_id:document.getElementById('customerSelect').value || null})}); var data=await res.json(); if(data.success){ closeConfirmModal(); window.lastTransactionId=data.transaction_id; document.getElementById('modalInvoice').textContent='Invoice #'+data.transaction_id; document.getElementById('btnStruk').href='/kasir/'+data.transaction_id+'/struk'; document.getElementById('btnPdf').href='/kasir/'+data.transaction_id+'/struk-pdf'; document.getElementById('cetakStatusKasir').textContent=''; document.getElementById('modalStruk').style.display='flex'; clearCart(); document.getElementById('paidInput').value=''; document.getElementById('discountInput').value='0'; document.getElementById('customerSelect').value=''; recalculate(); } else alert(data.message || 'Terjadi kesalahan.'); } catch(e){ alert('Gagal terhubung ke server.'); console.error(e); } finally{ btn.disabled=false; btn.textContent='Konfirmasi & Bayar'; } }
function closeModal(){ document.getElementById('modalStruk').style.display='none'; }
document.getElementById('searchProduct').addEventListener('input', filterProducts);
document.getElementById('categoryTabs').addEventListener('click', function(e){ var btn=e.target.closest('.cat-btn'); if(!btn) return; document.querySelectorAll('.cat-btn').forEach(function(b){b.classList.remove('active')}); btn.classList.add('active'); document.querySelectorAll('.product-section').forEach(function(sec){sec.style.display = (btn.dataset.category==='top' ? sec.dataset.section==='top' : sec.dataset.section==='all') ? 'grid':'none';}); filterProducts(); });
function filterProducts(){ var q=document.getElementById('searchProduct').value.toLowerCase(); var active=document.querySelector('.cat-btn.active').dataset.category; document.querySelectorAll('.product-section[style*="grid"], .product-section:not([style])').forEach(function(section){ section.querySelectorAll('.product-card').forEach(function(c){ var matchSearch=c.dataset.search.includes(q); var matchCat=active==='all' || active==='top' || c.dataset.category===active; c.style.display = (matchSearch && matchCat) ? '' : 'none'; }); }); }
document.addEventListener('click', function(e){ var card=e.target.closest('.product-card'); if(card && !card.disabled) addToCart(card.dataset.id, card.dataset.name, card.dataset.price, card.dataset.stock); });
document.getElementById('modalKonfirmasi').addEventListener('click', function(e){ if(e.target===this) closeConfirmModal(); });
document.getElementById('modalStruk').addEventListener('click', function(e){ if(e.target===this) closeModal(); });
if (window.jQuery && jQuery.fn.select2) { jQuery('#customerSelect').select2({ width: '100%', placeholder: 'Pilih pelanggan', allowClear: true }); }
renderCart();

// ===== Cetak Langsung ESC/POS (QZ Tray untuk PC, RawBT untuk Android) =====
var IS_ANDROID = /Android/i.test(navigator.userAgent);
function setCetakStatus(m){ var el=document.getElementById('cetakStatusKasir'); if(el) el.textContent=m||''; }

function cetakLangsungKasir(){
    var id = window.lastTransactionId;
    if(!id){ setCetakStatus('Transaksi tidak ditemukan.'); return; }
    var escposUrl = '/kasir/'+id+'/escpos';
    if (IS_ANDROID) {
        setCetakStatus('Mengirim ke RawBT...');
        window.location.href = 'rawbt:' + new URL(escposUrl+'?format=raw', window.location.origin).href;
        setTimeout(function(){ setCetakStatus('Jika tidak tercetak, pastikan RawBT terpasang & printer terhubung.'); }, 1500);
        return;
    }
    if (typeof qz === 'undefined') { setCetakStatus('Library QZ Tray gagal dimuat.'); return; }
    setCetakStatus('Menghubungkan ke QZ Tray...');
    (qz.websocket.isActive() ? Promise.resolve() : qz.websocket.connect())
        .then(function(){ return fetch(escposUrl).then(function(r){ return r.json(); }); })
        .then(function(data){
            return qz.printers.getDefault().then(function(printer){
                var cfg = qz.configs.create(printer, { encoding: 'ISO-8859-1' });
                return qz.print(cfg, [{ type:'raw', format:'base64', data:data.base64 }]);
            });
        })
        .then(function(){ setCetakStatus('Struk berhasil dikirim ke printer.'); })
        .catch(function(err){ console.error(err); setCetakStatus('Gagal: pastikan QZ Tray berjalan di PC ini.'); });
}

function kirimWaKasir(){
    var id = window.lastTransactionId;
    if(!id){ setCetakStatus('Transaksi tidak ditemukan.'); return; }
    setCetakStatus('Menyiapkan struk WhatsApp...');
    fetch('/kasir/'+id+'/whatsapp')
        .then(function(r){ return r.json(); })
        .then(function(data){
            var input = prompt('Masukkan nomor WhatsApp pembeli\n(contoh: 081234567890)', data.phone || '');
            if(input === null){ setCetakStatus(''); return; }   // batal
            var phone = input.replace(/[^0-9]/g, '');
            if(phone.charAt(0) === '0'){ phone = '62' + phone.slice(1); }
            else if(phone.charAt(0) === '8'){ phone = '62' + phone; }
            if(phone.length < 9){ setCetakStatus('Nomor WhatsApp tidak valid.'); return; }
            var url = 'https://api.whatsapp.com/send/?phone=' + phone + '&text=' + encodeURIComponent(data.text);
            window.open(url, '_blank');
            setCetakStatus('Membuka WhatsApp ke '+phone+'...');
        })
        .catch(function(err){ console.error(err); setCetakStatus('Gagal menyiapkan struk WhatsApp.'); });
}
</script>
<script src="https://cdn.jsdelivr.net/npm/qz-tray@2.2.4/qz-tray.js"></script>
@endpush
