@extends('admin.layouts.app')

@section('title', 'Paket Internet')
 
@push('styles')
<style>
  /* Page-specific styles for slide-over */
  #slideover-overlay{transition:opacity .2s ease}
  #slideover-panel{transition:transform .3s ease-in-out}
  .slideover-open #slideover-overlay{opacity:1;pointer-events:auto}
  .slideover-open #slideover-panel{transform:translateX(0)}
</style>
@endpush

@section('content')
    <div class="bg-[var(--bg-card)] border border-[var(--border-main)] px-3 py-1.5 rounded-sm shadow-sm flex items-center justify-between shrink-0 mb-1 transition-colors">
      <h1 class="text-sm font-bold text-[var(--text-main)]">Paket Internet / Layanan</h1>
      @if(auth()->user()->hasPermission('packages.manage'))
      <button onclick="openSlideover()" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded font-semibold shadow-sm text-xs active:scale-95 transition-all">
        Tambah Paket
      </button>
      @endif
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
        @foreach($packages as $package)
        <div class="bg-[var(--bg-card)] border border-[var(--border-main)] rounded-sm shadow-sm flex flex-col transition-colors" data-json="{{ json_encode($package) }}">
           <div class="bg-[var(--bg-table-head)] px-3 py-1.5 border-b border-[var(--border-main)] flex justify-between items-center transition-colors rounded-t-sm">
              <span class="text-[10px] font-black text-blue-700 dark:text-white">{{ $package->name }}</span>
              <span class="badge-{{ $package->is_active ? 'up' : 'down' }}">{{ $package->is_active ? 'Aktif' : 'Non-aktif' }}</span>
           </div>
           <div class="p-3 flex-1 flex flex-col gap-2">
              <div class="flex items-baseline gap-1">
                 <span class="text-[10px] font-bold text-[var(--text-muted)]">Rp</span>
                 <span class="text-xl font-black text-[var(--text-main)]">{{ number_format($package->monthly_price, 0, ',', '.') }}</span>
                 <span class="text-[9px] font-bold text-[var(--text-muted)]">/ bulan</span>
              </div>
              <div class="text-[10px] space-y-1.5 text-[var(--text-muted)] mt-2">
                 <div class="flex justify-between"><span>Bandwidth / Profile</span><span class="font-bold text-[var(--text-main)]">{{ $package->profile ?? 'N/A' }}</span></div>
                 <div class="flex justify-between"><span>Metode</span><span class="font-bold text-[var(--text-main)]">PPPoE</span></div>
                 @if($package->installation_fee > 0)
                 <div class="flex justify-between"><span>Biaya Pasang</span><span class="font-bold text-[var(--text-main)]">Rp {{ number_format($package->installation_fee, 0, ',', '.') }}</span></div>
                 @endif
              </div>

              @if($package->features)
               <div class="mt-1 pt-2 border-t border-[var(--border-sub)]">
                  <div class="text-[9px] font-black text-blue-700 dark:text-white uppercase tracking-tighter mb-1.5 opacity-80">Fitur & Fasilitas</div>
                  <ul class="space-y-1">
                     @foreach((is_array($package->features) ? $package->features : explode(',', $package->features)) as $feature)
                     <li class="flex items-start gap-1.5 text-[10px] text-[var(--text-main)] font-medium leading-tight">
                        <svg class="w-3 h-3 text-green-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        <span>{{ trim($feature) }}</span>
                     </li>
                     @endforeach
                  </ul>
               </div>
               @endif
           </div>
           <div class="p-2 border-t border-[var(--border-sub)] bg-[var(--bg-table-odd)] flex justify-end transition-colors relative rounded-b-sm">
               <button onclick="toggleActionDropdown(event, this)" class="bg-[var(--bg-card)] border border-[var(--border-main)] p-1 rounded shadow-sm hover:bg-[var(--nav-hover)] transition-all flex items-center justify-center w-7 h-7 leading-none text-[var(--text-muted)]">
                   <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 10c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0-6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 12c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg>
               </button>
               <!-- Dropdown menu -->
               @if(auth()->user()->hasPermission('packages.manage'))
               <div class="action-dropdown absolute right-2 top-full mt-1 w-28 bg-[var(--bg-card)] border border-[var(--border-main)] rounded shadow-lg z-50 overflow-hidden">
                   <button onclick="openSlideover('edit', this.closest('[data-json]'))" class="action-item block w-full text-left px-3 py-1.5 text-[10px] font-bold text-slate-700 dark:text-slate-300 border-b border-gray-100 dark:border-slate-700 hover:bg-[var(--nav-hover)] flex items-center gap-2 transition-colors">
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                      Edit
                   </button>
                   <button onclick="confirmDelete('{{ $package->id }}')" class="action-item block w-full text-left px-3 py-1.5 text-[10px] font-bold text-slate-700 dark:text-slate-300 hover:bg-[var(--nav-hover)] flex items-center gap-2 transition-colors">
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                      Hapus
                   </button>
               </div>
               @endif
           </div>
        </div>
        @endforeach
    </div>
 
    <!-- TAMBAH/EDIT PAKET SLIDE-OVER -->
    <div id="slideover-container" class="fixed inset-0 z-[60] pointer-events-none">
      <div id="slideover-overlay" onclick="closeSlideover()" class="absolute inset-0 bg-black/40 backdrop-blur-none opacity-0 pointer-events-none transition-opacity"></div>
      <div id="slideover-panel" class="absolute inset-y-0 right-0 w-full max-w-md bg-[var(--bg-card)] shadow-none border-l border-[var(--border-main)] flex flex-col translate-x-full pointer-events-auto transition-transform">
        <div class="bg-white dark:bg-slate-800 border-b border-[var(--border-main)] p-3 flex items-center justify-between shrink-0">
          <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            <h2 id="slideover-title" class="text-[13px] font-bold text-blue-800 dark:text-white">Tambah Paket Baru</h2>
          </div>
          <button onclick="closeSlideover()" class="text-blue-900/60 hover:text-blue-900 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>
        <form id="package-form" action="{{ route('admin.packages.store') }}" method="POST" class="flex-1 overflow-auto flex flex-col bg-white dark:bg-slate-900">
           @csrf
           <input type="hidden" name="_method" id="method-field" value="POST">
           
           <div class="p-3 space-y-3">
              <div class="space-y-1">
                <label class="text-[10px] font-bold text-slate-600 dark:text-slate-400">Nama Paket *</label>
                <input type="text" name="name" required placeholder="Contoh: 10 Mbps Home" class="w-full bg-[#f8fafc] dark:bg-slate-800 border border-[var(--border-main)] rounded-sm p-1.5 text-xs focus:ring-1 focus:ring-blue-500 outline-none text-slate-800 dark:text-slate-200 placeholder:text-slate-400">
              </div>
              
              <div class="grid grid-cols-2 gap-3">
                  <div class="space-y-1">
                    <div class="flex justify-between items-end pb-0.5">
                        <label class="text-[10px] font-bold text-slate-600 dark:text-slate-400 tracking-tight">Profile Mikrotik *</label>
                        <button type="button" onclick="fetchMikrotikProfiles('pppoe_profile_select', 'profile_input', 'profile')" class="text-[9px] text-blue-600 hover:underline font-bold leading-none mb-[1px]">Ambil dari Mikrotik</button>
                    </div>
                    <input type="text" id="profile_input" name="profile" required placeholder="Contoh: pppoe-10M" class="w-full bg-[#f8fafc] dark:bg-slate-800 border border-[var(--border-main)] rounded-sm p-1.5 text-xs focus:ring-1 focus:ring-blue-500 outline-none text-slate-800 dark:text-slate-200 font-mono h-[31px]">
                    <select id="pppoe_profile_select" class="hidden w-full bg-[#f8fafc] dark:bg-slate-800 border border-[var(--border-main)] rounded-sm p-1.5 text-xs focus:ring-1 focus:ring-blue-500 outline-none text-slate-800 dark:text-slate-200 font-mono h-[31px]"></select>
                  </div>
                  <div class="space-y-1">
                    <label class="text-[10px] font-bold text-slate-600 dark:text-slate-400">Badge/Label</label>
                    <input type="text" name="badge" placeholder="Contoh: POPULER" class="w-full bg-[#f8fafc] dark:bg-slate-800 border border-[var(--border-main)] rounded-sm p-1.5 text-xs focus:ring-1 focus:ring-blue-500 outline-none text-slate-800 dark:text-slate-200">
                  </div>
              </div>
 
              <div class="grid grid-cols-2 gap-3">
                  <div class="space-y-1">
                    <label class="text-[10px] font-bold text-slate-600 dark:text-slate-400">Harga per Bulan *</label>
                    <div class="relative">
                        <span class="absolute left-2 top-1.5 text-[10px] font-bold text-slate-400">Rp</span>
                        <input type="number" name="monthly_price" required placeholder="150000" class="w-full bg-[#f8fafc] dark:bg-slate-800 border border-[var(--border-main)] rounded-sm py-1.5 pl-7 pr-1.5 text-xs focus:ring-1 focus:ring-blue-500 outline-none text-slate-800 dark:text-slate-200 font-bold">
                    </div>
                  </div>
                  <div class="space-y-1">
                    <label class="text-[10px] font-bold text-slate-600 dark:text-slate-400">Biaya Pasang</label>
                    <div class="relative">
                        <span class="absolute left-2 top-1.5 text-[10px] font-bold text-slate-400">Rp</span>
                        <input type="number" name="installation_fee" placeholder="0" class="w-full bg-[#f8fafc] dark:bg-slate-800 border border-[var(--border-main)] rounded-sm py-1.5 pl-7 pr-1.5 text-xs focus:ring-1 focus:ring-blue-500 outline-none text-slate-800 dark:text-slate-200">
                    </div>
                  </div>
              </div>
 
              <div class="space-y-1">
                <label class="text-[10px] font-bold text-slate-600 dark:text-slate-400">Fitur (Pisahkan dengan koma)</label>
                <textarea name="features" rows="3" placeholder="Unlimited Kuota, Speed 10Mbps, Support 24/7" class="w-full bg-[#f8fafc] dark:bg-slate-800 border border-[var(--border-main)] rounded-sm p-1.5 text-xs focus:ring-1 focus:ring-blue-500 outline-none text-slate-800 dark:text-slate-200 placeholder:text-slate-400"></textarea>
              </div>
 
              <div class="space-y-1">
                <label class="text-[10px] font-bold text-slate-600 dark:text-slate-400">Deskripsi</label>
                <textarea name="description" rows="2" placeholder="Keterangan tambahan..." class="w-full bg-[#f8fafc] dark:bg-slate-800 border border-[var(--border-main)] rounded-sm p-1.5 text-xs focus:ring-1 focus:ring-blue-500 outline-none text-slate-800 dark:text-slate-200 placeholder:text-slate-400"></textarea>
              </div>
 
              <div class="flex items-center gap-2 pt-1">
                 <input type="checkbox" name="is_active" id="is_active" value="1" checked class="rounded-sm border-[var(--border-main)] text-blue-600 focus:ring-blue-500">
                 <label for="is_active" class="text-[10px] font-bold text-slate-700 dark:text-slate-300">Aktifkan Paket</label>
              </div>
           </div>
 
           <div class="mt-auto bg-slate-100 dark:bg-slate-800 p-3 border-t border-[var(--border-main)] flex items-center justify-between">
              <span class="text-[9px] text-slate-500">* Wajib diisi</span>
              <div class="flex gap-2">
                 <button type="button" onclick="closeSlideover()" class="bg-white border border-[var(--border-main)] text-slate-700 dark:text-slate-300 dark:bg-slate-700 font-bold py-1.5 px-4 rounded-sm hover:bg-slate-50 dark:hover:bg-slate-600 active:scale-95 transition-all text-[11px]">Batal</button>
                 <button type="submit" class="bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-800 text-white font-bold py-1.5 px-6 rounded-sm shadow-sm active:scale-95 transition-all text-[11px]">Simpan</button>
              </div>
           </div>
        </form>
      </div>
    </div>
 
    <form id="delete-form" action="" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>
@endsection
 
@push('scripts')
<script>
    function openSlideover(mode = 'add', row = null) {
      const form = $('#package-form');
      const methodField = $('#method-field');
      const title = $('#slideover-title');
 
      if (mode === 'edit' && row) {
        const data = JSON.parse(row.dataset.json);
        title.text('Edit Paket: ' + data.name);
        form.attr('action', `/packages/${data.id}`);
        methodField.val('PUT');
        
        // Populate fields
        form.find('[name="name"]').val(data.name);
        form.find('[name="profile"]').val(data.profile);
        form.find('[name="badge"]').val(data.badge);
        form.find('[name="monthly_price"]').val(parseInt(data.monthly_price));
        form.find('[name="installation_fee"]').val(parseInt(data.installation_fee || 0));
        form.find('[name="description"]').val(data.description);
        
        // Features array to comma separated string
        if (Array.isArray(data.features)) {
            form.find('[name="features"]').val(data.features.join(', '));
        } else {
            form.find('[name="features"]').val('');
        }
        
        form.find('[name="is_active"]').prop('checked', data.is_active);
      } else {
        title.text('Tambah Paket Baru');
        form.attr('action', '{{ route("admin.packages.store") }}');
        methodField.val('POST');
        form[0].reset();
        form.find('[name="is_active"]').prop('checked', true);
      }
 
      $('body').addClass('slideover-open');
      $('#slideover-container').removeClass('pointer-events-none');
    }
 
    function closeSlideover() {
        $('body').removeClass('slideover-open');
        setTimeout(() => {
            $('#slideover-container').addClass('pointer-events-none');
        }, 300);
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
 
    function confirmDelete(id) {
        if (confirm('Apakah Anda yakin ingin menghapus paket ini?')) {
            const form = document.getElementById('delete-form');
            form.action = `/packages/${id}`;
            form.submit();
        }
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
 
                // Hide input, show select
                input.classList.add('hidden');
                input.removeAttribute('name');
                if (input.hasAttribute('required')) {
                    input.removeAttribute('required');
                    select.setAttribute('required', 'required');
                }
                
                select.classList.remove('hidden');
                select.setAttribute('name', fieldName);
                
                // If input had value, try to select it
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
</script>
@endpush