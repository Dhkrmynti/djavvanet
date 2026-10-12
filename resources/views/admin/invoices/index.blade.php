@extends('admin.layouts.app')

@section('title', 'Tagihan')

@push('styles')
<style>
  /* Page-specific styles for modals */
  /* Mikrotik Style Modals (Centered but Sharp) */
  /* Mikrotik Style Modals (Centered but Sharp) */
  #detail-overlay, #slideover-overlay{transition:opacity .2s ease}
  #detail-modal-panel, #slideover-panel{transition:transform .3s ease-in-out}
  .detail-open #detail-overlay, .slideover-open #slideover-overlay{opacity:1;pointer-events:auto}
  .detail-open #detail-modal-panel{transform:scale(1); opacity: 1; pointer-events: auto;}
  .detail-open #detail-modal-container{pointer-events:auto}
  .slideover-open #slideover-panel{transform:translateX(0)}
</style>
@endpush

@section('content')
    <!-- Page Title Bar -->
    <div class="bg-[var(--bg-card)] border border-[var(--border-main)] px-3 py-1.5 rounded-sm shadow-sm flex items-center justify-between shrink-0 mb-1 transition-colors">
      <div class="flex items-center gap-3">
        <h1 class="text-sm font-bold text-[var(--text-main)]">Billing / Tagihan</h1>
        <div class="flex items-center gap-1">
          <span class="bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 px-1.5 py-0.5 rounded text-[10px] font-bold" id="tagihan-count">{{ $totalInvoices }} invoice</span>
        </div>
      </div>
      <div class="flex items-center gap-4 text-xs font-normal">
        @if(auth()->user()->hasPermission('invoices.generate'))
        <form action="{{ route('admin.invoices.generate') }}" method="POST" onsubmit="return confirmGenerate()" class="flex items-center gap-2">
          @csrf
          <select name="period" id="generate-period-select" class="bg-[var(--bg-card)] border border-[var(--border-main)] rounded px-1.5 py-0.5 outline-none text-[10px] font-bold text-[var(--text-main)] focus:border-blue-500 transition-colors cursor-pointer">
              @php
                  $lastMonth = now()->subMonth()->startOfMonth();
                  $thisMonth = now()->startOfMonth();
                  $nextMonth = (clone $thisMonth)->addMonth();
              @endphp
              <option value="{{ $lastMonth->format('Y-m') }}" selected>Periode {{ $lastMonth->translatedFormat('F Y') }} (Bulan Lalu)</option>
              <option value="{{ $thisMonth->format('Y-m') }}">Periode {{ $thisMonth->translatedFormat('F Y') }} (Bulan Ini)</option>
              <option value="{{ $nextMonth->format('Y-m') }}">Periode {{ $nextMonth->translatedFormat('F Y') }} (Bulan Depan)</option>
          </select>
          <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded font-semibold shadow-sm flex items-center gap-1.5 active:scale-95 transition-all">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Generate
          </button>
        </form>
        @endif
      </div>
    </div>

    <div class="bg-[var(--bg-card)] border border-[var(--border-main)] rounded-sm shadow-sm flex flex-col shrink-0 grow overflow-hidden transition-colors">
      <!-- Toolbar -->
      <div class="bg-[var(--bg-table-head)] border-b border-[var(--border-main)] flex items-center gap-2 px-2 py-1 shrink-0 grow-0 text-[11px] transition-colors">
        @if(auth()->user()->hasPermission('invoices.whatsapp'))
        <button onclick="bulkWA()" class="bg-[var(--bg-card)] border border-[var(--border-main)] hover:bg-[var(--bg-table-head)] px-2 py-0.5 rounded shadow-sm text-green-700 dark:text-green-500 flex items-center gap-1 transition-all active:scale-95 font-bold">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
          WA Masal
        </button>
        @endif

        <button onclick="downloadInvoices()" class="bg-[var(--bg-card)] border border-[var(--border-main)] hover:bg-[var(--bg-table-head)] px-2 py-0.5 rounded shadow-sm text-blue-700 dark:text-blue-500 flex items-center gap-1 transition-all active:scale-95 font-bold">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
          Download Data
        </button>

        <div class="flex items-center gap-1.5 text-[var(--text-muted)]">
          <span>Tampil</span>
          <select id="invoices-length" class="border border-[var(--border-main)] bg-[var(--bg-card)] px-1 py-0.5 rounded-sm outline-none font-bold text-[var(--text-main)]">
            <option>10</option><option>25</option><option>50</option>
          </select>
        </div>

        <div class="flex-1"></div>
        
        <!-- Periode Filter -->
        <div class="flex items-center gap-1.5 mr-2">
            <span class="text-slate-500 font-bold uppercase text-[9px] tracking-tighter">Periode:</span>
            <select id="period-select" class="bg-[var(--bg-card)] border border-[var(--border-main)] rounded px-1.5 py-0.5 outline-none text-[10px] font-bold text-[var(--text-main)] focus:border-blue-500 transition-colors cursor-pointer">
                <option value="">Semua Periode</option>
                @php
                    $start = \Carbon\Carbon::create(2026, 4, 1);
                    $end = now()->startOfMonth();
                    
                    if ($end->lt($start)) { $end = (clone $start); }

                    $current = (clone $end);
                    while ($current->gte($start)) {
                        $val = $current->format('Y-m');
                        $label = $current->translatedFormat('F Y');
                        echo "<option value=\"{$val}\">{$label}</option>";
                        $current->subMonth();
                    }
                @endphp
            </select>
        </div>

        <div class="flex items-center border border-[var(--border-main)] bg-[var(--bg-card)] rounded overflow-hidden">
          <input id="invoices-search" type="text" placeholder="Cari invoice..." class="outline-none px-2 py-0.5 w-32 focus:w-48 transition-all text-[11px] bg-[var(--bg-card)] text-[var(--text-main)]">
        </div>
      </div>

      <div class="flex-1 overflow-auto bg-[var(--bg-table-even)] transition-colors">
        <table id="invoices-table" class="w-full border-collapse tbl tbl-compact">
          <thead class="bg-[var(--bg-table-head)] border-b border-[var(--border-main)] sticky top-0 transition-colors">
            <tr class="font-bold text-[var(--text-muted)]">
              <th class="text-center w-8">#</th>
              <th>No Invoice</th>
              <th>ID Pelanggan</th>
              <th>Nama Pelanggan</th>
              <th>Alamat</th>
              <th>Periode</th>
              <th class="text-right">Total</th>
              <th class="text-center">WA</th>
              <th class="text-center">Status</th>
              <th class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody id="tbody-tagihan"></tbody>
        </table>
      </div>

      <!-- Pagination bar -->
      <div class="bg-[var(--bg-table-head)] border-t border-[var(--border-main)] px-3 py-1.5 flex justify-between items-center text-[10px] text-[var(--text-muted)] shrink-0 transition-colors">
        <span id="invoices-info">Memuat data tagihan...</span>
        <div id="invoices-paginate" class="flex gap-1"></div>
      </div>
    </div>

    <!-- INVOICE DETAIL MODAL (Sharp High-Density Style) -->
    <div id="detail-modal-container" class="fixed inset-0 z-[100] flex items-center justify-center p-4 pointer-events-none transition-all duration-300">
      <div id="detail-overlay" onclick="closeDetail()" class="absolute inset-0 bg-slate-900/60 opacity-0 pointer-events-none transition-opacity"></div>
      <div id="detail-modal-panel" class="relative w-full max-w-lg bg-white dark:bg-slate-900 shadow-2xl border border-slate-300 dark:border-slate-700 rounded-sm flex flex-col scale-95 opacity-0 pointer-events-none transition-all duration-200 overflow-hidden text-slate-800 dark:text-slate-200">
        <!-- Modal Header -->
        <div class="border-b border-slate-200 dark:border-slate-800 p-2 flex items-center justify-between bg-white dark:bg-slate-800">
          <h2 class="text-[12px] font-bold text-blue-800 dark:text-white">Detail Tagihan — <span id="det-inv-number-title" class="font-mono">...</span></h2>
          <button onclick="closeDetail()" class="text-blue-800 dark:text-white hover:bg-slate-100 dark:hover:bg-slate-700 p-0.5 rounded transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>

        <!-- Modal Body -->
        <div class="p-0 bg-white dark:bg-slate-900">
           <div class="grid grid-cols-2 text-[10.5px]">
              <!-- Row 1 -->
              <div class="p-2 border-r border-b border-slate-100 dark:border-slate-800">
                 <div class="text-[8.5px] font-bold text-slate-400 mb-0.5">No. Invoice</div>
                 <div class="font-bold text-[11px] font-mono leading-none" id="det-inv-number">...</div>
              </div>
              <div class="p-2 border-b border-slate-100 dark:border-slate-800">
                 <div class="text-[8.5px] font-bold text-slate-400 mb-0.5">Status</div>
                 <div id="det-status" class="leading-none">...</div>
              </div>
              <!-- Row 2 -->
              <div class="p-2 border-r border-b border-slate-100 dark:border-slate-800">
                 <div class="text-[8.5px] font-bold text-slate-400 mb-0.5">Nama Pelanggan</div>
                 <div class="font-bold text-[11px] leading-none" id="det-customer-name">...</div>
              </div>
              <div class="p-2 border-b border-slate-100 dark:border-slate-800">
                 <div class="text-[8.5px] font-bold text-slate-400 mb-0.5">ID Pelanggan</div>
                 <div class="font-bold text-[11px] leading-none" id="det-customer-code">...</div>
              </div>
              <!-- Row 3 -->
              <div class="p-2 border-r border-b border-slate-100 dark:border-slate-800">
                 <div class="text-[8.5px] font-bold text-slate-400 mb-0.5">Periode Tagihan</div>
                 <div class="font-bold leading-none" id="det-period">...</div>
              </div>
              <div class="p-2 border-b border-slate-100 dark:border-slate-800">
                 <div class="text-[8.5px] font-bold text-slate-400 mb-0.5">Jatuh Tempo</div>
                 <div class="font-bold text-red-600 dark:text-red-400 leading-none" id="det-due-date">...</div>
              </div>
              <!-- Row 4 -->
              <div class="p-2 border-r border-b border-slate-100 dark:border-slate-800">
                 <div class="text-[8.5px] font-bold text-slate-400 mb-0.5">Paket / Profile</div>
                 <div class="font-bold leading-none" id="det-package">...</div>
              </div>
              <div class="p-2 border-b border-slate-100 dark:border-slate-800">
                 <div class="text-[8.5px] font-bold text-slate-400 mb-0.5">Kontak / WA</div>
                 <div class="font-bold leading-none" id="det-whatsapp">...</div>
              </div>
              <!-- Row 5 -->
              <div class="p-2 border-r border-b border-slate-100 dark:border-slate-800">
                 <div class="text-[8.5px] font-bold text-slate-400 mb-0.5">Total Tagihan</div>
                 <div class="font-black text-[13px] text-slate-900 dark:text-white leading-none" id="det-total-amount">...</div>
              </div>
               <div class="p-2 border-r border-b border-slate-100 dark:border-slate-800">
                  <div class="text-[8.5px] font-bold text-slate-400 mb-0.5">Metode Bayar</div>
                  <div class="italic text-slate-500 dark:text-slate-400 leading-none" id="det-payment-method">Transfer (VA)</div>
               </div>
               <div class="p-2 border-b border-slate-100 dark:border-slate-800">
                  <div class="text-[8.5px] font-bold text-slate-400 mb-0.5">Tanggal Aktivasi</div>
                  <div class="font-bold text-blue-600 dark:text-blue-400 leading-none" id="det-active-since">-</div>
               </div>
              <!-- Row 6: Address Full Width -->
              <div class="col-span-2 p-2 border-b border-slate-100 dark:border-slate-800">
                 <div class="text-[8.5px] font-bold text-slate-400 mb-0.5">Alamat</div>
                 <div class="text-slate-600 dark:text-slate-300 leading-tight" id="det-address">...</div>
              </div>
           </div>
        </div>

        <div class="bg-slate-50 dark:bg-slate-800 px-3 py-2 flex justify-end gap-1.5 border-t border-slate-200 dark:border-slate-700">
           <button onclick="copyInvoiceDetail()" class="bg-white border border-blue-600 text-blue-700 dark:text-blue-400 px-3 py-1 rounded-sm font-bold text-[10px] hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-colors flex items-center gap-1">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
              Salin Data
           </button>
           <button onclick="window.location.href='/invoices/'+currentInvoiceData.id+'/pdf'" class="px-3 py-1 border border-blue-600 text-blue-600 dark:text-white rounded-sm font-bold text-[10px] hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-colors">Cetak</button>
           <button onclick="closeDetail()" class="px-3 py-1 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-200 rounded-sm font-bold text-[10px] hover:bg-slate-100 dark:hover:bg-slate-600 transition-colors">Tutup</button>
        </div>
      </div>
    </div>

    <!-- EDIT TAGIHAN SLIDE-OVER -->
    <div id="slideover-container" class="fixed inset-0 z-[110] pointer-events-none">
      <div id="slideover-overlay" onclick="closeSlideover()" class="absolute inset-0 bg-black/40 backdrop-blur-none opacity-0 pointer-events-none transition-opacity"></div>
      <div id="slideover-panel" class="absolute inset-y-0 right-0 w-full max-w-sm bg-[var(--bg-card)] shadow-none border-l border-[var(--border-main)] flex flex-col translate-x-full pointer-events-auto transition-transform">
        <div class="bg-white dark:bg-slate-800 border-b border-[var(--border-main)] p-3 flex items-center justify-between shrink-0">
          <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-blue-600 dark:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            <h2 class="text-[13px] font-bold text-blue-800 dark:text-white">Edit Data Tagihan</h2>
          </div>
          <button onclick="closeSlideover()" class="text-slate-400 hover:text-slate-600 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>
        <form id="edit-invoice-form" action="" method="POST" class="flex-1 overflow-auto flex flex-col bg-white dark:bg-slate-900">
           @csrf
           @method('PUT')
           <div class="p-4 space-y-4">
              <div class="space-y-1">
                 <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-tight">NOMINAL TAGIHAN *</label>
                 <div class="relative">
                    <span class="absolute left-2 top-2 text-[10px] font-bold text-slate-400">Rp</span>
                    <input type="number" name="amount" required class="w-full bg-[#f8fafc] dark:bg-slate-800 border border-[var(--border-main)] rounded-sm p-1.5 pl-7 text-xs focus:ring-1 focus:ring-blue-500 outline-none text-slate-800 dark:text-slate-200 font-bold">
                 </div>
              </div>
              <div class="space-y-1">
                 <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-tight">TANGGAL JATUH TEMPO *</label>
                 <input type="date" name="due_date" required class="w-full bg-[#f8fafc] dark:bg-slate-800 border border-[var(--border-main)] rounded-sm p-1.5 text-xs focus:ring-1 focus:ring-blue-500 outline-none text-slate-800 dark:text-slate-200">
              </div>
              <div class="space-y-1">
                 <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-tight">STATUS PEMBAYARAN *</label>
                 <select name="status" required class="w-full bg-[#f8fafc] dark:bg-slate-800 border border-[var(--border-main)] rounded-sm p-1.5 text-xs focus:ring-1 focus:ring-blue-500 outline-none text-slate-800 dark:text-slate-200">
                    <option value="unpaid">Belum Bayar</option>
                    <option value="paid_transfer">Lunas (Transfer)</option>
                    <option value="paid_cash">Lunas (Cash)</option>
                    <option value="paid_gateway">Lunas (PG)</option>
                    <option value="overdue">Terlambat</option>
                    <option value="cancelled">Dibatalkan</option>
                 </select>
              </div>
 
              <div class="pt-4 mt-4 border-t border-slate-100 opacity-60">
                 <div class="text-[9px] font-bold text-slate-400">INFO PELANGGAN</div>
                 <div class="text-[11px] font-bold text-slate-700" id="edit-customer-display">-</div>
                 <div class="text-[10px] text-slate-500 font-mono" id="edit-inv-number-display">-</div>
              </div>
           </div>
           
           <div class="mt-auto bg-slate-50 dark:bg-slate-800 p-3 border-t border-[var(--border-main)] flex gap-2">
              <button type="button" onclick="closeSlideover()" class="flex-1 bg-white dark:bg-slate-700 border border-[var(--border-main)] text-slate-700 dark:text-slate-200 font-bold py-1.5 rounded-sm hover:bg-slate-50 dark:hover:bg-slate-600 active:scale-95 transition-all text-[11px]">Batal</button>
              <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-1.5 rounded-sm shadow-sm active:scale-95 transition-all text-[11px]">Simpan Perubahan</button>
           </div>
        </form>
      </div>
    </div>
 
    <form id="wa-form" action="" method="POST" class="hidden">
      @csrf
    </form>
    <form id="bulk-wa-form" action="{{ route('admin.invoices.bulk_whatsapp') }}" method="POST" class="hidden">
      @csrf
      <input type="hidden" name="period" id="bulk-wa-period">
    </form>
    <form id="delete-invoice-form" action="" method="POST" class="hidden">
      @csrf
      @method('DELETE')
    </form>
@endsection

@push('scripts')
<script>
    const canEditInvoice = @json(auth()->user()->hasPermission('invoices.edit'));
    const canDeleteInvoice = @json(auth()->user()->hasPermission('invoices.delete'));
    const canWAInvoice = @json(auth()->user()->hasPermission('invoices.whatsapp'));

    function toggleActionDropdown(event, btn) {
        event.stopPropagation();
        const dropdown = $(btn).next('.action-dropdown');
        $('.action-dropdown').not(dropdown).removeClass('show');
        dropdown.toggleClass('show');
    }

    $(document).on('click', function() {
        $('.action-dropdown').removeClass('show');
    });

    let currentInvoiceData = null;
 
    $(document).ready(function() {
      const table = $('#invoices-table').DataTable({
        processing: true,
        serverSide: true,
        ordering: true,
        searching: true,
        pageLength: 10,
        lengthChange: false,
        dom: 't',
        ajax: {
          url: '{{ route("admin.invoices.data") }}',
          data: function(d) {
            d.period = $('#period-select').val();
          }
        },
        order: [[1, 'desc']],
        columns: [
          {
            data: 'status',
            orderable: false,
            searchable: false,
            className: 'text-center',
            render: function(data) {
              if (data === 'paid' || data === 'paid_transfer') return '<span class="text-[#22863a] font-bold">✓</span>';
              if (data === 'paid_cash') return '<span class="text-[#0050b3] font-bold">✓</span>';
              if (data === 'paid_gateway') return '<span class="text-purple-600 font-bold">✓</span>';
              if (data === 'overdue') return '<span class="text-[#cb2431] font-bold">!</span>';
              if (data === 'cancelled') return '<span class="text-[#999] font-bold">✕</span>';
              return '<span class="text-[#e36209] font-bold">○</span>';
            }
          },
          {
            data: 'invoice_number',
            name: 'invoice_number',
            className: 'font-bold text-blue-700 dark:text-blue-400',
            render: function(data) {
              return `<button type="button" class="hover:underline" onclick="openDetail(this.closest('tr'))">${data ?? '-'}</button>`;
            }
          },
          { data: 'customer_code', name: 'customer_code', className: 'font-mono text-[10px] text-[var(--text-muted)]' },
          { data: 'customer_name', name: 'customer_name', className: 'font-bold' },
          { data: 'customer_address', name: 'customer_address', className: 'text-[11px] text-[var(--text-muted)]' },
          { data: 'period_label', name: 'period_label' },
          {
            data: 'total_amount',
            name: 'total_amount',
            className: 'text-right font-bold',
            render: function(data) {
              const amount = Number(data || 0);
              return `Rp ${amount.toLocaleString('id-ID')}`;
            }
          },
          {
            data: 'is_wa_sent',
            name: 'is_wa_sent',
            className: 'text-center',
            render: function(data, type, row) {
               if (data) {
                 const dateStr = row.wa_sent_at ? row.wa_sent_at.split('T')[0] : '';
                 const timeStr = row.wa_sent_at ? row.wa_sent_at.split('T')[1].substring(0, 5) : '';
                 return `<div class="flex flex-col items-center leading-tight">
                            <span class="bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400 px-1 py-0.5 rounded-[2px] text-[8px] font-bold border border-green-200 dark:border-green-800 uppercase tracking-tighter">Terkirim</span>
                            <span class="text-[7px] text-slate-400 dark:text-slate-500 mt-0.5 font-mono">${dateStr} ${timeStr}</span>
                         </div>`;
               }
               return '<span class="bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 px-1 py-0.5 rounded-[2px] text-[8px] font-bold border border-slate-200 dark:border-slate-700 uppercase tracking-tighter">Belum</span>';
            }
          },
          {
            data: 'status',
            name: 'status',
            className: 'text-center',
            render: function(data) {
               if (data === 'paid' || data === 'paid_transfer') return '<span class="badge-up">Lunas (TF)</span>';
               if (data === 'paid_cash') return '<span class="badge-up bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-800">Lunas (Cash)</span>';
               if (data === 'paid_gateway') return '<span class="badge-up bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 border-purple-200 dark:border-purple-800">Lunas (PG)</span>';
              if (data === 'overdue') return '<span class="badge-down">Terlambat</span>';
              if (data === 'cancelled') return '<span class="badge-down opacity-50">Batal</span>';
              return '<span class="badge-warn">Belum Bayar</span>';
            }
          },
          {
            data: null,
            orderable: false,
            searchable: false,
            className: 'text-center',
            render: function(data, type, row) {
               let actionItems = `
                <button onclick="openDetail(this.closest('tr'))" class="action-item block w-full text-left px-3 py-1.5 text-[10px] hover:bg-blue-50 dark:hover:bg-blue-900/40 hover:text-blue-700 dark:hover:text-blue-300 border-b border-gray-100 dark:border-slate-800 flex items-center gap-2 font-bold transition-colors">
                  Detail
                </button>
              `;

              if (canEditInvoice) {
                actionItems += `
                  <button onclick="openEditFromDropdown(this.closest('tr'))" class="action-item block w-full text-left px-3 py-1.5 text-[10px] hover:bg-orange-50 hover:text-orange-700 border-b border-gray-100 flex items-center gap-2 font-bold">
                    Edit
                  </button>
                `;
              }

              actionItems += `
                <button onclick="window.location.href='/invoices/${row.id}/pdf'" class="action-item block w-full text-left px-3 py-1.5 text-[10px] hover:bg-green-50 hover:text-green-700 border-b border-gray-100 flex items-center gap-2 font-bold">
                  Cetak PDF
                </button>
              `;

              if (canWAInvoice) {
                actionItems += `
                  <button onclick="sendWA('${row.id}')" class="action-item block w-full text-left px-3 py-1.5 text-[10px] hover:bg-green-50 hover:text-green-700 border-b border-gray-100 flex items-center gap-2 font-bold">
                    Kirim WA
                  </button>
                `;
              }

              if (canDeleteInvoice) {
                actionItems += `
                  <button onclick="deleteInvoice('${row.id}')" class="action-item block w-full text-left px-3 py-1.5 text-[10px] hover:bg-red-50 dark:hover:bg-red-900/40 hover:text-red-700 dark:hover:text-red-400 flex items-center gap-2 font-bold transition-colors">
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
          row.classList.add('invoice-row', 'transition-colors');
          row.dataset.id = data.id;
          row.dataset.json = JSON.stringify(data.raw_invoice ?? {});
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
            $('#invoices-info').text(`Menampilkan ${info.start + 1} - ${info.end} dari ${info.recordsDisplay} tagihan (total ${info.recordsTotal})`);
          } else {
            $('#invoices-info').text(`Data tidak ditemukan (total ${info.recordsTotal} tagihan)`);
          }
          drawInvoicesPagination(table, info);
        }
      });

      $('#invoices-search').on('keyup', function() {
        table.search(this.value).draw();
      });

      $('#invoices-length').on('change', function() {
        table.page.len(Number(this.value)).draw();
      });

      $('#period-select').on('change', function() {
        table.ajax.reload();
      });

      window.filterTagihan = function(val) {
        table.search(val).draw();
      };
    });

    function drawInvoicesPagination(table, info) {
      let html = `<button class="px-2 py-0.5 border border-[var(--border-main)] bg-[var(--bg-card)] text-[var(--text-muted)] rounded-sm ${info.page === 0 ? 'cursor-not-allowed opacity-60' : ''}" ${info.page === 0 ? 'disabled' : ''} onclick="changeInvoicesPage('prev')">Previous</button>`;

      const pages = info.pages || 1;
      for (let i = 0; i < pages; i++) {
        if (pages <= 7 || i === 0 || i === pages - 1 || Math.abs(i - info.page) <= 1) {
          html += `<button class="px-2 py-0.5 border rounded-sm ${i === info.page ? 'border-blue-600 bg-blue-600 text-white' : 'border-[var(--border-main)] bg-[var(--bg-card)] text-[var(--text-muted)]'}" onclick="changeInvoicesPage(${i})">${i + 1}</button>`;
        } else if (i === 1 || i === pages - 2) {
          html += `<span class="px-1 text-[var(--text-muted)]">...</span>`;
        }
      }

      html += `<button class="px-2 py-0.5 border border-[var(--border-main)] bg-[var(--bg-card)] text-[var(--text-muted)] rounded-sm ${info.page >= pages - 1 ? 'cursor-not-allowed opacity-60' : ''}" ${info.page >= pages - 1 ? 'disabled' : ''} onclick="changeInvoicesPage('next')">Next</button>`;
      $('#invoices-paginate').html(html);
      window.invoicesTableRef = table;
    }

    window.changeInvoicesPage = function(page) {
      if (!window.invoicesTableRef) return;
      if (page === 'prev') {
        window.invoicesTableRef.page('previous').draw('page');
      } else if (page === 'next') {
        window.invoicesTableRef.page('next').draw('page');
      } else {
        window.invoicesTableRef.page(page).draw('page');
      }
    };
 
    function selectRow(tr) {
      $('.invoice-row').removeClass('bg-blue-50 dark:bg-blue-900/30');
      $(tr).addClass('bg-blue-50 dark:bg-blue-900/30');
    }
 
    function openDetail(tr) {
      if(!tr || !tr.dataset.json) return;
      try {
        const data = JSON.parse(tr.dataset.json);
        currentInvoiceData = data;
        const customer = data.customer || {};
        const pkg = customer.package || {};
 
        $('#det-inv-number-title').text(data.invoice_number);
        $('#det-inv-number').text(data.invoice_number);
        $('#det-customer-name').text(customer.full_name || 'N/A');
        $('#det-customer-code').text(customer.customer_code || 'N/A');
        
        let pStart = data.period_start ? new Date(data.period_start) : new Date();
        $('#det-period').text(pStart.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' }));
        
        let dDate = data.due_date ? new Date(data.due_date) : null;
        $('#det-due-date').text(dDate ? dDate.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '-');
        
        $('#det-package').text(`${pkg.name || '-'} (${customer.username || '-'})`);
        $('#det-whatsapp').text(customer.whatsapp_number || '-');
        $('#det-total-amount').text('Rp ' + parseInt(data.total_amount).toLocaleString('id-ID'));
        
        if (customer.active_since) {
            const d = new Date(customer.active_since);
            $('#det-active-since').text(d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }));
        } else {
            $('#det-active-since').text('-');
        }

        $('#det-address').text(customer.address || '-');
 
        const statusContainer = $('#det-status');
        let statusHtml = '<span class="badge-warn">Belum Bayar</span>';
        
        if (data.status === 'paid' || data.status === 'paid_transfer') {
           statusHtml = '<span class="badge-up">Lunas (Transfer)</span>';
        } else if (data.status === 'paid_cash') {
           statusHtml = '<span class="badge-up">Lunas (Cash)</span>';
        } else if (data.status === 'paid_gateway') {
           statusHtml = '<span class="badge-up bg-purple-50 text-purple-700 border-purple-200">Lunas (PG)</span>';
        } else if (data.status === 'overdue') {
           statusHtml = '<span class="badge-down">Terlambat</span>';
        } else if (data.status === 'cancelled') {
           statusHtml = '<span class="badge-down opacity-50">Dibatalkan</span>';
        }
        
        statusContainer.html(statusHtml);
 
        $('body').addClass('detail-open');
      } catch(e) {
        console.error('JSON Parse Error', e);
      }
    }
 
    function closeDetail() {
      $('body').removeClass('detail-open');
    }

    function confirmGenerate() {
        const periodText = $('#generate-period-select option:selected').text();
        const periodVal = $('#generate-period-select').val();
        const today = new Date();
        const date = today.getDate();
        
        const [year, month] = periodVal.split('-').map(Number);
        const selectedDate = new Date(year, month - 1, 1);
        const currentMonthStart = new Date(today.getFullYear(), today.getMonth(), 1);

        if (selectedDate >= currentMonthStart && date !== 1) {
            return confirm(`⚠️ PERINGATAN: Hari ini tanggal ${date}, bukan tanggal 1.\n\nAnda memilih ${periodText}.\n\nGenerate tagihan periode berjalan/mendatang biasanya dilakukan hanya setiap tanggal 1.\n\nApakah Anda yakin ingin melanjutkan?`);
        }
        
        return confirm(`Sistem akan melakukan Scan-and-Fill billing sampai periode ${periodText}.\nSemua periode yang "bolong" (belum ada tagihan) akan otomatis dibuatkan.\n\nLanjutkan?`);
    }
 
    function openEditSlideover() {
      if (!currentInvoiceData) return;
      const data = currentInvoiceData;
      const form = $('#edit-invoice-form');
      
      form.attr('action', `/invoices/${data.id}`);
      form.find('[name="amount"]').val(parseInt(data.total_amount));
      form.find('[name="due_date"]').val(data.due_date ? data.due_date.split('T')[0] : '');
      form.find('[name="status"]').val(data.status);
      
      $('#edit-customer-display').text(data.customer ? data.customer.full_name : '-');
      $('#edit-inv-number-display').text(data.invoice_number);
      
      $('body').addClass('slideover-open');
      $('#slideover-container').removeClass('pointer-events-none');
    }
 
    function openEditFromDropdown(tr) {
      if(!tr || !tr.dataset.json) return;
      currentInvoiceData = JSON.parse(tr.dataset.json);
      openEditSlideover();
    }

    function closeSlideover() {
       $('body').removeClass('slideover-open');
       setTimeout(() => {
           $('#slideover-container').addClass('pointer-events-none');
       }, 300);
    }
 
    function sendWA(id) {
       if (confirm('Kirim invoice via WhatsApp ke pelanggan ini?')) {
          const form = $('#wa-form');
          form.attr('action', `/invoices/${id}/whatsapp`);
          form.submit();
       }
    }

    function bulkWA() {
        const period = $('#period-select').val();
        const periodText = $('#period-select option:selected').text();
        let msg = "Kirim WhatsApp masal ke SEMUA tagihan Belum Bayar?";
        
        if (period) {
            msg = `Kirim WhatsApp masal ke semua tagihan Belum Bayar periode ${periodText}?`;
        }

        if (confirm(msg)) {
            $('#bulk-wa-period').val(period);
            $('#bulk-wa-form').submit();
        }
    }

    function deleteInvoice(id) {
       if (confirm('Apakah Anda yakin ingin menghapus tagihan ini? Tindakan ini tidak dapat dibatalkan.')) {
          const form = $('#delete-invoice-form');
          form.attr('action', `/invoices/${id}`);
          form.submit();
       }
    }

    function downloadInvoices() {
        const period = $('#period-select').val();
        window.location.href = `{{ route('admin.invoices.export') }}?period=${period}`;
    }

    function copyInvoiceDetail() {
      if(!currentInvoiceData) return;
      const data = currentInvoiceData;
      const customer = data.customer || {};
      
      const pStart = data.period_start ? new Date(data.period_start) : new Date();
      const period = pStart.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
      const total = 'Rp ' + parseInt(data.total_amount).toLocaleString('id-ID');
      const dueDate = data.due_date ? new Date(data.due_date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '-';
      const activeSince = customer.active_since ? new Date(customer.active_since).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '-';
      const payLink = `https://payment.djavva.net/pay/${data.payment_hash}`;

      const text = `No. Invoice: ${data.invoice_number}\n` +
                   `Nama: ${customer.full_name || '-'}\n` +
                   `ID Pelanggan: ${customer.customer_code || '-'}\n` +
                   `Tgl Aktif: ${activeSince}\n` +
                   `Periode: ${period}\n` +
                   `Total: ${total}\n` +
                   `Jatuh Tempo: ${dueDate}\n` +
                   `Link Bayar: ${payLink}`;

      navigator.clipboard.writeText(text).then(() => {
          alert('Data tagihan berhasil disalin!');
      });
    }
</script>
@endpush