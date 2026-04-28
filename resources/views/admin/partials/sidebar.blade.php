<!-- resources/views/admin/partials/sidebar.blade.php -->
@php
    $currentRoute = Request::path();
@endphp
<aside id="main-sidebar" class="bg-[var(--bg-sidebar)] border-r border-[var(--border-header)] {{ (isset($_COOKIE['sidebarCollapsed']) && $_COOKIE['sidebarCollapsed'] === 'true') ? 'w-12' : 'w-56' }} flex flex-col shrink-0 overflow-hidden transition-all duration-200 z-40 shadow-sm relative transition-colors">
  <nav class="flex-1 overflow-y-auto py-1 custom-scrollbar">
    
    <!-- Dashboard -->
    @if(auth()->user()->hasPermission('dashboard.view'))
    <a href="{{ route('admin.dashboard.index') }}" class="nav-link {{ str_contains($currentRoute, 'dashboard') || $currentRoute == 'admin' ? 'active' : '' }}">
      <div class="flex items-center gap-2.5">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 11-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        <span class="nav-text font-medium truncate">Dashboard</span>
      </div>
    </a>
    @endif

    @if(auth()->user()->hasPermission('customers.view') || auth()->user()->hasPermission('invoices.view') || auth()->user()->hasPermission('packages.view') || auth()->user()->hasPermission('staff.manage') || auth()->check())
    <div class="text-[9px] font-black px-3 py-2 text-[var(--text-muted)] uppercase tracking-widest nav-text opacity-70">Manajemen</div>
    @endif

    <!-- Pelanggan -->
    @if(auth()->user()->hasPermission('customers.view'))
    <a href="{{ route('admin.customers') }}" class="nav-link {{ str_contains($currentRoute, 'customers') ? 'active' : '' }}">
      <div class="flex items-center gap-2.5">
         <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
         <span class="nav-text font-medium truncate">Pelanggan</span>
      </div>
      @php $count = \App\Models\Customer::count(); @endphp
      <span class="bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-white text-[9px] px-1 rounded-sm border border-blue-200 dark:border-blue-800 nav-arrow font-black">{{ $count }}</span>
    </a>
    @endif

    <!-- Tagihan -->
    @if(auth()->user()->hasPermission('invoices.view'))
    <a href="{{ route('admin.invoices') }}" class="nav-link {{ str_contains($currentRoute, 'invoices') ? 'active' : '' }}">
      <div class="flex items-center gap-2.5">
         <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
         <span class="nav-text font-medium truncate">Tagihan</span>
      </div>
      @php $unpaid = \App\Models\Invoice::where('status', 'unpaid')->count(); @endphp
      <span class="bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400 text-[9px] px-1 rounded-sm border border-orange-200 dark:border-orange-800 nav-arrow font-black">{{ $unpaid }}</span>
    </a>
    @endif

    <a href="{{ route('admin.chat.index') }}" class="nav-link {{ str_contains($currentRoute, 'chat') ? 'active' : '' }}">
      <div class="flex items-center gap-2.5">
         <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-4l-4 4v-4H9z"/></svg>
         <span class="nav-text font-medium truncate">Live Chat</span>
      </div>
      @php $chatUnread = \App\Models\ChatConversation::sum('unread_for_admin_count'); @endphp
      <span id="admin-chat-sidebar-badge" class="bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 text-[9px] px-1 rounded-sm border border-emerald-200 dark:border-emerald-800 nav-arrow font-black {{ $chatUnread > 0 ? '' : 'hidden' }}">{{ $chatUnread }}</span>
    </a>

    <!-- Paket Internet -->
    @if(auth()->user()->hasPermission('packages.view'))
    <a href="{{ route('admin.packages') }}" class="nav-link {{ str_contains($currentRoute, 'packages') ? 'active' : '' }}">
       <div class="flex items-center gap-2.5">
          <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
          <span class="nav-text font-medium truncate">Paket Internet</span>
       </div>
    </a>
    @endif

    @if(auth()->user()->hasPermission('staff.manage'))
    <!-- User Management -->
    <a href="{{ route('admin.staff.index') }}" class="nav-link {{ str_contains($currentRoute, 'staff') ? 'active' : '' }}">
      <div class="flex items-center gap-2.5">
         <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
         <span class="nav-text font-medium truncate">User Management</span>
      </div>
    </a>
    @endif

    @if(auth()->user()->hasPermission('settings.mikrotik') || auth()->user()->hasPermission('settings.xendit') || auth()->user()->hasPermission('settings.whatsapp'))
    <div class="text-[9px] font-black px-3 py-2 text-[var(--text-muted)] uppercase tracking-widest nav-text opacity-70">Layanan & API</div>
    @endif

    <!-- Mikrotik -->
    @if(auth()->user()->hasPermission('settings.mikrotik'))
    <a href="{{ route('admin.settings.mikrotik') }}" class="nav-link {{ str_contains($currentRoute, 'mikrotik') ? 'active' : '' }}">
      <div class="flex items-center gap-2.5">
         <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 00-2 2zM9 9h6v6H9V9z"/></svg>
         <span class="nav-text font-medium truncate">Mikrotik API</span>
      </div>
    </a>
    @endif

    <!-- Xendit -->
    @if(auth()->user()->hasPermission('settings.xendit'))
    <a href="{{ route('admin.settings.xendit') }}" class="nav-link {{ str_contains($currentRoute, 'xendit') ? 'active' : '' }}">
      <div class="flex items-center gap-2.5">
         <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
         <span class="nav-text font-medium truncate">Xendit Pay</span>
      </div>
    </a>
    @endif

    <!-- WhatsApp -->
    @if(auth()->user()->hasPermission('settings.whatsapp'))
    <a href="{{ route('admin.settings.whatsapp_template') }}" class="nav-link {{ str_contains($currentRoute, 'whatsapp') ? 'active' : '' }}">
      <div class="flex items-center gap-2.5">
         <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
         <span class="nav-text font-medium truncate">Template WA</span>
      </div>
    </a>
    @endif

  </nav>

  <!-- Sidebar Footer / Version Info -->
  <div class="p-3 bg-[var(--bg-table-head)] border-t border-[var(--border-header)] nav-text transition-colors">
     <div class="text-[8px] font-black text-[var(--text-muted)] tracking-wider">Session Active</div>
     <div class="text-[10px] font-bold text-blue-700 dark:text-white mt-0.5 truncate uppercase font-mono tracking-tighter">
         @auth 
            {{ auth()->user()->username ?? 'admin' }} 
            <span class="text-[8px] opacity-60">({{ auth()->user()->role ?? 'admin' }})</span>
         @endauth
     </div>
  </div>
</aside>

<script>
    // Handle initial collapse state without flicker
    (function() {
        const sidebar = document.getElementById('main-sidebar');
        const collapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        if (collapsed) {
            sidebar.classList.add('w-12');
            sidebar.classList.remove('w-56');
        } else {
            sidebar.classList.add('w-56');
            sidebar.classList.remove('w-12');
        }
    })();
</script>
