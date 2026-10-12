@extends('admin.layouts.app')

@section('title', 'Mikrotik API')

@section('content')
    <!-- Connection Status Bar -->
    <div class="bg-white dark:bg-slate-800 border border-[var(--border-main)] px-4 py-2 rounded-sm shadow-sm flex items-center justify-between mb-3 transition-colors">
      <div class="flex items-center gap-8">
        <div class="flex flex-col gap-0.5">
          <span class="text-[9px] font-black text-blue-700 dark:text-blue-400 tracking-widest uppercase opacity-70">Status Koneksi</span>
          <div class="flex items-center gap-2">
            @php
                $connOk = (bool)($settings?->is_active);
                $identity = $routerInfo['identity'] ?? '';
                $connText = $connOk ? ($identity ? 'CONNECTED: ' . $identity : 'CONNECTED') : 'DISCONNECTED';
            @endphp
            <span id="conn-indicator" class="h-2.5 w-2.5 rounded-full {{ $connOk ? 'bg-green-500 animate-pulse shadow-[0_0_8px_rgba(34,197,94,0.6)]' : 'bg-red-500' }}"></span>
            <span id="conn-status" class="{{ $connOk ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }} text-[11px] font-black uppercase tracking-tight">{{ $connText }}</span>
          </div>
        </div>
        <div class="h-8 w-px bg-slate-200 dark:bg-slate-700"></div>
        <div class="flex flex-col gap-0.5">
          <span class="text-[9px] font-black text-slate-400 tracking-widest uppercase">Update Terakhir</span>
          <span class="text-[11px] font-bold text-slate-700 dark:text-slate-300">{{ $settings?->last_connected_at?->translatedFormat('d M Y, H:i') ?? '—' }}</span>
        </div>
      </div>
      <button id="btn-test" onclick="testConnection()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-1.5 rounded-sm font-bold shadow-sm text-[11px] active:scale-95 transition-all flex items-center gap-2 uppercase tracking-tight">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        Test Koneksi API
      </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-3 mb-4">
      <!-- Left Column: Config -->
      <div class="lg:col-span-5 flex flex-col gap-3">
        <div class="flex flex-col border border-[var(--border-main)] rounded-sm bg-white dark:bg-slate-800 overflow-hidden shadow-sm transition-colors">
          <div class="bg-slate-50 dark:bg-slate-900/50 px-3 py-2 border-b border-[var(--border-main)] flex items-center justify-between">
              <h3 class="text-[10px] font-black text-slate-700 dark:text-slate-300 uppercase tracking-widest italic">Konfigurasi API MikroTik</h3>
              <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37a1.724 1.724 0 002.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
          </div>
          <form action="{{ route('admin.settings.mikrotik.save') }}" method="POST" class="p-4 space-y-4">
             @csrf
             <div class="space-y-1.5">
               <label class="text-[9px] font-black text-slate-500 uppercase tracking-wider">Label / Nama Router</label>
               <input name="label" required class="w-full bg-slate-50 dark:bg-slate-900 border border-[var(--border-main)] rounded-sm p-2 text-xs font-bold text-slate-800 dark:text-slate-200 focus:ring-1 focus:ring-blue-500 outline-none" value="{{ $settings->label ?? '' }}" placeholder="Contoh: Router Core Utama">
             </div>
             <div class="grid grid-cols-3 gap-3">
                 <div class="col-span-2 space-y-1.5">
                   <label class="text-[9px] font-black text-slate-500 uppercase tracking-wider">Host / IP Address</label>
                   <input name="host" required class="w-full bg-slate-50 dark:bg-slate-900 border border-[var(--border-main)] rounded-sm p-2 text-xs font-mono font-bold text-blue-700 dark:text-blue-400 focus:ring-1 focus:ring-blue-500 outline-none" value="{{ $settings->host ?? '' }}" placeholder="192.168.1.1">
                 </div>
                 <div class="space-y-1.5">
                   <label class="text-[9px] font-black text-slate-500 uppercase tracking-wider">Port</label>
                   <input name="port" required type="number" class="w-full bg-slate-50 dark:bg-slate-900 border border-[var(--border-main)] rounded-sm p-2 text-xs font-mono font-bold text-slate-800 dark:text-slate-200 focus:ring-1 focus:ring-blue-500 outline-none" value="{{ $settings->port ?? 8728 }}">
                 </div>
             </div>
             <div class="grid grid-cols-2 gap-3">
                 <div class="space-y-1.5">
                   <label class="text-[9px] font-black text-slate-500 uppercase tracking-wider">Username API</label>
                   <input name="username" required class="w-full bg-slate-50 dark:bg-slate-900 border border-[var(--border-main)] rounded-sm p-2 text-xs font-bold text-slate-800 dark:text-slate-200 focus:ring-1 focus:ring-blue-500 outline-none" value="{{ $settings->username ?? '' }}">
                 </div>
                 <div class="space-y-1.5">
                   <label class="text-[9px] font-black text-slate-500 uppercase tracking-wider">Password API</label>
                   <input name="password" type="password" class="w-full bg-slate-50 dark:bg-slate-900 border border-[var(--border-main)] rounded-sm p-2 text-xs font-bold text-slate-800 dark:text-slate-200 focus:ring-1 focus:ring-blue-500 outline-none" placeholder="********">
                 </div>
             </div>
             <div class="grid grid-cols-2 gap-3 border-t border-slate-100 dark:border-slate-700 pt-4">
                 <div class="space-y-1.5">
                   <label class="text-[9px] font-black text-slate-500 uppercase tracking-wider">Isolir Profile</label>
                   <input name="isolir_profile" required class="w-full bg-slate-50 dark:bg-slate-900 border border-[var(--border-main)] rounded-sm p-2 text-xs font-bold text-red-600 dark:text-red-400 focus:ring-1 focus:ring-blue-500 outline-none" value="{{ $settings->isolir_profile ?? 'isolir' }}">
                 </div>
                 <div class="space-y-1.5">
                    <label class="text-[9px] font-black text-slate-500 uppercase tracking-wider">Default Service</label>
                    <select name="default_service" class="w-full bg-slate-50 dark:bg-slate-800 border border-[var(--border-main)] rounded-sm p-2 text-xs font-bold text-slate-800 dark:text-slate-200 outline-none cursor-pointer">
                        <option value="any" {{ ($settings->default_service ?? '') == 'any' ? 'selected' : '' }}>Any Service</option>
                        <option value="pppoe" {{ ($settings->default_service ?? '') == 'pppoe' ? 'selected' : '' }}>PPPoE Only</option>
                    </select>
                 </div>
             </div>
             <input name="default_profile" type="hidden" value="{{ $settings->default_profile ?? 'default' }}">
             <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2.5 text-[11px] font-black rounded-sm shadow-sm transition-all active:scale-95 uppercase tracking-widest mt-2">
                Simpan Konfigurasi & Sync
             </button>
          </form>
        </div>
      </div>

      <!-- Right Column: Info & Actions -->
      <div class="lg:col-span-7 flex flex-col gap-3">
        <!-- Router Info Card -->
        <div id="router-info-card" class="flex flex-col border border-[var(--border-main)] rounded-sm bg-white dark:bg-slate-800 overflow-hidden shadow-sm transition-colors {{ empty($routerInfo) ? 'hidden' : '' }}">
          <div class="bg-slate-50 dark:bg-slate-900/50 px-3 py-2 border-b border-[var(--border-main)] flex items-center justify-between">
              <h3 class="text-[10px] font-black text-slate-700 dark:text-slate-300 uppercase tracking-widest italic">Informasi Hardware Router</h3>
              <span class="text-[8px] font-bold bg-green-100 text-green-700 px-1.5 py-0.5 rounded-sm">REALTIME</span>
          </div>
          <div class="p-4 grid grid-cols-2 md:grid-cols-3 gap-6">
             <div class="flex flex-col">
                <span class="text-[8px] font-black text-slate-400 uppercase tracking-wider mb-1">Router Identity</span>
                <p id="info-identity" class="text-[13px] font-black text-slate-800 dark:text-white font-mono">{{ $routerInfo['identity'] ?? '—' }}</p>
             </div>
             <div class="flex flex-col">
                <span class="text-[8px] font-black text-slate-400 uppercase tracking-wider mb-1">Board / Model</span>
                <p id="info-board" class="text-[12px] font-bold text-slate-700 dark:text-slate-300">{{ $routerInfo['board'] ?? '—' }}</p>
             </div>
             <div class="flex flex-col">
                <span class="text-[8px] font-black text-slate-400 uppercase tracking-wider mb-1">ROS Version</span>
                <p id="info-version" class="text-[12px] font-black text-blue-600 dark:text-blue-400 font-mono">{{ $routerInfo['version'] ?? '—' }}</p>
             </div>
             <div class="flex flex-col">
                <span class="text-[8px] font-black text-slate-400 uppercase tracking-wider mb-1">System Uptime</span>
                <p id="info-uptime" class="text-[12px] font-bold text-slate-700 dark:text-slate-300">{{ $routerInfo['uptime'] ?? '—' }}</p>
             </div>
             <div class="flex flex-col">
                <span class="text-[8px] font-black text-slate-400 uppercase tracking-wider mb-1">CPU Load</span>
                <div class="flex items-center gap-2">
                    <p id="info-cpu" class="text-[13px] font-black text-slate-800 dark:text-white">{{ ($routerInfo['cpu_load'] ?? '0') }}%</p>
                    <div class="flex-1 h-1.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden max-w-[60px]">
                        <div id="cpu-bar" class="h-full bg-blue-500" style="width: {{ ($routerInfo['cpu_load'] ?? '0') }}%"></div>
                    </div>
                </div>
             </div>
             <div class="flex flex-col">
                <span class="text-[8px] font-black text-slate-400 uppercase tracking-wider mb-1">Free Memory</span>
                <p id="info-mem" class="text-[12px] font-black text-green-600 dark:text-green-400 italic">{{ $routerInfo['free_mem'] ?? '—' }}</p>
             </div>
          </div>
        </div>

        <!-- Caching Card -->
        <div class="flex flex-col border border-[var(--border-main)] rounded-sm bg-white dark:bg-slate-800 overflow-hidden shadow-sm transition-colors">
          <div class="bg-slate-50 dark:bg-slate-900/50 px-3 py-2 border-b border-[var(--border-main)]">
              <h3 class="text-[10px] font-black text-slate-700 dark:text-slate-300 uppercase tracking-widest italic">Pemeliharaan & Cache</h3>
          </div>
          <div class="p-4 grid grid-cols-2 gap-4">
             <div class="border border-slate-100 dark:border-slate-700 rounded-sm p-3 bg-slate-50/50 dark:bg-slate-900/30">
                <h5 class="text-[11px] font-black text-slate-800 dark:text-white mb-1 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
                    Sync Cache Dashboard
                </h5>
                <p class="text-[9px] text-slate-500 leading-tight mb-3">Tarik data PPPoE Secret terbaru dari router ke database lokal agar statistik dashboard lebih cepat.</p>
                <button id="btn-secrets" onclick="loadSecrets()" class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700 py-2 text-[10px] font-black rounded-sm shadow-sm transition-all active:scale-95 text-slate-700 dark:text-slate-200 uppercase tracking-tighter">
                    Sync Ke Cache Sekarang
                </button>
             </div>
             
             <div class="border border-blue-100 dark:border-blue-900/30 rounded-sm p-3 bg-blue-50/30 dark:bg-blue-900/10">
                <h5 class="text-[11px] font-black text-blue-800 dark:text-blue-300 mb-1 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    Statistik Singkat
                </h5>
                <div class="space-y-1.5 mt-2">
                    <div class="flex justify-between text-[10px] font-bold">
                        <span class="text-slate-500 uppercase">Pelanggan Aktif</span>
                        <span class="text-slate-800 dark:text-white">{{ $stats['customers'] }} User</span>
                    </div>
                    <div class="flex justify-between text-[10px] font-bold">
                        <span class="text-slate-500 uppercase">Status Mikrotik</span>
                        <span class="text-green-600 font-black">ACTIVE</span>
                    </div>
                </div>
             </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Mikrotik Real-time Status Table -->
    <div class="flex flex-col border border-[var(--border-main)] rounded-sm bg-white dark:bg-slate-800 shadow-sm transition-colors">
        <div class="bg-slate-50 dark:bg-slate-900/50 border-b border-[var(--border-main)] flex items-center gap-4 px-4 py-2 shrink-0">
           <div class="flex items-center gap-2">
             <span class="font-black uppercase text-[10px] text-slate-500 tracking-widest">LIMIT</span>
             <select id="mikrotik-length" class="bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 px-2 py-0.5 rounded-sm outline-none font-bold text-slate-800 dark:text-slate-200 text-[11px] cursor-pointer">
               <option value="10">10</option>
               <option value="25">25</option>
               <option value="50">50</option>
             </select>
           </div>
           <div class="flex-1 text-center">
               <h2 class="text-[11px] font-black text-blue-800 dark:text-blue-400 uppercase tracking-[0.3em] italic">Real-time PPPoE Secrets Monitoring</h2>
           </div>
           <div class="flex items-center border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 rounded-sm overflow-hidden px-2 py-1">
             <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
             <input type="text" id="mikrotik-search" placeholder="Cari Identitas User..." class="outline-none px-2 py-0 w-40 focus:w-64 transition-all text-[11px] bg-transparent text-slate-800 dark:text-slate-200">
           </div>
        </div>

        <div class="overflow-x-auto bg-white dark:bg-slate-900">
            <table id="mikrotikTable" class="w-full border-collapse tbl tbl-compact">
                <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-[var(--border-main)] sticky top-0 z-10 text-left">
                    <tr>
                        <th class="text-center w-10 px-3 py-2 text-[9px] font-black text-slate-400 uppercase border-r border-slate-100 dark:border-slate-700">#</th>
                        <th class="px-3 py-2 text-[9px] font-black text-slate-400 uppercase border-r border-slate-100 dark:border-slate-700">Identitas / Username</th>
                        <th class="px-3 py-2 text-[9px] font-black text-slate-400 uppercase border-r border-slate-100 dark:border-slate-700">Pelanggan (Database)</th>
                        <th class="px-3 py-2 text-[9px] font-black text-slate-400 uppercase border-r border-slate-100 dark:border-slate-700">Profile / Speed</th>
                        <th class="text-center w-16 px-3 py-2 text-[9px] font-black text-slate-400 uppercase border-r border-slate-100 dark:border-slate-700">Status</th>
                        <th class="text-center w-20 px-3 py-2 text-[9px] font-black text-slate-400 uppercase border-r border-slate-100 dark:border-slate-700">Sesi</th>
                        <th class="text-center w-16 px-3 py-2 text-[9px] font-black text-slate-400 uppercase">Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>

        <div class="bg-slate-50 dark:bg-slate-800/50 border-t border-[var(--border-main)] px-4 py-2 flex justify-between items-center text-[10px] text-slate-500 font-bold uppercase transition-colors">
            <span id="mikrotik-info">Memuat data dari router...</span>
            <div id="mikrotik-paginate" class="flex gap-1.5"></div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function setConnStatus(ok, label) {
        const ind = document.getElementById('conn-indicator');
        const st  = document.getElementById('conn-status');
        ind.className = `h-2.5 w-2.5 rounded-full ${ok ? 'bg-green-500 animate-pulse shadow-[0_0_8px_rgba(34,197,94,0.6)]' : 'bg-red-500'}`;
        st.textContent = label;
        st.className   = `${ok ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'} text-[11px] font-black uppercase tracking-tight`;
    }

    async function testConnection() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const btn = document.getElementById('btn-test');
        btn.disabled = true;
        btn.innerHTML = '<span class="animate-spin inline-block w-3 h-3 border-2 border-white border-t-transparent rounded-full"></span> TESTING...';
        try {
            const res  = await fetch('{{ route("admin.settings.mikrotik.test") }}', { method: 'POST', headers: {'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json'} });
            const data = await res.json();
            if (data.ok) {
                setConnStatus(true, 'CONNECTED: ' + data.identity);
                const card = document.getElementById('router-info-card');
                card.classList.remove('hidden');
                document.getElementById('info-identity').textContent = data.identity;
                document.getElementById('info-board').textContent    = data.board;
                document.getElementById('info-version').textContent  = data.version;
                document.getElementById('info-uptime').textContent   = data.uptime;
                document.getElementById('info-cpu').textContent      = data.cpu_load + '%';
                document.getElementById('cpu-bar').style.width       = data.cpu_load + '%';
                const fmb = n => (parseInt(n) / 1048576).toFixed(1) + ' MB';
                document.getElementById('info-mem').textContent      = fmb(data.free_mem) + ' / ' + fmb(data.memory);
                alert('Koneksi Berhasil!');
            } else {
                setConnStatus(false, 'DISCONNECTED');
                alert(data.message);
            }
        } catch (e) {
            setConnStatus(false, 'ERROR');
            alert('Koneksi Gagal: ' + e.message);
        }
        btn.disabled = false;
        btn.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg> Test Koneksi API';
    }

    async function loadSecrets() {
        const btn = document.getElementById('btn-secrets');
        btn.disabled = true; btn.textContent = 'CACHING...';
        try {
            const res  = await fetch('{{ route("admin.settings.mikrotik.secrets") }}', { headers: {'Accept': 'application/json'} });
            const data = await res.json();
            if (data.ok) {
                alert(`${data.secrets.length} data user berhasil di-cache.`);
                $('#mikrotikTable').DataTable().ajax.reload(null, false);
            } else { alert(data.message); }
        } catch(e) { alert('Sync Gagal.'); }
        btn.disabled = false; btn.textContent = 'Sync Ke Cache Sekarang';
    }

    $(document).ready(function() {
        const mikTable = $('#mikrotikTable').DataTable({
            processing: true,
            serverSide: false,
            ordering: true,
            order: [[1, 'asc']],
            ajax: {
                url: "{{ route('admin.settings.mikrotik.data') }}",
                error: function (xhr, error, code) {
                    console.log(xhr);
                    alert("Gagal memuat data: " + code);
                }
            },
            dom: 't',
            pageLength: 10,
            columns: [
                { data: null, orderable: false, render: (d,t,r,m) => m.row + 1, className: 'text-center border-r border-slate-50 dark:border-slate-800 text-slate-400' },
                { data: 'name', name: 'name', orderable: true, className: 'font-bold text-blue-700 dark:text-blue-400 px-3 border-r border-slate-50 dark:border-slate-800' },
                { data: 'db_customer', name: 'db_customer', orderable: true, className: 'px-3 border-r border-slate-50 dark:border-slate-800 py-1' },
                { data: 'profile', name: 'profile', orderable: true, className: 'font-mono text-[10px] px-3 border-r border-slate-50 dark:border-slate-800 text-slate-700 dark:text-slate-300' },
                { data: 'status', name: 'status', orderable: false, className: 'text-center border-r border-slate-50 dark:border-slate-800' },
                { data: 'online', name: 'online', orderable: false, className: 'text-center border-r border-slate-50 dark:border-slate-800' },
                { data: 'action', name: 'action', orderable: false, className: 'text-center' }
            ],
            drawCallback: function(settings) {
                const api = this.api();
                
                // Penomoran urut global
                api.column(0, {search:'applied', order:'applied'}).nodes().each(function (cell, i) {
                    cell.innerHTML = i + 1;
                });

                drawCustomPagination(api);
                const info = api.page.info();
                $('#mikrotik-info').text(`TAMPIL: ${info.start + 1}-${info.end} DARI ${info.recordsTotal} USER`);
            }
        });

        $('#mikrotik-length').on('change', function() { mikTable.page.len($(this).val()).draw(); });
        $('#mikrotik-search').on('keyup', function() { mikTable.search(this.value).draw(); });

        function drawCustomPagination(api) {
            const info = api.page.info();
            let html = '';
            html += `<button class="cust-page-btn" ${info.page === 0 ? 'disabled' : ''} onclick="changePage('prev')">PREV</button>`;
            for (let i = 0; i < info.pages; i++) {
                if (info.pages <= 5 || i === 0 || i === info.pages - 1 || Math.abs(i - info.page) <= 1) {
                    html += `<button class="cust-page-btn ${i === info.page ? 'active' : ''}" onclick="changePage(${i})">${i + 1}</button>`;
                } else if (i === 1 || i === info.pages - 2) {
                    html += `<span class="text-slate-400 px-1">..</span>`;
                }
            }
            html += `<button class="cust-page-btn" ${info.page === info.pages - 1 ? 'disabled' : ''} onclick="changePage('next')">NEXT</button>`;
            $('#mikrotik-paginate').html(html);
        }

        window.changePage = function(page) {
            if (page === 'prev') mikTable.page('previous').draw('page');
            else if (page === 'next') mikTable.page('next').draw('page');
            else mikTable.page(page).draw('page');
        };

        $(document).on('click', function(e) {
            if (!$(e.target).closest('.relative').length) {
                $('.action-dropdown').removeClass('show');
            }
        });
    });

    function toggleActionMenu(btn) {
        const dropdown = $(btn).next('.action-dropdown');
        $('.action-dropdown').not(dropdown).removeClass('show');
        dropdown.toggleClass('show');
    }

    function toggleUser(username, action) {
        if (!confirm(`Yakin ingin ${action === 'enable' ? 'mengaktifkan' : 'menonaktifkan'} user ${username}?`)) return;
        $.post("{{ route('admin.settings.mikrotik.toggle') }}", {
            _token: "{{ csrf_token() }}",
            username: username,
            action: action
        }).done(res => {
            alert(res.message);
            $('#mikrotikTable').DataTable().ajax.reload(null, false);
        });
    }

    function disconnectUser(username) {
        if (!confirm(`Yakin ingin memutus koneksi user ${username}?`)) return;
        $.post("{{ route('admin.settings.mikrotik.disconnect') }}", {
            _token: "{{ csrf_token() }}",
            username: username
        }).done(res => {
            alert(res.message);
            $('#mikrotikTable').DataTable().ajax.reload(null, false);
        });
    }

    function isolirUser(username) {
        if (!confirm(`Yakin ingin mengisolir user ${username}?`)) return;
        $.post("{{ route('admin.settings.mikrotik.isolir') }}", {
            _token: "{{ csrf_token() }}",
            username: username
        }).done(res => {
            alert(res.message);
            $('#mikrotikTable').DataTable().ajax.reload(null, false);
        });
    }

    function restoreUser(username) {
        if (!confirm(`Yakin ingin memulihkan user ${username}?`)) return;
        $.post("{{ route('admin.settings.mikrotik.restore') }}", {
            _token: "{{ csrf_token() }}",
            username: username
        }).done(res => {
            alert(res.message);
            $('#mikrotikTable').DataTable().ajax.reload(null, false);
        });
    }
</script>

<style>
.action-dropdown {
    display: none;
    position: absolute;
    right: 0;
    top: 100%;
    margin-top: 2px;
    width: 140px;
    background: white;
    border: 1px solid #e2e8f0;
    z-index: 100;
    box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1);
    border-radius: 2px;
}
.dark .action-dropdown { background: #1e293b; border-color: #334155; }
.action-dropdown.show { display: block; }
.action-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    width: 100%;
    text-align: left;
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
    border-bottom: 1px solid #f1f5f9;
}
.dark .action-item { border-bottom-color: #334155; color: #cbd5e1; }
.action-item:last-child { border-bottom: none; }
.action-item:hover { background-color: #f8fafc; color: #2563eb; }
.dark .action-item:hover { background-color: #334155; color: white; }

.cust-page-btn {
    padding: 3px 10px;
    border: 1px solid #e2e8f0;
    background: white;
    color: #64748b;
    border-radius: 2px;
    font-size: 9px;
    font-weight: 800;
    cursor: pointer;
    transition: all 0.2s;
}
.dark .cust-page-btn { background: #1e293b; border-color: #334155; color: #94a3b8; }
.cust-page-btn.active { background: #2563eb; color: white; border-color: #2563eb; }
.cust-page-btn:disabled { opacity: 0.4; cursor: not-allowed; }

@keyframes dot-blink {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.4; transform: scale(1.2); }
}
.dot-online { 
    width: 7px; 
    height: 7px; 
    border-radius: 50%; 
    display: inline-block; 
    background-color: #22c55e; 
    box-shadow: 0 0 8px rgba(34,197,94,0.5);
    animation: dot-blink 1.5s infinite ease-in-out;
}
</style>
@endpush