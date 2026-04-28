<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal Pelanggan') — PT Djavva Network Solution</title>

    <link rel="icon" type="image/png" href="{{ asset('images/logo/djawa_net_biru.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Inter', sans-serif; }
        .glass-card {
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body class="min-h-screen bg-[#020617] text-slate-300 selection:bg-brand-500/30 selection:text-white flex flex-col">

    <!-- Background Decoration -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden z-[-1]" aria-hidden="true">
        <div class="absolute top-[-10%] right-[-10%] w-[600px] h-[600px] rounded-full" style="background: radial-gradient(circle, rgba(14,165,233,0.08) 0%, transparent 70%);"></div>
        <div class="absolute bottom-[-10%] left-[-10%] w-[600px] h-[600px] rounded-full" style="background: radial-gradient(circle, rgba(37,99,235,0.06) 0%, transparent 70%);"></div>
    </div>

    <div class="max-w-6xl mx-auto w-full flex flex-col min-h-screen">
        <!-- Simple Header -->
        <header class="h-20 flex items-center justify-between px-6 md:px-12 border-b border-white/5 bg-[#020617]/50 backdrop-blur-md sticky top-0 z-50 print:hidden rounded-b-[32px]">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/logo/djawa_net_putih.png') }}" alt="Logo" class="h-8 w-auto">
                <span class="h-5 w-[1px] bg-white/10"></span>
                <span class="text-[10px] font-black uppercase tracking-[0.3em] text-brand-400">Portal Pelanggan</span>
            </div>

            @if(auth()->guard('customer')->check())
            <form action="{{ route('portal.logout') }}" method="POST">
                @csrf
                <button type="submit" class="text-[10px] font-black uppercase tracking-widest text-slate-500 hover:text-white transition flex items-center gap-2 group">
                    <span>Keluar</span>
                    <svg class="w-3 h-3 text-slate-600 group-hover:text-red-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </button>
            </form>
            @endif
        </header>

        <main class="flex-1">
            @yield('content')
        </main>

        <footer class="py-10 text-center border-t border-white/5 print:hidden">
            <p class="text-[10px] font-bold text-slate-700 uppercase tracking-[0.2em]">© {{ date('Y') }} PT DJAVVA NETWORK SOLUTION. HAK CIPTA DILINDUNGI.</p>
        </footer>
    </div>

    @yield('scripts')
</body>
</html>
