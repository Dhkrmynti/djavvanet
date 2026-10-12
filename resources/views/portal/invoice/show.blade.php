@extends('layouts.portal')

@section('title', 'Detail Tagihan #' . $invoice->invoice_number)

@section('content')
<div class="max-w-4xl mx-auto p-4 md:p-8 space-y-6 md:space-y-8 print:hidden">
    
    <div class="fade-up">
        <a href="{{ route('portal.dashboard') }}" class="inline-flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.3em] text-slate-700 hover:text-white transition group">
            <svg class="h-3 w-3 group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M15 19l-7-7 7-7"/></svg>
            Kembali ke Dashboard
        </a>
    </div>

    <div class="relative group fade-up" style="animation-delay: 100ms;">
        <div class="glass-card rounded-[32px] md:rounded-[40px] p-6 md:p-10 border-white/10 relative overflow-hidden">
            <!-- Header Invoice -->
            <div class="flex flex-col gap-6 md:flex-row md:justify-between mb-6 md:mb-8 pb-6 md:pb-8 border-b border-white/5">
                <div>
                    <div class="flex items-center gap-3 mb-6">
                        <img src="{{ asset('images/logo/djawa_net_biru.png') }}" alt="Logo" class="h-8 md:h-10 w-auto">
                        <div class="flex flex-col">
                            <span class="text-[10px] md:text-xs font-black uppercase tracking-[0.2em] text-white">PT DJAVVA NETWORK SOLUTION</span>
                            <span class="text-[7px] md:text-[8px] font-bold text-slate-500 uppercase tracking-widest">Digital Provider & Network Solution</span>
                        </div>
                    </div>
                    <h1 class="font-display text-4xl md:text-5xl font-black text-white tracking-tighter">INVOICE</h1>
                    <p class="mt-3 md:mt-4 text-[10px] md:text-[11px] font-black uppercase tracking-[0.3em] md:tracking-[0.4em] text-slate-600">No. Tagihan: <span class="text-brand-400 font-mono tracking-normal">{{ $invoice->invoice_number }}</span></p>
                </div>
                
                <div class="text-left md:text-right">
                    @php
                        $statusLabels = [
                            'unpaid' => ['label' => 'BELUM BAYAR', 'class' => 'bg-amber-500/10 text-amber-500 border-amber-500/20'],
                            'paid' => ['label' => 'LUNAS', 'class' => 'bg-green-500/10 text-green-500 border-green-500/20'],
                            'paid_transfer' => ['label' => 'LUNAS TRANSFER', 'class' => 'bg-green-500/10 text-green-500 border-green-500/20'],
                            'paid_gateway' => ['label' => 'LUNAS GATEWAY', 'class' => 'bg-purple-500/10 text-purple-500 border-purple-500/20'],
                            'paid_cash' => ['label' => 'LUNAS TUNAI', 'class' => 'bg-blue-500/10 text-blue-500 border-blue-500/20'],
                            'overdue' => ['label' => 'TERLAMBAT', 'class' => 'bg-red-500/10 text-red-500 border-red-500/20'],
                        ];
                        $curr = $statusLabels[$invoice->status] ?? ['label' => $invoice->status, 'class' => 'bg-slate-800 text-slate-400'];
                    @endphp
                    <div class="inline-block px-6 md:px-8 py-2 md:py-3 rounded-2xl border font-black text-lg md:text-2xl rotate-[-3deg] tracking-tight {{ $curr['class'] }}">
                        {{ $curr['label'] }}
                    </div>
                </div>
            </div>

            <!-- Customer Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-12 pb-8 md:pb-10 border-b border-white/5 text-cap">
                <div class="space-y-4">
                    <p class="text-[9px] font-black uppercase tracking-[0.3em] text-slate-600">Tagihan Untuk</p>
                    <div class="space-y-1">
                        <p class="text-2xl md:text-3xl font-black text-white tracking-tight">{{ $invoice->customer->full_name }}</p>
                        <p class="text-[10px] font-black text-brand-400 uppercase tracking-widest">{{ $invoice->customer->customer_code }}</p>
                    </div>
                    <p class="text-slate-500 font-medium leading-relaxed text-[11px] max-w-[320px]">
                        {{ $invoice->customer->address }}
                    </p>
                </div>
                
                <div class="grid grid-cols-2 gap-4 h-fit">
                    <div class="bg-white/5 p-4 rounded-3xl border border-white/5">
                        <p class="text-[8px] font-black uppercase tracking-widest text-slate-600 mb-2">Periode</p>
                        <p class="text-sm font-black text-white">{{ \Carbon\Carbon::parse($invoice->period_start)->translatedFormat('F Y') }}</p>
                    </div>
                    <div class="bg-white/5 p-4 rounded-3xl border border-white/5">
                        <p class="text-[8px] font-black uppercase tracking-widest text-slate-600 mb-2">Batas Bayar</p>
                        <p class="text-sm font-black text-red-500">{{ \Carbon\Carbon::parse($invoice->due_date)->translatedFormat('d M Y') }}</p>
                    </div>
                </div>
            </div>

            <div class="py-10">
                <div class="flex justify-between items-center text-xs mb-4">
                    <span class="text-slate-400 font-medium">Layanan Internet — {{ $invoice->customer->package->name }}</span>
                    <span class="text-white font-black">Rp{{ number_format($invoice->amount, 0, ',', '.') }}</span>
                </div>
                
                <div class="mt-6 pt-6 border-t border-brand-500/20 text-left md:text-right">
                    <p class="text-[9px] md:text-[10px] font-black uppercase tracking-[0.3em] md:tracking-[0.5em] text-slate-600">Total Pembayaran</p>
                    <p class="text-3xl md:text-5xl font-black text-white tracking-tighter">Rp{{ number_format($invoice->total_amount, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="bg-white/5 p-8 rounded-[32px] border border-white/5 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h3 class="font-black text-white uppercase tracking-tight">Opsi Dokumen</h3>
            <p class="text-[10px] font-black uppercase tracking-widest text-slate-600 mt-1">Gunakan link ini untuk cetak fisik atau bayar online.</p>
        </div>
        <div class="flex flex-wrap gap-4">
            <a href="{{ route('portal.invoice.pdf', $invoice->id) }}" target="_blank" class="h-12 flex items-center gap-3 rounded-full bg-white/5 border border-white/10 px-8 text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-white hover:bg-white/10 transition-all">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 00-2 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak / Unduh PDF
            </a>

            @if($invoice->status === 'unpaid' || $invoice->status === 'overdue')
            <a href="{{ route('payment.public', $invoice->payment_hash) }}" class="h-12 flex items-center gap-3 rounded-full bg-brand-500 hover:bg-brand-400 px-8 text-[10px] font-black uppercase tracking-[0.3em] text-white shadow-xl shadow-brand-500/20 active:scale-95 transition-all">
                Bayar Sekarang
            </a>
            @endif
        </div>
    </div>
</div>

@endsection

