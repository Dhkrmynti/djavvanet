@extends('admin.layouts.app')

@section('title', 'Mikrotik API')

@section('content')
    <div class="bg-[var(--bg-card)] border border-[var(--border-main)] px-3 py-1.5 rounded-sm shadow-sm flex items-center justify-between shrink-0 mb-1 transition-colors">
      <div class="flex items-center gap-10">
        <div class="flex flex-col gap-0.5">
          <span class="text-[9px] font-bold text-blue-700 dark:text-blue-400 tracking-widest opacity-80 leading-none">Status Koneksi</span>
          <div class="flex items-center gap-2">
            @php
                $connOk = (bool)($settings?->is_active);
                $identity = $routerInfo['identity'] ?? '';
                $connText = $connOk ? ($identity ? 'CONNECTED: ' . $identity : 'CONNECTED') : 'DISCONNECTED';
            @endphp
            <span id="conn-indicator" class="h-2 w-2 rounded-full {{ $connOk ? 'bg-green-500 animate-pulse' : 'bg-red-500' }}"></span>
            <span id="conn-status" class="{{ $connOk ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }} text-[10px] font-black leading-none uppercase">{{ $connText }}</span>
          </div>
        </div>
        <div class="h-6 w-px bg-[var(--border-main)]"></div>
        <div class="flex flex-col gap-0.5">
          <span class="text-[9px] font-bold text-[var(--text-muted)] tracking-widest opacity-80 leading-none text-right">Update Terakhir</span>
          <span class="text-[10px] font-bold text-[var(--text-main)] leading-none text-right">{{ $settings?->last_connected_at?->translatedFormat('d M, H:i') ?? '—' }}</span>
        </div>
      </div>
      <button id="btn-test" onclick="testConnection()" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded font-semibold shadow-sm text-xs active:scale-95 transition-all flex items-center gap-1.5">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        Test Koneksi
      </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-2 flex-1 min-h-0">
      <!-- Left Column: Config -->
      <div class="lg:col-span-4 flex flex-col gap-2">
        <div class="flex flex-col border border-[var(--border-main)] rounded-sm bg-[var(--bg-card)] overflow-hidden shadow-sm transition-colors">
          <div class="wf-section-title">Konfigurasi API MikroTik</div>
          <form action="{{ route('admin.settings.mikrotik.save') }}" method="POST" class="p-3 space-y-3">
             @csrf
             <div class="space-y-1">
               <label class="text-[9px] font-black text-[var(--text-muted)]">Label Router</label>
               <input name="label" required class="w-full bg-[var(--bg-page)] border border-[var(--border-main)] rounded p-1.5 text-xs text-[var(--text-main)]" value="{{ $settings->label ?? '' }}">
             </div>
             <div class="grid grid-cols-3 gap-2">
                 <div class="col-span-2 space-y-1">
                   <label class="text-[9px] font-black text-[var(--text-muted)]">Host / IP Address</label>
                   <input name="host" required class="w-full bg-[var(--bg-page)] border border-[var(--border-main)] rounded p-1.5 text-xs font-mono text-blue-700 dark:text-blue-300" value="{{ $settings->host ?? '' }}">
                 </div>
                 <div class="space-y-1">
                   <label class="text-[9px] font-black text-[var(--text-muted)]">Port</label>
                   <input name="port" required type="number" class="w-full bg-[var(--bg-page)] border border-[var(--border-main)] rounded p-1.5 text-xs font-mono text-[var(--text-main)]" value="{{ $settings->port ?? 8728 }}">
                 </div>
             </div>
             <div class="grid grid-cols-2 gap-2">
                 <div class="space-y-1">
                   <label class="text-[9px] font-black text-[var(--text-muted)]">Username</label>
                   <input name="username" required class="w-full bg-[var(--bg-page)] border border-[var(--border-main)] rounded p-1.5 text-xs text-[var(--text-main)]" value="{{ $settings->username ?? '' }}">
                 </div>
                 <div class="space-y-1">
                   <label class="text-[9px] font-black text-[var(--text-muted)]">Password</label>
                   <input name="password" type="password" class="w-full bg-[var(--bg-page)] border border-[var(--border-main)] rounded p-1.5 text-xs text-[var(--text-main)]" placeholder="********">
                 </div>
             </div>
             <div class="grid grid-cols-2 gap-2 border-t border-[var(--border-sub)] pt-3">
                 <div class="space-y-1">
                   <label class="text-[9px] font-black text-[var(--text-muted)]">Isolir Profile</label>
                   <input name="isolir_profile" required class="w-full bg-[var(--bg-page)] border border-[var(--border-main)] rounded p-1.5 text-xs text-[var(--text-main)]" value="{{ $settings->isolir_profile ?? 'isolir' }}">
                 </div>
                 <div class="space-y-1">
                    <label class="text-[9px] font-black text-[var(--text-muted)]">Service</label>
                    <select name="default_service" class="w-full bg-[var(--bg-page)] border border-[var(--border-main)] rounded p-1.5 text-xs text-[var(--text-main)]">
                        <option value="any" {{ ($settings->default_service ?? '') == 'any' ? 'selected' : '' }}>Any</option>
                        <option value="pppoe" {{ ($settings->default_service ?? '') == 'pppoe' ? 'selected' : '' }}>PPPoE</option>
                    </select>
                 </div>
             </div>
             <input name="default_profile" type="hidden" value="{{ $settings->default_profile ?? 'default' }}">
             <button type="submit" class="w-full bg-[var(--bg-table-head)] border border-[var(--border-main)] hover:bg-[var(--nav-hover)] py-2 text-[10px] font-black rounded shadow-sm transition-colors mt-2">
                Simpan Konfigurasi →
             </button>
          </form>
        </div>

        <div class="bg-blue-50 dark:bg-blue-900/10 border border-blue-200 dark:border-blue-800 p-3 rounded-sm">
            <h4 class="text-[9px] font-black text-blue-700 dark:text-blue-400 tracking-widest mb-2 italic">Quick Stats</h4>
            <div class="flex justify-between text-[11px] font-bold text-[var(--text-main)]">
                <span>Total User: {{ $stats['customers'] }}</span>
                <span class="text-green-600 dark:text-green-400">Aktif: {{ $stats['active'] }}</span>
            </div>
        </div>
      </div>

      <!-- Right Column: Info & Actions -->
      <div class="lg:col-span-8 flex flex-col gap-2">
        <div id="router-info-card" class="flex flex-col border border-[var(--border-main)] rounded-sm bg-[var(--bg-card)] overflow-hidden shadow-sm transition-colors {{ empty($routerInfo) ? 'hidden' : '' }}">
          <div class="wf-section-title">Informasi Router</div>
          <div class="p-3 grid grid-cols-2 md:grid-cols-4 gap-4">
             <div class="space-y-0.5">
                <span class="text-[8px] font-black text-[var(--text-muted)]">Identity</span>
                <p id="info-identity" class="text-xs font-bold text-[var(--text-main)]">{{ $routerInfo['identity'] ?? '—' }}</p>
             </div>
             <div class="space-y-0.5">
                <span class="text-[8px] font-black text-[var(--text-muted)]">Board / Model</span>
                <p id="info-board" class="text-xs font-bold text-[var(--text-main)]">{{ $routerInfo['board'] ?? '—' }}</p>
             </div>
             <div class="space-y-0.5">
                <span class="text-[8px] font-black text-[var(--text-muted)]">ROS Version</span>
                <p id="info-version" class="text-xs font-bold text-[var(--text-main)] text-blue-600 dark:text-blue-400">{{ $routerInfo['version'] ?? '—' }}</p>
             </div>
             <div class="space-y-0.5">
                <span class="text-[8px] font-black text-[var(--text-muted)]">Uptime</span>
                <p id="info-uptime" class="text-xs font-bold text-[var(--text-main)]">{{ $routerInfo['uptime'] ?? '—' }}</p>
             </div>
             <div class="space-y-0.5">
                <span class="text-[8px] font-black text-[var(--text-muted)]">CPU Load</span>
                <p id="info-cpu" class="text-xs font-bold text-[var(--text-main)]">{{ ($routerInfo['cpu_load'] ?? '0') }}%</p>
             </div>
             <div class="space-y-0.5 bg-[var(--bg-page)] p-1 rounded-sm border border-[var(--border-main)]">
                <span class="text-[8px] font-black text-[var(--text-muted)]">Free Memory</span>
                <p id="info-mem" class="text-[10px] font-bold text-green-600 dark:text-green-400 italic">{{ $routerInfo['free_mem'] ?? '—' }}</p>
             </div>
          </div>
        </div>

        <div class="flex flex-col border border-[var(--border-main)] rounded-sm bg-[var(--bg-card)] overflow-hidden shadow-sm transition-colors">
          <div class="wf-section-title">Aksi & Sinkronisasi</div>
          <div class="p-4 grid grid-cols-1 gap-4">
             <div class="border border-[var(--border-main)] rounded p-3 bg-[var(--bg-page)]/50">
                <h5 class="text-[10px] font-black text-[var(--text-main)] mb-1">Cache Dashboard Data</h5>
                <p class="text-[10px] text-[var(--text-muted)] mb-3">Load data PPPoE Secret ke dalam cache untuk optimasi dashboard.</p>
                <button id="btn-secrets" onclick="loadSecrets()" class="w-full bg-[var(--bg-table-head)] border border-[var(--border-main)] hover:bg-[var(--nav-hover)] py-2 text-[10px] font-black rounded shadow-sm transition-all active:scale-95 text-[var(--text-main)]">
                    Load To Dashboard Cache
                </button>
             </div>
          </div>
        </div>
      </div>
    </div>
@endsection

@push('scripts')
<script>

    function setConnStatus(ok, label) {
        const ind = document.getElementById('conn-indicator');
        const st  = document.getElementById('conn-status');
        ind.className = `h-2 w-2 rounded-full ${ok ? 'bg-green-500 animate-pulse' : 'bg-red-500'}`;
        st.textContent = label;
        st.className   = `${ok ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'} text-[10px] font-black leading-none uppercase`;
    }

    async function testConnection() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const btn = document.getElementById('btn-test');
        btn.disabled = true;
        btn.innerHTML = '<span class="animate-spin">●</span> Loading...';
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
                const fmb = n => (parseInt(n) / 1048576).toFixed(1) + ' MB';
                document.getElementById('info-mem').textContent      = fmb(data.free_mem) + ' / ' + fmb(data.memory);
                alert(data.message);
            } else {
                setConnStatus(false, 'DISCONNECTED');
                alert(data.message);
            }
        } catch (e) {
            setConnStatus(false, 'ERROR');
            console.error('Test Connection Error:', e);
            alert('Koneksi Gagal/Error API. Detail: ' + e.message);
        }
        btn.disabled = false;
        btn.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg> Test Koneksi';
    }



    async function loadSecrets() {
        const btn = document.getElementById('btn-secrets');
        btn.disabled = true; btn.textContent = 'CACHING...';
        try {
            const res  = await fetch('{{ route("admin.settings.mikrotik.secrets") }}', { headers: {'Accept': 'application/json'} });
            const data = await res.json();
            if (data.ok) {
                alert(`${data.secrets.length} user dikumpulkan ke cache.`);
            } else { alert(data.message); }
        } catch(e) { alert('Load Error.'); }
        btn.disabled = false; btn.textContent = 'Load To Dashboard Cache';
    }
</script>
@endpush