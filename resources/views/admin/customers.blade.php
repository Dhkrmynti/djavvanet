@extends('admin.layouts.app')

@section('title', 'Pelanggan')

@section('content')
    <!-- Page Title Bar -->
    <div class="bg-[var(--bg-card)] border border-[var(--border-main)] px-3 py-1.5 rounded-sm shadow-sm flex items-center justify-between shrink-0 mb-1 transition-colors">
      <div class="flex items-center gap-3">
        <h1 class="text-sm font-bold text-[var(--text-main)]">Pelanggan</h1>
        <div class="flex items-center gap-1">
          <span class="bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-white px-1.5 py-0.5 rounded text-[10px] font-bold" id="pelanggan-count">{{ $totalCustomers }} total pelanggan</span>
        </div>
      </div>
      <div class="flex items-center gap-2 text-xs font-semibold">
        @if(auth()->user()->hasPermission('customers.view'))
        <a href="{{ route('admin.customers.export.csv') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1 rounded-sm shadow-sm flex items-center gap-1.5 transition-colors">
          Download CSV
        </a>
        <a href="{{ route('admin.customers.export.excel') }}" class="bg-teal-600 hover:bg-teal-700 text-white px-3 py-1 rounded-sm shadow-sm flex items-center gap-1.5 transition-colors">
          Download Excel
        </a>
        @endif
        @if(auth()->user()->hasPermission('customers.whatsapp'))
        <button type="button" onclick="openBlastModal()" class="bg-orange-600 hover:bg-orange-700 text-white px-3 py-1 rounded-sm shadow-sm flex items-center gap-1.5 transition-colors">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
          Blast Informasi
        </button>
        @endif
        
        @if(auth()->user()->hasPermission('customers.create'))
        <button type="button" onclick="openSlideover()" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded-sm shadow-sm flex items-center gap-1.5 transition-colors">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
          Tambah Pelanggan
        </button>
        @endif
      </div>
    </div>

    @if(auth()->user()->hasPermission('customers.edit'))
    <form action="{{ route('admin.customers.backup_settings') }}" method="POST" class="bg-[var(--bg-card)] border border-[var(--border-main)] px-3 py-2 rounded-sm shadow-sm mb-1 text-[11px]">
      @csrf
      <div class="flex flex-wrap items-end gap-3">
        <!-- Mode Toggle -->
        <div>
          <label class="block text-[10px] font-bold text-[var(--text-muted)] mb-1">Mode Backup</label>
          <div class="flex items-center gap-0.5 bg-[var(--bg-table-head)] border border-[var(--border-main)] rounded-sm overflow-hidden">
            <label class="cursor-pointer">
              <input type="radio" name="backup_mode" value="manual" class="hidden peer" {{ ($backupSettings['mode'] ?? 'manual') === 'manual' ? 'checked' : '' }} onchange="toggleBackupMode()">
              <span class="block px-3 py-1 text-[11px] font-bold peer-checked:bg-blue-600 peer-checked:text-white transition-colors text-[var(--text-muted)] flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Manual
              </span>
            </label>
            <label class="cursor-pointer">
              <input type="radio" name="backup_mode" value="automatic" class="hidden peer" {{ ($backupSettings['mode'] ?? 'manual') === 'automatic' ? 'checked' : '' }} onchange="toggleBackupMode()">
              <span class="block px-3 py-1 text-[11px] font-bold peer-checked:bg-blue-600 peer-checked:text-white transition-colors text-[var(--text-muted)] flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Otomatis
              </span>
            </label>
          </div>
        </div>

        <!-- Automatic Settings (hidden when manual) -->
        <div id="auto-backup-settings" class="flex flex-wrap items-end gap-2" style="{{ ($backupSettings['mode'] ?? 'manual') === 'manual' ? 'display:none' : '' }}">
          <div>
            <label class="block text-[10px] font-bold text-[var(--text-muted)] mb-0.5">Frekuensi</label>
            <select name="frequency" class="border border-[var(--border-main)] bg-[var(--bg-card)] rounded-sm px-2 py-1 text-[11px]">
              <option value="disabled" {{ ($backupSettings['frequency'] ?? 'disabled') === 'disabled' ? 'selected' : '' }}>Nonaktif</option>
              <option value="daily" {{ ($backupSettings['frequency'] ?? '') === 'daily' ? 'selected' : '' }}>Harian</option>
              <option value="weekly" {{ ($backupSettings['frequency'] ?? '') === 'weekly' ? 'selected' : '' }}>Mingguan</option>
            </select>
          </div>
          <div>
            <label class="block text-[10px] font-bold text-[var(--text-muted)] mb-0.5">Hari (untuk mingguan)</label>
            <select name="weekday" class="border border-[var(--border-main)] bg-[var(--bg-card)] rounded-sm px-2 py-1 text-[11px]">
              @php
                $days = [
                  'monday' => 'Senin',
                  'tuesday' => 'Selasa',
                  'wednesday' => 'Rabu',
                  'thursday' => 'Kamis',
                  'friday' => 'Jumat',
                  'saturday' => 'Sabtu',
                  'sunday' => 'Minggu',
                ];
              @endphp
              @foreach($days as $key => $label)
                <option value="{{ $key }}" {{ ($backupSettings['weekday'] ?? 'monday') === $key ? 'selected' : '' }}>{{ $label }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="block text-[10px] font-bold text-[var(--text-muted)] mb-0.5">Jam Backup</label>
            <select name="hour" class="border border-[var(--border-main)] bg-[var(--bg-card)] rounded-sm px-2 py-1 text-[11px]">
              @for($h = 0; $h < 24; $h++)
                @php $hVal = str_pad($h, 2, '0', STR_PAD_LEFT); @endphp
                <option value="{{ $hVal }}" {{ ($backupSettings['hour'] ?? '06') === $hVal ? 'selected' : '' }}>{{ $hVal }}:00 WIB</option>
              @endfor
            </select>
          </div>
          <div class="flex-1 min-w-[240px]">
            <label class="block text-[10px] font-bold text-[var(--text-muted)] mb-0.5">Email Tujuan (pisahkan koma)</label>
            <input type="text" name="emails" value="{{ $backupSettings['emails'] ?? '' }}" placeholder="backup@domain.com, owner@domain.com" class="w-full border border-[var(--border-main)] bg-[var(--bg-card)] rounded-sm px-2 py-1 text-[11px]">
          </div>
        </div>

        <!-- Manual Info (hidden when automatic) -->
        <div id="manual-backup-info" class="flex items-center gap-2 text-[var(--text-muted)]" style="{{ ($backupSettings['mode'] ?? 'manual') !== 'manual' ? 'display:none' : '' }}">
          <span class="text-[10px]">Gunakan tombol <strong>Download CSV</strong> / <strong>Download Excel</strong> di atas untuk backup manual.</span>
        </div>

        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded-sm font-bold">Simpan</button>
      </div>
    </form>

    <script>
    function toggleBackupMode() {
      const mode = document.querySelector('input[name="backup_mode"]:checked').value;
      document.getElementById('auto-backup-settings').style.display = mode === 'automatic' ? '' : 'none';
      document.getElementById('manual-backup-info').style.display = mode === 'manual' ? '' : 'none';
    }
    </script>
    @endif

    <div class="bg-[var(--bg-card)] border border-[var(--border-main)] rounded-sm shadow-sm flex flex-col shrink-0 grow overflow-hidden transition-colors">
      <!-- Toolbar -->
      <div class="bg-[var(--bg-table-head)] border-b border-[var(--border-main)] flex items-center gap-2 px-2 py-1 shrink-0 grow-0 text-[11px] transition-colors">
         <div class="flex items-center gap-1.5 text-[var(--text-muted)]">
           <span>Tampil</span>
           <select id="customers-length" class="border border-[var(--border-main)] bg-[var(--bg-card)] px-1 py-0.5 rounded-sm outline-none font-bold text-[var(--text-main)]">
             <option>10</option><option>25</option><option>50</option>
           </select>
         </div>
         <div class="flex-1"></div>
         <div class="flex items-center border border-[var(--border-main)] bg-[var(--bg-card)] rounded overflow-hidden">
           <input id="customers-search" type="text" placeholder="Cari pelanggan..." class="outline-none px-2 py-0.5 w-32 focus:w-48 transition-all text-[11px] bg-[var(--bg-card)] text-[var(--text-main)]">
         </div>
      </div>

      <div class="flex-1 overflow-auto bg-[var(--bg-table-even)] transition-colors">
        <table id="customers-table" class="w-full border-collapse tbl tbl-compact">
          <thead>
            <tr class="font-bold text-[var(--text-muted)]">
              <th class="text-center w-8">#</th>
              <th>ID Pelanggan</th>
              <th>Nama Pelanggan</th>
              <th>Kontak/CP</th>
              <th>Alamat</th>
              <th>Paket</th>
              <th>Profile</th>
              <th class="text-right">Harga</th>
              <th class="text-center">Aktivasi</th>
              <th class="text-center">Status</th>
              <th class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody id="tbody-pelanggan"></tbody>
        </table>
      </div>

      <!-- Pagination bar -->
      <div class="bg-[var(--bg-table-head)] border-t border-[var(--border-main)] px-3 py-1.5 flex justify-between items-center text-[10px] text-[var(--text-muted)] shrink-0 transition-colors">
        <span id="customers-info">Memuat data pelanggan...</span>
        <div id="customers-paginate" class="flex gap-1"></div>
      </div>
    </div>

    <!-- SLIDE-OVER SECTION -->
    <div id="slideover-container" class="fixed inset-0 z-[60] pointer-events-none">
      <div id="slideover-overlay" onclick="closeSlideover()" class="absolute inset-0 bg-black/40 backdrop-blur-none opacity-0 pointer-events-none transition-opacity"></div>
      <div id="slideover-panel" class="absolute inset-y-0 right-0 w-full max-w-md bg-[var(--bg-card)] shadow-none border-l border-[var(--border-main)] flex flex-col translate-x-full pointer-events-auto transition-transform">
        <div class="bg-white dark:bg-slate-800 border-b border-[var(--border-main)] p-3 flex items-center justify-between shrink-0">
          <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
            <h2 id="slideover-title" class="text-[13px] font-bold text-blue-800 dark:text-white">Tambah Pelanggan Baru</h2>
          </div>
          <button onclick="closeSlideover()" class="text-blue-900/60 hover:text-blue-900 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>
        <div id="registration-tabs" class="flex border-b border-[var(--border-main)] shrink-0">
            <button type="button" onclick="switchTab('new')" id="tab-new" class="flex-1 py-2 text-[10px] font-black uppercase tracking-widest text-blue-600 border-b-2 border-blue-600 bg-blue-50/30 dark:bg-blue-900/10 transition-all">Pelanggan Baru</button>
            <button type="button" onclick="switchTab('existing')" id="tab-existing" class="flex-1 py-2 text-[10px] font-black uppercase tracking-widest text-slate-500 hover:text-slate-700 dark:text-slate-400 transition-all">Pelanggan Lama</button>
        </div>
        <form id="customer-form" action="{{ route('admin.customers.store') }}" method="POST" class="flex-1 flex flex-col bg-white dark:bg-slate-900 overflow-auto">
            <input type="hidden" name="registration_type" id="registration_type" value="new">
           @csrf
           <input type="hidden" name="_method" id="method-field" value="POST">
           
           <div class="p-0 flex-1 flex flex-col">
               <!-- INFO DASAR SECTION -->
               <div class="bg-slate-100 dark:bg-slate-800 px-3 py-2 border-b border-[var(--border-main)] text-[10px] font-black text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                 INFO DASAR
               </div>
               <div class="p-3 space-y-3">
                   <div class="flex gap-3">
                      <div class="w-1/3 space-y-1">
                        <label class="text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-tight">ID PELANGGAN *</label>
                        <input type="text" name="customer_code" id="input_customer_code" required placeholder="188xxx" oninput="debounceCheckId()" class="w-full bg-[#f8fafc] dark:bg-slate-800 border border-[var(--border-main)] rounded-sm px-2 text-xs focus:ring-1 focus:ring-blue-500 outline-none text-slate-800 dark:text-slate-200 font-bold h-[32px]">
                        <span id="code-error" class="text-[9px] text-red-500 font-bold hidden block mt-0.5">ID sudah terdaftar!</span>
                      </div>
                      <div class="w-2/3 space-y-1">
                        <label class="text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-tight">NAMA LENGKAP *</label>
                        <input type="text" name="full_name" required placeholder="Budi Santoso" class="w-full bg-[#f8fafc] dark:bg-slate-800 border border-[var(--border-main)] rounded-sm px-2 text-xs focus:ring-1 focus:ring-blue-500 outline-none text-slate-800 dark:text-slate-200 placeholder:text-slate-400 h-[32px]">
                      </div>
                   </div>
                  
                   <div class="space-y-1">
                     <label class="text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-tight">NO. WHATSAPP / CP *</label>
                     <input type="text" name="whatsapp_number" id="input_whatsapp" required placeholder="08123456789" oninput="debounceCheckWhatsApp()" class="w-full bg-[#f8fafc] dark:bg-slate-800 border border-[var(--border-main)] rounded-sm p-1.5 text-xs focus:ring-1 focus:ring-blue-500 outline-none text-slate-800 dark:text-slate-200 placeholder:text-slate-400">
                     <div id="wa-status" class="text-[9px] font-bold mt-0.5 hidden"></div>
                   </div>
                   <div class="space-y-1">
                     <label class="text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-tight">EMAIL</label>
                     <input type="email" name="email" placeholder="email@domain.com" class="w-full bg-[#f8fafc] dark:bg-slate-800 border border-[var(--border-main)] rounded-sm p-1.5 text-xs focus:ring-1 focus:ring-blue-500 outline-none text-slate-800 dark:text-slate-200 placeholder:text-slate-400">
                   </div>
                   <div class="space-y-1">
                     <label class="text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-tight">ALAMAT LENGKAP *</label>
                     <textarea name="address" required rows="3" placeholder="Jl. Merdeka No.10, Jakarta Pusat" class="w-full bg-[#f8fafc] dark:bg-slate-800 border border-[var(--border-main)] rounded-sm p-1.5 text-xs focus:ring-1 focus:ring-blue-500 outline-none text-slate-800 dark:text-slate-200 placeholder:text-slate-400"></textarea>
                   </div>
               </div>

               <!-- INFO LAYANAN SECTION -->
               <div class="bg-slate-100 dark:bg-slate-800 px-3 py-2 border-y border-[var(--border-main)] text-[10px] font-black text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                 INFO LAYANAN
               </div>
               <div class="p-3 space-y-3">
                   <div class="flex gap-3">
                       <div class="w-1/2 space-y-1">
                         <label class="text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-tight">PAKET INTERNET *</label>
                         <select name="package_id" required onchange="updateProfile(this)" class="w-full bg-[#f8fafc] dark:bg-slate-800 border border-[var(--border-main)] rounded-sm p-1.5 text-xs focus:ring-1 focus:ring-blue-500 outline-none text-slate-800 dark:text-slate-200 h-[32px]">
                            <option value="">-- Pilih Paket --</option>
                            @foreach($packages as $pkg)
                                <option value="{{ $pkg->id }}" data-profile="{{ $pkg->profile }}">{{ $pkg->name }}</option>
                            @endforeach
                         </select>
                       </div>
                       <div class="w-1/2 space-y-1">
                          <div class="flex justify-between items-end pb-0.5">
                              <label class="text-[10px] font-bold text-slate-600 dark:text-slate-400 tracking-tight">Profile Mikrotik *</label>
                              <button type="button" onclick="fetchMikrotikProfiles('pppoe_profile_select_cust', 'pppoe_profile', 'mikrotik_profile_override')" class="text-[9px] text-blue-600 hover:underline font-bold leading-none mb-[1px]">Ambil dari Mikrotik</button>
                          </div>
                          <input type="text" id="pppoe_profile" name="mikrotik_profile_override" placeholder="pppoe-10M" class="w-full bg-[#f8fafc] dark:bg-slate-800 border border-[var(--border-main)] rounded-sm px-2 text-xs focus:ring-1 focus:ring-blue-500 outline-none text-slate-800 dark:text-slate-200 font-mono h-[32px]">
                          <select id="pppoe_profile_select_cust" class="hidden w-full bg-[#f8fafc] dark:bg-slate-800 border border-[var(--border-main)] rounded-sm px-2 text-xs focus:ring-1 focus:ring-blue-500 outline-none text-slate-800 dark:text-slate-200 font-mono h-[32px]"></select>
                       </div>
                   </div>

                   <div class="flex gap-3">
                       <div class="w-1/2 space-y-1">
                         <label for="input_username" class="text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-tight">USERNAME MIKROTIK *</label>
                         <div class="flex items-stretch h-[32px]">
                             <input type="text" name="username" id="input_username" placeholder="1029xxx" class="flex-1 min-w-0 bg-[#f8fafc] dark:bg-slate-800 border border-[var(--border-main)] rounded-l-sm px-2 text-xs focus:ring-1 focus:ring-blue-500 outline-none text-slate-800 dark:text-slate-200 border-r-0">
                             <span id="domain_suffix" class="bg-gray-100 dark:bg-slate-700 border border-[var(--border-main)] rounded-r-sm px-2 text-[9px] font-bold text-slate-500 dark:text-slate-300 flex items-center whitespace-nowrap border-l-[0.5px]">@djavva.net</span>
                         </div>
                       </div>
                       <div class="w-1/2 space-y-1">
                         <label for="input_password" class="text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-tight">PASSWORD PPPOE</label>
                         <input type="text" name="password" id="input_password" placeholder="password123" class="w-full bg-[#f8fafc] dark:bg-slate-800 border border-[var(--border-main)] rounded-sm px-2 text-xs focus:ring-1 focus:ring-blue-500 outline-none text-slate-800 dark:text-slate-200 placeholder:text-slate-400 h-[32px]">
                       </div>
                   </div>

                   <div class="flex gap-3">
                       <div class="w-1/2 space-y-1">
                         <label class="text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-tight">DOMAIN *</label>
                         <select name="domain" required onchange="fetchNextId()" class="w-full bg-[#f8fafc] dark:bg-slate-800 border border-[var(--border-main)] rounded-sm p-1.5 text-xs focus:ring-1 focus:ring-blue-500 outline-none text-slate-800 dark:text-slate-200 h-[32px]">
                            <option value="djavva.net">djavva.net</option>
                            <option value="urban.wifi">urban.wifi</option>
                         </select>
                       </div>
                       <div class="w-1/2 space-y-1">
                          <label class="text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-tight">TANGGAL TAGIHAN</label>
                          <div class="w-full bg-slate-100 dark:bg-slate-800/50 border border-[var(--border-main)] rounded-sm px-2 py-1.5 text-xs font-bold text-blue-600 dark:text-blue-400 h-[32px] flex items-center gap-1.5">
                             <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                             Tanggal 1 (Fixed)
                          </div>
                          <input type="hidden" name="billing_cycle" value="1">


                    <div class="space-y-1" id="active-since-field-group">
                      <label class="text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-tight">TANGGAL AKTIF / AKTIVASI *</label>
                      <input type="date" name="active_since" id="input_active_since" required class="w-full bg-[#f8fafc] dark:bg-slate-800 border border-[var(--border-main)] rounded-sm p-1.5 text-xs focus:ring-1 focus:ring-blue-500 outline-none text-slate-800 dark:text-slate-200">
                      <p class="text-[9px] text-slate-400 mt-0.5">Digunakan untuk menentukan perhitungan tagihan (Prorata).</p>
                    </div>
                          <select name="unused_billing_cycle" class="hidden">
                            <option value="">-- Pilih tanggal --</option>
                            @for($i=1; $i<=28; $i++)
                             <option value="{{ $i }}">Tanggal {{ $i }}</option>
                            @endfor
                         </select>
                        </div>
                    </div>
                   <div class="space-y-1" id="status-field-group">
                     <label class="text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-tight">STATUS PELANGGAN *</label>
                     <select name="status" class="w-full bg-[#f8fafc] dark:bg-slate-800 border border-[var(--border-main)] rounded-sm p-1.5 text-xs focus:ring-1 focus:ring-blue-500 outline-none text-slate-800 dark:text-slate-200">
                        <option value="active">Aktif</option>
                        <option value="suspend">Isolir</option>
                        <option value="inactive">Non-aktif</option>
                     </select>
                   </div>
               </div>
               
               <!-- Footer Aksi -->
               <div class="mt-auto bg-slate-50 dark:bg-slate-800 p-3 border-t border-[var(--border-main)] flex gap-2">
                  <button type="button" onclick="closeSlideover()" class="flex-1 bg-white dark:bg-slate-700 border border-[var(--border-main)] text-slate-700 dark:text-slate-200 font-bold py-1.5 rounded-sm hover:bg-slate-50 dark:hover:bg-slate-600 active:scale-95 transition-all text-[11px]">Batal</button>
                  <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-1.5 rounded-sm shadow-sm active:scale-95 transition-all text-[11px]">Simpan</button>
               </div>
           </div>
        </form>
      </div>
    </div>

    <!-- DETAIL PELANGGAN MODAL (Mikrotik/WebFig Style) -->
    <div id="detail-modal-container" class="fixed inset-0 z-[70] flex items-center justify-center p-4 transition-all duration-300 opacity-0 pointer-events-none">
      <div id="detail-modal-overlay" onclick="closeDetail()" class="absolute inset-0 bg-black/50"></div>
      <div id="detail-modal-panel" class="relative w-full max-w-lg bg-white dark:bg-slate-900 border border-[var(--border-main)] rounded-sm flex flex-col shadow-2xl transform scale-95 transition-all duration-300">
        <div class="bg-white dark:bg-slate-800 border-b border-[var(--border-main)] p-2 flex items-center justify-between shrink-0">
          <h2 class="text-[12px] font-bold text-blue-800 dark:text-white">Detail Pelanggan — <span id="det-name-title">...</span></h2>
          <button onclick="closeDetail()" class="text-blue-900/60 hover:text-blue-900 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>
        
        <div class="flex-1 overflow-auto bg-white dark:bg-slate-900 border-b border-[var(--border-main)]">
           <div class="grid grid-cols-2">
              <div class="p-2 border-r border-b border-gray-200 dark:border-slate-800">
                <label class="text-[9px] font-bold text-gray-400 uppercase block leading-tight">ID PELANGGAN</label>
                <div class="text-[11px] font-bold text-slate-800 dark:text-slate-200" id="det-id">...</div>
              </div>
              <div class="p-2 border-b border-gray-200 dark:border-slate-800">
                <label class="text-[9px] font-bold text-gray-400 uppercase block leading-tight">STATUS</label>
                <div id="det-status-container" class="mt-0.5">...</div>
              </div>
              
              <div class="p-2 border-r border-b border-gray-200 dark:border-slate-800">
                <label class="text-[9px] font-bold text-gray-400 uppercase block leading-tight">NAMA CUSTOMER</label>
                <div class="text-[11px] font-bold text-slate-800 dark:text-slate-200" id="det-name">...</div>
              </div>
              <div class="p-2 border-b border-gray-200 dark:border-slate-800">
                <label class="text-[9px] font-bold text-gray-400 uppercase block leading-tight">TANGGAL AKTIVASI</label>
                <div class="text-[11px] font-bold text-blue-600 dark:text-blue-400" id="det-active-since">-</div>
              </div>

              <div class="p-2 border-r border-b border-gray-200 dark:border-slate-800">
                <label class="text-[9px] font-bold text-gray-400 uppercase block leading-tight">PAKET</label>
                <div class="text-[11px] font-bold text-slate-800 dark:text-slate-200" id="det-package">...</div>
              </div>
              <div class="p-2 border-r border-b border-gray-200 dark:border-slate-800">
                <label class="text-[9px] font-bold text-gray-400 uppercase block leading-tight">CONTACT PERSON</label>
                <div class="text-[11px] font-bold text-slate-800 dark:text-slate-200" id="det-cp">...</div>
              </div>
              <div class="p-2 border-b border-gray-200 dark:border-slate-800">
                <label class="text-[9px] font-bold text-gray-400 uppercase block leading-tight">HARGA</label>
                <div class="text-[11px] font-bold text-slate-800 dark:text-slate-200" id="det-price">...</div>
              </div>

              <div class="p-2 border-r border-b border-gray-200 dark:border-slate-800">
                <label class="text-[9px] font-bold text-gray-400 uppercase block leading-tight">USERNAME MIKROTIK</label>
                <div class="text-[11px] font-bold text-slate-800 dark:text-slate-200" id="det-username">...</div>
              </div>
              <div class="p-2 border-b border-gray-200 dark:border-slate-800">
                <label class="text-[9px] font-bold text-gray-400 uppercase block leading-tight">PASSWORD PPPOE</label>
                <div class="text-[11px] font-bold text-orange-600 dark:text-orange-400 font-mono" id="det-password">...</div>
              </div>

              <div class="p-2 border-r border-b border-gray-200 dark:border-slate-800">
                <label class="text-[9px] font-bold text-gray-400 uppercase block leading-tight">PROFILE MIKROTIK</label>
                <div class="text-[11px] font-bold text-slate-800 dark:text-slate-200" id="det-profile">...</div>
              </div>
              <div class="p-2 border-b border-gray-200 dark:border-slate-800 bg-gray-50/10">
                <label class="text-[9px] font-bold text-gray-400 uppercase block leading-tight">ALAMAT</label>
                <div class="text-[11px] font-bold text-slate-800 dark:text-slate-200 leading-normal" id="det-address">...</div>
              </div>
           </div>
        </div>

         <div class="p-2 flex justify-end gap-2 bg-[#eef2f7] dark:bg-slate-800/50">
            <button type="button" onclick="copyDetail(event)" class="bg-white dark:bg-slate-700 border border-blue-600 dark:border-blue-500 text-blue-700 dark:text-blue-400 px-3 py-1 rounded-md text-[11px] font-bold hover:bg-blue-50 dark:hover:bg-slate-600 transition-all flex items-center gap-1.5 cursor-pointer">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
              Copy Data
            </button>
            @if(auth()->user()->hasPermission('customers.edit'))
            <button type="button" onclick="event.stopPropagation(); editFromDetail()" class="bg-white dark:bg-slate-700 border border-orange-500 dark:border-orange-600 text-orange-600 dark:text-orange-400 px-3 py-1 rounded-md text-[11px] font-bold hover:bg-orange-50 dark:hover:bg-slate-600 transition-all">Edit Pelanggan</button>
            @endif
            <button type="button" onclick="event.stopPropagation(); closeDetail()" class="bg-white dark:bg-slate-700 border border-gray-400 dark:border-slate-500 text-gray-600 dark:text-slate-300 px-4 py-1 rounded-md text-[11px] font-bold hover:bg-gray-50 dark:hover:bg-slate-600 transition-all">Tutup</button>
        </div>
      </div>
    </div>
    <!-- RIWAYAT TAGIHAN MODAL (Mikrotik/WebFig Style) -->
    <div id="history-modal-container" class="fixed inset-0 z-[70] flex items-center justify-center p-4 transition-all duration-300 opacity-0 pointer-events-none">
      <div id="history-modal-overlay" onclick="closeHistory()" class="absolute inset-0 bg-black/50"></div>
      <div id="history-modal-panel" class="relative w-full max-w-2xl bg-white border border-[var(--border-main)] rounded-sm flex flex-col shadow-2xl transform scale-95 transition-all duration-300">
        <div class="bg-white dark:bg-slate-800 border-b border-[var(--border-main)] p-2 flex items-center justify-between shrink-0">
          <h2 class="text-[12px] font-bold text-blue-800">Riwayat Tagihan — <span id="hist-name-title">...</span></h2>
          <button onclick="closeHistory()" class="text-blue-900/60 hover:text-blue-900 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>
        
        <div class="flex-1 overflow-auto bg-white dark:bg-slate-900 border-b border-gray-200 dark:border-slate-800">
           <table class="w-full border-collapse text-[11px]">
              <thead>
                 <tr class="bg-slate-100 dark:bg-slate-800 border-b border-gray-300 dark:border-slate-700">
                    <th class="p-2 text-left border-r border-gray-300 dark:border-slate-700 font-bold text-slate-700 dark:text-slate-300">No Invoice</th>
                    <th class="p-2 text-left border-r border-gray-300 dark:border-slate-700 font-bold text-slate-700 dark:text-slate-300">Periode</th>
                    <th class="p-2 text-right border-r border-gray-300 dark:border-slate-700 font-bold text-slate-700 dark:text-slate-300">Total</th>
                    <th class="p-2 text-center border-r border-gray-300 dark:border-slate-700 font-bold text-slate-700 dark:text-slate-300">Status</th>
                    <th class="p-2 text-center font-bold text-slate-700 dark:text-slate-300">Aksi</th>
                 </tr>
              </thead>
              <tbody id="history-table-body">
                 <!-- Rows will be injected here -->
              </tbody>
           </table>
        </div>

        <div class="p-2 flex justify-between items-center bg-[#eef2f7] dark:bg-slate-800/50">
            <span class="text-[10px] font-bold text-slate-500 italic">5 bulan terakhir</span>
            <button type="button" onclick="closeHistory()" class="bg-white dark:bg-slate-700 border border-gray-400 dark:border-slate-500 text-gray-600 dark:text-slate-300 px-6 py-1 rounded-md text-[11px] font-bold hover:bg-gray-50 dark:hover:bg-slate-600 transition-all uppercase leading-none h-[25px]">Tutup</button>
        </div>
      </div>
    </div>
    <form id="delete-customer-form" action="" method="POST" class="hidden">
      @csrf
      @method('DELETE')
    </form>

    <!-- BLAST MODAL -->
    <div id="blast-modal-container" class="fixed inset-0 z-[70] flex items-center justify-center p-4 transition-all duration-300 opacity-0 pointer-events-none">
      <div id="blast-modal-overlay" onclick="closeBlastModal()" class="absolute inset-0 bg-black/50"></div>
      <div id="blast-modal-panel" class="relative w-full max-w-md bg-white dark:bg-slate-900 border border-[var(--border-main)] rounded-sm flex flex-col shadow-2xl transform scale-95 transition-all duration-300">
        <div class="bg-white dark:bg-slate-800 border-b border-[var(--border-main)] p-2 flex items-center justify-between shrink-0 text-orange-600">
          <h2 class="text-[12px] font-bold flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
            Blast Informasi (Maintenance)
          </h2>
          <button onclick="closeBlastModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>
        
        <div class="p-4 space-y-4">
            <div class="bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-900/50 p-3 rounded-sm">
                <p class="text-[11px] text-orange-800 dark:text-orange-300 leading-relaxed font-medium">
                    Fitur ini akan mengirimkan pesan **maintenance** ke seluruh pelanggan yang berstatus **AKTIF**. 
                    Pastikan template dan gambar sudah diatur di menu Pengaturan WA.
                </p>
            </div>

            <div class="space-y-2">
                <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Target Pelanggan</label>
                <div class="flex items-center gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="blast_target" value="all" checked class="text-blue-600 focus:ring-blue-500">
                        <span class="text-[11px] font-bold text-slate-700 dark:text-slate-300">Seluruh Pelanggan Aktif</span>
                    </label>
                </div>
            </div>
            
            <div class="p-3 border border-[var(--border-main)] rounded-sm bg-slate-50 dark:bg-slate-800/50">
               <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">Pratinjau Pesan</label>
               <p class="text-[10px] italic text-slate-500">Isi pesan akan sesuai dengan template "Maintenance" di Pengaturan WhatsApp.</p>
            </div>
        </div>

        <div class="p-2 flex justify-end gap-2 bg-slate-50 dark:bg-slate-800 border-t border-[var(--border-main)]">
            <button type="button" onclick="closeBlastModal()" class="bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-300 px-4 py-1.5 rounded-sm text-[11px] font-bold hover:bg-slate-50 transition-all">Batal</button>
            <button type="button" onclick="executeBlast()" id="btn-execute-blast" class="bg-orange-600 text-white px-6 py-1.5 rounded-sm text-[11px] font-bold hover:bg-orange-700 shadow-sm transition-all flex items-center gap-2">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Mulai Kirim Sekarang
            </button>
        </div>
      </div>
    </div>
@endsection

@push('scripts')
<script>
    const canEditCustomer = @json(auth()->user()->hasPermission('customers.edit'));
    const canDeleteCustomer = @json(auth()->user()->hasPermission('customers.delete'));
    const canSyncCustomer = @json(auth()->user()->hasPermission('customers.sync'));

    $(document).ready(function() {
      const table = $('#customers-table').DataTable({
        processing: true,
        serverSide: true,
        ordering: true,
        searching: true,
        pageLength: 10,
        lengthChange: false,
        dom: 't',
        ajax: '{{ route("admin.customers.data") }}',
        columns: [
          { 
            data: 'id', 
            name: 'id', 
            orderable: false,
            searchable: false,
            className: 'text-center font-bold text-[#888]',
            render: function(data, type, row, meta) {
              return meta.row + meta.settings._iDisplayStart + 1;
            }
          },
          {
            data: 'customer_code',
            name: 'customer_code',
            className: 'font-bold text-[#0000ff] dark:text-white',
            render: function(data) {
              return `<button type="button" class="hover:underline" onclick="openDetail(this.closest('tr'))">${data ?? '-'}</button>`;
            }
          },
          { data: 'full_name', name: 'full_name', className: 'font-bold' },
          { data: 'whatsapp_number', name: 'whatsapp_number', className: 'font-mono text-[9px]' },
          { data: 'address', name: 'address', className: 'max-w-[150px] truncate' },
          { data: 'package_name', name: 'package_name' },
          { data: 'profile_name', name: 'profile_name', className: 'font-mono text-[9px] text-[#666]' },
          {
            data: 'monthly_price',
            name: 'monthly_price',
            className: 'text-right font-bold',
            render: function(data) {
              const price = Number(data || 0);
              return `Rp ${price.toLocaleString('id-ID')}`;
            }
          },
          { 
            data: 'active_since', 
            name: 'active_since', 
            className: 'text-center font-mono text-[10px]',
            render: function(data) {
                if(!data) return '-';
                const d = new Date(data);
                return d.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' });
            }
          },
          {
            data: 'status',
            name: 'status',
            className: 'text-center',
            render: function(data) {
              if (data === 'active') return '<span class="badge-up">Aktif</span>';
              if (data === 'suspend') return '<span class="badge-warn">Isolir</span>';
              return '<span class="badge-down">Non-aktif</span>';
            }
          },
          {
            data: null,
            orderable: false,
            searchable: false,
            className: 'text-center',
            render: function(data, type, row) {
              const nameEscaped = (row.full_name || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');
              let actionItems = `
                <button onclick="openDetail(this.closest('tr'))" class="action-item block w-full text-left px-3 py-1.5 text-[10px] hover:bg-blue-50 hover:text-blue-700 border-b border-gray-100 flex items-center gap-2">
                  Detail
                </button>
              `;

              if (canEditCustomer) {
                actionItems += `
                  <button onclick="openSlideover('edit', JSON.parse(this.closest('tr').dataset.json))" class="action-item block w-full text-left px-3 py-1.5 text-[10px] hover:bg-orange-50 hover:text-orange-700 border-b border-gray-100 flex items-center gap-2 font-bold">
                    Edit
                  </button>
                `;
              }

              actionItems += `
                <button onclick="openHistory(this.closest('tr'))" class="action-item block w-full text-left px-3 py-1.5 text-[10px] hover:bg-green-50 hover:text-green-700 border-b border-gray-100 flex items-center gap-2">
                  Riwayat
                </button>
                <button type="button" onclick="sendGreeting('${row.id}', this)" class="action-item block w-full text-left px-3 py-1.5 text-[10px] hover:bg-teal-50 hover:text-teal-700 border-b border-gray-100 flex items-center gap-2 font-bold">
                  Kirim Greeting
                </button>
              `;

              if (canSyncCustomer && !row.is_synced) {
                actionItems += `
                  <button onclick="syncSingleCustomer('${row.id}', this)" class="action-item block w-full text-left px-3 py-1.5 text-[10px] hover:bg-purple-50 hover:text-purple-700 border-b border-gray-100 flex items-center gap-2 font-bold">
                    Push ke Router
                  </button>
                `;
              }

              if (canDeleteCustomer) {
                actionItems += `
                  <button onclick="confirmDeleteCustomer('${row.id}', '${nameEscaped}')" class="action-item block w-full text-left px-3 py-1.5 text-[10px] hover:bg-red-50 hover:text-red-700 flex items-center gap-2 font-bold">
                    Hapus
                  </button>
                `;
              }

              return `
                <div class="relative inline-block text-left">
                  <button type="button" onclick="toggleActionDropdown(event, this)" class="text-[var(--text-main)] p-1 rounded-full hover:bg-[var(--nav-hover)] transition-all flex items-center justify-center w-7 h-7 leading-none">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 10c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0-6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 12c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg>
                  </button>
                  <div class="action-dropdown absolute right-0 top-full mt-1 w-28 bg-[var(--bg-card)] border border-[var(--border-main)] rounded shadow-lg z-50 overflow-hidden">
                    ${actionItems}
                  </div>
                </div>
              `;
            }
          }
        ],
        createdRow: function(row, data) {
          row.classList.add('pelanggan-row', 'transition-colors');
          row.dataset.id = data.id;
          row.dataset.json = JSON.stringify(data.raw_customer ?? {});
          row.setAttribute('onclick', 'selectRow(this)');
        },
        rowCallback: function(row, data, displayIndex) {
          row.classList.remove('bg-white', 'bg-[#fcfcfc]');
          if (displayIndex % 2 === 0) {
            row.classList.add('bg-white');
          } else {
            row.classList.add('bg-[#fcfcfc]');
          }
        },
        drawCallback: function() {
          const info = table.page.info();
          if (info.recordsDisplay > 0) {
            $('#customers-info').text(`Menampilkan ${info.start + 1} - ${info.end} dari ${info.recordsDisplay} pelanggan (total ${info.recordsTotal})`);
          } else {
            $('#customers-info').text(`Data tidak ditemukan (total ${info.recordsTotal} pelanggan)`);
          }
          drawCustomersPagination(table, info);
        }
      });

      $('#customers-search').on('keyup', function() {
        table.search(this.value).draw();
      });

      $('#customers-length').on('change', function() {
        table.page.len(Number(this.value)).draw();
      });

      window.filterPelanggan = function(val) {
        table.search(val).draw();
      };
    });

    function drawCustomersPagination(table, info) {
      let html = `<button class="px-2 py-0.5 border border-[var(--border-main)] bg-[var(--bg-card)] text-[var(--text-muted)] rounded-sm ${info.page === 0 ? 'cursor-not-allowed opacity-60' : ''}" ${info.page === 0 ? 'disabled' : ''} onclick="changeCustomersPage('prev')">Previous</button>`;

      const pages = info.pages || 1;
      for (let i = 0; i < pages; i++) {
        if (pages <= 7 || i === 0 || i === pages - 1 || Math.abs(i - info.page) <= 1) {
          html += `<button class="px-2 py-0.5 border rounded-sm ${i === info.page ? 'border-blue-600 bg-blue-600 text-white' : 'border-[var(--border-main)] bg-[var(--bg-card)] text-[var(--text-muted)]'}" onclick="changeCustomersPage(${i})">${i + 1}</button>`;
        } else if (i === 1 || i === pages - 2) {
          html += `<span class="px-1 text-[var(--text-muted)]">...</span>`;
        }
      }

      html += `<button class="px-2 py-0.5 border border-[var(--border-main)] bg-[var(--bg-card)] text-[var(--text-muted)] rounded-sm ${info.page >= pages - 1 ? 'cursor-not-allowed opacity-60' : ''}" ${info.page >= pages - 1 ? 'disabled' : ''} onclick="changeCustomersPage('next')">Next</button>`;
      $('#customers-paginate').html(html);
      window.customersTableRef = table;
    }

    window.changeCustomersPage = function(page) {
      if (!window.customersTableRef) return;
      if (page === 'prev') {
        window.customersTableRef.page('previous').draw('page');
      } else if (page === 'next') {
        window.customersTableRef.page('next').draw('page');
      } else {
        window.customersTableRef.page(page).draw('page');
      }
    };

    function selectRow(tr) {
      $('.pelanggan-row').removeClass('bg-blue-50 dark:bg-blue-900/20');
      $(tr).addClass('bg-blue-50 dark:bg-blue-900/20');
    }
 
    function toggleActionDropdown(e, btn) {
        e.stopPropagation();
        const dropdown = $(btn).next('.action-dropdown');
        $('.action-dropdown').not(dropdown).removeClass('show');
        dropdown.toggleClass('show');
    }
 
    $(document).on('click', function() {
        $('.action-dropdown').removeClass('show');
    });

    let currentCustomerData = null;

    function openSlideover(mode = 'add', data = null) {
      const form = $('#customer-form');
      const methodField = $('#method-field');
      const title = $('#slideover-title');

      if (mode === 'edit' && data) {
        $('#registration-tabs').addClass('hidden');
        form.find('[name="password"]').val(''); 
        title.text('Edit Pelanggan: ' + data.full_name);
        form.attr('action', `/customers/${data.id}`);
        methodField.val('PUT');
        
        // Populate fields
        form.find('[name="customer_code"]').val(data.customer_code);
        form.find('[name="full_name"]').val(data.full_name);
        form.find('[name="whatsapp_number"]').val(data.whatsapp_number);
        form.find('[name="email"]').val(data.email);
        form.find('[name="address"]').val(data.address);
        form.find('[name="package_id"]').val(data.package_id).trigger('change');
        
        // Extract username and domain suffix
        let parts = data.username ? data.username.split("@") : ["", "djavva.net"];
        form.find('[name="username"]').val(parts[0]);
        let domain = parts[1] || "djavva.net";
        form.find('[name="domain"]').val(domain);
        $('#domain_suffix').text("@" + domain);

        // Populate additional fields
        form.find('[name="status"]').val(data.status);
        $('#pppoe_profile').val(data.mikrotik_profile); // Note: name is mikrotik_profile_override but we use ID
        form.find('[name="active_since"]').val(data.active_since ? data.active_since.split('T')[0] : '');
        
        $('#status-field-group').show();
        $('#code-error').addClass('hidden');
        $('#input_password').removeAttr('required');
        $('label[for="input_password"]').text('PASSWORD PPPOE');
      } else {
        $('#registration-tabs').removeClass('hidden');
        title.text('Tambah Pelanggan Baru');
        form.attr('action', '{{ route("admin.customers.store") }}');
        methodField.val('POST');
        form[0].reset();
        $('#pppoe_profile').val('');
        form.find('[name="active_since"]').val(new Date().toISOString().split('T')[0]);
        $('#status-field-group').hide();
        $('#customer-form').data('editing-id', '');
        $('#code-error').addClass('hidden');
        $('#input_password').attr('required', 'required');
        
        // Default suffix
        $('#domain_suffix').text('@djavva.net');

        // Auto-generate details
        switchTab('new');
      }

      $('body').addClass('slideover-open');
      $('#slideover-container').removeClass('pointer-events-none');
    }

    function fetchNextId() {
      const dom = $('[name="domain"]').val() || "djavva.net";
      $("#domain_suffix").text("@" + dom);
      
      if ($('#registration_type').val() !== 'new') {
        return;
      }

      $.get('{{ route("admin.customers.next_id") }}', { domain: dom }, function(res) {
        const form = $('#customer-form');
        form.find('[name="customer_code"]').val(res.next_id);
        form.find('[name="username"]').val(res.next_id);
        form.find('[name="password"]').val(res.password);
        $("#code-error").addClass("hidden");
      });
    }

    let checkTimeout = null;
    function debounceCheckId() {
        if(checkTimeout) clearTimeout(checkTimeout);
        $('#code-error').addClass('hidden');
        
        checkTimeout = setTimeout(() => {
            const code = $('#input_customer_code').val();
            const dom = $('[name="domain"]').val() || 'djavva.net';
            const editId = $('#customer-form').data('editing-id');
            
            if(code.length < 3) return;

            $.post('{{ route("admin.customers.check_id") }}', {
                _token: '{{ csrf_token() }}',
                code: code,
                domain: dom,
                exclude_id: editId
            }, function(res) {
                if(res.exists) {
                    $('#code-error').text(res.message).removeClass('hidden');
                    $('#input_customer_code').addClass('border-red-500');
                } else {
                    $('#code-error').addClass('hidden');
                    $('#input_customer_code').removeClass('border-red-500');
                }
            });
        }, 500);
    }

    let waCheckTimeout = null;
    function debounceCheckWhatsApp() {
        if(waCheckTimeout) clearTimeout(waCheckTimeout);
        const input = $('#input_whatsapp');
        const status = $('#wa-status');
        const val = input.val().replace(/\D/g, ''); 

        if (val.length < 11) {
            status.addClass('hidden').removeClass('text-green-600 text-red-500 text-blue-600');
            return;
        }

        status.text('🔍 Mengecek WhatsApp...').removeClass('hidden text-green-600 text-red-500').addClass('text-blue-600');

        waCheckTimeout = setTimeout(() => {
            $.post('{{ route("admin.customers.check_whatsapp") }}', {
                _token: '{{ csrf_token() }}',
                number: val
            }, function(res) {
                if(res.valid) {
                    status.text('✅ ' + res.message).removeClass('text-blue-600 text-red-500').addClass('text-green-600');
                    input.removeClass('border-red-500').addClass('border-green-500');
                } else {
                    status.text('❌ ' + (res.message || 'Tidak terhubung ke WhatsApp')).removeClass('text-blue-600 text-green-600').addClass('text-red-500');
                    input.removeClass('border-green-500').addClass('border-red-500');
                }
            }).fail(function() {
                status.text('⚠️ Gagal cek status').removeClass('text-blue-600 text-green-600').addClass('text-red-500');
            });
        }, 800);
    }

    function closeSlideover() { 
        $('body').removeClass('slideover-open');
        setTimeout(() => {
            $('#slideover-container').addClass('pointer-events-none');
        }, 300);
    }

    function switchTab(type) {
      const tabNew = $('#tab-new');
      const tabExisting = $('#tab-existing');
      const regType = $('#registration_type');
      const inputs = $('#input_customer_code, #input_username, #input_password');
      
      regType.val(type);
      
      if (type === 'new') {
        tabNew.addClass('text-blue-600 border-blue-600 bg-blue-50/30 dark:bg-blue-900/10 border-b-2').removeClass('text-slate-500');
        tabExisting.removeClass('text-blue-600 border-blue-600 bg-blue-50/30 dark:bg-blue-900/10 border-b-2').addClass('text-slate-500');
        
        inputs.attr('readonly', true).addClass('bg-slate-50 dark:bg-slate-800/50 cursor-not-allowed opacity-75');
        fetchNextId();
      } else {
        tabExisting.addClass('text-blue-600 border-blue-600 bg-blue-50/30 dark:bg-blue-900/10 border-b-2').removeClass('text-slate-500');
        tabNew.removeClass('text-blue-600 border-blue-600 bg-blue-50/30 dark:bg-blue-900/10 border-b-2').addClass('text-slate-500');
        
        inputs.removeAttr('readonly').removeClass('bg-slate-50 dark:bg-slate-800/50 cursor-not-allowed opacity-75').val('');
      }
    }

    function updateProfile(select) {
      const option = select.options[select.selectedIndex];
      const profile = option ? option.getAttribute('data-profile') : '';
      $('#pppoe_profile').val(profile || '');
    }

    function editFromDetail() {
      closeDetail();
      openSlideover('edit', currentCustomerData);
    }

    function openDetail(tr) {
      const data = JSON.parse(tr.dataset.json);
      currentCustomerData = data;
      
      $('#det-name-title').text(data.full_name);
      $('#det-id').text(data.customer_code);
      $('#det-name').text(data.full_name);
      $('#det-address').text(data.address);
      $('#det-package').text(data.package ? data.package.name : 'N/A');
      $('#det-cp').text(data.whatsapp_number || '-');
      $('#det-profile').text(data.mikrotik_profile || (data.package ? data.package.profile : '-'));
      $('#det-username').text(data.username || '-');
      $('#det-password').text(data.password_plain || '******');
      $('#det-price').text(data.package ? 'Rp ' + parseInt(data.package.monthly_price).toLocaleString('id-ID') + ' /bulan' : 'Rp 0 /bulan');
      $('#det-billing-date').text(data.billing_cycle ? 'Setiap tgl ' + data.billing_cycle + ' per bulan' : '-');
      
      if (data.active_since) {
          const d = new Date(data.active_since);
          $('#det-active-since').text(d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }));
      } else {
          $('#det-active-since').text('-');
      }

      const badgeContainer = $('#det-status-container');
      const status = data.status.toLowerCase();
      let badgeHtml = '';
      if(status === 'active') {
        badgeHtml = '<span class="px-2 border border-green-600 text-green-700 bg-green-50 text-[10px] font-bold rounded-sm">Aktif</span>';
      } else if(status === 'suspend') {
        badgeHtml = '<span class="px-2 border border-orange-500 text-orange-600 bg-orange-50 text-[10px] font-bold rounded-sm">Isolir</span>';
      } else {
        badgeHtml = '<span class="px-2 border border-red-500 text-red-600 bg-red-50 text-[10px] font-bold rounded-sm">Nonaktif</span>';
      }
      badgeContainer.html(badgeHtml);

      $('body').addClass('modal-open');
      $('#detail-modal-container').removeClass('pointer-events-none opacity-0').addClass('opacity-100');
      $('#detail-modal-panel').removeClass('scale-95').addClass('scale-100');
    }
 
    function closeDetail() {
      $('body').removeClass('modal-open');
      $('#detail-modal-container').addClass('pointer-events-none opacity-0').removeClass('opacity-100');
      $('#detail-modal-panel').addClass('scale-95').removeClass('scale-100');
    }

    function openHistory(tr) {
      const data = JSON.parse(tr.dataset.json);
      $('#hist-name-title').text(data.full_name);
      $('#history-table-body').html('<tr><td colspan="5" class="p-4 text-center text-slate-400 italic">Memuat data...</td></tr>');
      $('body').addClass('history-open');
      $('#history-modal-container').removeClass('pointer-events-none opacity-0').addClass('opacity-100');
      $('#history-modal-panel').removeClass('scale-95').addClass('scale-100');

      $.get(`/customers/${data.id}/invoices`, function(res) {
        let html = '';
        if(res.invoices.length === 0) {
          html = '<tr><td colspan="5" class="p-4 text-center text-slate-400">Tidak ada riwayat tagihan.</td></tr>';
        } else {
          res.invoices.forEach(inv => {
            let statusHtml = '<span class="px-2 border border-orange-500 text-orange-600 bg-orange-50 text-[10px] font-bold rounded-sm uppercase">Pending</span>';
            if (inv.status === 'paid') {
              statusHtml = '<span class="px-2 border border-green-600 text-green-700 bg-green-50 text-[10px] font-bold rounded-sm uppercase">Lunas</span>';
            } else if (inv.status === 'paid_cash') {
              statusHtml = '<span class="px-2 border border-blue-600 text-blue-700 bg-blue-50 text-[10px] font-bold rounded-sm uppercase">Lunas (Cash)</span>';
            } else if (inv.status === 'overdue') {
              statusHtml = '<span class="px-2 border border-red-500 text-red-600 bg-red-50 text-[10px] font-bold rounded-sm uppercase">Terlambat</span>';
            } else if (inv.status === 'cancelled') {
              statusHtml = '<span class="px-2 border border-gray-400 text-gray-500 bg-gray-50 text-[10px] font-bold rounded-sm uppercase">Batal</span>';
            }
            
            html += `
              <tr class="border-b border-gray-100 hover:bg-gray-50/50">
                <td class="p-1 px-2 border-r border-gray-100 font-mono text-[10px]">${inv.invoice_number}</td>
                <td class="p-1 px-2 border-r border-gray-100">${inv.period}</td>
                <td class="p-1 px-2 border-r border-gray-100 text-right font-bold">${inv.total}</td>
                <td class="p-1 px-2 border-r border-gray-100 text-center">${statusHtml}</td>
                <td class="p-1 px-2 text-center">
                  <a href="/invoices/${inv.id}/pdf" target="_blank" class="bg-blue-50 border border-blue-200 text-blue-700 px-2 py-0.5 rounded-sm text-[9px] font-bold hover:bg-blue-100 leading-none inline-block">Cetak</a>
                </td>
              </tr>
            `;
          });
        }
        $('#history-table-body').html(html);
      });
    }

    function closeHistory() {
      $('body').removeClass('history-open');
      $('#history-modal-container').addClass('pointer-events-none opacity-0').removeClass('opacity-100');
      $('#history-modal-panel').addClass('scale-95').removeClass('scale-100');
    }
 
    function confirmDeleteCustomer(id, name) {
        if (confirm(`Apakah Anda yakin ingin menghapus pelanggan "${name}"?\nSemua data tagihan juga akan dihapus (jika diijinkan oleh sistem).`)) {
            const form = $('#delete-customer-form');
            form.attr('action', `/customers/${id}`);
            form.submit();
        }
    }

    function copyDetail(e) {
      if(e) e.stopPropagation();
      if(!currentCustomerData) return;
      const data = currentCustomerData;
      
      const profile = data.mikrotik_profile || (data.package ? data.package.profile : '-');
      const price = data.package ? 'Rp ' + parseInt(data.package.monthly_price).toLocaleString('id-ID') : 'Rp 0';
      const billingDate = data.billing_cycle ? 'Tgl ' + data.billing_cycle : '-';
      
      const text = `ID Pelanggan: ${data.customer_code}
Nama: ${data.full_name}
No. WA: ${data.whatsapp_number || '-'}
Paket: ${data.package ? data.package.name : '-'}
Profile: ${profile}
Harga: ${price} /bulan
Username: ${data.username || '-'}
Password: ${data.password_plain || '-'}
Tagihan: ${billingDate}
Alamat: ${data.address || '-'}`;

      if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(() => {
          alert('Data pelanggan berhasil disalin!');
        }).catch(err => {
          fallbackCopyTextToClipboard(text);
        });
      } else {
        fallbackCopyTextToClipboard(text);
      }
    }

    function fallbackCopyTextToClipboard(text) {
      const textArea = document.createElement("textarea");
      textArea.value = text;
      textArea.style.position = "fixed";
      textArea.style.left = "-9999px";
      textArea.style.top = "0";
      document.body.appendChild(textArea);
      textArea.focus();
      textArea.select();
      try {
        document.execCommand('copy');
        alert('Data pelanggan berhasil disalin! (Fallback)');
      } catch (err) {
        console.error('Fallback: Gagal menyalin', err);
        alert('Gagal menyalin data. Silakan salin secara manual.');
      }
      document.body.removeChild(textArea);
    }

    function syncSingleCustomer(customerId, btn) {
        if(!confirm('Push pelanggan ini ke router Mikrotik?')) return;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="px-2">Memproses...</span>';
        btn.disabled = true;

        $.post(`/customers/${customerId}/sync`, {
            _token: '{{ csrf_token() }}'
        }).done(function(res) {
            if(res.ok) {
                alert(res.message);
                window.location.reload();
            } else {
                alert(res.message);
            }
        }).fail(function() {
            alert('Gagal menghubungi server.');
        }).always(function() {
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }

    function fetchMikrotikProfiles(selectId, inputId, fieldName) {
        const btn = event.target;
        const originalText = btn.innerText;
        btn.innerText = 'Loading...';
        btn.disabled = true;
 
        $.get('{{ route("admin.settings.mikrotik.profiles") }}', function(res) {
            if (res.ok) {
                const select = document.getElementById(selectId);
                const input = document.getElementById(inputId);
                
                select.innerHTML = '<option value="">-- Pilih Profile --</option>';
                res.profiles.forEach(p => {
                    const opt = document.createElement('option');
                    opt.value = p;
                    opt.innerText = p;
                    select.appendChild(opt);
                });
 
                input.classList.add('hidden');
                input.removeAttribute('name');
                if (input.hasAttribute('required')) {
                    input.removeAttribute('required');
                    select.setAttribute('required', 'required');
                }
                
                select.classList.remove('hidden');
                select.setAttribute('name', fieldName);
                
                if (input.value) select.value = input.value;
                
                alert('Berhasil mengambil ' + res.profiles.length + ' profile dari Mikrotik.');
            } else {
                alert('Gagal: ' + res.message);
            }
        }).fail(function() {
            alert('Gagal menghubungi server. Pastikan API Mikrotik sudah terkonfigurasi.');
        }).always(function() {
            btn.innerText = originalText;
            btn.disabled = false;
        });
    }

    function sendGreeting(customerId, btn) {
        if(!confirm('Kirim pesan greeting WhatsApp ke pelanggan ini?')) return;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span>Mengirim...</span>';
        btn.disabled = true;

        $.post(`/customers/${customerId}/greeting`, {
            _token: '{{ csrf_token() }}'
        }).done(function(res) {
            alert(res.message);
        }).fail(function() {
            alert('Gagal mengirim pesan.');
        }).always(function() {
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }

    function openBlastModal() {
        $('body').addClass('blast-open');
        $('#blast-modal-container').removeClass('pointer-events-none opacity-0').addClass('opacity-100');
        $('#blast-modal-panel').removeClass('scale-95').addClass('scale-100');
    }

    function closeBlastModal() {
        $('body').removeClass('blast-open');
        $('#blast-modal-container').addClass('pointer-events-none opacity-0').removeClass('opacity-100');
        $('#blast-modal-panel').addClass('scale-95').removeClass('scale-100');
    }

    function executeBlast() {
        const target = $('input[name="blast_target"]:checked').val();
        if(!confirm('Konfirmasi: Anda akan mengirim blast WA ke seluruh pelanggan aktif. Lanjutkan?')) return;

        const btn = $('#btn-execute-blast');
        const originalText = btn.html();
        btn.html('<svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Memproses...');
        btn.disabled = true;

        $.post('{{ route("admin.customers.blast_maintenance") }}', {
            _token: '{{ csrf_token() }}',
            target: target
        }).done(function(res) {
            alert(res.message);
            closeBlastModal();
        }).fail(function() {
            alert('Terjadi kesalahan saat proses blast.');
        }).always(function() {
            btn.html(originalText);
            btn.disabled = false;
        });
    }
</script>
@endpush
