<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.tracking')
    @include('partials.app-head')
    <title>@yield('title', 'Dashboard') — {{ $currentTenant->name ?? ($appSettings['app_name'] ?? 'Tokaku') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { fontFamily: { sans: ['Inter', 'sans-serif'] }, colors: { primary: { 50: '#f0fdf6', 100: '#dcfce9', 200: '#bbf7d2', 700: '#0F6E56', 800: '#085041' } } } } }</script>
    <style>
        * {
            -webkit-font-smoothing: antialiased;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 14px;
            border-radius: 10px;
            font-size: 13.5px;
            font-weight: 500;
            color: #6b7280;
            transition: all 0.15s;
            text-decoration: none;
        }

        .sidebar-link:hover {
            background: #f9fafb;
            color: #111827;
        }

        .sidebar-link.active {
            background: #0F6E56;
            color: #fff;
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #0F6E56;
            color: #fff;
            font-size: 13.5px;
            font-weight: 500;
            padding: 9px 16px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            transition: background 0.15s;
            text-decoration: none;
            font-family: Inter, sans-serif;
        }

        .btn-primary:hover {
            background: #085041;
        }

        .btn-secondary {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #fff;
            color: #374151;
            font-size: 13.5px;
            font-weight: 500;
            padding: 9px 16px;
            border-radius: 10px;
            border: 1.5px solid #e2e8f0;
            cursor: pointer;
            transition: all 0.15s;
            text-decoration: none;
            font-family: Inter, sans-serif;
        }

        .btn-secondary:hover {
            background: #f9fafb;
        }

        .form-input {
            width: 100%;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 13.5px;
            font-family: Inter, sans-serif;
            outline: none;
            transition: all 0.15s;
            background: #fafafa;
            color: #111827;
            box-sizing: border-box;
        }

        .form-input:focus {
            border-color: #0F6E56;
            box-shadow: 0 0 0 3px rgba(15, 110, 86, 0.1);
            background: #fff;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
        }

        .mobile-nav-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.4);
            z-index: 30;
        }

        .mobile-nav-overlay.open {
            display: block;
        }

        @media(max-width:1023px) {
            .sidebar-desktop {
                transform: translateX(-100%);
                position: fixed;
                z-index: 40;
                height: 100vh;
                transition: transform 0.25s ease;
            }

            .sidebar-desktop.open {
                transform: translateX(0);
            }
        }

        /* Collapse sidebar di desktop/tablet (>=1024px): sembunyikan total, konten melebar */
        @media(min-width:1024px) {
            .sidebar-desktop {
                transition: width 0.2s ease, margin 0.2s ease, opacity 0.15s ease;
            }
            body.sidebar-collapsed .sidebar-desktop,
            html.pre-sidebar-collapsed .sidebar-desktop {
                width: 0 !important;
                margin-left: -1px;
                opacity: 0;
                overflow: hidden;
                pointer-events: none;
                border: none;
            }
        }

        /* ============================================================
           RESPONSIVE TABLE ENGINE
           Tabel apa pun yang dibungkus .table-responsive akan otomatis
           berubah menjadi kartu (card) di layar <640px. Tiap <td> cukup
           diberi atribut data-label="Judul Kolom".
           ============================================================ */
        @media(max-width:639px) {
            .table-responsive { overflow-x: visible !important; }
            .table-responsive > table { min-width: 0 !important; width: 100% !important; }
            .table-responsive thead { display: none; }
            .table-responsive tbody,
            .table-responsive tr,
            .table-responsive td { display: block; width: 100%; box-sizing: border-box; }
            .table-responsive tr {
                background: #fff;
                border: 1px solid #eef2f7 !important;
                border-radius: 14px;
                padding: 6px 4px;
                margin-bottom: 12px;
                box-shadow: 0 1px 2px rgba(15,23,42,.04);
            }
            .table-responsive td {
                display: flex !important;
                align-items: center;
                justify-content: space-between;
                gap: 14px;
                text-align: right !important;
                padding: 9px 14px !important;
                border: none !important;
                border-bottom: 1px solid #f5f7fa !important;
            }
            .table-responsive tr td:last-child { border-bottom: none !important; }
            .table-responsive td::before {
                content: attr(data-label);
                flex: 0 0 auto;
                text-align: left;
                font-size: 11px;
                font-weight: 600;
                color: #94a3b8;
                text-transform: uppercase;
                letter-spacing: .3px;
                white-space: nowrap;
            }
            /* Sel tanpa label (mis. baris kosong / colspan) tampil normal */
            .table-responsive td:not([data-label])::before { content: ''; }
            .table-responsive td[data-empty] { justify-content: center; text-align: center !important; }
            .table-responsive td[data-empty]::before { content: ''; }
            /* Kolom aksi: tombol rata kanan tetap nyaman ditekan */
            .table-responsive td[data-label="Aksi"] > * { margin-left: auto; }
        }

        /* Tipografi & spacing global lebih nyaman di layar kecil */
        @media(max-width:639px) {
            .grid { gap: 12px !important; }
        }
    </style>
    @stack('styles')
    <script>
        // Terapkan preferensi collapse sidebar sedini mungkin (sebelum render)
        // agar tidak ada efek "kedip". Class ditambah ke <html>, lalu dipindah
        // ke <body> saat body siap.
        try {
            if (localStorage.getItem('tokaku_sidebar_collapsed') === '1'
                && window.matchMedia('(min-width:1024px)').matches) {
                document.documentElement.classList.add('pre-sidebar-collapsed');
            }
        } catch (e) {}
    </script>
</head>

<body style="background:#f8fafc;font-family:Inter,sans-serif;" class="antialiased">
<script>
    // Sinkronkan preferensi awal: pindah dari <html> ke <body> agar toggle konsisten.
    if (document.documentElement.classList.contains('pre-sidebar-collapsed')) {
        document.body.classList.add('sidebar-collapsed');
        document.documentElement.classList.remove('pre-sidebar-collapsed');
    }
</script>
@include('partials.gtm-noscript')
    <div class="flex h-screen overflow-hidden">

        <div class="mobile-nav-overlay" id="mobileOverlay" onclick="closeSidebar()"></div>

        <aside class="sidebar-desktop w-60 bg-white border-r border-gray-100 flex flex-col flex-shrink-0" id="sidebar">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    @if(!empty($currentTenant?->logo_path))
                        <div style="width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden;background:#fff;border:1px solid #f1f5f9;">
                            <img src="{{ Storage::url($currentTenant->logo_path) }}" alt="Logo {{ $currentTenant->name }}" style="width:100%;height:100%;object-fit:contain;">
                        </div>
                    @elseif(!empty($appSettings['app_logo_path']))
                        <div style="width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden;background:#fff;border:1px solid #f1f5f9;">
                            <img src="{{ Storage::url($appSettings['app_logo_path']) }}" alt="{{ $appSettings['app_name'] ?? 'Tokaku' }}" style="width:100%;height:100%;object-fit:contain;">
                        </div>
                    @else
                        <div style="width:36px;height:36px;background:#0F6E56;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden;">
                            <svg width="16" height="16" viewBox="0 0 18 18" fill="none">
                                <path d="M3 5h12M3 9h8M3 13h5" stroke="white" stroke-width="2" stroke-linecap="round" />
                            </svg>
                        </div>
                    @endif
                    <div class="leading-tight">
                        <p style="font-size:15px;font-weight:700;color:#0F6E56;letter-spacing:-0.3px;">{{ $appSettings['app_name'] ?? 'Tokaku' }}</p>
                        <p style="font-size:11px;color:#9ca3af;margin-top:1px;" class="truncate max-w-[110px]">
                            {{ $currentTenant->name ?? '' }}
                        </p>
                    </div>
                </div>
                <button onclick="closeSidebar()" class="lg:hidden text-gray-400 p-1">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">
                <p
                    style="font-size:10.5px;font-weight:600;color:#9ca3af;letter-spacing:0.8px;text-transform:uppercase;padding:0 14px;margin-bottom:6px;">
                    Menu</p>

                @if(auth()->user()->isOwner())
                <a href="{{ route('tenant.dashboard') }}"
                    class="sidebar-link {{ request()->routeIs('tenant.dashboard') ? 'active' : '' }}">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Dashboard
                </a>
                @endif
                @if(auth()->user()->hasAccess('kasir'))
                <a href="{{ route('tenant.kasir.index') }}"
                    class="sidebar-link {{ request()->routeIs('tenant.kasir.*') ? 'active' : '' }}">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 7H6a2 2 0 00-2 2v9a2 2 0 002 2h9a2 2 0 002-2v-3M9 7V5a2 2 0 012-2h2a2 2 0 012 2v2M9 7h6m-3 4v4m-2-2h4" />
                    </svg>
                    Kasir
                </a>
                @endif
                @if(auth()->user()->hasAccess('produk'))
                <a href="{{ route('tenant.products.index') }}"
                    class="sidebar-link {{ request()->routeIs('tenant.products.*') ? 'active' : '' }}">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    Produk
                </a>
                @endif
                @if(auth()->user()->hasAccess('kategori'))
                <a href="{{ route('tenant.categories.index') }}"
                    class="sidebar-link {{ request()->routeIs('tenant.categories.*') ? 'active' : '' }}">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    Kategori
                </a>
                @endif
                @if(auth()->user()->hasAccess('laporan'))
                <a href="{{ route('tenant.laporan.index') }}"
                    class="sidebar-link {{ request()->routeIs('tenant.laporan.*') ? 'active' : '' }}">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Laporan
                </a>
                @endif
                @if(auth()->user()->hasAccess('stok'))
                <a href="{{ route('tenant.stok.index') }}"
                    class="sidebar-link {{ request()->routeIs('tenant.stok.*') ? 'active' : '' }}">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    Stok
                </a>
                @endif
                @if(auth()->user()->hasAccess('pelanggan'))
                <a href="{{ route('tenant.pelanggan.index') }}"
                    class="sidebar-link {{ request()->routeIs('tenant.pelanggan.*') ? 'active' : '' }}">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Pelanggan
                </a>
                @endif
                @if(auth()->user()->hasAccess('promo'))
                <a href="{{ route('tenant.promo.index') }}"
                    class="sidebar-link {{ request()->routeIs('tenant.promo.*') ? 'active' : '' }}">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    Promo & Diskon
                </a>
                @endif
                @if(auth()->user()->hasAccess('pengeluaran'))
                <a href="{{ route('tenant.expenses.index') }}"
                    class="sidebar-link {{ request()->routeIs('tenant.expenses.*') ? 'active' : '' }}">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-2m2-5h2a2 2 0 012 2v3a2 2 0 01-2 2h-2m0-7h-4m4 0v7" />
                    </svg>
                    Pengeluaran
                </a>
                @endif
                @if(auth()->user()->hasAccess('hutang'))
                <a href="{{ route('tenant.hutang.index') }}"
                    class="sidebar-link {{ request()->routeIs('tenant.hutang.*') ? 'active' : '' }}">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Hutang Piutang
                </a>
                @endif
                @if(auth()->user()->hasAccess('shift'))
                <a href="{{ route('tenant.shift.index') }}"
                    class="sidebar-link {{ request()->routeIs('tenant.shift.*') ? 'active' : '' }}">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Shift Kasir
                </a>
                @endif

                @if(auth()->user()->isAdmin())
                    <a href="{{ route('tenant.users.index') }}"
                        class="sidebar-link {{ request()->routeIs('tenant.users.*') ? 'active' : '' }}">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Tim Toko
                    </a>
                    <div style="padding-top:12px;">
                        <p
                            style="font-size:10.5px;font-weight:600;color:#9ca3af;letter-spacing:0.8px;text-transform:uppercase;padding:0 14px;margin-bottom:6px;">
                            Pengaturan</p>
                        <a href="{{ route('tenant.billing.index') }}"
                            class="sidebar-link {{ request()->routeIs('tenant.billing.*') ? 'active' : '' }}">
                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                            Tagihan & Langganan
                        </a>
                        <a href="{{ route('tenant.profil') }}"
                            class="sidebar-link {{ request()->routeIs('tenant.profil*') ? 'active' : '' }}">
                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Pengaturan Toko
                        </a>
                    </div>
                @endif
            </nav>

            <div class="px-3 py-3 border-t border-gray-100">
                <div class="flex items-center gap-3 px-3 py-2 rounded-xl">
                    <div
                        style="width:32px;height:32px;background:#dcfce9;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <span
                            style="color:#0F6E56;font-size:12px;font-weight:700;">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p style="font-size:13px;font-weight:500;color:#111827;" class="truncate">
                            {{ auth()->user()->name }}
                        </p>
                        <p style="font-size:11px;color:#9ca3af;text-transform:capitalize;">{{ auth()->user()->role }}
                        </p>
                    </div>
                </div>
                <div style="display:flex;gap:6px;margin-top:8px;">
                    <a href="{{ route('tenant.password.edit') }}"
                        style="flex:1;display:flex;align-items:center;justify-content:center;gap:6px;font-size:12.5px;font-weight:500;color:#374151;background:#fff;border:1.5px solid #e2e8f0;border-radius:10px;padding:8px 10px;text-decoration:none;transition:all .15s;"
                        onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='#fff'">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                        Password
                    </a>
                    <form method="POST" action="{{ route('logout') }}" style="flex:1;">
                        @csrf
                        <button type="submit"
                            style="width:100%;display:flex;align-items:center;justify-content:center;gap:6px;font-size:12.5px;font-weight:500;color:#be123c;background:#fff1f2;border:1.5px solid #fecdd3;border-radius:10px;padding:8px 10px;cursor:pointer;font-family:Inter,sans-serif;transition:all .15s;"
                            onmouseover="this.style.background='#ffe4e6'" onmouseout="this.style.background='#fff1f2'">
                            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <main class="flex-1 overflow-y-auto min-w-0">
            <header
                class="bg-white border-b border-gray-100 px-4 lg:px-8 py-4 flex items-center justify-between sticky top-0 z-20">
                <div class="flex items-center gap-3">
                    <button onclick="toggleSidebar()" class="text-gray-500 p-1 -ml-1" aria-label="Tampilkan/sembunyikan menu" title="Tampilkan/sembunyikan menu">
                        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <div>
                        <h1 style="font-size:15px;font-weight:600;color:#111827;">@yield('page-title', 'Dashboard')</h1>
                        <p style="font-size:12px;color:#9ca3af;margin-top:1px;" class="hidden sm:block">
                            @yield('page-subtitle', '')</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">@yield('header-actions')</div>
            </header>

            @if(session('success') || session('error'))
                <div class="px-4 lg:px-8 pt-5">
                    @if(session('success'))
                        <div
                            style="display:flex;align-items:center;gap:10px;background:#f0fdf6;border:1px solid #bbf7d2;color:#15803d;font-size:13.5px;border-radius:12px;padding:12px 16px;">
                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                                style="flex-shrink:0;">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ session('success') }}
                        </div>
                    @endif
                    @if(session('error'))
                        <div
                            style="display:flex;align-items:center;gap:10px;background:#fff1f2;border:1px solid #fecdd3;color:#be123c;font-size:13.5px;border-radius:12px;padding:12px 16px;">
                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                                style="flex-shrink:0;">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ session('error') }}
                        </div>
                    @endif
                </div>
            @endif

            <div class="px-4 lg:px-8 py-6">@yield('content')</div>
        </main>
    </div>

    <script>
        function openSidebar() { document.getElementById('sidebar').classList.add('open'); document.getElementById('mobileOverlay').classList.add('open'); document.body.style.overflow = 'hidden'; }
        function closeSidebar() { document.getElementById('sidebar').classList.remove('open'); document.getElementById('mobileOverlay').classList.remove('open'); document.body.style.overflow = ''; }

        // Toggle universal: layar kecil (<1024px) buka/tutup overlay,
        // layar besar collapse sidebar (konten melebar). Preferensi desktop
        // disimpan agar konsisten saat pindah halaman.
        function toggleSidebar() {
            if (window.matchMedia('(max-width:1023px)').matches) {
                document.getElementById('sidebar').classList.contains('open') ? closeSidebar() : openSidebar();
            } else {
                var collapsed = document.body.classList.toggle('sidebar-collapsed');
                try { localStorage.setItem('tokaku_sidebar_collapsed', collapsed ? '1' : '0'); } catch (e) {}
            }
        }
    </script>

    {{-- Format separator ribuan global untuk semua input .input-rupiah --}}
    <script>
    (function(){
        function onlyDigits(v){ return (v||'').toString().replace(/[^0-9]/g,''); }
        // Buang bagian desimal (.00 / ,00) dari nilai awal yang berasal dari
        // kolom decimal database, supaya tidak ikut terbaca jadi digit ribuan.
        function stripDecimal(v){ return (v||'').toString().replace(/[.,]\d{1,2}$/, ''); }
        function formatRupiahInput(el){
            var d = onlyDigits(el.value);
            el.value = d ? parseInt(d,10).toLocaleString('id-ID') : '';
        }
        // Format khusus nilai awal: buang desimal dulu sebelum diformat.
        function formatRupiahInitial(el){
            var d = onlyDigits(stripDecimal(el.value));
            el.value = d ? parseInt(d,10).toLocaleString('id-ID') : '';
        }
        // Helper global: ambil nilai numeric murni dari sebuah input rupiah
        window.rupiahValue = function(el){
            if(typeof el === 'string') el = document.querySelector(el);
            return el ? (parseInt(onlyDigits(el.value),10)||0) : 0;
        };
        function init(){
            document.querySelectorAll('input.input-rupiah').forEach(function(el){
                // format nilai awal (mis. dari old() / data edit)
                if(el.value) formatRupiahInitial(el);
                el.addEventListener('input', function(){ formatRupiahInput(el); });
            });
            // Saat submit form, bersihkan titik agar backend terima angka murni
            document.querySelectorAll('form').forEach(function(form){
                form.addEventListener('submit', function(){
                    form.querySelectorAll('input.input-rupiah').forEach(function(el){
                        el.value = onlyDigits(el.value);
                    });
                });
            });
        }
        if(document.readyState !== 'loading') init();
        else document.addEventListener('DOMContentLoaded', init);
    })();
    </script>
    @stack('scripts')
</body>

</html>