<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('partials.tracking')
    @if(!empty($appSettings['app_favicon']))
        <link rel="icon" href="{{ Storage::url($appSettings['app_favicon']) }}">
    @endif
    <title>Daftar — {{ $appSettings['app_name'] ?? 'Tokaku' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: {
            fontFamily: { sans: ['Inter','sans-serif'] },
            colors: { primary: { 50:'#f0fdf6',100:'#dcfce9',200:'#bbf7d2',700:'#0F6E56',800:'#085041' } }
        } } }
    </script>
    <style>* { -webkit-font-smoothing:antialiased; }</style>
</head>
<body class="min-h-screen bg-gray-50 flex font-sans" style="font-family:Inter,sans-serif;">
@include('partials.gtm-noscript')

    {{-- Left branding --}}
    <div class="hidden lg:flex lg:w-5/12 xl:w-1/2 bg-primary-700 flex-col justify-between p-12 relative overflow-hidden">
        <div class="absolute -top-20 -right-20 w-64 h-64 bg-white/5 rounded-full"></div>
        <div class="absolute -bottom-10 right-20 w-52 h-52 bg-white/5 rounded-full"></div>
        <div class="relative z-10 flex items-center gap-3">
            @if(!empty($appSettings['app_logo_full']))
                <img src="{{ Storage::url($appSettings['app_logo_full']) }}" alt="{{ $appSettings['app_name'] ?? 'Tokaku' }}" style="height:40px;max-width:200px;object-fit:contain;filter:brightness(0) invert(1);">
            @else
                <span class="text-white text-2xl font-bold">{{ $appSettings['app_name'] ?? 'Tokaku' }}</span>
            @endif
        </div>
        <div class="relative z-10">
            <h2 class="text-white text-3xl font-bold leading-tight mb-3">Kelola toko Anda<br>jadi lebih mudah</h2>
            <p class="text-primary-100 text-sm leading-relaxed max-w-sm">Daftar sekarang dan mulai kelola penjualan, stok, dan laporan dalam satu aplikasi kasir.</p>
        </div>
        <div class="relative z-10 text-primary-200 text-xs">&copy; {{ date('Y') }} {{ $appSettings['app_name'] ?? 'Tokaku' }}</div>
    </div>

    {{-- Right form --}}
    <div class="flex-1 flex items-center justify-center p-6 lg:p-12 overflow-y-auto">
        <div class="w-full max-w-md py-8">

            @if(session('registered'))
                {{-- State sukses pendaftaran --}}
                <div class="text-center">
                    <div class="w-16 h-16 mx-auto rounded-full bg-primary-100 flex items-center justify-center mb-5">
                        <svg width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="#0F6E56" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">Pendaftaran Berhasil!</h1>
                    <p class="text-gray-500 text-sm leading-relaxed mb-6">Terima kasih sudah mendaftar. Akun Anda sedang <b>menunggu persetujuan</b> administrator. Kami akan mengaktifkannya secepatnya — silakan coba masuk beberapa saat lagi.</p>
                    <a href="{{ route('login') }}" class="inline-block w-full bg-primary-700 hover:bg-primary-800 text-white font-semibold py-3 rounded-xl transition">Ke Halaman Masuk</a>
                </div>
            @else
                <h1 class="text-2xl font-bold text-gray-900 mb-1">Buat Akun Toko</h1>
                <p class="text-gray-500 text-sm mb-6">Isi data di bawah untuk mulai mencoba gratis.</p>

                @if($errors->any())
                    <div class="bg-rose-50 border border-rose-200 text-rose-700 text-sm rounded-xl px-4 py-3 mb-5">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('register.post') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nama Lengkap *</label>
                        <input type="text" name="owner_name" value="{{ old('owner_name') }}" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-primary-700" placeholder="Nama Anda">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Email *</label>
                        <input type="email" name="email" value="{{ old('email') }}" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-primary-700" placeholder="email@contoh.com">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nomor HP *</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" required inputmode="numeric" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-primary-700" placeholder="08xxxxxxxxxx">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nama Usaha *</label>
                        <input type="text" name="business_name" value="{{ old('business_name') }}" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-primary-700" placeholder="mis. Warung Makan Berkah">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Jenis Usaha *</label>
                        <input type="text" name="business_type" value="{{ old('business_type') }}" required list="bizTypes" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-primary-700" placeholder="mis. Kuliner, Retail, Minuman">
                        <datalist id="bizTypes">
                            <option value="Kuliner / Makanan"><option value="Minuman / Cafe"><option value="Retail / Toko Kelontong"><option value="Fashion"><option value="Jasa"><option value="Lainnya">
                        </datalist>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Password *</label>
                        <input type="password" name="password" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-primary-700" placeholder="Minimal 8 karakter">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Ulangi Password *</label>
                        <input type="password" name="password_confirmation" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-primary-700" placeholder="Ketik ulang password">
                    </div>

                    <button type="submit" class="w-full bg-primary-700 hover:bg-primary-800 text-white font-semibold py-3 rounded-xl transition mt-2">Daftar Sekarang</button>
                </form>

                <p class="text-center text-sm text-gray-500 mt-6">Sudah punya akun?
                    <a href="{{ route('login') }}" class="text-primary-700 font-semibold hover:underline">Masuk di sini</a>
                </p>
            @endif

        </div>
    </div>
</body>
</html>
