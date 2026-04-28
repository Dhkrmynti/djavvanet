@extends('layouts.portal')

@section('title', 'Pusat Pembayaran — ' . $invoice->invoice_number)

@section('content')
<div class="min-h-[80vh] flex items-center justify-center p-4 md:p-8">
    <div class="max-w-4xl w-full relative group fade-up">
        {{-- Aksen Sudut Premium --}}
        <div class="absolute -top-4 -left-4 w-12 h-12 border-t-2 border-l-2 border-brand-500/30"></div>
        <div class="absolute -bottom-4 -right-4 w-12 h-12 border-b-2 border-r-2 border-brand-500/30"></div>

        <div class="glass-card rounded-[32px] md:rounded-[40px] overflow-hidden border-white/10 shadow-2xl">
            <div class="grid grid-cols-1 md:grid-cols-2">
                
                {{-- Left Side: Invoice Summary --}}
                <div class="p-8 md:p-10 bg-white/[0.02] border-b md:border-b-0 md:border-r border-white/5">
                    <div class="flex items-center gap-3 mb-6">
                        <img src="{{ asset('images/logo/djawa_net_biru.png') }}" alt="Logo" class="h-8 w-auto">
                        <span class="text-[10px] font-black uppercase tracking-[0.3em] text-white">Sistem Pembayaran</span>
                    </div>

                    <div class="space-y-8">
                        <div>
                            <p class="text-[9px] font-black uppercase tracking-widest text-slate-600 mb-2">Tagihan Untuk</p>
                            <h2 class="text-2xl font-black text-white tracking-tight">{{ $invoice->customer->full_name }}</h2>
                            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-1">ID Pelanggan: {{ $invoice->customer->username ?? $invoice->customer->customer_code }}</p>
                        </div>

                        <div class="bg-white/5 rounded-3xl p-6 border border-white/5">
                            <p class="text-[9px] font-black uppercase tracking-widest text-slate-600 mb-4">Rincian Paket</p>
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-xs text-slate-400 font-bold uppercase tracking-wider">{{ $invoice->customer->package->name }}</span>
                            </div>
                            <div class="flex justify-between items-center text-[11px]">
                                <span class="text-slate-500 font-bold uppercase tracking-widest">Periode</span>
                                <span class="text-slate-300 font-black">{{ \Carbon\Carbon::parse($invoice->period_start)->translatedFormat('F Y') }}</span>
                            </div>
                        </div>

                        <div class="pt-6">
                            <p class="text-[9px] font-black uppercase tracking-widest text-slate-600 mb-2">No. Tagihan</p>
                            <p class="text-xs font-mono text-brand-400">#{{ $invoice->invoice_number }}</p>
                        </div>
                    </div>
                </div>

                {{-- Right Side: Payment Total & Action --}}
                <div class="p-8 md:p-12 flex flex-col justify-between bg-slate-900/50">
                    <div class="text-right">
                        @if (session('error'))
                            <div class="mb-4 rounded-xl border border-red-500/40 bg-red-500/10 px-4 py-3 text-left text-xs font-bold text-red-200">
                                {{ session('error') }}
                            </div>
                        @endif
                        @if (session('success'))
                            <div class="mb-4 rounded-xl border border-emerald-500/40 bg-emerald-500/10 px-4 py-3 text-left text-xs font-bold text-emerald-200">
                                {{ session('success') }}
                            </div>
                        @endif
                        <div class="inline-block px-4 py-1.5 bg-amber-500/10 text-amber-500 rounded-full border border-amber-500/20 text-[9px] font-black uppercase tracking-widest mb-6">
                            Menunggu Pembayaran
                        </div>
                    </div>

                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.4em] text-slate-500 mb-2">Tagihan Layanan</p>
                        <h1 class="text-4xl md:text-6xl font-black text-white tracking-tighter mb-4">
                            Rp{{ number_format($invoice->total_amount, 0, ',', '.') }}
                        </h1>
                        <p class="text-[10px] text-slate-600 font-bold leading-relaxed max-w-[280px]">
                            Biaya paket internet belum termasuk biaya admin pihak ketiga (Payment Gateway).
                        </p>
                    </div>

                    <div class="mt-8 space-y-3">
                        <p class="text-[9px] font-black uppercase tracking-[0.3em] text-brand-400/60 mb-2">Pilih Metode Pembayaran</p>
                        
                        @foreach($fees as $fee)
                        @php
                            $calculatedFee = $fee->calculateFee($invoice->total_amount);
                            $totalWithFee = $invoice->total_amount + $calculatedFee;
                        @endphp
                        <a href="{{ route('payment.checkout', ['hash' => $invoice->payment_hash, 'fee_id' => $fee->id]) }}" 
                           class="w-full h-14 flex items-center justify-between px-5 bg-white/[0.03] hover:bg-white/[0.08] border border-white/5 hover:border-brand-500/30 text-white rounded-2xl text-[10px] font-bold uppercase tracking-wider active:scale-[0.98] transition-all group">
                            <div class="flex flex-col items-start leading-tight">
                                <span class="group-hover:text-brand-400 transition-colors">{{ $fee->name }}</span>
                                <span class="text-[8px] text-slate-500 font-medium lowercase italic">biaya: +Rp{{ number_format($calculatedFee, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-slate-200 font-black">Rp{{ number_format($totalWithFee, 0, ',', '.') }}</span>
                                <svg class="h-3 w-3 text-slate-600 group-hover:text-brand-400 group-hover:translate-x-1 transition-all" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                            </div>
                        </a>
                        @endforeach

                        <div class="pt-6 flex items-center justify-center gap-6 opacity-30 grayscale hover:grayscale-0 transition-all duration-500">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/e/eb/Logo_Xendit.png" alt="Xendit" class="h-3 w-auto">
                            <div class="h-3 w-px bg-white/20"></div>
                            <span class="text-[7px] font-black text-white uppercase tracking-[0.2em]">Payment Gateway Terverifikasi</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Footer Note --}}
        <div class="mt-8 text-center">
            <p class="text-[9px] font-bold text-slate-700 uppercase tracking-widest">
                &copy; {{ date('Y') }} PT DJAVVA NETWORK SOLUTION — Halaman Resmi Penagihan
            </p>
        </div>
    </div>
</div>
@endsection

