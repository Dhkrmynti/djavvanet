<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Login') — PT Djavva Network Solution</title>

    <link rel="icon" type="image/png" href="{{ asset('images/logo/djawa_net_biru.png') }}">

    <link rel="dns-prefetch" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@600;700;800;900&display=swap" rel="stylesheet">

    <!-- Critical CSS (Anti-Flicker) -->
    <style>
        html { 
            background-color: #020617 !important; 
            visibility: hidden; 
        }
        body { 
            background-color: #020617 !important;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif; 
            overflow-x: hidden; 
            color: #94a3b8;
        }
        .glass-card {
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
    </style>

    <script>
        // Immediatelly show HTML to prevent white flash if JS or CSS takes too long
        document.addEventListener("DOMContentLoaded", function() {
            document.documentElement.style.visibility = "visible";
        });
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#020617] text-slate-300 selection:bg-brand-500/30 selection:text-white flex flex-col items-center justify-center p-4">

    <!-- Background Decoration -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden z-0" aria-hidden="true">
        <div class="absolute top-[-10%] right-[-10%] w-[600px] h-[600px] rounded-full" style="background: radial-gradient(circle, rgba(14,165,233,0.08) 0%, transparent 70%);"></div>
        <div class="absolute bottom-[-10%] left-[-10%] w-[600px] h-[600px] rounded-full" style="background: radial-gradient(circle, rgba(37,99,235,0.06) 0%, transparent 70%);"></div>
    </div>

    <div class="w-full max-w-[360px] z-10 px-4">
        @yield('content')
    </div>

    @stack('scripts')
    @yield('scripts')
</body>
</html>
