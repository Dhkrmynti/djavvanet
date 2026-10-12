<!-- resources/views/admin/partials/header.blade.php -->
<header class="bg-[var(--bg-header)] text-[var(--text-header)] flex items-center justify-between px-3 py-1 shrink-0 border-b border-[var(--border-header)] shadow-sm relative z-50 transition-colors">
  <div class="flex items-center gap-4 text-xs">
    <div class="flex items-center gap-2 mr-2">
      <button onclick="toggleSidebar()" class="hover:bg-blue-50 dark:hover:bg-blue-900/40 p-1 rounded transition-colors text-[var(--text-header)]/80 hover:text-[var(--text-header)] mr-1" title="Toggle Sidebar">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
      </button>
      <div class="flex items-center gap-2 border-r border-[var(--border-header)] pr-5">
        <svg class="w-4 h-4 text-blue-600 dark:text-blue-300" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
        <span class="font-black text-[12px] tracking-wide text-blue-700 dark:text-white">PT Djavva Network Solution</span>
      </div>
    </div>
    <div class="flex items-center gap-4 opacity-90 font-medium">
      <span class="text-green-600 dark:text-green-300 font-bold flex items-center gap-1.5">
         <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span>
         Connected
      </span>
    </div>
  </div>
  <div class="flex items-center gap-3">
    @if(auth()->user()->hasPermission('chat.manage'))
    <a href="{{ route('admin.chat.index') }}" class="relative inline-flex items-center justify-center h-8 w-8 rounded-sm border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 transition-colors" title="Live Chat Inbox">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-4l-4 4v-4H9z"/></svg>
      <span id="admin-chat-header-badge" data-count="{{ \App\Models\ChatConversation::sum('unread_for_admin_count') }}" class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-emerald-600 text-white text-[9px] font-black flex items-center justify-center {{ \App\Models\ChatConversation::sum('unread_for_admin_count') > 0 ? '' : 'hidden' }}">{{ \App\Models\ChatConversation::sum('unread_for_admin_count') }}</span>
    </a>
    @endif
    <div class="text-[9px] text-[var(--text-muted)] font-black opacity-80 tracking-widest hidden md:block select-none">
      {{ auth()->user()->name ?? auth()->user()->email ?? 'Admin' }}
    </div>
    
    <!-- Dark Mode Toggle -->
    <button onclick="toggleDarkMode()" id="theme-toggle-btn" class="bg-blue-50 dark:bg-blue-900/40 border border-blue-200 dark:border-blue-800 hover:bg-blue-100 dark:hover:bg-blue-900/60 p-1.5 rounded-sm shadow-sm transition-all active:scale-95 flex items-center justify-center" title="Toggle Theme">
       <span id="theme-icon-container"></span>
    </button>

    <form action="{{ route('admin.logout') }}" method="POST">
       @csrf
       <button type="submit" class="bg-red-600 hover:bg-red-500 px-3 py-1 rounded-sm border border-red-800 text-[10px] font-black uppercase tracking-wider text-white shadow-sm active:scale-95 transition-all">Logout</button>
    </form>
  </div>
</header>

<script>
    function updateThemeIcon() {
        const isDark = document.documentElement.classList.contains('dark');
        const container = document.getElementById('theme-icon-container');
        if (isDark) {
            container.innerHTML = `<svg class="w-3.5 h-3.5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"/></svg>`;
        } else {
            container.innerHTML = `<svg class="w-3.5 h-3.5 text-blue-600" fill="currentColor" viewBox="0 0 20 20"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/></svg>`;
        }
    }

    window.toggleDarkMode = function() {
        const isDark = document.documentElement.classList.toggle('dark');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
        updateThemeIcon();
    };

    window.toggleSidebar = function() {
        const sidebar = document.getElementById('main-sidebar');
        if (sidebar) {
            const isCollapsed = sidebar.classList.toggle('w-12');
            sidebar.classList.toggle('w-56');
            localStorage.setItem('sidebarCollapsed', isCollapsed ? 'true' : 'false');
        }
    };

    function updateAdminChatBadge(count) {
        const badge = document.getElementById('admin-chat-header-badge');
        if (!badge) return;

        badge.dataset.count = String(count);
        badge.textContent = count;
        badge.classList.toggle('hidden', count <= 0);

        const baseTitle = 'Admin Panel';
        document.title = count > 0 ? `(${count}) ${baseTitle}` : document.title.replace(/^\(\d+\)\s*/, '');
    }

    function playAdminChatNotification() {
        try {
            const audio = new Audio("{{ asset('fahh.mp3') }}");
            audio.play();
        } catch (error) {
        }
    }

    function handleAdminChatRealtime(payload) {
        const badge = document.getElementById('admin-chat-header-badge');
        const currentCount = Number(badge?.dataset.count || 0);
        const previousCount = window.__adminChatUnreadCount ?? currentCount;
        const incomingUnread = Number(payload?.conversation?.unread_for_admin_count || 0);
        const activeConversationId = Number(document.getElementById('chat-message-list')?.dataset.currentConversationId || 0);
        const sameConversation = activeConversationId && activeConversationId === Number(payload?.conversation?.id || 0);
        const nextCount = sameConversation
            ? Math.max(0, previousCount - incomingUnread)
            : previousCount + incomingUnread;

        window.__adminChatUnreadCount = nextCount;
        updateAdminChatBadge(nextCount);

        const sidebarBadge = document.getElementById('admin-chat-sidebar-badge');
        if (sidebarBadge) {
            sidebarBadge.textContent = nextCount;
            sidebarBadge.classList.toggle('hidden', nextCount <= 0);
        }

        if (payload?.message?.sender_type === 'customer' && !sameConversation) {
            playAdminChatNotification();
        }

        window.dispatchEvent(new CustomEvent('admin-chat-message', { detail: payload }));
    }

    // Init
    (function() {
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') document.documentElement.classList.add('dark');
        updateThemeIcon();
        updateAdminChatBadge(Number(document.getElementById('admin-chat-header-badge')?.dataset.count || 0));

        const initEcho = () => {
            if (window.Echo) {
                const globalChannel = window.Echo.private('admin.chat');
                globalChannel.listen('.chat.message.created', handleAdminChatRealtime);
                globalChannel.listen('.chat.conversation.updated', (payload) => {
                    window.dispatchEvent(new CustomEvent('admin-chat-conversation-updated', { detail: payload }));
                });
                globalChannel.listen('.chat.typing.updated', (payload) => {
                    window.dispatchEvent(new CustomEvent('admin-chat-typing', { detail: payload }));
                });

                // Listen for global mark all read event
                window.addEventListener('admin-chat-mark-all-read', () => {
                    window.__adminChatUnreadCount = 0;
                    updateAdminChatBadge(0);
                    const sbBadge = document.getElementById('admin-chat-sidebar-badge');
                    if (sbBadge) {
                        sbBadge.textContent = '0';
                        sbBadge.classList.add('hidden');
                    }
                });
            } else {
                setTimeout(initEcho, 500);
            }
        };
        initEcho();
    })();
</script>
