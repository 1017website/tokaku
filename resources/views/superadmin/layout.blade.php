<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.tracking')
    @include('partials.app-head')
    <title>@yield('title','Super Admin') — {{ $appSettings['app_name'] ?? 'Tokaku' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{fontFamily:{sans:['Inter','sans-serif']},colors:{primary:{50:'#f0fdf6',700:'#0F6E56',800:'#085041'}}}}}</script>
    <style>
        *{-webkit-font-smoothing:antialiased;}
        .sa-link{display:flex;align-items:center;gap:10px;padding:9px 14px;border-radius:10px;font-size:13.5px;font-weight:500;color:#6b7280;transition:all 0.15s;text-decoration:none;}
        .sa-link:hover{background:#f9fafb;color:#111827;}
        .sa-link.active{background:#0F6E56;color:#fff;}
        .btn-primary{display:inline-flex;align-items:center;gap:6px;background:#0F6E56;color:#fff;font-size:13.5px;font-weight:500;padding:9px 16px;border-radius:10px;border:none;cursor:pointer;transition:background 0.15s;text-decoration:none;font-family:Inter,sans-serif;}
        .btn-primary:hover{background:#085041;}
        .btn-secondary{display:inline-flex;align-items:center;gap:6px;background:#fff;color:#374151;font-size:13.5px;font-weight:500;padding:9px 16px;border-radius:10px;border:1.5px solid #e2e8f0;cursor:pointer;transition:all 0.15s;text-decoration:none;font-family:Inter,sans-serif;}
        .btn-secondary:hover{background:#f9fafb;}
        .form-input{width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:10px 14px;font-size:13.5px;font-family:Inter,sans-serif;outline:none;background:#fafafa;box-sizing:border-box;transition:all 0.15s;}
        .form-input:focus{border-color:#0F6E56;box-shadow:0 0 0 3px rgba(15,110,86,0.1);background:#fff;}
        .mobile-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:30;}
        .mobile-overlay.open{display:block;}
        @media(max-width:1023px){.sa-sidebar{transform:translateX(-100%);position:fixed;z-index:40;height:100vh;transition:transform 0.25s ease;}.sa-sidebar.open{transform:translateX(0);}}
        /* Responsive table -> card di layar <640px (atribut data-label per <td>) */
        @media(max-width:639px){
            .table-responsive{overflow-x:visible !important;}
            .table-responsive>table{min-width:0 !important;width:100% !important;}
            .table-responsive thead{display:none;}
            .table-responsive tbody,.table-responsive tr,.table-responsive td{display:block;width:100%;box-sizing:border-box;}
            .table-responsive tr{background:#fff;border:1px solid #eef2f7 !important;border-radius:14px;padding:6px 4px;margin-bottom:12px;box-shadow:0 1px 2px rgba(15,23,42,.04);}
            .table-responsive td{display:flex !important;align-items:center;justify-content:space-between;gap:14px;text-align:right !important;padding:9px 14px !important;border:none !important;border-bottom:1px solid #f5f7fa !important;}
            .table-responsive tr td:last-child{border-bottom:none !important;}
            .table-responsive td::before{content:attr(data-label);flex:0 0 auto;text-align:left;font-size:11px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.3px;white-space:nowrap;}
            .table-responsive td:not([data-label])::before{content:'';}
            .table-responsive td[data-empty]{justify-content:center;text-align:center !important;}
            .table-responsive td[data-empty]::before{content:'';}
            .table-responsive td[data-label="Aksi"]>*{margin-left:auto;}
            .grid{gap:12px !important;}
        }
    </style>
    @stack('styles')
</head>
<body style="background:#f8fafc;font-family:Inter,sans-serif;">
@include('partials.gtm-noscript')
<div class="flex h-screen overflow-hidden">

<div class="mobile-overlay" id="saOverlay" onclick="closeSA()"></div>

<aside class="sa-sidebar w-60 bg-white border-r border-gray-100 flex flex-col flex-shrink-0" id="saSidebar">
    <div style="padding:16px 20px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;">
        <div style="display:flex;align-items:center;gap:10px;">
            @if(!empty($appSettings['app_logo_path']))
                <div style="width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;overflow:hidden;background:#fff;border:1px solid #f1f5f9;">
                    <img src="{{ Storage::url($appSettings['app_logo_path']) }}" alt="{{ $appSettings['app_name'] ?? 'Tokaku' }}" style="width:100%;height:100%;object-fit:contain;">
                </div>
            @else
                <div style="width:36px;height:36px;background:#0F6E56;border-radius:10px;display:flex;align-items:center;justify-content:center;overflow:hidden;">
                    <svg width="16" height="16" viewBox="0 0 18 18" fill="none"><path d="M3 5h12M3 9h8M3 13h5" stroke="white" stroke-width="2" stroke-linecap="round"/></svg>
                </div>
            @endif
            <div>
                <p style="font-size:14px;font-weight:700;color:#0F6E56;">{{ $appSettings['app_name'] ?? 'Tokaku' }}</p>
                <p style="font-size:11px;color:#9ca3af;">Super Admin</p>
            </div>
        </div>
        <button onclick="closeSA()" class="lg:hidden" style="background:none;border:none;cursor:pointer;color:#9ca3af;">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    <nav style="flex:1;padding:12px;overflow-y:auto;display:flex;flex-direction:column;gap:2px;">
        <p style="font-size:10.5px;font-weight:600;color:#9ca3af;letter-spacing:0.8px;text-transform:uppercase;padding:4px 14px 6px;margin-top:4px;">Overview</p>

        <a href="{{ route('superadmin.dashboard') }}" class="sa-link {{ request()->routeIs('superadmin.dashboard')?'active':'' }}">
            <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Dashboard
        </a>

        <p style="font-size:10.5px;font-weight:600;color:#9ca3af;letter-spacing:0.8px;text-transform:uppercase;padding:4px 14px 6px;margin-top:8px;">Manajemen</p>

        <a href="{{ route('superadmin.tenants') }}" class="sa-link {{ request()->routeIs('superadmin.tenants')?'active':'' }}">
            <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            Kelola Tenant
        </a>

        <a href="{{ route('superadmin.laporan') }}" class="sa-link {{ request()->routeIs('superadmin.laporan')?'active':'' }}">
            <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            Laporan Global
        </a>

        <a href="{{ route('superadmin.payments') }}" class="sa-link {{ request()->routeIs('superadmin.payments')?'active':'' }}">
            <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            Verifikasi Pembayaran
        </a>

        <a href="{{ route('superadmin.pricing.index') }}" class="sa-link {{ request()->routeIs('superadmin.pricing.*')?'active':'' }}">
            <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5a1.99 1.99 0 011.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.99 1.99 0 013 12V7a4 4 0 014-4z"/></svg>
            Kelola Harga
        </a>

        <a href="{{ route('superadmin.users') }}" class="sa-link {{ request()->routeIs('superadmin.users')?'active':'' }}">
            <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Semua User
        </a>

        <a href="{{ route('superadmin.settings') }}" class="sa-link {{ request()->routeIs('superadmin.settings')?'active':'' }}">
            <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.607 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Pengaturan
        </a>


        <a href="{{ route('superadmin.maintenance') }}" class="sa-link {{ request()->routeIs('superadmin.maintenance*')?'active':'' }}">
            <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 9l3 3-3 3m5 0h3M5 5h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z"/></svg>
            Maintenance
        </a>
    </nav>

    <div style="padding:10px;border-top:1px solid #f1f5f9;">
        <div style="display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px;" class="hover:bg-gray-50 group">
            <div style="width:32px;height:32px;background:#dcfce9;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <span style="color:#0F6E56;font-size:12px;font-weight:700;">{{ strtoupper(substr(auth()->user()->name,0,2)) }}</span>
            </div>
            <div style="flex:1;min-width:0;">
                <p style="font-size:13px;font-weight:500;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ auth()->user()->name }}</p>
                <p style="font-size:11px;color:#9ca3af;">Super Admin</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">@csrf
                <button type="submit" style="background:none;border:none;cursor:pointer;color:#d1d5db;" onmouseover="this.style.color='#f43f5e'" onmouseout="this.style.color='#d1d5db'">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </button>
            </form>
        </div>
    </div>
</aside>

<main class="flex-1 overflow-y-auto min-w-0">
    <header style="background:#fff;border-bottom:1px solid #f1f5f9;padding:14px 20px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:20;">
        <div style="display:flex;align-items:center;gap:10px;">
            <button onclick="openSA()" class="lg:hidden" style="background:none;border:none;cursor:pointer;color:#6b7280;padding:2px;margin-left:-4px;">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <div>
                <p style="font-size:15px;font-weight:600;color:#0f172a;">@yield('page-title')</p>
                <p style="font-size:12px;color:#9ca3af;margin-top:1px;" class="hidden sm:block">@yield('page-subtitle','')</p>
            </div>
        </div>
        <div style="display:flex;gap:8px;">@yield('header-actions')</div>
    </header>

    <div style="padding:20px;">
        @if(session('success'))
        <div style="display:flex;align-items:center;gap:10px;background:#f0fdf6;border:1px solid #bbf7d2;color:#15803d;font-size:13.5px;border-radius:12px;padding:12px 16px;margin-bottom:16px;">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div style="display:flex;align-items:center;gap:10px;background:#fff1f2;border:1px solid #fecdd3;color:#be123c;font-size:13.5px;border-radius:12px;padding:12px 16px;margin-bottom:16px;">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('error') }}
        </div>
        @endif
        @yield('content')
    </div>
</main>
</div>
<script>
function openSA(){document.getElementById('saSidebar').classList.add('open');document.getElementById('saOverlay').classList.add('open');document.body.style.overflow='hidden';}
function closeSA(){document.getElementById('saSidebar').classList.remove('open');document.getElementById('saOverlay').classList.remove('open');document.body.style.overflow='';}
</script>
@stack('scripts')
</body>
</html>
