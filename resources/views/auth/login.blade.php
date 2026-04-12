<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — {{ isset($currentTenant) ? $currentTenant->name : 'Tokaku' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md">

        {{-- Logo --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center gap-2">
                <div class="w-10 h-10 bg-emerald-700 rounded-xl flex items-center justify-center">
                    <span class="text-white font-bold text-lg">T</span>
                </div>
                <span class="text-2xl font-semibold text-gray-800">
                    {{ isset($currentTenant) ? $currentTenant->name : 'Tokaku' }}
                </span>
            </div>
            @isset($currentTenant)
                <p class="text-sm text-gray-400 mt-1">Masuk ke dashboard toko Anda</p>
            @endisset
        </div>

        {{-- Form --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-8">
            <form method="POST" action="{{ route('login.post') }}">
                @csrf

                {{-- Error --}}
                @if ($errors->any())
                    <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-3 mb-5">
                        {{ $errors->first() }}
                    </div>
                @endif

                {{-- Email --}}
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="email@toko.com"
                        autofocus
                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                    >
                </div>

                {{-- Password --}}
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                    <input
                        type="password"
                        name="password"
                        placeholder="••••••••"
                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                    >
                </div>

                {{-- Remember --}}
                <div class="flex items-center mb-6">
                    <input type="checkbox" name="remember" id="remember" class="rounded text-emerald-600">
                    <label for="remember" class="ml-2 text-sm text-gray-600">Ingat saya</label>
                </div>

                <button type="submit" class="w-full bg-emerald-700 hover:bg-emerald-800 text-white font-medium py-2.5 rounded-lg text-sm transition-colors">
                    Masuk
                </button>
            </form>
        </div>

        <p class="text-center text-xs text-gray-400 mt-6">
            Tokaku &copy; {{ date('Y') }} &middot; by 1017studios.id
        </p>
    </div>
</body>
</html>
