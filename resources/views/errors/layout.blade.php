{{--
    Layout dasar halaman error Tokaku.
    Self-contained (inline CSS, tanpa Vite/asset eksternal) supaya
    tetap tampil rapi walau error terjadi sebelum asset ter-load.
    Tema: putih dengan aksen hijau, selaras dengan tampilan aplikasi.

    Variabel child view:
    - $code, $title, $message
    - $home : (opsional) URL tombol utama
--}}
@php
    $appName = \App\Models\AppSetting::getValue('app_name', config('app.name', 'Tokaku'));
    $homeUrl = $home ?? (auth()->check() ? (auth()->user()->role === 'superadmin' ? route('superadmin.dashboard') : auth()->user()->homeRoute()) : url('/'));
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $code }} &middot; {{ $appName }}</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --bg: #f0fdf6;
            --surface: #ffffff;
            --line: #dcfce9;
            --brand: #0F6E56;
            --brand-dark: #085041;
            --brand-hover: #0a5a47;
            --text: #111827;
            --muted: #6b7280;
        }
        html, body { height: 100%; }
        body {
            background:
                radial-gradient(1200px 600px at 50% -10%, #dcfce9, transparent 60%),
                var(--bg);
            color: var(--text);
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }
        .card {
            width: 100%;
            max-width: 460px;
            text-align: center;
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 20px;
            padding: 44px 32px;
            box-shadow: 0 10px 40px -12px rgba(15, 110, 86, .18);
        }
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: var(--brand-dark);
            background: var(--bg);
            border: 1px solid var(--line);
            padding: 6px 14px;
            border-radius: 999px;
            margin-bottom: 24px;
        }
        .badge .dot { width: 7px; height: 7px; border-radius: 50%; background: var(--brand); }
        .code {
            font-size: clamp(64px, 16vw, 104px);
            font-weight: 800;
            line-height: 1;
            letter-spacing: -.04em;
            color: var(--brand);
        }
        .title {
            font-size: clamp(19px, 5vw, 24px);
            font-weight: 700;
            margin-top: 10px;
            color: var(--text);
        }
        .message {
            color: var(--muted);
            font-size: 15px;
            line-height: 1.65;
            margin-top: 12px;
        }
        .actions {
            margin-top: 28px;
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 600;
            padding: 11px 20px;
            border-radius: 10px;
            text-decoration: none;
            border: 1px solid transparent;
            transition: background .15s ease, border-color .15s ease, transform .05s ease;
            cursor: pointer;
        }
        .btn:active { transform: translateY(1px); }
        .btn-primary { background: var(--brand); color: #fff; }
        .btn-primary:hover { background: var(--brand-hover); }
        .btn-ghost { background: var(--surface); color: var(--brand-dark); border-color: var(--line); }
        .btn-ghost:hover { border-color: var(--brand); background: var(--bg); }
        .footer {
            margin-top: 32px;
            font-size: 13px;
            color: var(--muted);
        }
        .footer strong { color: var(--brand-dark); font-weight: 600; }
    </style>
</head>
<body>
    <main class="card">
        <span class="badge"><span class="dot"></span>{{ $appName }}</span>
        <div class="code">@yield('code', $code)</div>
        <h1 class="title">@yield('title', $title ?? 'Terjadi Kesalahan')</h1>
        <p class="message">@yield('message', $message ?? 'Maaf, ada masalah saat memproses permintaan Anda.')</p>

        <div class="actions">
            <a class="btn btn-primary" href="{{ $homeUrl }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                Ke Beranda
            </a>
            <a class="btn btn-ghost" href="javascript:history.back()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                Kembali
            </a>
        </div>

        <p class="footer">&copy; {{ date('Y') }} <strong>{{ $appName }}</strong></p>
    </main>
</body>
</html>
