@extends('layouts.auth')

@section('title', 'Login Pelanggan — Portal Akses Mandiri')

@section('content')
<div class="w-full">
    {{-- Header --}}
    <div class="text-center mb-10">
        <div class="inline-flex items-center justify-center h-14 w-14 rounded-2xl bg-slate-900 border border-white/10 shadow-lg mb-4">
            <img src="{{ asset('images/logo/djawa_net_biru.png') }}" class="h-7" alt="Logo">
        </div>

        <h1 class="text-2xl font-bold text-white">
            Portal Pelanggan
        </h1>

        <p class="text-xs text-slate-500 tracking-widest uppercase mt-1">
            Akses Khusus Pelanggan Terdaftar
        </p>
    </div>

    {{-- Card --}}
    <div class="bg-slate-900/60 backdrop-blur-xl border border-white/10 rounded-2xl p-8 shadow-xl">
        @if(session('error'))
            <div class="mb-6 text-sm text-red-400 bg-red-500/10 border border-red-500/20 p-3 rounded-lg text-center font-medium">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 text-sm text-red-400 bg-red-500/10 border border-red-500/20 p-3 rounded-lg text-center font-medium">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('portal.authenticate') }}" method="POST" class="space-y-6">
            @csrf

            {{-- Login/ID Field --}}
            <div class="space-y-2">
                <label class="text-xs text-slate-500 uppercase tracking-wider font-medium">
                    ID Pelanggan / Username
                </label>
                <input type="text" 
                       name="login" 
                       value="{{ old('login') }}"
                       placeholder="18817xxx atau Username"
                       required
                       autofocus
                       autocomplete="username"
                       class="w-full px-4 py-3 rounded-lg bg-slate-950 border border-white/10 text-white text-sm 
                              focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 
                              transition-all duration-200 placeholder:text-slate-600">
            </div>

            {{-- Submit Button --}}
            <button type="submit"
                    class="w-full py-3 rounded-lg bg-gradient-to-r from-brand-500 to-brand-600 
                           hover:from-brand-600 hover:to-brand-700 text-white text-sm tracking-widest 
                           uppercase font-semibold transition-all duration-200 active:scale-95 
                           shadow-lg shadow-brand-500/20">
                Masuk ke Portal
            </button>
        </form>
    </div>

    {{-- Footer --}}
    <div class="text-center mt-8">
        <p class="text-[10px] text-slate-600 uppercase tracking-widest">
            Customer Portal v2.0
        </p>
    </div>
</div>
@endsection
