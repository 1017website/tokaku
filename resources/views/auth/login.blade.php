<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — Tokaku</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter','sans-serif'] },
                    colors: { primary: { 50:'#f0fdf6',100:'#dcfce9',200:'#bbf7d2',700:'#0F6E56',800:'#085041' } }
                }
            }
        }
    </script>
    <style>* { -webkit-font-smoothing:antialiased; } </style>
</head>
<body class="min-h-screen bg-gray-50 flex font-sans" style="font-family:Inter,sans-serif;">

    {{-- Left branding — hidden on mobile --}}
    <div class="hidden lg:flex lg:w-5/12 xl:w-1/2 bg-primary-700 flex-col justify-between p-12 relative overflow-hidden">
        <div class="absolute -top-20 -right-20 w-64 h-64 bg-white/5 rounded-full"></div>
        <div class="absolute top-1/2 -left-10 w-40 h-40 bg-white/5 rounded-full"></div>
        <div class="absolute -bottom-10 right-20 w-52 h-52 bg-white/5 rounded-full"></div>

        <div class="relative z-10 flex items-center gap-3">
            <div class="w-10 h-10 bg-white/20 rounded-2xl flex items-center justify-center">
                <svg width="22" height="22" viewBox="0 0 18 18" fill="none"><path d="M3 5h12M3 9h8M3 13h5" stroke="white" stroke-width="2" stroke-linecap="round"/></svg>
            </div>
            <span style="color:white;font-size:20px;font-weight:700;letter-spacing:-0.4px;">Tokaku</span>
        </div>

        <div class="relative z-10">
            <h2 style="color:white;font-size:32px;font-weight:700;line-height:1.25;letter-spacing:-0.5px;margin-bottom:16px;">
                Toko kamu,<br>lebih mudah.
            </h2>
            <p style="color:rgba(255,255,255,0.7);font-size:14px;line-height:1.7;max-width:300px;">
                Kelola kasir, stok, dan laporan penjualan dalam satu platform yang simpel dan cepat.
            </p>
            <div style="margin-top:32px;display:flex;flex-direction:column;gap:12px;">
                @foreach(['Kasir digital yang mudah dipakai','Laporan penjualan real-time','Kelola stok otomatis','Multi-user & multi-role'] as $f)
                <div style="display:flex;align-items:center;gap:10px;">
                    <div style="width:20px;height:20px;background:rgba(255,255,255,0.15);border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <span style="color:rgba(255,255,255,0.85);font-size:13.5px;">{{ $f }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <div class="relative z-10">
            <p style="color:rgba(255,255,255,0.4);font-size:12px;">by <span style="color:rgba(255,255,255,0.7);font-weight:500;">1017studios.id</span></p>
        </div>
    </div>

    {{-- Right form --}}
    <div class="w-full lg:w-7/12 xl:w-1/2 flex items-center justify-center p-6 sm:p-10">
        <div class="w-full max-w-sm">

            {{-- Mobile logo --}}
            <div class="flex items-center gap-2 mb-10 lg:hidden">
                <div class="w-9 h-9 bg-primary-700 rounded-xl flex items-center justify-center">
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M3 5h12M3 9h8M3 13h5" stroke="white" stroke-width="2" stroke-linecap="round"/></svg>
                </div>
                <span style="font-size:18px;font-weight:700;color:#0F6E56;letter-spacing:-0.3px;">Tokaku</span>
            </div>

            <div style="margin-bottom:28px;">
                <h1 style="font-size:24px;font-weight:700;color:#0f172a;letter-spacing:-0.5px;">Selamat datang</h1>
                <p style="font-size:14px;color:#64748b;margin-top:6px;">Masuk untuk melanjutkan ke dashboard Anda</p>
            </div>

            @if($errors->any())
            <div style="display:flex;align-items:flex-start;gap:10px;background:#fff1f2;border:1px solid #fecdd3;border-radius:12px;padding:12px 16px;margin-bottom:20px;">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#be123c" stroke-width="2" style="flex-shrink:0;margin-top:1px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p style="font-size:13.5px;color:#be123c;">{{ $errors->first() }}</p>
            </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}">
                @csrf

                <div style="margin-bottom:16px;">
                    <label style="display:block;font-size:13px;font-weight:500;color:#374151;margin-bottom:6px;">Email</label>
                    <div style="position:relative;">
                        <div style="position:absolute;left:14px;top:50%;transform:translateY(-50%);pointer-events:none;">
                            <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="#9ca3af" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <input type="email" name="email" value="{{ old('email') }}" autofocus placeholder="email@toko.com"
                            style="width:100%;border:1.5px solid #e2e8f0;border-radius:12px;padding:11px 14px 11px 40px;font-size:14px;font-family:Inter,sans-serif;outline:none;background:#fafafa;color:#0f172a;transition:all 0.15s;box-sizing:border-box;"
                            onfocus="this.style.borderColor='#0F6E56';this.style.boxShadow='0 0 0 3px rgba(15,110,86,0.1)';this.style.background='#fff';"
                            onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none';this.style.background='#fafafa';">
                    </div>
                </div>

                <div style="margin-bottom:20px;">
                    <label style="display:block;font-size:13px;font-weight:500;color:#374151;margin-bottom:6px;">Password</label>
                    <div style="position:relative;">
                        <div style="position:absolute;left:14px;top:50%;transform:translateY(-50%);pointer-events:none;">
                            <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="#9ca3af" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <input type="password" name="password" id="passInput" placeholder="••••••••"
                            style="width:100%;border:1.5px solid #e2e8f0;border-radius:12px;padding:11px 44px 11px 40px;font-size:14px;font-family:Inter,sans-serif;outline:none;background:#fafafa;color:#0f172a;transition:all 0.15s;box-sizing:border-box;"
                            onfocus="this.style.borderColor='#0F6E56';this.style.boxShadow='0 0 0 3px rgba(15,110,86,0.1)';this.style.background='#fff';"
                            onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none';this.style.background='#fafafa';">
                        <button type="button" onclick="togglePass()" style="position:absolute;right:14px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#9ca3af;padding:0;display:flex;">
                            <svg id="eyeOn" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg id="eyeOff" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" style="display:none;"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                        </button>
                    </div>
                </div>

                <div style="display:flex;align-items:center;margin-bottom:24px;">
                    <input type="checkbox" name="remember" id="rem" style="width:15px;height:15px;accent-color:#0F6E56;cursor:pointer;">
                    <label for="rem" style="font-size:13.5px;color:#64748b;margin-left:8px;cursor:pointer;">Ingat saya</label>
                </div>

                <button type="submit"
                    style="width:100%;background:#0F6E56;color:white;font-family:Inter,sans-serif;font-size:14px;font-weight:600;padding:13px;border-radius:12px;border:none;cursor:pointer;transition:all 0.15s;display:flex;align-items:center;justify-content:center;gap:8px;letter-spacing:-0.2px;"
                    onmouseover="this.style.background='#085041'" onmouseout="this.style.background='#0F6E56'">
                    Masuk ke Dashboard
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </button>
            </form>

            <p style="text-align:center;font-size:12px;color:#94a3b8;margin-top:28px;">
                Tokaku &copy; {{ date('Y') }} &middot; by
                <a href="https://1017studios.id" style="color:#64748b;font-weight:500;text-decoration:none;" onmouseover="this.style.color='#0F6E56'" onmouseout="this.style.color='#64748b'">1017studios.id</a>
            </p>
        </div>
    </div>

<script>
function togglePass() {
    const i = document.getElementById('passInput');
    const on = document.getElementById('eyeOn');
    const off = document.getElementById('eyeOff');
    if (i.type === 'password') { i.type = 'text'; on.style.display='none'; off.style.display='block'; }
    else { i.type = 'password'; on.style.display='block'; off.style.display='none'; }
}
</script>
</body>
</html>
