@extends('layouts.portal')

@section('title', 'Dashboard Tagihan')

@section('content')
<div class="max-w-5xl mx-auto p-4 md:p-8 space-y-6 md:space-y-10">
    
    {{-- Header / Welcome --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-4 fade-up">
        <div class="space-y-2">
            <h1 class="font-display text-3xl md:text-4xl font-black text-white uppercase tracking-tight leading-none">
                Halo, {{ $customer->full_name }}
            </h1>
            <p class="text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-[0.3em] flex flex-wrap items-center gap-x-3 gap-y-1">
                <span class="text-brand-400">ID: {{ $customer->username ?? $customer->customer_code }}</span>
                <span class="hidden md:inline h-1 w-1 rounded-full bg-slate-700"></span>
                <span class="text-slate-400">{{ $customer->package->name ?? '-' }}</span>
            </p>
        </div>
        <div class="glass-card rounded-2xl md:rounded-3xl px-5 py-3 md:px-6 md:py-4 border-l-4 border-brand-500 self-start md:self-auto">
            <p class="text-[8px] md:text-[9px] font-black uppercase tracking-widest text-slate-500 mb-0.5 md:mb-1">Status Langganan</p>
            <p class="text-xs md:text-sm font-black text-white uppercase tracking-widest">
                {{ strtoupper($customer->status) }}
            </p>
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 fade-up mb-6" style="animation-delay: 100ms">
        @php
            $unpaidCount = $invoices->where('status', 'unpaid')->count();
            $totalUnpaid = $invoices->where('status', 'unpaid')->sum('total_amount');
        @endphp
        
        <div class="glass-card rounded-[32px] p-6 relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-6 opacity-5 group-hover:opacity-10 transition-opacity">
                <svg class="h-20 w-20 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <p class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-3">
                Tagihan Belum Dibayar
            </p>
            <p class="text-3xl font-black text-white tracking-tight mb-1">
                {{ $unpaidCount }}
            </p>
            <p class="text-[11px] font-bold text-brand-400 uppercase tracking-widest">
                Total: Rp{{ number_format($totalUnpaid, 0, ',', '.') }}
            </p>
        </div>

        <div class="glass-card rounded-[32px] p-6 border-white/5 bg-white/[0.02]">
            <p class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-3">
                Metode Pembayaran
            </p>
            <div class="flex items-center gap-3 mb-2">
                <div class="h-8 w-12 bg-white/5 rounded-lg flex items-center justify-center border border-white/10">
                    <span class="text-[8px] font-black text-brand-400">XENDIT</span>
                </div>
                <p class="text-xs font-bold text-slate-300">Virtual Account / QRIS</p>
            </div>
            <p class="text-[9px] text-slate-600 font-bold leading-relaxed">
                Nikmati kemudahan bayar otomatis melalui Xendit Payment Gateway.
            </p>
        </div>

        <div class="glass-card rounded-[32px] p-6 border-white/5 flex flex-col justify-center">
            <p class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-2">
                Dukungan Layanan
            </p>
            <a href="https://wa.me/62812XXXXXXXX" target="_blank" class="flex items-center gap-3 text-green-400 hover:text-green-300 transition group">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
                <div class="flex flex-col">
                    <span class="text-[11px] font-black uppercase tracking-widest group-hover:tracking-[0.15em] transition-all">
                        WhatsApp Penagihan
                    </span>
                    <span class="text-[8px] text-slate-500 font-bold uppercase tracking-widest">
                        Respon Cepat 08:00 - 17:00
                    </span>
                </div>
            </a>
        </div>
    </div>

    {{-- Invoices Table --}}
    <div class="fade-up" style="animation-delay: 200ms">
        <div class="flex items-center gap-4 mb-8">
            <div class="h-10 w-10 bg-brand-500/10 rounded-2xl flex items-center justify-center text-brand-400">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
            </div>
            <div>
                <h2 class="font-display text-2xl font-black text-white uppercase tracking-tight">
                    Riwayat Tagihan
                </h2>
                <p class="text-[9px] font-bold text-slate-600 uppercase tracking-widest">
                    Daftar tagihan 6 bulan terakhir
                </p>
            </div>
        </div>

        <div class="glass-card rounded-[40px] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-white/5 text-left text-[8px] md:text-[10px] font-black uppercase tracking-[0.2em] md:tracking-[0.3em] text-slate-500">
                            <th class="px-4 md:px-6 py-4">Periode</th>
                            <th class="px-4 md:px-6 py-4 hidden sm:table-cell">Jatuh Tempo</th>
                            <th class="px-4 md:px-6 py-4 text-right">Total</th>
                            <th class="px-4 md:px-6 py-4 text-center">Status</th>
                            <th class="px-4 md:px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse($invoices as $inv)
                        <tr class="hover:bg-white/[0.02] transition-colors group h-auto md:h-[64px]">
                            <td class="px-4 md:px-6 py-4">
                                <p class="text-xs md:text-sm font-black text-white uppercase tracking-tight">
                                    {{ \Carbon\Carbon::parse($inv->period_start)->translatedFormat('F Y') }}
                                </p>
                                <p class="text-[8px] md:text-[9px] text-slate-600 font-bold uppercase tracking-widest">
                                    #{{ $inv->invoice_number }}
                                </p>
                            </td>
                            <td class="px-4 md:px-6 py-4 hidden sm:table-cell">
                                <p class="text-[10px] md:text-[11px] font-bold text-slate-400 uppercase">
                                    {{ \Carbon\Carbon::parse($inv->due_date)->translatedFormat('d M Y') }}
                                </p>
                            </td>
                            <td class="px-4 md:px-6 py-4 text-right">
                                <p class="text-xs md:text-sm font-black text-white">
                                    Rp{{ number_format($inv->total_amount / 1000, 0) }}k
                                </p>
                            </td>
                            <td class="px-4 md:px-6 py-4 text-center">
                                @php
                                    $statusLabels = [
                                        'unpaid' => ['label' => 'BELUM BAYAR', 'class' => 'bg-amber-500/10 text-amber-500 border-amber-500/20'],
                                        'paid' => ['label' => 'LUNAS', 'class' => 'bg-green-500/10 text-green-500 border-green-500/20'],
                                        'paid_cash' => ['label' => 'LUNAS (CASH)', 'class' => 'bg-blue-500/10 text-blue-500 border-blue-500/20'],
                                        'overdue' => ['label' => 'TERLAMBAT', 'class' => 'bg-red-500/10 text-red-500 border-red-500/20'],
                                        'cancelled' => ['label' => 'BATAL', 'class' => 'bg-slate-700/10 text-slate-500 border-slate-700/20'],
                                    ];
                                    $curr = $statusLabels[$inv->status] ?? ['label' => $inv->status, 'class' => 'bg-slate-800 text-slate-400'];
                                @endphp
                                <span class="px-2 md:px-3 py-1 rounded-full text-[7px] md:text-[9px] font-black uppercase tracking-widest border {{ $curr['class'] }}">
                                    {{ $curr['label'] }}
                                </span>
                            </td>
                            <td class="px-4 md:px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2 md:gap-4">
                                    @if($inv->status === 'unpaid' || $inv->status === 'overdue')
                                    <a href="{{ route('payment.public', $inv->payment_hash) }}" 
                                       class="inline-flex items-center bg-brand-500 hover:bg-brand-400 text-white rounded-lg md:rounded-xl px-3 py-2 md:px-5 md:py-2.5 text-[8px] md:text-[9px] font-black uppercase tracking-widest shadow-lg shadow-brand-500/20 transition-all active:scale-95">
                                        Bayar
                                    </a>
                                    @endif
                                    <a href="{{ route('portal.invoice.pdf', $inv->id) }}" target="_blank"
                                       class="h-8 w-8 md:h-10 md:w-10 rounded-lg md:rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-slate-500 hover:text-white hover:bg-white/10 transition-colors"
                                       title="Unduh PDF">
                                        <svg class="h-4 w-4 md:h-5 md:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-20 text-center">
                                <p class="text-xs font-bold text-slate-600 uppercase tracking-widest">
                                    Belum ada riwayat tagihan dalam 6 bulan terakhir.
                                </p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="fade-up" style="animation-delay: 260ms">
        <div class="flex items-center gap-4 mb-8">
            <div class="h-10 w-10 bg-green-500/10 rounded-2xl flex items-center justify-center text-green-400">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-4l-4 4v-4H9z"/>
                </svg>
            </div>
            <div>
                <h2 class="font-display text-2xl font-black text-white uppercase tracking-tight">
                    Live Chat Support
                </h2>
                <p class="text-[9px] font-bold text-slate-600 uppercase tracking-widest">
                    Chat langsung dengan CS tanpa pindah aplikasi
                </p>
            </div>
        </div>

        <div class="glass-card rounded-[40px] overflow-hidden">
            <div class="border-b border-white/5 px-6 py-5 flex items-center justify-between gap-4">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-brand-400">Support Portal</p>
                    <p class="text-sm font-bold text-white mt-1">Thread pelanggan {{ $supportConversation->display_code }}</p>
                </div>
                <div class="text-right">
                    <p class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-600">Status</p>
                    <p class="text-xs font-bold text-white uppercase">{{ $supportConversation->status_label }}</p>
                    <p class="text-[9px] font-bold uppercase tracking-[0.2em] text-slate-600 mt-1">Priority {{ $supportConversation->priority_label }}</p>
                </div>
            </div>

            <div id="portal-chat-messages" class="h-[420px] overflow-y-auto px-5 py-5 space-y-3" data-conversation-id="{{ $supportConversation->id }}" data-read-url="{{ route('portal.support.read', $supportConversation) }}">
                @forelse($supportConversation->messages as $message)
                    @php $isCustomer = $message->sender_type === 'customer'; @endphp
                    <div class="portal-chat-message flex {{ $isCustomer ? 'justify-end' : 'justify-start' }}" data-message-id="{{ $message->id }}">
                        <div class="max-w-[85%] rounded-[24px] px-4 py-3 border {{ $isCustomer ? 'bg-brand-500 text-white border-brand-400/30' : 'bg-white/5 text-slate-100 border-white/10' }}">
                            <div class="text-[9px] font-black uppercase tracking-widest {{ $isCustomer ? 'text-white/70' : 'text-slate-500' }}">
                                {{ $isCustomer ? 'Anda' : ($message->senderAdmin->name ?? 'CS Djavva') }}
                            </div>
                            <div class="mt-2 whitespace-pre-wrap text-sm leading-relaxed">{{ $message->message }}</div>
                            <div class="mt-2 text-[10px] {{ $isCustomer ? 'text-white/70' : 'text-slate-500' }}">{{ $message->created_at->format('d M Y H:i') }}</div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-slate-500 text-sm py-16">
                        Belum ada pesan. Tulis kendala Anda, CS kami akan masuk ke thread ini secara realtime.
                    </div>
                @endforelse
            </div>

            <form id="portal-chat-form" action="{{ route('portal.support.messages') }}" method="POST" class="border-t border-white/5 p-5 space-y-3">
                @csrf
                <textarea id="portal-chat-input" name="message" rows="3" class="w-full rounded-[24px] border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder:text-slate-600 focus:outline-none focus:ring-2 focus:ring-brand-500/30" placeholder="Tulis pertanyaan atau kendala Anda di sini..." required></textarea>
                <div class="flex items-center justify-between gap-3">
                    <div class="flex flex-col">
                        <p id="portal-chat-status" class="text-[10px] font-bold uppercase tracking-widest text-slate-600">Realtime support aktif</p>
                        <p id="portal-chat-typing" class="text-[10px] text-brand-400 hidden">CS sedang mengetik...</p>
                    </div>
                    <button id="portal-chat-button" type="submit" class="inline-flex items-center bg-brand-500 hover:bg-brand-400 text-white rounded-full px-6 py-3 text-[10px] font-black uppercase tracking-widest transition-all active:scale-95">
                        Kirim Pesan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
(() => {
    const listEl = document.getElementById('portal-chat-messages');
    const form = document.getElementById('portal-chat-form');
    const input = document.getElementById('portal-chat-input');
    const button = document.getElementById('portal-chat-button');
    const status = document.getElementById('portal-chat-status');
    const typingLabel = document.getElementById('portal-chat-typing');

    if (!listEl || !form || !window.Echo) {
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const conversationId = listEl.dataset.conversationId;
    const readUrl = listEl.dataset.readUrl;
    const typingUrl = '{{ route('portal.support.typing') }}';
    const knownMessageIds = new Set(Array.from(document.querySelectorAll('.portal-chat-message')).map((element) => Number(element.dataset.messageId)));
    let typingTimeout = null;
    let remoteTypingTimeout = null;

    const escapeHtml = (value) => {
        const div = document.createElement('div');
        div.textContent = value ?? '';
        return div.innerHTML;
    };

    const appendMessage = (message) => {
        if (knownMessageIds.has(Number(message.id))) {
            return;
        }

        const isCustomer = message.sender_type === 'customer';
        listEl.insertAdjacentHTML('beforeend', `
            <div class="portal-chat-message flex ${isCustomer ? 'justify-end' : 'justify-start'}" data-message-id="${message.id}">
                <div class="max-w-[85%] rounded-[24px] px-4 py-3 border ${isCustomer ? 'bg-brand-500 text-white border-brand-400/30' : 'bg-white/5 text-slate-100 border-white/10'}">
                    <div class="text-[9px] font-black uppercase tracking-widest ${isCustomer ? 'text-white/70' : 'text-slate-500'}">${escapeHtml(isCustomer ? 'Anda' : (message.sender_name || 'CS Djavva'))}</div>
                    <div class="mt-2 whitespace-pre-wrap text-sm leading-relaxed">${escapeHtml(message.message)}</div>
                    <div class="mt-2 text-[10px] ${isCustomer ? 'text-white/70' : 'text-slate-500'}">${escapeHtml(message.created_at_label || '')}</div>
                </div>
            </div>
        `);
        knownMessageIds.add(Number(message.id));
        listEl.scrollTop = listEl.scrollHeight;
    };

    const markRead = async () => {
        if (!readUrl) return;

        await fetch(readUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
        });
    };

    const sendTyping = async (isTyping) => {
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
            body: JSON.stringify({ is_typing: !!isTyping }),
        });
    };

    window.Echo.private(`customer.chat.${conversationId}`)
        .listen('.chat.message.created', async (payload) => {
            appendMessage(payload.message);

            if (payload.message.sender_type === 'admin') {
                status.textContent = 'Pesan baru dari CS masuk realtime.';
                await markRead();
            }
        })
        .listen('.chat.typing.updated', (payload) => {
            if (payload.actor_type !== 'admin') return;

            if (payload.is_typing) {
                typingLabel.textContent = `${payload.actor_name || 'CS'} sedang mengetik...`;
                typingLabel.classList.remove('hidden');
                clearTimeout(remoteTypingTimeout);
                remoteTypingTimeout = setTimeout(() => typingLabel.classList.add('hidden'), 2500);
            } else {
                typingLabel.classList.add('hidden');
            }
        });

    input.addEventListener('input', () => {
        clearTimeout(typingTimeout);
        sendTyping(true);
        typingTimeout = setTimeout(() => sendTyping(false), 1200);
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const message = input.value.trim();
        if (!message) return;

        button.disabled = true;
        status.textContent = 'Mengirim pesan ke CS...';
        clearTimeout(typingTimeout);
        sendTyping(false);

        try {
            const socketId = window.Echo?.socketId?.();
            const headers = {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            };
            if (socketId) headers['X-Socket-ID'] = socketId;

            const response = await fetch(form.action, {
                method: 'POST',
                headers: headers,
                body: JSON.stringify({ message }),
            });

            const payload = await response.json();
            appendMessage(payload.chat_message);
            input.value = '';
            typingLabel.classList.add('hidden');
            status.textContent = 'Pesan terkirim. Menunggu balasan CS...';
        } catch (error) {
            status.textContent = 'Gagal mengirim pesan.';
        } finally {
            button.disabled = false;
        }
    });
})();
</script>
@endsection

