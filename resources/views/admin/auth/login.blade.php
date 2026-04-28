@extends('layouts.auth')

@section('title', 'Login Staff — Akses Teridentifikasi')

@section('content')
<div class="w-full">
    {{-- Header --}}
    <div class="text-center mb-10">
        <div class="inline-flex items-center justify-center h-14 w-14 rounded-2xl bg-slate-900 border border-white/10 shadow-lg mb-4">
            <img src="{{ asset('images/logo/djawa_net_biru.png') }}" class="h-7" alt="Logo">
        </div>

        <h1 class="text-2xl font-bold text-white">
            Staff Login
        </h1>

        <p class="text-xs text-slate-500 tracking-widest uppercase mt-1">
            Secure Access Panel
        </p>
    </div>

    {{-- Card --}}
    <div class="bg-slate-900/60 backdrop-blur-xl border border-white/10 rounded-2xl p-8 shadow-xl">
        @if($errors->any())
            <div class="mb-6 text-sm text-red-400 bg-red-500/10 border border-red-500/20 p-3 rounded-lg text-center">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="/login" method="POST" class="space-y-6">
            @csrf

            {{-- Email Field --}}
            <div class="space-y-2">
                <label class="text-xs text-slate-500 uppercase tracking-wider font-medium">
                    Email Address
                </label>
                <input type="email" 
                       name="email" 
                       value="{{ old('email') }}"
                       placeholder="name@djavva.net"
                       required
                       class="w-full px-4 py-3 rounded-lg bg-slate-950 border border-white/10 text-white text-sm 
                              focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 
                              transition-all duration-200 placeholder:text-slate-600">
            </div>

            {{-- Password Field --}}
            <div class="space-y-2">
                <label class="text-xs text-slate-500 uppercase tracking-wider font-medium">
                    Password
                </label>
                <div class="relative flex items-center">
                    <input type="password" 
                           name="password" 
                           id="password"
                           placeholder="••••••••"
                           required
                           class="w-full px-4 py-3 pr-12 rounded-lg bg-slate-950 border border-white/10 text-white text-sm 
                                  focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 
                                  transition-all duration-200 placeholder:text-slate-600">
                    
                    <button type="button" 
                            onclick="togglePassword()"
                            class="absolute right-0 inset-y-0 w-12 flex items-center justify-center text-slate-500 hover:text-slate-300 transition-colors duration-200 focus:outline-none">
                        <svg id="eye-icon" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg id="eye-off-icon" class="w-4 h-4 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7 .67-2.138 1.97-3.964 3.67-5.271m3.447-1.645A9.956 9.956 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-1.293 2.53M9.878 9.879a3 3 0 104.243 4.243M3 3l18 18" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Role Selection --}}
            <div class="space-y-2">
                <label class="text-xs text-slate-500 uppercase tracking-wider font-medium">
                    Access Role
                </label>
                <select name="role"
                        class="w-full px-4 py-3 rounded-lg bg-slate-950 border border-white/10 text-white text-sm 
                               focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 
                               transition-all duration-200 cursor-pointer">
                    <option value="admin" class="bg-slate-900">Administrator</option>
                    <option value="teknisi" class="bg-slate-900">Teknisi / Lapangan</option>
                    <option value="sales" class="bg-slate-900">Sales / Billing</option>
                </select>
            </div>

            {{-- Remember Me --}}
            <div class="flex items-center gap-3">
                <input type="checkbox" 
                       name="remember" 
                       id="remember"
                       class="w-4 h-4 rounded border-white/20 bg-slate-950 text-brand-500 
                              focus:ring-2 focus:ring-brand-500/30 focus:ring-offset-0
                              accent-brand-500">
                <label for="remember" class="text-sm text-slate-400 cursor-pointer select-none">
                    Remember me
                </label>
            </div>

            {{-- Submit Button --}}
            <button type="submit"
                    class="w-full py-3 rounded-lg bg-gradient-to-r from-brand-500 to-brand-600 
                           hover:from-brand-600 hover:to-brand-700 text-white text-sm tracking-widest 
                           uppercase font-semibold transition-all duration-200 active:scale-95 
                           shadow-lg shadow-brand-500/20">
                Login
            </button>
        </form>
    </div>

    {{-- Footer --}}
    <div class="text-center mt-8">
        <p class="text-[10px] text-slate-600 uppercase tracking-widest">
            System Core v4.0
        </p>
    </div>
</div>
@endsection

@section('scripts')
<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eye-icon');
    const eyeOffIcon = document.getElementById('eye-off-icon');
    const isHidden = passwordInput.getAttribute('type') === 'password';
    passwordInput.setAttribute('type', isHidden ? 'text' : 'password');
    eyeIcon.classList.toggle('hidden', isHidden);
    eyeOffIcon.classList.toggle('hidden', !isHidden);
}
</script>
@endsection