{{-- Modal detail item transaksi (reusable). Sertakan SEKALI di halaman. --}}
<div id="trxDetailModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);align-items:center;justify-content:center;z-index:60;padding:16px;" onclick="if(event.target===this)closeTrxDetail()">
    <div style="background:#fff;border-radius:20px;width:100%;max-width:440px;max-height:85vh;overflow:auto;padding:24px;">
        <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:4px;">
            <div>
                <h3 style="font-size:16px;font-weight:700;color:#0f172a;" id="trxModalInvoice">Invoice</h3>
                <p style="font-size:12.5px;color:#64748b;" id="trxModalMeta"></p>
                <span id="trxModalBadge" style="display:none;font-size:10px;font-weight:700;padding:2px 8px;border-radius:99px;background:#fee2e2;color:#b91c1c;margin-top:6px;">DIBATALKAN</span>
            </div>
            <button onclick="closeTrxDetail()" style="background:none;border:none;font-size:20px;color:#94a3b8;cursor:pointer;line-height:1;">&times;</button>
        </div>
        <div id="trxModalCancelBox" style="display:none;margin:14px 0 0;background:#fff7f7;border:1px solid #fecaca;border-radius:12px;padding:12px 14px;">
            <p style="font-size:11px;font-weight:700;color:#b91c1c;text-transform:uppercase;letter-spacing:.3px;margin-bottom:4px;">Transaksi Dibatalkan</p>
            <p id="trxModalCancelReason" style="font-size:13px;color:#7f1d1d;"></p>
            <p id="trxModalCancelMeta" style="font-size:11.5px;color:#9ca3af;margin-top:4px;"></p>
        </div>
        <div id="trxModalItems" style="margin:14px 0;border-top:1px solid #f1f5f9;"></div>
        <div id="trxModalSummary" style="border-top:1px solid #f1f5f9;padding-top:12px;display:flex;flex-direction:column;gap:6px;"></div>
        <a id="trxModalReprint" href="#" target="_blank" rel="noopener"
           style="display:flex;align-items:center;justify-content:center;gap:7px;margin-top:18px;background:#0F6E56;color:#fff;font-weight:600;font-size:13.5px;text-decoration:none;border-radius:11px;padding:11px;font-family:system-ui,sans-serif;">
            <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Cetak Ulang Struk
        </a>
    </div>
</div>

<script>
function fmtRp(n){ return 'Rp ' + Math.round(n||0).toLocaleString('id-ID'); }
function showTrxDetail(t){
    document.getElementById('trxModalInvoice').textContent = t.invoice_no || ('Invoice #'+t.id);
    var meta = [t.time, t.cashier, t.method].filter(Boolean).join(' · ');
    document.getElementById('trxModalMeta').textContent = meta;

    var itemsHtml = '';
    (t.items||[]).forEach(function(i){
        itemsHtml += '<div style="display:flex;justify-content:space-between;gap:10px;padding:9px 0;border-bottom:1px solid #f8fafc;">'
            + '<div><b style="font-size:13.5px;color:#0f172a;">'+escapeTrx(i.name)+'</b>'
            + '<p style="font-size:12px;color:#64748b;">'+fmtRp(i.price)+' × '+i.qty+'</p></div>'
            + '<b style="font-size:13px;color:#0f172a;">'+fmtRp(i.subtotal)+'</b></div>';
    });
    if(!itemsHtml) itemsHtml = '<p style="padding:14px 0;text-align:center;color:#94a3b8;font-size:13px;">Tidak ada item.</p>';
    document.getElementById('trxModalItems').innerHTML = itemsHtml;

    var s = '<div style="display:flex;justify-content:space-between;font-size:13px;color:#64748b;"><span>Subtotal</span><b style="color:#0f172a;">'+fmtRp(t.subtotal)+'</b></div>';
    if(t.discount > 0) s += '<div style="display:flex;justify-content:space-between;font-size:13px;color:#64748b;"><span>Diskon</span><b style="color:#0f172a;">-'+fmtRp(t.discount)+'</b></div>';
    if(t.tax > 0) s += '<div style="display:flex;justify-content:space-between;font-size:13px;color:#64748b;"><span>Pajak</span><b style="color:#0f172a;">'+fmtRp(t.tax)+'</b></div>';
    s += '<div style="display:flex;justify-content:space-between;font-size:15px;border-top:1px solid #e2e8f0;padding-top:8px;margin-top:2px;"><b style="color:#0f172a;">Total</b><b style="color:#0F6E56;">'+fmtRp(t.total)+'</b></div>';
    document.getElementById('trxModalSummary').innerHTML = s;

    // Tampilkan info pembatalan bila transaksi dibatalkan.
    var badge = document.getElementById('trxModalBadge');
    var cancelBox = document.getElementById('trxModalCancelBox');
    if (t.cancelled) {
        badge.style.display = 'inline-block';
        cancelBox.style.display = 'block';
        document.getElementById('trxModalCancelReason').textContent =
            (t.cancel_reason && t.cancel_reason.trim()) ? t.cancel_reason : 'Tanpa alasan.';
        var cm = [];
        if (t.cancelled_by) cm.push('Oleh ' + t.cancelled_by);
        if (t.cancelled_at) cm.push(t.cancelled_at);
        document.getElementById('trxModalCancelMeta').textContent = cm.join(' · ');
    } else {
        badge.style.display = 'none';
        cancelBox.style.display = 'none';
    }

    // Set tujuan tombol cetak ulang -> halaman struk transaksi ini.
    // Sembunyikan untuk transaksi yang dibatalkan.
    var reprint = document.getElementById('trxModalReprint');
    if (reprint) {
        if (t.cancelled) {
            reprint.style.display = 'none';
        } else {
            reprint.style.display = 'flex';
            if (t.id) reprint.href = '{{ url('kasir') }}/' + t.id + '/struk';
        }
    }

    document.getElementById('trxDetailModal').style.display = 'flex';
}
function closeTrxDetail(){ document.getElementById('trxDetailModal').style.display = 'none'; }
function escapeTrx(s){ var d=document.createElement('div'); d.textContent=s==null?'':s; return d.innerHTML; }
</script>
