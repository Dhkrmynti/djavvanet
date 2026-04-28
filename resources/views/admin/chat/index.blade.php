@extends('admin.layouts.app')

@section('title', 'Live Chat')

@php
    $quickReplies = [
        'Halo Kak, kami sudah menerima pesan Anda dan sedang kami cek sekarang.',
        'Baik, mohon tunggu 1-3 menit ya, tim kami sedang melakukan pengecekan.',
        'Boleh kirim lokasi lengkap atau patokan rumah agar tim kami lebih cepat bantu?',
        'Kendala sudah kami catat. Jika perlu teknisi onsite, kami akan informasikan jadwalnya.',
    ];
@endphp

@push('styles')
<style>
    .chat-admin-stat {
        border: 1px solid var(--border-main);
        background: var(--bg-table-head);
        color: var(--text-main);
        font-weight: 700;
    }
    .chat-admin-stat--open {
        border-color: rgb(167 243 208);
        color: rgb(4 120 87);
        background: rgb(236 253 245);
    }
    .chat-admin-stat--pending {
        border-color: rgb(253 230 138);
        color: rgb(180 83 9);
        background: rgb(255 251 235);
    }
    .chat-admin-stat--closed {
        border-color: var(--border-main);
        color: var(--text-muted);
        background: var(--bg-table-even);
    }
    .chat-admin-stat--unread {
        border-color: rgb(254 202 202);
        color: rgb(185 28 28);
        background: rgb(254 242 242);
    }
    .chat-admin-conversation {
        background: var(--bg-card);
        border-left: 3px solid transparent;
    }
    .chat-admin-conversation:hover {
        background: var(--bg-table-head);
    }
    .chat-admin-conversation.is-selected {
        background: var(--bg-table-head);
        border-left-color: rgb(37 99 235);
    }
    .chat-admin-pill {
        border: 1px solid var(--border-main);
        background: var(--bg-table-head);
        color: var(--text-muted);
    }
    .chat-admin-pill--source-guest {
        color: rgb(3 105 161);
        background: rgb(240 249 255);
        border-color: rgb(186 230 253);
    }
    .chat-admin-pill--source-customer {
        color: rgb(67 56 202);
        background: rgb(238 242 255);
        border-color: rgb(199 210 254);
    }
    .chat-admin-pill--priority-urgent {
        color: rgb(185 28 28);
        background: rgb(254 242 242);
        border-color: rgb(254 202 202);
    }
    .chat-admin-pill--priority-high {
        color: rgb(194 65 12);
        background: rgb(255 247 237);
        border-color: rgb(254 215 170);
    }
    .chat-admin-pill--priority-normal {
        color: rgb(4 120 87);
        background: rgb(236 253 245);
        border-color: rgb(167 243 208);
    }
    .chat-admin-pill--priority-low,
    .chat-admin-pill--status-closed {
        color: var(--text-muted);
        background: var(--bg-table-even);
        border-color: var(--border-main);
    }
    .chat-admin-pill--status-open {
        color: rgb(4 120 87);
        background: rgb(236 253 245);
        border-color: rgb(167 243 208);
    }
    .chat-admin-pill--status-pending {
        color: rgb(180 83 9);
        background: rgb(255 251 235);
        border-color: rgb(253 230 138);
    }
    .chat-admin-unread {
        background: rgb(217 119 6);
        color: #fff;
    }
    .chat-admin-action {
        border: 1px solid var(--border-main);
        background: var(--bg-card);
        color: var(--text-main);
    }
    .chat-admin-action:hover {
        background: var(--bg-table-head);
    }
    .chat-admin-action--primary {
        background: rgb(37 99 235);
        border-color: rgb(29 78 216);
        color: #fff;
    }
    .chat-admin-action--primary:hover {
        background: rgb(29 78 216);
    }
    .chat-admin-bubble {
        border: 1px solid var(--border-main);
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        position: relative;
        padding: 8px 12px;
        line-height: 1.5;
    }
    .chat-admin-bubble--admin {
        background: rgb(37 99 235);
        border-color: rgb(29 78 216);
        color: #fff;
        border-radius: 12px 12px 2px 12px;
    }
    .chat-admin-bubble--customer {
        background: var(--bg-card);
        color: var(--text-main);
        border-radius: 12px 12px 12px 2px;
    }
    .chat-admin-bubble-meta--admin {
        color: rgba(255, 255, 255, 0.8);
        font-weight: 700;
    }
    .chat-admin-bubble-meta--customer {
        color: var(--text-muted);
        font-weight: 700;
    }
    
    /* New Chat Modal Styles */
    .chat-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 100;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        pointer-events: none;
        transition: 0.2s;
    }
    .chat-modal-overlay.is-active {
        opacity: 1;
        pointer-events: auto;
    }
    .chat-modal-card {
        background: var(--bg-card);
        border: 1px solid var(--border-main);
        width: 100%;
        max-width: 440px;
        border-radius: 2px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        transform: scale(0.95);
        transition: 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .chat-modal-overlay.is-active .chat-modal-card {
        transform: scale(1);
    }
</style>
@endpush

@section('content')
<div class="bg-[var(--bg-card)] border border-[var(--border-main)] px-3 py-1.5 rounded-sm shadow-sm flex items-center justify-between shrink-0 transition-colors">
    <div>
        <h1 class="text-sm font-bold text-[var(--text-main)]">Live Chat Command Center</h1>
        <p class="text-[10px] text-[var(--text-muted)] mt-0.5">Inbox realtime untuk website publik dan portal pelanggan, lengkap dengan workflow CS profesional.</p>
    </div>
    <div class="flex items-center gap-2 text-[10px]">
        <span class="chat-admin-stat px-2 py-1 rounded-sm">{{ $stats['total'] }} percakapan</span>
        <span class="chat-admin-stat chat-admin-stat--open px-2 py-1 rounded-sm">{{ $stats['open'] }} open</span>
        <span class="chat-admin-stat chat-admin-stat--pending px-2 py-1 rounded-sm">{{ $stats['pending'] }} pending</span>
        <span class="chat-admin-stat chat-admin-stat--closed px-2 py-1 rounded-sm">{{ $stats['closed'] }} closed</span>
        <span class="chat-admin-stat chat-admin-stat--unread px-2 py-1 rounded-sm">{{ $stats['unread'] }} unread</span>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-[360px_minmax(0,1fr)] gap-2 flex-1 min-h-0">
    <section class="bg-[var(--bg-card)] border border-[var(--border-main)] rounded-sm shadow-sm flex flex-col min-h-0 overflow-hidden">
        <div class="p-3 border-b border-[var(--border-main)] flex flex-col gap-2">
            <button id="btn-open-new-chat-modal" class="w-full chat-admin-action chat-admin-action--primary rounded-sm px-3 py-2 text-[11px] font-bold transition-colors">
                + Buka Percakapan Baru
            </button>
            <button id="btn-mark-all-read" class="w-full bg-[var(--bg-table-head)] border border-[var(--border-main)] hover:bg-[var(--bg-card)] text-[var(--text-main)] rounded-sm px-3 py-1.5 text-[10px] font-semibold transition-colors flex items-center justify-center gap-1.5 shadow-sm">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Tandai Semua Telah Dibaca
            </button>
        </div>

        <div class="px-3 py-2 border-b border-[var(--border-main)] bg-[var(--bg-table-head)] space-y-2">
            <div class="flex items-center justify-between">
                <div class="text-[11px] font-bold text-[var(--text-main)]">Inbox Agent</div>
                <div class="text-[10px] text-[var(--text-muted)]">Realtime, sortable, filterable</div>
            </div>
            <input id="chat-filter-search" type="text" placeholder="Cari nama, subjek, source, agent..." class="w-full border border-[var(--border-main)] bg-[var(--bg-card)] rounded-sm px-2 py-2 text-[11px] text-[var(--text-main)] outline-none">
            <div class="grid grid-cols-2 gap-2">
                <select id="chat-filter-status" class="border border-[var(--border-main)] bg-[var(--bg-card)] rounded-sm px-2 py-2 text-[11px] text-[var(--text-main)] outline-none">
                    <option value="all">Semua status</option>
                    <option value="open">Open</option>
                    <option value="pending">Pending</option>
                    <option value="closed">Closed</option>
                </select>
                <select id="chat-filter-source" class="border border-[var(--border-main)] bg-[var(--bg-card)] rounded-sm px-2 py-2 text-[11px] text-[var(--text-main)] outline-none">
                    <option value="all">Semua source</option>
                    <option value="guest">Website</option>
                    <option value="customer">Portal</option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <select id="chat-filter-assignment" class="border border-[var(--border-main)] bg-[var(--bg-card)] rounded-sm px-2 py-2 text-[11px] text-[var(--text-main)] outline-none">
                    <option value="all">Semua agent</option>
                    <option value="mine">Assigned to me</option>
                    <option value="unassigned">Unassigned</option>
                </select>
                <select id="chat-filter-priority" class="border border-[var(--border-main)] bg-[var(--bg-card)] rounded-sm px-2 py-2 text-[11px] text-[var(--text-main)] outline-none">
                    <option value="all">Semua priority</option>
                    <option value="urgent">Urgent</option>
                    <option value="high">High</option>
                    <option value="normal">Normal</option>
                    <option value="low">Low</option>
                </select>
            </div>
        </div>

        <div id="chat-conversation-list" class="flex-1 overflow-auto">
            @forelse($conversations as $conversation)
                @php
                    $isSelected = $selectedConversation && $selectedConversation->id === $conversation->id;
                    $latest = $conversation->latestMessage;
                @endphp
                <div class="js-conversation-wrapper relative group">
                    <a
                        href="{{ route('admin.chat.index', ['conversation' => $conversation->id]) }}"
                        class="js-conversation-item chat-admin-conversation {{ $isSelected ? 'is-selected' : '' }} block px-3 py-2.5 border-b border-[var(--border-main)] transition-colors"
                        data-conversation-id="{{ $conversation->id }}"
                        data-status="{{ $conversation->status }}"
                        data-source="{{ $conversation->source_type }}"
                        data-priority="{{ $conversation->priority }}"
                        data-assigned="{{ $conversation->assigned_admin_id }}"
                        data-search="{{ strtolower(trim($conversation->display_name.' '.$conversation->subject.' '.$conversation->source_label.' '.($conversation->assignedAdmin->name ?? ''))) }}"
                    >
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <div class="js-conversation-customer font-bold text-[11px] text-[var(--text-main)] truncate">
                                        {{ $conversation->display_name }}
                                        @if($conversation->display_code && $conversation->display_code !== $conversation->display_name)
                                            <span class="text-[10px] font-medium opacity-60 ml-0.5">[{{ $conversation->display_code }}]</span>
                                        @endif
                                    </div>
                                    <span class="chat-admin-pill {{ $conversation->source_type === 'guest' ? 'chat-admin-pill--source-guest' : 'chat-admin-pill--source-customer' }} px-1.5 py-0.5 rounded-sm text-[9px] font-bold">{{ $conversation->source_label }}</span>
                                    <span class="js-conversation-priority chat-admin-pill {{ $conversation->priority === 'urgent' ? 'chat-admin-pill--priority-urgent' : ($conversation->priority === 'high' ? 'chat-admin-pill--priority-high' : ($conversation->priority === 'low' ? 'chat-admin-pill--priority-low' : 'chat-admin-pill--priority-normal')) }} px-1.5 py-0.5 rounded-sm text-[9px] font-bold">{{ strtoupper($conversation->priority) }}</span>
                                </div>
                                <div class="js-conversation-subject text-[10px] text-[var(--text-muted)] truncate mt-1">{{ $conversation->subject ?: 'Percakapan tanpa subjek' }}</div>
                            </div>
                            <span class="js-conversation-unread chat-admin-unread shrink-0 px-1.5 py-0.5 rounded-sm text-[9px] font-bold {{ $conversation->unread_for_admin_count > 0 ? '' : 'hidden' }}">{{ $conversation->unread_for_admin_count }}</span>
                        </div>
                        <div class="js-conversation-preview mt-2 text-[10px] text-[var(--text-muted)] truncate">
                            {{ $latest?->message ?: 'Belum ada pesan.' }}
                        </div>
                        <div class="mt-2 flex items-center justify-between text-[9px] gap-2">
                            <span class="js-conversation-status chat-admin-pill {{ $conversation->status === 'open' ? 'chat-admin-pill--status-open' : ($conversation->status === 'pending' ? 'chat-admin-pill--status-pending' : 'chat-admin-pill--status-closed') }} px-1.5 py-0.5 rounded-sm font-bold uppercase">{{ $conversation->status }}</span>
                            <span class="js-conversation-agent truncate text-[var(--text-muted)]">{{ $conversation->assignedAdmin->name ?? 'Unassigned' }}</span>
                            <span class="js-conversation-time text-[var(--text-muted)]">{{ optional($conversation->last_message_at)->diffForHumans() ?: 'baru dibuat' }}</span>
                        </div>
                    </a>
                    <button class="absolute top-2 right-2 p-1.5 text-red-400 opacity-0 group-hover:opacity-100 transition-all hover:text-red-600 hover:bg-red-50 rounded-sm" 
                            title="Hapus Percakapan"
                            onclick="event.preventDefault(); event.stopPropagation(); window.deleteConversation({{ $conversation->id }}, this.closest('.js-conversation-wrapper'))">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            @empty
                <div class="p-4 text-[11px] text-[var(--text-muted)]">
                    Belum ada percakapan. Widget publik dan portal pelanggan akan mengalir ke inbox ini secara otomatis.
                </div>
            @endforelse
        </div>
    </section>

    <section class="bg-[var(--bg-card)] border border-[var(--border-main)] rounded-sm shadow-sm flex flex-col min-h-0 h-[70vh] xl:h-auto overflow-hidden">
        @if($selectedConversation)
            <div class="px-4 py-3 border-b border-[var(--border-main)] bg-[var(--bg-table-head)] flex flex-col gap-3">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <div class="font-bold text-[13px] text-[var(--text-main)]">{{ $selectedConversation->display_name }}</div>
                        <div class="text-[10px] text-[var(--text-muted)]">
                            {{ $selectedConversation->display_code }} | {{ $selectedConversation->source_label }} | {{ $selectedConversation->subject ?: 'Tanpa subjek' }} | Agent: <span id="chat-current-agent">{{ $selectedConversation->assignedAdmin->name ?? 'Belum di-assign' }}</span>
                        </div>
                    </div>
                    <div class="text-right text-[10px] text-[var(--text-muted)]">
                        <div>First response: {{ $selectedConversation->first_response_at ? $selectedConversation->first_response_at->diffForHumans() : 'Belum ada' }}</div>
                        <div>Pesan: {{ $selectedConversation->messages->count() }}</div>
                    </div>
                </div>

                <div class="grid md:grid-cols-[1fr_1fr_1fr_auto] gap-2 items-end">
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-wide text-[var(--text-muted)]">Status</label>
                        <select id="chat-status-control" class="mt-1 w-full border border-[var(--border-main)] bg-[var(--bg-card)] rounded-sm px-2 py-2 text-[11px] text-[var(--text-main)] outline-none">
                            <option value="open" {{ $selectedConversation->status === 'open' ? 'selected' : '' }}>Open</option>
                            <option value="pending" {{ $selectedConversation->status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="closed" {{ $selectedConversation->status === 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-wide text-[var(--text-muted)]">Priority</label>
                        <select id="chat-priority-control" class="mt-1 w-full border border-[var(--border-main)] bg-[var(--bg-card)] rounded-sm px-2 py-2 text-[11px] text-[var(--text-main)] outline-none">
                            <option value="low" {{ $selectedConversation->priority === 'low' ? 'selected' : '' }}>Low</option>
                            <option value="normal" {{ $selectedConversation->priority === 'normal' ? 'selected' : '' }}>Normal</option>
                            <option value="high" {{ $selectedConversation->priority === 'high' ? 'selected' : '' }}>High</option>
                            <option value="urgent" {{ $selectedConversation->priority === 'urgent' ? 'selected' : '' }}>Urgent</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-wide text-[var(--text-muted)]">Assign Agent</label>
                        <select id="chat-agent-control" class="mt-1 w-full border border-[var(--border-main)] bg-[var(--bg-card)] rounded-sm px-2 py-2 text-[11px] text-[var(--text-main)] outline-none">
                            <option value="">Belum di-assign</option>
                            @foreach($agents as $agent)
                                <option value="{{ $agent->id }}" {{ $selectedConversation->assigned_admin_id === $agent->id ? 'selected' : '' }}>{{ $agent->name }} ({{ strtoupper($agent->role) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <button id="chat-workflow-save" type="button" class="h-[30px] chat-admin-action chat-admin-action--primary rounded-sm px-4 text-[10px] font-bold transition-colors">Simpan</button>
                </div>
            </div>

            <div class="px-4 py-2 border-b border-[var(--border-main)] bg-[var(--bg-card)] flex items-center gap-2 overflow-x-auto">
                <button type="button" class="js-quick-reply chat-admin-action px-2 py-1 rounded-sm text-[10px] font-bold whitespace-nowrap">Cek sekarang</button>
                @foreach($quickReplies as $reply)
                    <button type="button" class="js-quick-reply chat-admin-action px-2 py-1 rounded-sm text-[10px] font-bold whitespace-nowrap" data-reply="{{ $reply }}">{{ \Illuminate\Support\Str::limit($reply, 36) }}</button>
                @endforeach
            </div>

            <div
                id="chat-message-list"
                class="flex-1 overflow-auto px-4 py-2 space-y-2 bg-[var(--bg-table-even)]"
                data-current-conversation-id="{{ $selectedConversation->id }}"
                data-current-admin-id="{{ auth()->id() }}"
                data-read-url="{{ route('admin.chat.read', $selectedConversation) }}"
                data-update-url="{{ route('admin.chat.update', $selectedConversation) }}"
                data-typing-url="{{ route('admin.chat.typing', $selectedConversation) }}"
            >
                @forelse($selectedConversation->messages as $message)
                    @php $isAdmin = $message->sender_type === 'admin'; @endphp
                    <div class="js-chat-message flex {{ $isAdmin ? 'justify-end' : 'justify-start' }} mb-1" data-message-id="{{ $message->id }}">
                        <div class="chat-admin-bubble {{ $isAdmin ? 'chat-admin-bubble--admin' : 'chat-admin-bubble--customer' }} max-w-[85%] text-[11px]">
                            <div class="text-[9px] uppercase tracking-wide {{ $isAdmin ? 'chat-admin-bubble-meta--admin' : 'chat-admin-bubble-meta--customer' }}">
                                {{ $isAdmin ? ($selectedConversation->assignedAdmin->name ?? $message->senderAdmin->name ?? 'Admin') : ($message->senderCustomer->full_name ?? $selectedConversation->display_name) }}
                            </div>
                            <div class="mt-0.5 whitespace-pre-wrap leading-relaxed">{{ $message->message }}</div>
                            @if(!empty($message->metadata['attachment_url']))
                                <div class="mt-2">
                                    <a href="{{ $message->metadata['attachment_url'] }}" target="_blank" class="block">
                                        <img src="{{ $message->metadata['attachment_url'] }}" alt="Lampiran" class="max-w-[200px] rounded-sm shadow-sm border border-white/20 hover:opacity-90 transition-opacity">
                                    </a>
                                </div>
                            @endif
                            <div class="mt-1 text-right text-[8px] opacity-70 {{ $isAdmin ? 'text-white' : 'text-gray-500' }}">{{ $message->created_at->format('H:i') }}</div>
                        </div>
                    </div>
                @empty
                    <div class="h-full min-h-[320px] flex items-center justify-center text-center text-[11px] text-[var(--text-muted)]">
                        Percakapan ini masih kosong. Kirim pesan pertama dari panel bawah untuk mulai interaksi.
                    </div>
                @endforelse
                <div id="chat-typing-indicator" class="hidden text-[11px] text-[var(--text-muted)] italic">Customer sedang mengetik...</div>
            </div>

            <form id="chat-send-form" action="{{ route('admin.chat.messages.store', $selectedConversation) }}" method="POST" onsubmit="return false;" class="border-t border-[var(--border-main)] p-3 bg-[var(--bg-card)]">
                @csrf
                <label class="text-[10px] font-bold uppercase tracking-wide text-[var(--text-muted)]">Balas sebagai admin</label>
                <textarea id="chat-message-input" name="message" rows="2" maxlength="4000" class="mt-1 w-full border border-[var(--border-main)] bg-[var(--bg-card)] rounded-sm px-3 py-1.5 text-[11px] text-[var(--text-main)] outline-none" placeholder="Tulis balasan admin di sini..."></textarea>
                
                <!-- Image Preview Area -->
                <div id="chat-attachment-preview" class="hidden mt-2 p-2 border border-blue-200 bg-blue-50/50 rounded-sm flex items-center gap-3">
                    <div class="relative w-12 h-12 border border-[var(--border-main)] bg-white rounded-sm overflow-hidden">
                        <img id="attachment-preview-img" src="" class="w-full h-full object-cover">
                    </div>
                    <div class="flex-1 min-w-0">
                        <div id="attachment-preview-name" class="text-[10px] font-bold truncate text-blue-800">filename.jpg</div>
                        <div id="attachment-preview-size" class="text-[9px] text-blue-600">0 KB</div>
                    </div>
                    <button type="button" id="btn-remove-attachment" class="text-red-500 hover:text-red-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="mt-2 flex items-center justify-between gap-3">
                    <div class="flex flex-col">
                        <div id="chat-live-status" class="text-[10px] text-[var(--text-muted)]">Realtime inbox aktif lewat WebSocket.</div>
                        <div id="chat-admin-typing-hint" class="text-[10px] text-blue-700 hidden">Anda sedang mengetik...</div>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="file" id="chat-attachment-input" name="attachment" accept="image/*" class="hidden">
                        <button type="button" id="btn-trigger-attachment" class="chat-admin-pill px-3 py-2 rounded-sm hover:bg-blue-50 transition-colors flex items-center gap-1.5" title="Lampirkan Gambar">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                            <span class="text-[10px] font-bold uppercase">Gambar</span>
                        </button>
                        <button id="chat-send-button" type="submit" class="chat-admin-action chat-admin-action--primary rounded-sm px-4 py-2 text-[11px] font-bold transition-colors">Kirim Pesan</button>
                    </div>
                </div>
            </form>
        @else
            <div class="flex-1 flex items-center justify-center text-center p-6">
                <div>
                    <div class="text-sm font-bold text-[var(--text-main)]">Inbox belum dipilih</div>
                    <p class="mt-1 text-[11px] text-[var(--text-muted)]">Pilih percakapan dari panel kiri untuk mulai menangani live chat masuk.</p>
                </div>
            </div>
        @endif
    </section>
</div>

<!-- Modal Buka Percakapan Baru -->
<div id="new-chat-modal" class="chat-modal-overlay">
    <div class="chat-modal-card">
        <div class="px-4 py-2 border-b border-[var(--border-main)] bg-[var(--bg-table-head)] flex items-center justify-between">
            <div class="text-[12px] font-bold text-blue-800 dark:text-blue-400">Buka Percakapan Baru</div>
            <button id="btn-close-new-chat-modal" class="text-[var(--text-muted)] hover:text-[var(--text-main)] transition-colors text-xl leading-none">&times;</button>
        </div>
        <form action="{{ route('admin.chat.conversations.store') }}" method="POST">
            @csrf
            <div class="p-4 space-y-4">
                <div>
                    <label class="text-[10px] font-bold uppercase tracking-tight text-[var(--text-muted)]">Customer *</label>
                    <select name="customer_id" class="mt-1 w-full border border-[var(--border-main)] bg-[#f8fafc] dark:bg-slate-800 rounded-sm px-2 py-2 text-[11px] text-[var(--text-main)] outline-none focus:ring-1 focus:ring-blue-500" required>
                        <option value="">-- Pilih Pelanggan --</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->full_name }} — {{ $customer->customer_code }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-tight text-[var(--text-muted)]">Subjek</label>
                        <input type="text" name="subject" maxlength="255" class="mt-1 w-full border border-[var(--border-main)] bg-[#f8fafc] dark:bg-slate-800 rounded-sm px-2 py-2 text-[11px] text-[var(--text-main)] outline-none focus:ring-1 focus:ring-blue-500" placeholder="Contoh: Gangguan internet">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-tight text-[var(--text-muted)]">Priority</label>
                        <div class="mt-1 w-full bg-slate-100 dark:bg-slate-800/50 border border-[var(--border-main)] rounded-sm px-2 py-2 text-[11px] font-bold text-blue-600 dark:text-blue-400">
                            Normal (Fixed)
                        </div>
                    </div>
                </div>
                <div>
                    <label class="text-[10px] font-bold uppercase tracking-tight text-[var(--text-muted)]">Pesan Awal</label>
                    <textarea name="opening_message" rows="4" maxlength="2000" class="mt-1 w-full border border-[var(--border-main)] bg-[#f8fafc] dark:bg-slate-800 rounded-sm px-2 py-2 text-[11px] text-[var(--text-main)] outline-none focus:ring-1 focus:ring-blue-500" placeholder="Tulis sapaan awal jika ingin langsung kirim pesan..."></textarea>
                </div>
            </div>
            <div class="p-2 flex justify-end gap-2 bg-[#eef2f7] dark:bg-slate-800/80 border-t border-[var(--border-main)]">
                <button type="button" id="btn-cancel-new-chat-modal" class="bg-white dark:bg-slate-700 border border-gray-400 text-gray-700 dark:text-gray-200 px-4 py-1 rounded-sm text-[11px] font-bold hover:bg-gray-50 transition-all">Tutup</button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-1 rounded-sm text-[11px] font-bold shadow-sm transition-all active:scale-95">Siapkan Chat</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (() => {
        const listEl = document.getElementById('chat-message-list');
        const scrollToBottom = () => {
            if (listEl) listEl.scrollTop = listEl.scrollHeight;
        };
        const conversationListEl = document.getElementById('chat-conversation-list');
        if (!conversationListEl) return;

        const searchInput = document.getElementById('chat-filter-search');
        const statusFilter = document.getElementById('chat-filter-status');
        const sourceFilter = document.getElementById('chat-filter-source');
        const assignmentFilter = document.getElementById('chat-filter-assignment');
        const priorityFilter = document.getElementById('chat-filter-priority');
        const sendForm = document.getElementById('chat-send-form');
        const messageInput = document.getElementById('chat-message-input');
        const sendButton = document.getElementById('chat-send-button');
        const attachmentInput = document.getElementById('chat-attachment-input');
        const btnTriggerAttachment = document.getElementById('btn-trigger-attachment');
        const attachmentPreview = document.getElementById('chat-attachment-preview');
        const previewImg = document.getElementById('attachment-preview-img');
        const previewName = document.getElementById('attachment-preview-name');
        const previewSize = document.getElementById('attachment-preview-size');
        const btnRemoveAttachment = document.getElementById('btn-remove-attachment');
        const liveStatus = document.getElementById('chat-live-status');
        const adminTypingHint = document.getElementById('chat-admin-typing-hint');
        const typingIndicator = document.getElementById('chat-typing-indicator');
        const workflowSaveButton = document.getElementById('chat-workflow-save');
        const statusControl = document.getElementById('chat-status-control');
        const priorityControl = document.getElementById('chat-priority-control');
        const agentControl = document.getElementById('chat-agent-control');
        const quickReplyButtons = document.querySelectorAll('.js-quick-reply');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const currentConversationId = listEl ? Number(listEl.dataset.currentConversationId) : null;
        const readUrl = listEl ? listEl.dataset.readUrl : null;
        const updateUrl = listEl ? listEl.dataset.updateUrl : null;
        const typingUrl = listEl ? listEl.dataset.typingUrl : null;
        const currentAdminId = Number(@json(auth()->id() ?? 0));
        const knownMessageIds = new Set(Array.from(document.querySelectorAll('.js-chat-message')).map((element) => Number(element.dataset.messageId)));
        let typingTimeout = null;
        let remoteTypingTimeout = null;

        const escapeHtml = (value) => {
            const div = document.createElement('div');
            div.textContent = value ?? '';
            return div.innerHTML;
        };

        const renderFilters = () => {
            const q = (searchInput?.value || '').toLowerCase().trim();
            const status = statusFilter?.value || 'all';
            const source = sourceFilter?.value || 'all';
            const assignment = assignmentFilter?.value || 'all';
            const priority = priorityFilter?.value || 'all';

            document.querySelectorAll('.js-conversation-item').forEach((item) => {
                const matchesSearch = !q || (item.dataset.search || '').includes(q);
                const matchesStatus = status === 'all' || item.dataset.status === status;
                const matchesSource = source === 'all' || item.dataset.source === source;
                const matchesPriority = priority === 'all' || item.dataset.priority === priority;
                const matchesAssignment = assignment === 'all'
                    || (assignment === 'mine' && item.dataset.assigned === String(currentAdminId))
                    || (assignment === 'unassigned' && (!item.dataset.assigned || item.dataset.assigned === ''));

                item.classList.toggle('hidden', !(matchesSearch && matchesStatus && matchesSource && matchesPriority && matchesAssignment));
            });
        };

        [searchInput, statusFilter, sourceFilter, assignmentFilter, priorityFilter].forEach((element) => {
            element?.addEventListener('input', renderFilters);
            element?.addEventListener('change', renderFilters);
        });
        renderFilters();

        const buildMessageHtml = (message, isAdmin) => `
            <div class="js-chat-message flex ${isAdmin ? 'justify-end' : 'justify-start'} mb-1" data-message-id="${message.id}">
                <div class="chat-admin-bubble ${isAdmin ? 'chat-admin-bubble--admin' : 'chat-admin-bubble--customer'} max-w-[85%] text-[11px]">
                    <div class="text-[9px] uppercase tracking-wide ${isAdmin ? 'chat-admin-bubble-meta--admin' : 'chat-admin-bubble-meta--customer'}">${escapeHtml(message.sender_name || 'System')}</div>
                    ${message.attachment_url ? `
                        <div class="mb-1 mt-1">
                            <a href="${message.attachment_url}" target="_blank">
                                <img src="${message.attachment_url}" class="rounded-sm max-w-full max-h-48 object-contain bg-black/5" onerror="this.onerror=null; this.src='https://placehold.co/400x300?text=Image+Load+Error';">
                            </a>
                        </div>
                    ` : ''}
                    <div class="mt-0.5 whitespace-pre-wrap leading-relaxed">${escapeHtml(message.message)}</div>
                    <div class="mt-1 text-right text-[8px] opacity-70 ${isAdmin ? 'text-white' : 'text-gray-500'}">${escapeHtml(message.created_at_label_short || '')}</div>
                </div>
            </div>
        `;

        const priorityClasses = {
            urgent: 'chat-admin-pill chat-admin-pill--priority-urgent',
            high: 'chat-admin-pill chat-admin-pill--priority-high',
            normal: 'chat-admin-pill chat-admin-pill--priority-normal',
            low: 'chat-admin-pill chat-admin-pill--priority-low',
        };

        const statusClasses = {
            open: 'chat-admin-pill chat-admin-pill--status-open',
            pending: 'chat-admin-pill chat-admin-pill--status-pending',
            closed: 'chat-admin-pill chat-admin-pill--status-closed',
        };

        const updateConversationItem = (payload) => {
            const conversation = payload.conversation;
            const message = payload.message;
            let item = conversationListEl.querySelector(`[data-conversation-id="${conversation.id}"]`);
            
            if (!item) {  // but for now let's just create the DOM element so it doesn't reload.
                const tempDiv = document.createElement('div');
                const chatUrl = `{{ route('admin.chat.index') }}?conversation=${conversation.id}`;
                
                tempDiv.innerHTML = `
                    <div class="js-conversation-wrapper relative group">
                        <a href="${chatUrl}" 
                           class="js-conversation-item block p-3 border-b border-[var(--border-main)] hover:bg-gray-50 transition-all relative"
                           data-conversation-id="${conversation.id}"
                           data-status="${conversation.status}"
                           data-source="${conversation.source_type}"
                           data-priority="${conversation.priority}"
                           data-assigned="${conversation.assigned_admin_id || ''}"
                           data-search="${(conversation.customer_name || '').toLowerCase()}">
                            <div class="flex justify-between items-start mb-1">
                                <span class="js-conversation-customer font-bold text-[12px] truncate pr-8">${conversation.customer_name || 'New Chat'}</span>
                                <span class="js-conversation-time text-[10px] text-gray-400 whitespace-nowrap">baru saja</span>
                            </div>
                            <div class="js-conversation-preview text-[11px] text-gray-500 truncate mb-2">${message?.message || '...'}</div>
                            <div class="flex items-center gap-1">
                                <span class="js-conversation-status px-1.5 py-0.5 rounded-sm font-bold uppercase text-[9px] ${statusClasses[conversation.status] || statusClasses.open}">${conversation.status}</span>
                                <span class="js-conversation-priority px-1.5 py-0.5 rounded-sm text-[9px] font-bold ${priorityClasses[conversation.priority] || priorityClasses.normal}">${(conversation.priority || 'normal').toUpperCase()}</span>
                                <span class="ml-auto text-[9px] text-gray-400 js-conversation-agent">${conversation.assigned_admin_name || 'Unassigned'}</span>
                            </div>
                            <span class="js-conversation-unread absolute top-3 right-3 bg-red-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full hidden">0</span>
                        </a>
                        <button class="absolute top-2 right-2 p-1.5 text-red-400 opacity-0 group-hover:opacity-100 transition-all hover:text-red-600 hover:bg-red-50 rounded-sm" 
                                title="Hapus Percakapan"
                                onclick="event.preventDefault(); event.stopPropagation(); window.deleteConversation(${conversation.id}, this.closest('.js-conversation-wrapper'))">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                `;
                item = tempDiv.firstElementChild;
                conversationListEl.prepend(item);
                item = item.querySelector('.js-conversation-item');
            }

            item.dataset.status = conversation.status;
            item.dataset.source = conversation.source_type;
            item.dataset.priority = conversation.priority;
            item.dataset.assigned = conversation.assigned_admin_id || '';
            item.dataset.search = `${conversation.customer_name || ''} ${conversation.subject || ''} ${conversation.source_label || ''} ${conversation.assigned_admin_name || ''}`.toLowerCase();

            const customerName = conversation.customer_name || 'Percakapan baru';
            const customerCode = conversation.customer_code || '';
            let customerHtml = customerName;
            if (customerCode && customerCode !== customerName) {
                customerHtml += ` <span class="text-[10px] font-medium opacity-60 ml-0.5">[${customerCode}]</span>`;
            }
            item.querySelector('.js-conversation-customer').innerHTML = customerHtml;
            item.querySelector('.js-conversation-preview').textContent = message?.message || item.querySelector('.js-conversation-preview').textContent;
            item.querySelector('.js-conversation-time').textContent = conversation.last_message_human || 'baru saja';
            item.querySelector('.js-conversation-status').textContent = conversation.status || 'open';
            item.querySelector('.js-conversation-status').className = `js-conversation-status px-1.5 py-0.5 rounded-sm ${statusClasses[conversation.status] || statusClasses.open} font-bold uppercase text-[9px]`;
            
            const subjectEl = item.querySelector('.js-conversation-subject');
            if (subjectEl) subjectEl.textContent = conversation.subject || 'Percakapan tanpa subjek';
            
            const priorityEl = item.querySelector('.js-conversation-priority');
            priorityEl.textContent = (conversation.priority || 'normal').toUpperCase();
            priorityEl.className = `js-conversation-priority px-1.5 py-0.5 rounded-sm text-[9px] font-bold ${priorityClasses[conversation.priority] || priorityClasses.normal}`;
            item.querySelector('.js-conversation-agent').textContent = conversation.assigned_admin_name || 'Unassigned';

            const unreadEl = item.querySelector('.js-conversation-unread');
            const unreadCount = Number(conversation.unread_for_admin_count || 0);
            if (currentConversationId && Number(conversation.id) === currentConversationId) {
                unreadEl.textContent = '0';
                unreadEl.classList.add('hidden');
            } else if (unreadCount > 0) {
                unreadEl.textContent = unreadCount;
                unreadEl.classList.remove('hidden');
            } else {
                unreadEl.textContent = '0';
                unreadEl.classList.add('hidden');
            }

            conversationListEl.prepend(item);
            renderFilters();
        };

        const appendMessage = (message) => {
            if (!listEl || knownMessageIds.has(Number(message.id))) return;
            const isAdmin = message.sender_type === 'admin';
            typingIndicator?.classList.add('hidden');
            listEl.insertAdjacentHTML('beforeend', buildMessageHtml(message, isAdmin));
            knownMessageIds.add(Number(message.id));
            scrollToBottom();
        };

        const updateAdminBubbleNames = (name) => {
            if (!listEl) return;
            listEl.querySelectorAll('.chat-admin-bubble--admin .text-[9px].uppercase').forEach(el => {
                el.textContent = name || 'Admin';
            });
        };

        window.deleteConversation = async (id, element) => {
            if (!confirm('Hapus percakapan ini secara permanen dari database?')) return;
            
            try {
                const response = await fetch(`/chat/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                });
                
                if (response.ok) {
                    element.remove();
                    if (currentConversationId === id) {
                        window.location.href = "{{ route('admin.chat.index') }}";
                    }
                } else {
                    alert('Gagal menghapus percakapan.');
                }
            } catch (error) {
                alert('Terjadi kesalahan jaringan.');
            }
        };

        const btnMarkAllRead = document.getElementById('btn-mark-all-read');
        if (btnMarkAllRead) {
            btnMarkAllRead.addEventListener('click', async () => {
                if (!confirm('Tandai semua percakapan sebagai telah dibaca?')) return;
                
                btnMarkAllRead.disabled = true;
                btnMarkAllRead.classList.add('opacity-50');
                
                try {
                    const response = await fetch("{{ route('admin.chat.mark_all_read') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                    });
                    
                        if (response.ok) {
                            document.querySelectorAll('.js-conversation-unread').forEach(el => {
                                el.textContent = '0';
                                el.classList.add('hidden');
                            });
                            
                            const unreadStat = document.querySelector('.chat-admin-stat--unread');
                            if (unreadStat) unreadStat.textContent = '0 unread';

                            window.dispatchEvent(new CustomEvent('admin-chat-mark-all-read'));
                        }
                } catch (error) {
                    console.error('Error marking all as read:', error);
                } finally {
                    btnMarkAllRead.disabled = false;
                    btnMarkAllRead.classList.remove('opacity-50');
                }
            });
        }

        const markConversationRead = async () => {
            if (!readUrl || !csrfToken) return;
            await fetch(readUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
            });
        };

        const sendTyping = async (isTyping) => {
            if (!typingUrl || !csrfToken) return;
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

        const saveWorkflow = async () => {
            if (!updateUrl || !csrfToken) return;
            workflowSaveButton.disabled = true;
            workflowSaveButton.textContent = 'Menyimpan...';

            try {
                const response = await fetch(updateUrl, {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        status: statusControl.value,
                        priority: priorityControl.value,
                        assigned_admin_id: agentControl.value || null,
                    }),
                });
                const payload = await response.json();
                updateConversationItem({ conversation: payload.conversation, message: null });
                updateAdminBubbleNames(payload.conversation.assigned_admin_name);
                document.getElementById('chat-current-agent').textContent = payload.conversation.assigned_admin_name || 'Belum di-assign';
                liveStatus.textContent = 'Workflow percakapan berhasil diperbarui.';
            } catch (error) {
                liveStatus.textContent = 'Gagal menyimpan workflow percakapan.';
            } finally {
                workflowSaveButton.disabled = false;
                workflowSaveButton.textContent = 'Simpan';
            }
        };

        workflowSaveButton?.addEventListener('click', saveWorkflow);

        quickReplyButtons.forEach((button) => {
            button.addEventListener('click', () => {
                const reply = button.dataset.reply || 'Baik, kami sedang cek sekarang.';
                if (!messageInput) return;
                messageInput.value = reply;
                messageInput.focus();
            });
        });

        window.addEventListener('admin-chat-message', async (event) => {
            const payload = event.detail;
            updateConversationItem(payload);

            if (currentConversationId && Number(payload.conversation.id) === currentConversationId) {
                if (payload.message) appendMessage(payload.message);
                if (payload.message?.sender_type === 'customer') await markConversationRead();
            }
        });

        window.addEventListener('admin-chat-conversation-updated', (event) => {
            const payload = event.detail;
            updateConversationItem(payload);
            if (currentConversationId && Number(payload.conversation.id) === currentConversationId) {
                updateAdminBubbleNames(payload.conversation.assigned_admin_name);
            }
        });

        window.addEventListener('admin-chat-typing', (event) => {
            const payload = event.detail;
            if (!currentConversationId || Number(payload.conversation_id) !== currentConversationId || payload.actor_type !== 'customer') return;

            if (payload.is_typing) {
                typingIndicator.textContent = `${payload.actor_name || 'Customer'} sedang mengetik...`;
                typingIndicator.classList.remove('hidden');
                clearTimeout(remoteTypingTimeout);
                remoteTypingTimeout = setTimeout(() => typingIndicator.classList.add('hidden'), 2500);
            } else {
                typingIndicator.classList.add('hidden');
            }
        });

        if (sendForm && messageInput && sendButton) {
            messageInput.addEventListener('input', () => {
                adminTypingHint?.classList.remove('hidden');
                clearTimeout(typingTimeout);
                sendTyping(true);
                typingTimeout = setTimeout(() => {
                    adminTypingHint?.classList.add('hidden');
                    sendTyping(false);
                }, 1200);
            });

            if (btnTriggerAttachment && attachmentInput) {
                btnTriggerAttachment.addEventListener('click', () => attachmentInput.click());
                attachmentInput.addEventListener('change', () => {
                    const file = attachmentInput.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            previewImg.src = e.target.result;
                            previewName.textContent = file.name;
                            previewSize.textContent = (file.size / 1024).toFixed(1) + ' KB';
                            attachmentPreview.classList.remove('hidden');
                        };
                        reader.readAsDataURL(file);
                    }
                });
                btnRemoveAttachment?.addEventListener('click', () => {
                    attachmentInput.value = '';
                    attachmentPreview.classList.add('hidden');
                });
            }

            const handleSendMessage = async (e) => {
                if (e) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                }
                const body = messageInput.value.trim();
                const attachmentFile = attachmentInput?.files[0];
                if (!body && !attachmentFile) return;

                sendButton.disabled = true;
                sendButton.textContent = 'Mengirim...';
                liveStatus.textContent = 'Mengirim pesan lewat jalur realtime...';
                adminTypingHint?.classList.add('hidden');
                clearTimeout(typingTimeout);
                sendTyping(false);

                try {
                    const socketId = window.Echo?.socketId?.();
                    const headers = {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    };
                    if (socketId) headers['X-Socket-ID'] = socketId;

                    const formData = new FormData(sendForm);
                    // Ensure the message from textarea is in sync with the formData
                    formData.set('message', body);
                    if (attachmentFile) formData.set('attachment', attachmentFile);

                    const response = await fetch(sendForm.action, {
                        method: 'POST',
                        headers: headers,
                        body: formData,
                    });
                    if (!response.ok) {
                        const errData = await response.json().catch(() => ({}));
                        throw new Error(errData.message || 'Gagal mengirim pesan');
                    }

                    const payload = await response.json();
                    appendMessage(payload.chat_message);
                    window.dispatchEvent(new CustomEvent('admin-chat-message', {
                        detail: {
                            conversation: { ...payload.conversation, unread_for_admin_count: 0 },
                            message: payload.chat_message,
                        }
                    }));
                    
                    // Reset
                    messageInput.value = '';
                    if (attachmentInput) attachmentInput.value = '';
                    attachmentPreview?.classList.add('hidden');
                    
                    liveStatus.textContent = 'Pesan terkirim dan inbox tersinkron realtime.';
                } catch (error) {
                    console.error('Send Error:', error);
                    liveStatus.textContent = 'Gagal kirim: ' + error.message;
                } finally {
                    sendButton.disabled = false;
                    sendButton.textContent = 'Kirim Pesan';
                }
            };

            sendForm.addEventListener('submit', handleSendMessage);
            messageInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    handleSendMessage(e);
                }
            });
        }

        // New Chat Modal Logic
        const newChatModal = document.getElementById('new-chat-modal');
        const btnOpenModal = document.getElementById('btn-open-new-chat-modal');
        const btnCloseModal = document.getElementById('btn-close-new-chat-modal');
        const btnCancelModal = document.getElementById('btn-cancel-new-chat-modal');

        if (newChatModal && btnOpenModal) {
            const toggleModal = (show) => {
                newChatModal.classList.toggle('is-active', show);
            };

            btnOpenModal.addEventListener('click', () => toggleModal(true));
            btnCloseModal?.addEventListener('click', () => toggleModal(false));
            btnCancelModal?.addEventListener('click', () => toggleModal(false));
            
            const newChatForm = newChatModal.querySelector('form');
            if (newChatForm) {
                newChatForm.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    const btn = newChatForm.querySelector('button[type="submit"]');
                    const originalText = btn.textContent;
                    try {
                        btn.disabled = true;
                        btn.textContent = 'Memproses...';
                        const formData = new FormData(newChatForm);
                        const response = await fetch(newChatForm.action, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                            body: formData
                        });
                        if (response.ok) {
                            const result = await response.json();
                            toggleModal(false);
                            newChatForm.reset();
                            // Redirect if necessary, or just update UI
                            if (result.redirect_url) {
                                window.location.href = result.redirect_url;
                            } else {
                                window.location.reload(); // Still reload if it's a completely new chat to initialize the chat room properly
                            }
                        } else {
                            alert('Gagal membuka percakapan baru.');
                        }
                    } catch (error) {
                        console.error(error);
                    } finally {
                        btn.disabled = false;
                        btn.textContent = originalText;
                    }
                });
            }

            newChatModal.addEventListener('click', (e) => {
                if (e.target === newChatModal) toggleModal(false);
            });
        }

        if (listEl) {
            window.addEventListener('load', scrollToBottom);
            setTimeout(scrollToBottom, 300);
        }
    })();
</script>
@endpush


