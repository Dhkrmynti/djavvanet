@extends('layouts.portal')

@section('title', 'Status Pembayaran')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center p-6 lg:p-12">
    <div class="max-w-md w-full glass-card rounded-[48px] p-12 text-center border-white/10 shadow-2xl relative overflow-hidden">
        
        {{-- Background Decoration --}}
        <div class="absolute -top-10 -right-10 w-40 h-40 bg-brand-500/10 blur-3xl opacity-50 rounded-full"></div>

        <div class="relative z-10">
            @if($status === 'success')
                <div class="w-20 h-20 bg-green-500/20 rounded-3xl flex items-center justify-center mx-auto mb-8 border border-green-500/30">
                    <svg class="h-10 w-10 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                </div>
                <h1 class="text-3xl font-black text-white tracking-tight mb-4 uppercase">Pembayaran Berhasil!</h1>
                <p class="text-sm font-bold text-slate-400 leading-relaxed mb-10">
                    Terima kasih! Pembayaran untuk invoice <span class="text-brand-400 font-mono">#{{ $invoice->invoice_number }}</span> telah kami terima dan sinkronisasi ke sistem.
                </p>
            @else
                <div class="w-20 h-20 bg-red-500/20 rounded-3xl flex items-center justify-center mx-auto mb-8 border border-red-500/30">
                    <svg class="h-10 w-10 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                <h1 class="text-3xl font-black text-white tracking-tight mb-4 uppercase">Pembayaran Gagal</h1>
                <p class="text-sm font-bold text-slate-400 leading-relaxed mb-10">
                    Mohon maaf, sepertinya terjadi kendala saat memproses tagihan Anda. Silakan coba beberapa saat lagi atau hubungi support.
                </p>
            @endif

            <div class="space-y-4">
                <a href="{{ route('portal.dashboard') }}" class="block w-full py-4 bg-brand-500 hover:bg-brand-400 text-white rounded-2xl text-[10px] font-black uppercase tracking-[0.3em] transition-all shadow-xl shadow-brand-500/10">
                    Kembali ke Dashboard
                </a>
                <p class="text-[8px] font-black text-slate-700 uppercase tracking-widest pt-6">
                    PT DJAVVA NETWORK SOLUTION — Official Billing System
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

