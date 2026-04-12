<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ $currentTenant->name ?? 'Tokaku' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{fontFamily:{sans:['Inter','sans-serif']},colors:{primary:{50:'#f0fdf6',100:'#dcfce9',200:'#bbf7d2',700:'#0F6E56',800:'#085041'}}}}}</script>
    <style>
        *{-webkit-font-smoothing:antialiased;}
        .sidebar-link{display:flex;align-items:center;gap:10px;padding:9px 14px;border-radius:10px;font-size:13.5px;font-weight:500;color:#6b7280;transition:all 0.15s;text-decoration:none;}
        .sidebar-link:hover{background:#f9fafb;color:#111827;}
        .sidebar-link.active{background:#0F6E56;color:#fff;}
        .btn-primary{display:inline-flex;align-items:center;gap:6px;background:#0F6E56;color:#fff;font-size:13.5px;font-weight:500;padding:9px 16px;border-radius:10px;border:none;cursor:pointer;transition:background 0.15s;text-decoration:none;font-family:Inter,sans-serif;}
        .btn-primary:hover{background:#085041;}
        .btn-secondary{display:inline-flex;align-items:center;gap:6px;background:#fff;color:#374151;font-size:13.5px;font-weight:500;padding:9px 16px;border-radius:10px;border:1.5px solid #e2e8f0;cursor:pointer;transition:all 0.15s;text-decoration:none;font-family:Inter,sans-serif;}
        .btn-secondary:hover{background:#f9fafb;}
        .form-input{width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:10px 14px;font-size:13.5px;font-family:Inter,sans-serif;outline:none;transition:all 0.15s;background:#fafafa;color:#111827;box-sizing:border-box;}
        .form-input:focus{border-color:#0F6E56;box-shadow:0 0 0 3px rgba(15,110,86,0.1);background:#fff;}
        .form-label{display:block;font-size:13px;font-weight:500;color:#374151;margin-bottom:6px;}
        .mobile-nav-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:30;}
        .mobile-nav-overlay.open{display:block;}
        @media(max-width:1023px){.sidebar-desktop{transform:translateX(-100%);position:fixed;z-index:40;height:100vh;transition:transform 0.25s ease;}.sidebar-desktop.open{transform:translateX(0);}}
    </style>
    @stack('styles')
</head>
<body style="background:#f8fafc;font-family:Inter,sans-serif;" class="antialiased">
<div class="flex h-screen overflow-hidden">

<div class="mobile-nav-overlay" id="mobileOverlay" onclick="closeSidebar()"></div>

<aside class="sidebar-desktop w-60 bg-white border-r border-gray-100 flex flex-col flex-shrink-0" id="sidebar">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <div class="flex items-center gap-2.5">
            <div style="width:32px;height:32px;background:#0F6E56;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="16" height="16" viewBox="0 0 18 18" fill="none"><path d="M3 5h12M3 9h8M3 13h5" stroke="white" stroke-width="2" stroke-linecap="round"/></svg>
            </div>
            <div class="leading-tight">
                <p style="font-size:15px;font-weight:700;color:#0F6E56;letter-spacing:-0.3px;">Tokaku</p>
                <p style="font-size:11px;color:#9ca3af;margin-top:1px;" class="truncate max-w-[110px]">{{ $currentTenant->name ?? '' }}</p>
            </div>
        </div>
        <button onclick="closeSidebar()" class="lg:hidden text-gray-400 p-1">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">
        <p style="font-size:10.5px;font-weight:600;color:#9ca3af;letter-spacing:0.8px;text-transform:uppercase;padding:0 14px;margin-bottom:6px;">Menu</p>

        <a href="{{ route('tenant.dashboard') }}" class="sidebar-link {{ request()->routeIs('tenant.dashboard')?'active':'' }}">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Dashboard
        </a>
        <a href="{{ route('tenant.kasir.index') }}" class="sidebar-link {{ request()->routeIs('tenant.kasir.*')?'active':'' }}">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 7H6a2 2 0 00-2 2v9a2 2 0 002 2h9a2 2 0 002-2v-3M9 7V5a2 2 0 012-2h2a2 2 0 012 2v2M9 7h6m-3 4v4m-2-2h4"/></svg>
            Kasir
        </a>
        <a href="{{ route('tenant.products.index') }}" class="sidebar-link {{ request()->routeIs('tenant.products.*')?'active':'' }}">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            Produk
        </a>
        <a href="{{ route('tenant.categories.index') }}" class="sidebar-link {{ request()->routeIs('tenant.categories.*')?'active':'' }}">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
            Kategori
        </a>
        <a href="{{ route('tenant.laporan.index') }}" class="sidebar-link {{ request()->routeIs('tenant.laporan.*')?'active':'' }}">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            Laporan
        </a>

        @if(auth()->user()->isAdmin())
        <a href="{{ route('tenant.users.index') }}" class="sidebar-link {{ request()->routeIs('tenant.users.*')?'active':'' }}">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Tim Toko
        </a>
        <div style="padding-top:12px;">
            <p style="font-size:10.5px;font-weight:600;color:#9ca3af;letter-spacing:0.8px;text-transform:uppercase;padding:0 14px;margin-bottom:6px;">Pengaturan</p>
            <a href="{{ route('tenant.profil') }}" class="sidebar-link {{ request()->routeIs('tenant.profil*')?'active':'' }}">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Pengaturan Toko
            </a>
        </div>
        @endif
    </nav>

    <div class="px-3 py-3 border-t border-gray-100">
        <div class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-gray-50 group transition-colors">
            <div style="width:32px;height:32px;background:#dcfce9;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <span style="color:#0F6E56;font-size:12px;font-weight:700;">{{ strtoupper(substr(auth()->user()->name,0,2)) }}</span>
            </div>
            <div class="flex-1 min-w-0">
                <p style="font-size:13px;font-weight:500;color:#111827;" class="truncate">{{ auth()->user()->name }}</p>
                <p style="font-size:11px;color:#9ca3af;text-transform:capitalize;">{{ auth()->user()->role }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-gray-300 hover:text-red-500 transition-colors opacity-0 group-hover:opacity-100">
                    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </button>
            </form>
        </div>
    </div>
</aside>

<main class="flex-1 overflow-y-auto min-w-0">
    <header class="bg-white border-b border-gray-100 px-4 lg:px-8 py-4 flex items-center justify-between sticky top-0 z-20">
        <div class="flex items-center gap-3">
            <button onclick="openSidebar()" class="lg:hidden text-gray-500 p-1 -ml-1">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <div>
                <h1 style="font-size:15px;font-weight:600;color:#111827;">@yield('page-title','Dashboard')</h1>
                <p style="font-size:12px;color:#9ca3af;margin-top:1px;" class="hidden sm:block">@yield('page-subtitle','')</p>
            </div>
        </div>
        <div class="flex items-center gap-2">@yield('header-actions')</div>
    </header>

    @if(session('success') || session('error'))
    <div class="px-4 lg:px-8 pt-5">
        @if(session('success'))
        <div style="display:flex;align-items:center;gap:10px;background:#f0fdf6;border:1px solid #bbf7d2;color:#15803d;font-size:13.5px;border-radius:12px;padding:12px 16px;">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div style="display:flex;align-items:center;gap:10px;background:#fff1f2;border:1px solid #fecdd3;color:#be123c;font-size:13.5px;border-radius:12px;padding:12px 16px;">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('error') }}
        </div>
        @endif
    </div>
    @endif

    <div class="px-4 lg:px-8 py-6">@yield('content')</div>
</main>
</div>

<script>
function openSidebar(){document.getElementById('sidebar').classList.add('open');document.getElementById('mobileOverlay').classList.add('open');document.body.style.overflow='hidden';}
function closeSidebar(){document.getElementById('sidebar').classList.remove('open');document.getElementById('mobileOverlay').classList.remove('open');document.body.style.overflow='';}
</script>
@stack('scripts')
</body>
</html>
