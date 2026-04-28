<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'DJAVVA') }} | Premium Fiber Connection</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo/djawa_net_biru.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .reveal { opacity: 0; transform: translateY(30px); transition: all 1s cubic-bezier(0.2, 0.8, 0.2, 1); }
        .reveal.active { opacity: 1; transform: translateY(0); }
        .animate-float { animation: float 10s ease-in-out infinite; }
        @keyframes float { 
            0%, 100% { transform: translateY(0px) rotate(0deg); } 
            50% { transform: translateY(-20px) rotate(1deg); } 
        }
        .faq-item[open] summary svg { transform: rotate(180deg); }
        .faq-item summary::-webkit-details-marker { display: none; }
    </style>
</head>

<body class="bg-[#020617] text-slate-400 font-sans selection:bg-brand-500/30 selection:text-white overflow-x-hidden">

    <!-- Background Layer -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden z-[-1]">
        <div class="absolute top-[10%] left-[-10%] w-[500px] h-[500px] bg-brand-500/5 blur-[120px] rounded-full animate-pulse"></div>
        <div class="absolute bottom-[20%] right-[-10%] w-[500px] h-[500px] bg-blue-600/5 blur-[120px] rounded-full" style="animation-delay: -5s;"></div>
    </div>

    <style>
        @media (min-width: 768px) {
            #mobile-menu-btn { display: none !important; }
            #mobile-menu { display: none !important; }
        }
    </style>
    <!-- Floating Navbar -->
    <header class="fixed top-8 left-1/2 -translate-x-1/2 z-[100] w-[95%] md:w-auto" id="navbar">
        <nav class="glass-pill rounded-full px-6 py-3 flex items-center justify-between gap-12 text-white">
            <a href="/" class="flex items-center gap-3">
                <img src="{{ asset('images/logo/djawa_net_biru.png') }}" alt="Logo" class="h-10 w-auto">
            </a>
            <div class="hidden md:flex items-center gap-10 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">
                <a href="#paket" class="hover:text-brand-400 transition">Paket</a>
                <a href="#faq" class="hover:text-brand-400 transition">FAQ</a>
                <a href="{{ route('portal.login') }}" class="px-6 py-2.5 rounded-full border border-white/10 bg-white/5 text-white hover:bg-brand-500 hover:border-brand-500 transition-all font-display font-black tracking-widest text-[9px]">PORTAL</a>
            </div>
            
            <button id="mobile-menu-btn" class="md:hidden text-white p-2 focus:outline-none hover:bg-white/5 rounded-lg transition-colors">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path id="menu-icon" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                    <path id="close-icon" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </nav>

        <!-- Mobile Menu Overlay -->
        <div id="mobile-menu" class="hidden absolute top-20 left-0 w-full px-4 md:hidden">
            <div class="glass-pill rounded-[32px] p-8 flex flex-col items-center gap-6 text-white shadow-2xl border-white/10">
                <a href="#paket" class="text-[12px] font-black uppercase tracking-[0.3em] hover:text-brand-400 transition" onclick="toggleMobileMenu()">Paket</a>
                <a href="#faq" class="text-[12px] font-black uppercase tracking-[0.3em] hover:text-brand-400 transition" onclick="toggleMobileMenu()">FAQ</a>
                <div class="w-full h-px bg-white/5 my-2"></div>
                <a href="{{ route('portal.login') }}" class="w-full bg-white text-black py-4 rounded-full text-center text-[10px] font-black uppercase tracking-widest shadow-xl">Portal Pelanggan</a>
            </div>
        </div>
    </header>

    <main class="relative z-10">
        <!-- Split Hero Section -->
        <section id="beranda" class="min-h-screen flex items-center px-5 pt-32 pb-12 max-w-7xl mx-auto">
            <div class="grid lg:grid-cols-[1.1fr_0.9fr] gap-12 lg:gap-24 items-center">
                <div class="reveal active space-y-10 text-center lg:text-left">
                    <p class="inline-flex items-center gap-2 rounded-full border border-brand-400/20 bg-brand-400/5 px-6 py-2 text-[10px] font-black uppercase tracking-[0.4em] text-brand-400">
                        Future of Fiber
                    </p>
                    <h1 class="font-display text-5xl md:text-7xl xl:text-8xl font-black leading-[1] tracking-tight text-white mb-8">
                        Koneksi <br><span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-400 to-white">Masa Depan.</span>
                    </h1>
                    <p class="text-lg md:text-xl text-slate-400 leading-relaxed font-medium max-w-xl mx-auto lg:mx-0 opacity-70">
                        Internet fiber murni dengan kecepatan simetris untuk produktivitas tanpa kompromi.
                    </p>
                    <div class="flex flex-col md:flex-row items-center gap-6 pt-4 justify-center lg:justify-start">
                        <a href="#paket" class="w-full md:w-auto bg-white text-black px-12 py-5 rounded-full font-display text-lg font-black transition-all hover:scale-105 shadow-xl active:scale-95">
                            Lihat Paket
                        </a>
                    </div>
                </div>

                <div class="reveal active relative hidden lg:block animate-float" style="transition-delay: 200ms;">
                    <div class="hero-visual-card relative rounded-[56px] p-6 overflow-hidden">
                        <img src="{{ asset('images/hero_visual.png') }}" alt="Tech Visual" class="w-full h-auto rounded-[40px] shadow-2xl">
                    </div>
                    <div class="absolute -bottom-6 -right-6 glass-bento rounded-[28px] p-6 px-10 border-brand-400/30">
                        <p class="text-4xl font-display font-black text-white leading-none">100%</p>
                        <p class="text-[9px] font-black uppercase tracking-[0.2em] text-brand-400 mt-1">Fiber Connection</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Minimalist Stats Section -->
        <section class="max-w-7xl mx-auto px-5 py-24 border-t border-white/5 reveal">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-12 lg:gap-24">
                <div class="text-center md:text-left">
                    <p class="font-display text-4xl font-black text-white mb-2">100%</p>
                    <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-600">Fiber Optic</p>
                </div>
                <div class="text-center md:text-left">
                    <p class="font-display text-4xl font-black text-white mb-2">24/7</p>
                    <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-600">Priority Support</p>
                </div>
                <div class="text-center md:text-left">
                    <p class="font-display text-4xl font-black text-white mb-2">99.9%</p>
                    <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-600">Network Uptime</p>
                </div>
                <div class="text-center md:text-left">
                    <p class="font-display text-4xl font-black text-white mb-2">LOW</p>
                    <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-600">Latency</p>
                </div>
            </div>
        </section>

        <!-- The Journey (Onboarding) -->
        <section class="py-32 px-5 max-w-7xl mx-auto overflow-hidden">
            <div class="grid lg:grid-cols-3 gap-12 relative">
                <div class="reveal">
                    <p class="text-[4rem] font-display font-black text-brand-500/10 leading-none mb-4">01</p>
                    <h3 class="text-2xl font-display font-black text-white mb-4 uppercase">Pilih Paket</h3>
                    <p class="text-sm text-slate-500 leading-relaxed">Tentukan kecepatan yang sesuai dengan gaya hidup digital Anda melalui katalog kami.</p>
                </div>
                <div class="reveal" style="transition-delay: 200ms;">
                    <p class="text-[4rem] font-display font-black text-brand-500/10 leading-none mb-4">02</p>
                    <h3 class="text-2xl font-display font-black text-white mb-4 uppercase">Instalasi</h3>
                    <p class="text-sm text-slate-500 leading-relaxed">Tim technical kami akan melakukan pemasangan perangkat fiber murni dalam waktu < 24 jam.</p>
                </div>
                <div class="reveal" style="transition-delay: 400ms;">
                    <p class="text-[4rem] font-display font-black text-brand-500/10 leading-none mb-4">03</p>
                    <h3 class="text-2xl font-display font-black text-white mb-4 uppercase">Go Digital</h3>
                    <p class="text-sm text-slate-500 leading-relaxed">Nikmati konektivitas premium tanpa batas (unlimited) untuk semua perangkat Anda.</p>
                </div>
            </div>
        </section>

        <!-- Pricing Section -->
        <section id="paket" class="py-32 px-5 max-w-7xl mx-auto">
            <div class="text-center mb-24 reveal">
                <p class="text-[10px] font-black uppercase tracking-[0.3em] text-brand-400 mb-6">Pilihan Paket</p>
                <h2 class="font-display text-5xl md:text-6xl font-black text-white">Langganan Tanpa Batas.</h2>
            </div>

            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($packages as $pkg)
                <article class="reveal glass-bento group rounded-[48px] p-10 flex flex-col justify-between h-full relative overflow-hidden transition-all hover:-translate-y-2 focus-within:ring-2 focus-within:ring-brand-500/50">
                    <div class="relative z-10">
                        <span class="inline-block px-4 py-1.5 rounded-full bg-brand-500/10 border border-brand-400/20 text-[10px] font-black uppercase tracking-widest text-brand-400 mb-8">{{ $pkg->profile ?? 'Personal' }}</span>
                        @if($pkg->badge)
                            <div class="absolute top-0 right-0 py-1.5 px-3 bg-brand-500 text-white text-[9px] font-black uppercase tracking-widest rounded-bl-xl rounded-tr-xl">
                                {{ $pkg->badge }}
                            </div>
                        @endif
                        <h3 class="font-display text-3xl font-bold text-white mb-4 group-hover:text-brand-400 transition-colors">{{ $pkg->name }}</h3>
                        <p class="text-sm text-slate-500 leading-relaxed mb-10">{{ $pkg->description }}</p>
                        
                        <div class="mb-10">
                            <span class="text-[10px] font-black uppercase tracking-widest text-slate-600 block mb-2">Mulai dari</span>
                            <div class="flex items-baseline gap-1">
                                <span class="text-sm font-bold text-white">Rp</span>
                                <span class="text-6xl font-display font-black text-white tracking-tighter">{{ number_format($pkg->monthly_price / 1000, 0) }}k</span>
                                <span class="text-sm font-bold text-slate-600">/bln</span>
                            </div>
                            @if($pkg->installation_fee > 0)
                                <p class="mt-4 text-[9px] font-black uppercase tracking-[0.2em] text-slate-500">
                                    + Biaya Pasang <span class="text-white">Rp{{ number_format($pkg->installation_fee / 1000, 0) }}k</span>
                                </p>
                            @else
                                <p class="mt-4 text-[9px] font-black uppercase tracking-[0.2em] text-brand-400">
                                    GRATIS PEMASANGAN
                                </p>
                            @endif
                        </div>

                        <ul class="space-y-4 mb-12">
                            @if(is_array($pkg->features))
                                @foreach ($pkg->features as $f)
                                <li class="flex items-center gap-3 text-[11px] font-bold text-slate-400">
                                    <div class="h-1 w-1 rounded-full bg-brand-500"></div> {{ $f }}
                                </li>
                                @endforeach
                            @else
                                <li class="flex items-center gap-3 text-[11px] font-bold text-slate-400">
                                    <div class="h-1 w-1 rounded-full bg-brand-500"></div> Unlimited High Speed
                                </li>
                                <li class="flex items-center gap-3 text-[11px] font-bold text-slate-400">
                                    <div class="h-1 w-1 rounded-full bg-brand-500"></div> 24/7 Priority Support
                                </li>
                            @endif
                        </ul>
                    </div>
                    <div class="relative z-10">
                        <a href="https://wa.me/6282246468335?text={{ urlencode('Halo PT Djavva Network Solution, Saya ingin memesan paket ' . $pkg->name) }}" class="w-full inline-flex items-center justify-center gap-3 bg-brand-500 hover:bg-brand-400 text-white py-5 rounded-[24px] font-display font-black uppercase tracking-widest text-[11px] transition-all hover:scale-[1.02] active:scale-95 shadow-xl shadow-brand-500/20">
                            Pesan Sekarang →
                        </a>
                    </div>
                </article>
                @endforeach
            </div>
        </section>

        <!-- FAQ Section (Glass Accordion) -->
        <section id="faq" class="py-32 px-5 max-w-4xl mx-auto">
            <div class="text-center mb-20 reveal">
                <h2 class="font-display text-4xl font-black text-white mb-4">Common Ground.</h2>
                <p class="text-sm text-slate-600 uppercase tracking-widest font-black">Pertanyaan Populer</p>
            </div>
            
            <div class="space-y-4">
                @php
                    $faqs = [
                        ['q' => 'Apakah harga paket sudah flat?', 'a' => 'Ya, semua harga yang tertera adalah flat per bulan tanpa ada biaya tambahan tersembunyi.'],
                        ['q' => 'Berapa lama proses pemasangan baru?', 'a' => 'Proses instalasi biasanya memakan waktu kurang dari 24 jam setelah registrasi divalidasi oleh tim kami.'],
                        ['q' => 'Apakah ada batasan kuota (FUP)?', 'a' => 'Tidak. PT Djavva Network Solution adalah layanan Unlimited murni tanpa kebijakan FUP atau penurunan kecepatan di tengah bulan.'],
                        ['q' => 'Bagaimana jika terjadi kendala teknis?', 'a' => 'Tim Support kami siaga 24/7 dan bisa Anda hubungi melalui tombol WhatsApp di website ini.'],
                    ];
                @endphp
                @foreach ($faqs as $faq)
                <details class="faq-item group glass-bento rounded-[24px] overflow-hidden reveal">
                    <summary class="flex items-center justify-between p-8 cursor-pointer list-none">
                        <span class="font-display text-lg font-bold text-white tracking-tight group-hover:text-brand-400 transition-colors">{{ $faq['q'] }}</span>
                        <svg class="h-6 w-6 text-slate-500 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </summary>
                    <div class="px-8 pb-8">
                        <div class="pt-4 border-t border-white/5 text-slate-400 leading-relaxed">
                            {{ $faq['a'] }}
                        </div>
                    </div>
                </details>
                @endforeach
            </div>
        </section>

        <!-- Footer -->
        <footer class="py-32 border-t border-white/5 bg-[#010411]">
            <div class="max-w-7xl mx-auto px-5">
                <div class="grid md:grid-cols-3 gap-16 md:gap-24 mb-24">
                    <!-- Brand Column -->
                    <div class="reveal">
                        <p class="font-display font-black text-2xl text-white mb-6 uppercase inline-block">PT DJAVVA <span class="text-brand-400">NETWORK SOLUTION</span></p>
                        <p class="text-sm text-slate-600 leading-relaxed max-w-xs">Penyedia layanan internet fiber optik premium dengan standar konektivitas masa depan.</p>
                    </div>

                    <!-- Links Column -->
                    <div class="reveal" style="transition-delay: 100ms;">
                        <p class="text-[10px] font-black uppercase tracking-[0.3em] text-white mb-8">Navigation</p>
                        <ul class="space-y-4 text-[11px] font-bold uppercase tracking-widest text-slate-500">
                            <li><a href="#paket" class="hover:text-brand-400 transition">Paket Layanan</a></li>
                            <li><a href="#faq" class="hover:text-brand-400 transition">Pertanyaan Umum</a></li>
                        </ul>
                    </div>

                    <!-- Location & Contact Column -->
                    <div class="reveal" style="transition-delay: 200ms;">
                        <p class="text-[10px] font-black uppercase tracking-[0.3em] text-white mb-8">Contact Person</p>
                        <div class="space-y-6">
                            <div>
                                <p class="text-[9px] font-black uppercase tracking-widest text-slate-600 mb-2">CS & Technical Support</p>
                                <a href="https://wa.me/6282246468335" class="text-lg font-display font-bold text-white hover:text-brand-400 transition">0822-4646-8335</a>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-400">djavvanetwork@gmail.com</p>
                                <p class="text-sm font-medium text-slate-500 mt-1">Perumahan Gramapuri Persada B02 25, Sukajaya Cibitung Jawa Barat</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col md:flex-row justify-between items-center pt-12 border-t border-white/5 gap-8">
                    <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-800">© 2026 PT DJAVVA NETWORK SOLUTION. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </main>

    <script>
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) entry.target.classList.add('active');
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

        // Navbar Scroll Effect
        window.addEventListener('scroll', () => {
            const nav = document.getElementById('navbar').querySelector('nav');
            if (window.scrollY > 100) {
                nav.style.background = 'rgba(15, 23, 42, 0.9)';
            } else {
                nav.style.background = 'rgba(15, 23, 42, 0.4)';
            }
        });

        // Mobile Menu Toggle
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        const menuIcon = document.getElementById('menu-icon');
        const closeIcon = document.getElementById('close-icon');

        function toggleMobileMenu() {
            mobileMenu.classList.toggle('hidden');
            menuIcon.classList.toggle('hidden');
            closeIcon.classList.toggle('hidden');
        }

        mobileMenuBtn.addEventListener('click', toggleMobileMenu);
    </script>

    <style>
        /* Modern Minimalist Chat Widget — Sleek, Clean, Premium */
        #support-chat-widget * { box-sizing: border-box; }

        /* Floating Toggle Button */
        #support-chat-toggle {
            position: fixed; bottom: 32px; right: 32px; z-index: 99999;
            width: 60px; height: 60px; border-radius: 50%;
            background: #ffffff; border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 16px 32px -8px rgba(0, 0, 0, 0.5);
            cursor: pointer; display: flex; align-items: center; justify-content: center;
            transition: all 0.4s cubic-bezier(0.2, 1, 0.3, 1);
        }
        #support-chat-toggle:hover { transform: scale(1.1) translateY(-4px); box-shadow: 0 24px 48px -12px rgba(0, 0, 0, 0.6); }
        #support-chat-toggle svg { width: 28px; height: 28px; stroke: #020617; stroke-width: 1.5; fill: none; }

        /* Chat Panel */
        .support-inbox-panel {
            position: fixed; bottom: 104px; right: 32px; z-index: 99998;
            width: 360px; max-width: calc(100vw - 3rem);
            height: 500px; max-height: calc(100vh - 160px);
            background: rgba(10, 15, 35, 0.85); backdrop-filter: blur(24px);
            border-radius: 28px; border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 40px 80px -20px rgba(0, 0, 0, 0.8);
            overflow: hidden; transition: all 0.3s ease;
            display: flex; flex-direction: column;
        }
        .support-inbox-panel.hidden { display: none !important; }

        /* Identity Modal */
        .support-modal-overlay {
            position: fixed; inset: 0; z-index: 100000;
            background: rgba(0, 0, 0, 0.6); backdrop-filter: blur(8px);
            display: flex; align-items: center; justify-content: center;
            padding: 2rem; opacity: 0; pointer-events: none; transition: opacity 0.3s ease;
        }
        .support-modal-overlay.active { opacity: 1; pointer-events: auto; }
        
        .support-modal-card {
            background: rgba(15, 23, 42, 0.9); border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 32px; width: 100%; max-width: 400px; padding: 2.5rem;
            box-shadow: 0 24px 48px rgba(0, 0, 0, 0.5);
            transform: scale(0.9) translateY(20px); transition: all 0.4s cubic-bezier(0.2, 1, 0.3, 1);
        }
        .support-modal-overlay.active .support-modal-card { transform: scale(1) translateY(0); }

        .inbox-header {
            padding: 1.25rem 1.5rem; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        .inbox-header h3 { font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 0.95rem; color: #fff; }
        
        #support-chat-room { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
        
        .inbox-messages-area {
            flex: 1; display: flex; flex-direction: column; gap: 0.75rem; padding: 1.25rem;
            overflow-y: auto; scrollbar-width: none;
        }
        .inbox-messages-area::-webkit-scrollbar { display: none; }

        .message-bubble { display: flex; flex-direction: column; max-width: 85%; animation: chatSlide 0.4s cubic-bezier(0.2, 1, 0.3, 1); flex-shrink: 0; }
        @keyframes chatSlide { from { opacity: 0; transform: translateY(12px) scale(0.95); } to { opacity: 1; transform: translateY(0) scale(1); } }
        
        .message-bubble.outgoing { align-items: flex-end; margin-left: auto; }
        .bubble-text { padding: 0.6rem 0.85rem; border-radius: 18px; font-size: 0.8rem; line-height: 1.45; }
        .incoming .bubble-text { background: rgba(255, 255, 255, 0.05); color: #e2e8f0; border-bottom-left-radius: 4px; }
        .outgoing .bubble-text { background: #fff; color: #020617; border-bottom-right-radius: 4px; }
        
        .sender-name { font-size: 0.55rem; font-weight: 700; text-transform: uppercase; margin-bottom: 3px; color: #64748b; letter-spacing: 0.05em; }
        .message-time { font-size: 0.5rem; margin-top: 3px; color: #475569; }

        .inbox-input-area { padding: 1.25rem; flex-shrink: 0; border-top: 1px solid rgba(255, 255, 255, 0.05); }
        .message-input-wrapper {
            background: rgba(255, 255, 255, 0.03); border-radius: 16px; border: 1px solid rgba(255, 255, 255, 0.08);
            display: flex; align-items: center; gap: 8px; padding: 6px 12px; transition: all 0.3s ease;
        }
        .message-input-wrapper:focus-within { border-color: rgba(255, 255, 255, 0.2); background: rgba(255, 255, 255, 0.05); }
        
        .inbox-input-area textarea {
            flex: 1; background: transparent; border: none; color: #fff; font-size: 0.8rem;
            resize: none; outline: none; padding: 4px 0; max-height: 60px; font-family: 'Inter', sans-serif;
        }
        
        .send-btn { background: none; border: none; padding: 4px; color: #fff; cursor: pointer; opacity: 0.4; transition: 0.2s; }
        .send-btn:hover { opacity: 1; transform: scale(1.1); }

        /* Modal specific input styles */
        .modal-input-group { display: flex; flex-direction: column; gap: 1rem; }
        .modal-input-group label { font-size: 0.7rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; }
        .modal-input-group input, .modal-input-group textarea {
            background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px;
            padding: 12px 16px; color: #fff; font-size: 0.85rem; outline: none; transition: 0.3s;
        }
        .modal-input-group input:focus, .modal-input-group textarea:focus { border-color: rgba(255, 255, 255, 0.2); background: rgba(255, 255, 255, 0.05); }
        
        .modal-submit-btn {
            background: #fff; color: #020617; border: none; border-radius: 12px; padding: 14px;
            font-weight: 800; font-size: 0.8rem; cursor: pointer; transition: 0.3s; margin-top: 1rem;
        }
        .modal-submit-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(255, 255, 255, 0.15); }
        .modal-close-btn { position: absolute; top: 1.5rem; right: 1.5rem; color: #475569; cursor: pointer; transition: 0.2s; }
        .modal-close-btn:hover { color: #fff; }
    </style>

    <div id="support-chat-widget">
        <!-- Identity Modal -->
        <div id="support-chat-modal" class="support-modal-overlay">
            <div class="support-modal-card relative">
                <div id="close-chat-modal" class="modal-close-btn">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                <div class="mb-8">
                    <h2 class="text-2xl font-extrabold text-white mb-2 font-serif">Support Chat</h2>
                    <p class="text-slate-400 text-sm font-medium">Bantu kami mengenal Anda lebih baik sebelum memulai.</p>
                </div>
                <div class="modal-input-group">
                    <input type="text" id="support-name" placeholder="Nama Lengkap" autocomplete="off">
                    <input type="tel" id="support-phone" placeholder="Nomor WhatsApp">
                    <textarea id="support-first-message" rows="3" placeholder="Apa yang bisa kami bantu?"></textarea>
                    <button id="support-start-button" class="modal-submit-btn">Mulai Percakapan</button>
                    <div id="support-modal-error" class="text-red-500 text-[10px] font-bold mt-4 text-center hidden"></div>
                </div>
            </div>
        </div>

        <!-- Chat Panel -->
        <div id="support-chat-panel" class="support-inbox-panel hidden">
            <div class="inbox-header">
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 bg-emerald-500 rounded-full shadow-[0_0_8px_#10b981]"></div>
                    <h3>Chat Support</h3>
                </div>
                <button id="close-chat-panel" class="opacity-40 hover:opacity-100 transition-opacity">
                    <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div id="support-chat-room">
                <div id="support-chat-messages" class="inbox-messages-area"></div>
                <div class="inbox-input-area">
                    <div class="message-input-wrapper">
                        <textarea id="support-chat-input" rows="1" placeholder="Tulis pesan..."></textarea>
                        <button id="support-send-button" class="send-btn">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                    </div>
                    <div id="support-chat-status" class="text-[9px] text-center mt-3 text-slate-500 font-bold uppercase tracking-widest opacity-30">Secure Channel</div>
                    <div id="support-chat-typing" class="hidden typing-indicator text-[10px] text-center mt-2 text-slate-400 font-bold italic">Typing...</div>
                </div>
            </div>
        </div>

        <button id="support-chat-toggle">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2v10z" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>
    </div>

    <script>
        (() => {
            const panel = document.getElementById('support-chat-panel');
            const toggle = document.getElementById('support-chat-toggle');
            const modal = document.getElementById('support-chat-modal');
            const roomView = document.getElementById('support-chat-room');
            const closeBtn = document.getElementById('close-chat-panel');
            const closeModalBtn = document.getElementById('close-chat-modal');
            const startButton = document.getElementById('support-start-button');
            const sendButton = document.getElementById('support-send-button');
            const messagesEl = document.getElementById('support-chat-messages');
            const statusEl = document.getElementById('support-chat-status');
            const typingEl = document.getElementById('support-chat-typing');
            const inputEl = document.getElementById('support-chat-input');
            const modalErrorEl = document.getElementById('support-modal-error');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const typingUrl = '{{ route('support.public.typing') }}';
            let publicToken = null;
            const knownMessageIds = new Set();
            let typingTimeout = null;
            let remoteTypingTimeout = null;

            const escapeHtml = (value) => {
                const div = document.createElement('div');
                div.textContent = value ?? '';
                return div.innerHTML;
            };

            const showRoom = () => {
                modal.classList.remove('active');
                panel.classList.remove('hidden');
                setTimeout(() => messagesEl.scrollTop = messagesEl.scrollHeight, 100);
            };

            const renderMessage = (message) => {
                if (knownMessageIds.has(Number(message.id))) return;

                const isCustomer = message.sender_type === 'customer';
                const senderName = isCustomer ? 'Anda' : (message.sender_name || 'Djavva Support');
                const timeLabel = message.created_at_label || new Date().toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'});

                messagesEl.insertAdjacentHTML('beforeend', `
                    <div class="message-bubble ${isCustomer ? 'outgoing' : 'incoming'}" data-message-id="${message.id}">
                        <div class="bubble-wrapper">
                            <div class="sender-name font-sans">${escapeHtml(senderName)}</div>
                            <div class="bubble-text shadow-sm">${escapeHtml(message.message)}</div>
                            <div class="message-time px-3">${escapeHtml(timeLabel)}</div>
                        </div>
                    </div>
                `);
                knownMessageIds.add(Number(message.id));
                messagesEl.scrollTop = messagesEl.scrollHeight;
            };

            const connectChannel = (token) => {
                if (!token) return;

                const attemptConnection = () => {
                    if (!window.Echo) {
                        setTimeout(attemptConnection, 500);
                        return;
                    }

                    window.Echo.channel(`chat.conversation.${token}`)
                        .listen('.chat.message.created', (payload) => {
                            renderMessage(payload.message);
                        })
                        .listen('.chat.typing.updated', (payload) => {
                            if (payload.actor_type !== 'admin') return;

                            if (payload.is_typing) {
                                typingEl.classList.remove('hidden');
                                typingEl.classList.add('flex');
                                clearTimeout(remoteTypingTimeout);
                                remoteTypingTimeout = setTimeout(() => {
                                    typingEl.classList.add('hidden');
                                    typingEl.classList.remove('flex');
                                }, 3000);
                            } else {
                                typingEl.classList.add('hidden');
                                typingEl.classList.remove('flex');
                            }
                        });
                };

                attemptConnection();
            };

            const hydrateConversation = (conversation) => {
                if (!conversation) return;

                publicToken = conversation.public_token;
                messagesEl.innerHTML = '';
                knownMessageIds.clear();
                conversation.messages.forEach(renderMessage);
                showRoom();
                connectChannel(publicToken);
            };

            toggle.addEventListener('click', async () => {
                const isHidden = panel.classList.contains('hidden');
                
                if (!isHidden) {
                    panel.classList.add('hidden');
                    return;
                }

                if (publicToken) {
                    panel.classList.remove('hidden');
                } else {
                    const response = await fetch('{{ route('support.public.bootstrap') }}', {
                        headers: { 'Accept': 'application/json' },
                    });
                    const payload = await response.json();
                    if (payload.conversation) {
                        hydrateConversation(payload.conversation);
                    } else {
                        modal.classList.add('active');
                    }
                }
            });

            closeBtn.addEventListener('click', () => {
                panel.classList.add('hidden');
            });

            closeModalBtn.addEventListener('click', () => {
                modal.classList.remove('active');
            });

            inputEl.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendButton.click();
                }
            });

            inputEl.addEventListener('input', async () => {
                clearTimeout(typingTimeout);
                const socketId = window.Echo?.socketId?.();
                const headers = {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                };
                if (socketId) headers['X-Socket-ID'] = socketId;

                await fetch(typingUrl, {
                    method: 'POST',
                    headers: headers,
                    body: JSON.stringify({ is_typing: true }),
                });

                typingTimeout = setTimeout(async () => {
                    await fetch(typingUrl, {
                        method: 'POST',
                        headers: headers,
                        body: JSON.stringify({ is_typing: false }),
                    });
                }, 1200);
            });

            startButton.addEventListener('click', async () => {
                const name = document.getElementById('support-name').value.trim();
                const phone = document.getElementById('support-phone').value.trim();
                const message = document.getElementById('support-first-message').value.trim();

                if (!name || !phone || !message) {
                    modalErrorEl.textContent = 'Nama, nomor, dan pesan awal wajib diisi.';
                    modalErrorEl.classList.remove('hidden');
                    return;
                }

                modalErrorEl.classList.add('hidden');
                startButton.disabled = true;
                startButton.textContent = 'Menghubungkan...';

                try {
                    const socketId = window.Echo?.socketId?.();
                    const headers = {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    };
                    if (socketId) headers['X-Socket-ID'] = socketId;

                    const response = await fetch('{{ route('support.public.start') }}', {
                        method: 'POST',
                        headers: headers,
                        body: JSON.stringify({ name, phone, message }),
                    });

                    if (!response.ok) {
                        const errorData = await response.json();
                        throw new Error(errorData.message || 'Gagal memulai percakapan.');
                    }

                    const payload = await response.json();
                    hydrateConversation(payload.conversation);
                } catch (error) {
                    modalErrorEl.textContent = error.message || 'Terjadi kesalahan sistem.';
                    modalErrorEl.classList.remove('hidden');
                } finally {
                    startButton.disabled = false;
                    startButton.textContent = 'Mulai Percakapan';
                }
            });

            sendButton.addEventListener('click', async () => {
                const message = inputEl.value.trim();
                if (!message || !publicToken) return;

                sendButton.disabled = true;
                statusEl.textContent = 'Mengirim pesan...';
                clearTimeout(typingTimeout);

                try {
                    const socketId = window.Echo?.socketId?.();
                    const headers = {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    };
                    if (socketId) headers['X-Socket-ID'] = socketId;

                    await fetch(typingUrl, {
                        method: 'POST',
                        headers: headers,
                        body: JSON.stringify({ is_typing: false }),
                    });
                    const response = await fetch('{{ route('support.public.send') }}', {
                        method: 'POST',
                        headers: headers,
                        body: JSON.stringify({ message }),
                    });
                    const payload = await response.json();
                    renderMessage(payload.chat_message);
                    inputEl.value = '';
                    typingEl.classList.add('hidden');
                    statusEl.textContent = 'Pesan terkirim. Menunggu balasan CS...';
                } catch (error) {
                    statusEl.textContent = 'Gagal mengirim pesan.';
                } finally {
                    sendButton.disabled = false;
                }
            });
        })();
    </script>
</body>
</html>
