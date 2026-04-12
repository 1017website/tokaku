<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — {{ $currentTenant->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">

{{-- Navbar --}}
<nav class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
    <div class="flex items-center gap-2">
        <div class="w-8 h-8 bg-emerald-700 rounded-lg flex items-center justify-center">
            <span class="text-white font-bold text-sm">T</span>
        </div>
        <span class="font-semibold text-gray-800">{{ $currentTenant->name }}</span>
    </div>
    <div class="flex items-center gap-4">
        <span class="text-sm text-gray-500">{{ auth()->user()->name }}</span>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="text-sm text-gray-500 hover:text-red-600 transition-colors">Keluar</button>
        </form>
    </div>
</nav>

<div class="max-w-6xl mx-auto px-6 py-8">

    {{-- Alert --}}
    @if (session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-lg px-4 py-3 mb-6">
            {{ session('success') }}
        </div>
    @endif

    {{-- Metric Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs text-gray-400 mb-1">Omzet hari ini</p>
            <p class="text-xl font-semibold text-gray-800">Rp {{ number_format($todayRevenue, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs text-gray-400 mb-1">Transaksi hari ini</p>
            <p class="text-xl font-semibold text-gray-800">{{ $todayCount }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs text-gray-400 mb-1">Omzet bulan ini</p>
            <p class="text-xl font-semibold text-gray-800">Rp {{ number_format($monthRevenue, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs text-gray-400 mb-1">Transaksi bulan ini</p>
            <p class="text-xl font-semibold text-gray-800">{{ $monthCount }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- Stok Menipis --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="font-medium text-gray-800 mb-4">Stok menipis</h2>
            @if ($lowStockProducts->isEmpty())
                <p class="text-sm text-gray-400">Semua stok aman.</p>
            @else
                <div class="space-y-3">
                    @foreach ($lowStockProducts as $product)
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-700">{{ $product->name }}</p>
                                <p class="text-xs text-gray-400">{{ $product->category?->name ?? '-' }}</p>
                            </div>
                            <span class="text-sm font-semibold {{ $product->stock <= 0 ? 'text-red-600' : 'text-amber-600' }}">
                                {{ $product->stock }} sisa
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Transaksi Terbaru --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="font-medium text-gray-800 mb-4">Transaksi terbaru</h2>
            @if ($recentTransactions->isEmpty())
                <p class="text-sm text-gray-400">Belum ada transaksi.</p>
            @else
                <div class="space-y-3">
                    @foreach ($recentTransactions as $trx)
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-700">{{ $trx->invoice_no }}</p>
                                <p class="text-xs text-gray-400">{{ $trx->user->name }} &middot; {{ $trx->created_at->diffForHumans() }}</p>
                            </div>
                            <span class="text-sm font-semibold text-gray-800">
                                Rp {{ number_format($trx->total, 0, ',', '.') }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>

    {{-- Menu Navigasi --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
        <a href="{{ route('tenant.kasir.index') }}" class="bg-emerald-700 hover:bg-emerald-800 text-white rounded-xl p-5 text-center transition-colors">
            <p class="font-medium">Kasir</p>
            <p class="text-xs text-emerald-200 mt-1">Buat transaksi</p>
        </a>
        <a href="{{ route('tenant.products.index') }}" class="bg-white hover:bg-gray-50 border border-gray-200 rounded-xl p-5 text-center transition-colors">
            <p class="font-medium text-gray-700">Produk</p>
            <p class="text-xs text-gray-400 mt-1">Kelola stok</p>
        </a>
        <a href="{{ route('tenant.laporan.index') }}" class="bg-white hover:bg-gray-50 border border-gray-200 rounded-xl p-5 text-center transition-colors">
            <p class="font-medium text-gray-700">Laporan</p>
            <p class="text-xs text-gray-400 mt-1">Omzet & transaksi</p>
        </a>
        @if (auth()->user()->isAdmin())
        <a href="{{ route('tenant.profil') }}" class="bg-white hover:bg-gray-50 border border-gray-200 rounded-xl p-5 text-center transition-colors">
            <p class="font-medium text-gray-700">Pengaturan</p>
            <p class="text-xs text-gray-400 mt-1">Profil toko</p>
        </a>
        @endif
    </div>

</div>
</body>
</html>
