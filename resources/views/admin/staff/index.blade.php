@extends('admin.layouts.app')

@section('title', 'Manajemen Staff/User')

@section('content')
    <!-- Page Title Bar -->
    <div class="bg-[var(--bg-card)] border border-[var(--border-main)] px-3 py-1.5 rounded-sm shadow-sm flex items-center justify-between shrink-0 mb-1 transition-colors">
      <div class="flex items-center gap-3">
        <h1 class="text-sm font-bold text-[var(--text-main)]">Manajemen Staff</h1>
        <div class="flex items-center gap-1">
          <span class="bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-white px-1.5 py-0.5 rounded text-[10px] font-bold">{{ $totalStaff }} user</span>
        </div>
      </div>
      <div class="flex items-center gap-4 text-xs font-normal">
        <button onclick="openSlideover('add')" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded font-semibold shadow-sm flex items-center gap-1.5 active:scale-95 transition-all">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
          Tambah User
        </button>
      </div>
    </div>

    <div class="bg-[var(--bg-card)] border border-[var(--border-main)] rounded-sm shadow-sm flex flex-col shrink-0 grow overflow-hidden transition-colors">
      <!-- Toolbar -->
      <div class="bg-[var(--bg-table-head)] border-b border-[var(--border-main)] flex items-center gap-2 px-2 py-1 shrink-0 grow-0 text-[11px] transition-colors">
         <div class="flex items-center gap-1.5 text-[var(--text-muted)]">
           <span>Tampil</span>
           <select id="staff-length" class="border border-[var(--border-main)] bg-[var(--bg-card)] px-1 py-0.5 rounded-sm outline-none font-bold text-[var(--text-main)]">
             <option>10</option><option>25</option><option>50</option>
           </select>
         </div>
         <div class="flex-1"></div>
         <div class="flex items-center border border-[var(--border-main)] bg-[var(--bg-card)] rounded overflow-hidden">
           <input id="staff-search" type="text" placeholder="Cari staff..." class="outline-none px-2 py-0.5 w-32 focus:w-48 transition-all text-[11px] bg-[var(--bg-card)] text-[var(--text-main)]">
         </div>
      </div>

      <div class="flex-1 overflow-auto bg-[var(--bg-table-even)] transition-colors">
        <table id="staff-table" class="w-full border-collapse tbl tbl-compact">
          <thead>
            <tr class="font-bold text-[var(--text-muted)]">
              <th class="text-center w-8">#</th>
              <th>Nama</th>
              <th>Email / ID Login</th>
              <th>Role</th>
              <th class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>

      <!-- Pagination bar -->
      <div class="bg-[var(--bg-table-head)] border-t border-[var(--border-main)] px-3 py-1.5 flex justify-between items-center text-[10px] text-[var(--text-muted)] shrink-0 transition-colors">
        <span id="staff-info">Memuat data staff...</span>
        <div id="staff-paginate" class="flex gap-1"></div>
      </div>
    </div>

    <!-- SLIDE-OVER SECTION -->
    <div id="slideover-container" class="fixed inset-0 z-[60] pointer-events-none">
      <div id="slideover-overlay" onclick="closeSlideover()" class="absolute inset-0 bg-black/40 backdrop-blur-none opacity-0 pointer-events-none transition-opacity"></div>
      <div id="slideover-panel" class="absolute inset-y-0 right-0 w-full max-w-md bg-[var(--bg-card)] shadow-none border-l border-[var(--border-main)] flex flex-col translate-x-full pointer-events-auto transition-transform">
        <div class="bg-white dark:bg-slate-800 border-b border-[var(--border-main)] p-3 flex items-center justify-between shrink-0">
          <div class="flex items-center gap-2">
            <h2 id="slideover-title" class="text-[13px] font-bold text-blue-800 dark:text-white uppercase tracking-wider">Tambah User Baru</h2>
          </div>
          <button onclick="closeSlideover()" class="text-slate-400 hover:text-slate-600 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>
        
        <form id="staff-form" action="{{ route('admin.staff.store') }}" method="POST" class="flex-1 flex flex-col bg-white dark:bg-slate-900 overflow-hidden">
           @csrf
           <input type="hidden" name="_method" id="method-field" value="POST">
           
           <div class="flex-1 overflow-auto p-4 space-y-4">
               <div class="space-y-1">
                 <label class="text-[10px] font-bold text-slate-500 uppercase tracking-tight">NAMA LENGKAP *</label>
                 <input type="text" name="name" id="input_name" required placeholder="Faza" class="w-full bg-[#f8fafc] dark:bg-slate-800 border border-[var(--border-main)] rounded-sm px-2 text-xs focus:ring-1 focus:ring-blue-500 outline-none text-slate-800 dark:text-slate-200 h-[32px] font-bold">
               </div>
               
                <div class="space-y-1">
                  <label class="text-[10px] font-bold text-slate-500 uppercase tracking-tight">EMAIL / ID LOGIN *</label>
                  <input type="text" name="email" id="input_email" required placeholder="faza@djavva.net" class="w-full bg-[#f8fafc] dark:bg-slate-800 border border-[var(--border-main)] rounded-sm px-2 text-xs focus:ring-1 focus:ring-blue-500 outline-none text-slate-800 dark:text-slate-200 h-[32px]">
                </div>

               <div class="space-y-1">
                 <label class="text-[10px] font-bold text-slate-500 uppercase tracking-tight">ROLE TINGKATAN *</label>
                 <select name="role" id="input_role" required class="w-full bg-[#f8fafc] dark:bg-slate-800 border border-[var(--border-main)] rounded-sm px-2 text-xs focus:ring-1 focus:ring-blue-500 outline-none text-slate-800 dark:text-slate-200 h-[32px] font-bold">
                    <option value="admin">Administrator (Full Access)</option>
                    <option value="teknisi">Teknisi</option>
                    <option value="sales">Sales</option>
                 </select>
               </div>

               <div class="space-y-1">
                 <label id="label_password" class="text-[10px] font-bold text-slate-500 uppercase tracking-tight">PASSWORD *</label>
                 <input type="password" name="password" id="input_password" placeholder="******" class="w-full bg-[#f8fafc] dark:bg-slate-800 border border-[var(--border-main)] rounded-sm px-2 text-xs focus:ring-1 focus:ring-blue-500 outline-none text-slate-800 dark:text-slate-200 h-[32px]">
                 <p id="password_hint" class="text-[9px] text-slate-400 hidden mt-1">Kosongkan jika tidak ingin mengubah password.</p>
               </div>

               <!-- PERMISSIONS SEKSI -->
               <div id="permissions-container" class="space-y-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                  <div class="flex items-center justify-between">
                    <label class="text-[10px] font-black text-blue-700 dark:text-blue-400 uppercase tracking-wider">HAK AKSES / PERMISSIONS</label>
                    <button type="button" onclick="toggleAllPermissions()" class="text-[9px] font-bold text-slate-500 hover:text-blue-600 underline uppercase">Toggle Semua</button>
                  </div>
                  
                  <div class="grid grid-cols-1 gap-4">
                    @foreach($availablePermissions as $group => $perms)
                    <div class="space-y-1.5 p-2 bg-slate-50 dark:bg-slate-800/50 rounded-sm border border-slate-100 dark:border-slate-800">
                       <div class="text-[9px] font-black text-slate-400 uppercase tracking-tighter border-b border-slate-200 dark:border-slate-700 pb-1 mb-1">{{ $group }}</div>
                       <div class="grid grid-cols-1 gap-1.5">
                          @foreach($perms as $key => $label)
                          <label class="flex items-center gap-2 cursor-pointer group">
                             <input type="checkbox" name="permissions[]" value="{{ $key }}" class="permission-checkbox rounded-sm border-slate-300 text-blue-600 focus:ring-blue-500 w-3.5 h-3.5">
                             <span class="text-[11px] text-slate-600 dark:text-slate-400 group-hover:text-blue-700 dark:group-hover:text-blue-300 transition-colors font-medium">{{ $label }}</span>
                          </label>
                          @endforeach
                       </div>
                    </div>
                    @endforeach
                  </div>
               </div>
           </div>

           <div class="mt-auto bg-slate-50 dark:bg-slate-800 p-3 border-t border-[var(--border-main)] flex gap-2">
              <button type="button" onclick="closeSlideover()" class="flex-1 bg-white dark:bg-slate-700 border border-[var(--border-main)] text-slate-700 dark:text-slate-200 font-bold py-1.5 rounded-sm hover:bg-slate-50 active:scale-95 transition-all text-xs">Batal</button>
              <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-1.5 rounded-sm shadow-sm active:scale-95 transition-all text-xs uppercase tracking-wider">Simpan User</button>
           </div>
        </form>
      </div>
    </div>

    <form id="delete-user-form" action="" method="POST" class="hidden">
      @csrf
      @method('DELETE')
    </form>
@endsection

@push('scripts')
<script>
    const currentUserId = @json(auth()->id());

    $(document).ready(function() {
      const table = $('#staff-table').DataTable({
        processing: true,
        serverSide: true,
        ordering: true,
        searching: true,
        pageLength: 10,
        lengthChange: false,
        dom: 't',
        ajax: '{{ route("admin.staff.data") }}',
        columns: [
          {
            data: null,
            orderable: false,
            searchable: false,
            className: 'text-center',
            render: function(data, type, row, meta) {
              return meta.row + meta.settings._iDisplayStart + 1;
            }
          },
          { data: 'name', name: 'name', className: 'font-bold' },
          { data: 'email', name: 'email', className: 'font-mono text-[11px]' },
          {
            data: 'role',
            name: 'role',
            render: function(data) {
              if (data === 'admin') return '<span class="badge-up">Administrator</span>';
              if (data === 'teknisi') return '<span class="badge-warn">Teknisi</span>';
              if (data === 'sales') return '<span class="badge-up bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-800">Sales</span>';
              return `<span class="badge-down">${data ? data.charAt(0).toUpperCase() + data.slice(1) : '-'}</span>`;
            }
          },
          {
            data: null,
            orderable: false,
            searchable: false,
            className: 'text-center',
            render: function(data, type, row) {
              const nameJson = JSON.stringify(row.name || '').replace(/'/g, "\\'");
              let actionItems = `
                <button onclick="openSlideover('edit', JSON.parse(this.closest('tr').dataset.json))" class="action-item block w-full text-left px-3 py-1.5 text-[10px] hover:bg-orange-50 dark:hover:bg-orange-900/40 hover:text-orange-700 dark:hover:text-orange-300 border-b border-gray-100 dark:border-slate-800 flex items-center gap-2 font-bold transition-colors">
                  Edit
                </button>
              `;

              if (row.id !== currentUserId) {
                actionItems += `
                  <button onclick="confirmDeleteUser('${row.id}', '${(row.name || '').replace(/'/g, "\\'").replace(/"/g, '&quot;')}')" class="action-item block w-full text-left px-3 py-1.5 text-[10px] hover:bg-red-50 hover:text-red-700 flex items-center gap-2 font-bold">
                    Hapus
                  </button>
                `;
              }

              return `
                <div class="relative inline-block text-left">
                  <button type="button" onclick="toggleActionDropdown(event, this)" class="text-[var(--text-main)] p-1 rounded-full hover:bg-[var(--nav-hover)] transition-all flex items-center justify-center w-7 h-7 leading-none">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 10c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0-6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 12c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg>
                  </button>
                  <div class="action-dropdown absolute right-0 top-full mt-1 w-28 bg-[var(--bg-card)] border border-[var(--border-main)] rounded shadow-lg z-50 overflow-hidden text-left">
                    ${actionItems}
                  </div>
                </div>
              `;
            }
          }
        ],
        createdRow: function(row, data) {
          row.classList.add('staff-row', 'transition-colors');
          row.dataset.json = JSON.stringify(data.raw_user ?? data);
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
            $('#staff-info').text(`Menampilkan ${info.start + 1} - ${info.end} dari ${info.recordsDisplay} user (total ${info.recordsTotal})`);
          } else {
            $('#staff-info').text(`Data tidak ditemukan (total ${info.recordsTotal} user)`);
          }
          drawStaffPagination(table, info);
        }
      });

      $('#staff-search').on('keyup', function() {
        table.search(this.value).draw();
      });

      $('#staff-length').on('change', function() {
        table.page.len(Number(this.value)).draw();
      });
    });

    function drawStaffPagination(table, info) {
      let html = `<button class="px-2 py-0.5 border border-[var(--border-main)] bg-[var(--bg-card)] text-[var(--text-muted)] rounded-sm ${info.page === 0 ? 'cursor-not-allowed opacity-60' : ''}" ${info.page === 0 ? 'disabled' : ''} onclick="changeStaffPage('prev')">Previous</button>`;

      const pages = info.pages || 1;
      for (let i = 0; i < pages; i++) {
        if (pages <= 7 || i === 0 || i === pages - 1 || Math.abs(i - info.page) <= 1) {
          html += `<button class="px-2 py-0.5 border rounded-sm ${i === info.page ? 'border-blue-600 bg-blue-600 text-white' : 'border-[var(--border-main)] bg-[var(--bg-card)] text-[var(--text-muted)]'}" onclick="changeStaffPage(${i})">${i + 1}</button>`;
        } else if (i === 1 || i === pages - 2) {
          html += `<span class="px-1 text-[var(--text-muted)]">...</span>`;
        }
      }

      html += `<button class="px-2 py-0.5 border border-[var(--border-main)] bg-[var(--bg-card)] text-[var(--text-muted)] rounded-sm ${info.page >= pages - 1 ? 'cursor-not-allowed opacity-60' : ''}" ${info.page >= pages - 1 ? 'disabled' : ''} onclick="changeStaffPage('next')">Next</button>`;
      $('#staff-paginate').html(html);
      window.staffTableRef = table;
    }

    window.changeStaffPage = function(page) {
      if (!window.staffTableRef) return;
      if (page === 'prev') {
        window.staffTableRef.page('previous').draw('page');
      } else if (page === 'next') {
        window.staffTableRef.page('next').draw('page');
      } else {
        window.staffTableRef.page(page).draw('page');
      }
    };

    function openSlideover(mode = 'add', data = null) {
      const form = $('#staff-form');
      const methodField = $('#method-field');
      const title = $('#slideover-title');
      const pwdLabel = $('#label_password');
      const pwdHint = $('#password_hint');
      const pwdInput = $('#input_password');

      // Clear permissions
      $('.permission-checkbox').prop('checked', false);

      if (mode === 'edit' && data) {
        title.text('Edit User: ' + data.name);
        form.attr('action', `/settings/staff/${data.id}`);
        methodField.val('PUT');
        
        $('#input_name').val(data.name);
        $('#input_email').val(data.email);
        $('#input_role').val(data.role);
        pwdInput.val('').removeAttr('required');
        pwdLabel.text('PASSWORD BARU');
        pwdHint.removeClass('hidden');

        // Check permissions
        if (data.permissions && Array.isArray(data.permissions)) {
            data.permissions.forEach(p => {
                $(`.permission-checkbox[value="${p}"]`).prop('checked', true);
            });
        }
      } else {
        title.text('Tambah User Baru');
        form.attr('action', '{{ route("admin.staff.store") }}');
        methodField.val('POST');
        form[0].reset();
        pwdInput.attr('required', 'required');
        pwdLabel.text('PASSWORD *');
        pwdHint.addClass('hidden');
      }

      $('body').addClass('slideover-open');
      $('#slideover-container').removeClass('pointer-events-none');
    }

    function toggleAllPermissions() {
        const checkboxes = $('.permission-checkbox');
        const anyUnchecked = checkboxes.filter(':not(:checked)').length > 0;
        checkboxes.prop('checked', anyUnchecked);
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

    function confirmDeleteUser(id, name) {
        if (confirm(`Apakah Anda yakin ingin menghapus user staff "${name}"?`)) {
            const form = $('#delete-user-form');
            form.attr('action', `/settings/staff/${id}`);
            form.submit();
        }
    }
</script>
@endpush
