@extends('admin.layouts.app')

@section('content')
<div class="space-y-2">
    <!-- Top Filter Bar -->
    <div class="card p-1.5 flex items-center justify-between transition-colors">
        <form action="{{ route('admin.dashboard.index') }}" method="GET" class="flex items-center gap-1.5">
            <div class="flex items-center bg-white dark:bg-slate-800 border border-[var(--border-main)] rounded-sm overflow-hidden text-[10px]">
                <span class="px-2 text-slate-500 font-bold uppercase text-[9px]">Periode</span>
                <input type="date" name="start_date" value="{{ $startDate }}" class="bg-transparent py-0.5 px-2 border-l border-r border-[var(--border-main)] font-bold text-slate-700 dark:text-slate-300 outline-none">
                <input type="date" name="end_date" value="{{ $endDate }}" class="bg-transparent py-0.5 px-2 font-bold text-slate-700 dark:text-slate-300 outline-none">
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-0.5 rounded-sm text-[10px] font-bold uppercase tracking-tighter shadow-sm flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                Filter Data
            </button>
        </form>
        <div class="text-[9px] font-bold text-slate-400 uppercase italic">Live Monitoring</div>
    </div>

    <!-- Stats Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-2">
        @php
            $cardStats = [
                ['label' => 'Total Pelanggan', 'value' => $stats['total_customers'], 'unit' => 'User', 'color' => 'bg-blue-600'],
                ['label' => 'Total Nilai Tagihan', 'value' => $stats['total_invoice_amount'], 'unit' => 'Rp', 'color' => 'bg-slate-400', 'is_price' => true],
                ['label' => 'Total Lunas', 'value' => $stats['paid_count'], 'unit' => 'INV', 'color' => 'bg-green-500'],
                ['label' => 'Pending (INV)', 'value' => $stats['unpaid_count'], 'unit' => 'INV', 'color' => 'bg-orange-500', 'label_color' => 'text-orange-600'],
            ];
        @endphp

        @foreach($cardStats as $stat)
        <div class="card overflow-hidden p-0 transition-colors">
            <div class="p-2">
                <div class="text-[8px] font-bold {{ $stat['label_color'] ?? 'text-slate-500' }} uppercase tracking-tighter leading-none mb-1">{{ $stat['label'] }}</div>
                <div class="flex items-baseline gap-1 leading-none">
                    @if($stat['unit'] === 'Rp') <span class="text-[9px] font-bold text-slate-400">Rp</span> @endif
                    <span class="text-lg font-bold text-slate-800 dark:text-white font-mono">
                        @if($stat['is_price'] ?? false)
                            {{ $stat['value'] >= 1000 ? number_format($stat['value']/1000, 0) . 'k' : number_format($stat['value']) }}
                        @else
                            {{ number_format($stat['value']) }}
                        @endif
                    </span>
                    @if($stat['unit'] !== 'Rp') <span class="text-[8px] font-bold text-slate-400 uppercase tracking-tighter">{{ $stat['unit'] }}</span> @endif
                </div>
            </div>
            <div class="h-1 w-full {{ $stat['color'] }}"></div>
        </div>
        @endforeach
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-2">
        <!-- Billing Column -->
        <div class="{{ auth()->user()->hasPermission('dashboard.mikrotik') ? 'lg:col-span-5' : 'lg:col-span-12' }} flex flex-col">
            <div class="card flex-1 flex flex-col overflow-hidden bg-[var(--bg-card)] p-0">
                <div class="bg-[var(--bg-table-head)] border-b border-[var(--border-main)] px-2 py-1 flex items-center justify-between shrink-0">
                    <h2 class="text-[9px] font-bold text-[var(--text-muted)] uppercase tracking-tighter italic">Billing Status (Latest 10)</h2>
                    <span class="px-1 py-0 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-white rounded text-[8px] font-bold">LIVE</span>
                </div>
                <div class="flex-1 overflow-auto bg-[var(--bg-table-even)] min-h-[300px]">
                    <table class="w-full border-collapse tbl tbl-compact">
                        <thead class="sticky top-0 z-10">
                            <tr>
                                <th class="w-20">ID Tagihan</th>
                                <th class="w-16 text-center">Tgl</th>
                                <th class="text-center w-12 border-r border-[var(--border-sub)]">St</th>
                                <th class="text-center w-10">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="font-bold">
                            @forelse($invoices as $i => $inv)
                            <tr class="invoice-row {{ $i % 2 === 0 ? 'bg-white dark:bg-slate-900' : 'bg-[#fcfcfc] dark:bg-slate-800/40' }} hover:bg-blue-50 dark:hover:bg-blue-900/10 transition-colors"
                                data-json="{{ json_encode($inv->load(['customer', 'customer.package'])) }}">
                                <td class="text-blue-700 dark:text-blue-400 cursor-pointer hover:underline" onclick="openInvoiceDetail(this.closest('tr'))">{{ $inv->invoice_number }}</td>
                                <td class="text-center text-slate-500 font-mono text-[9px]">{{ $inv->created_at->format('d M y') }}</td>
                                <td class="text-center border-r border-[var(--border-sub)]">
                                    <span class="{{ $inv->status === 'paid' ? 'text-[#22863a]' : 'text-[#e36209]' }}">
                                        {{ $inv->status === 'paid' ? 'LUNAS' : 'BELUM BAYAR' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button onclick="openInvoiceDetail(this.closest('tr'))" class="text-blue-600 dark:text-blue-400 hover:underline uppercase text-[9px]">Det</button>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="p-4 text-center text-slate-400 italic">No Data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <!-- Summary bar -->
                <div class="bg-[var(--bg-table-head)] border-t border-[var(--border-main)] px-2 py-1 flex justify-between items-center text-[10px] shrink-0">
                    <span class="font-bold text-green-700 uppercase italic text-[9px]">Summary (Lunas)</span>
                    <span class="font-bold text-[var(--text-main)] font-mono">Rp 0</span>
                </div>
            </div>
        </div>

        @if(auth()->user()->hasPermission('dashboard.mikrotik'))
        <!-- Mikrotik Column (~60%) -->
        <div class="lg:col-span-7 flex flex-col">
            <div class="card flex-1 flex flex-col overflow-hidden bg-[var(--bg-card)] p-0">
                <!-- Mikrotik Toolbar -->
                <div class="bg-[var(--bg-table-head)] border-b border-[var(--border-main)] flex items-center gap-3 px-2 py-0.5 shrink-0 grow-0 text-[10px]">
                   <div class="flex items-center gap-1 text-[var(--text-muted)]">
                     <span class="font-bold uppercase text-[8px]">LIMIT</span>
                     <select id="mikrotik-length" class="border border-[var(--border-main)] bg-[var(--bg-card)] px-0.5 py-0 rounded-sm outline-none font-bold text-[var(--text-main)] text-[9px]">
                       <option value="10">10</option>
                       <option value="25">25</option>
                       <option value="50">50</option>
                     </select>
                   </div>
                   <div class="flex-1 text-center">
                       <h2 class="text-[9px] font-bold text-[var(--text-muted)] uppercase tracking-tighter italic">Mikrotik Real-time Status</h2>
                   </div>
                   <div class="flex items-center border border-[var(--border-main)] bg-[var(--bg-card)] rounded-sm overflow-hidden px-1 py-0">
                     <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                     <input type="text" id="mikrotik-search" placeholder="Search..." class="outline-none px-1 py-0.5 w-24 focus:w-40 transition-all text-[9px] bg-[var(--bg-card)] text-[var(--text-main)]">
                   </div>
                </div>

                <div class="flex-1 overflow-auto bg-[var(--bg-table-even)] min-h-[300px]">
                    <table id="mikrotikTable" class="border-collapse tbl tbl-compact w-full">
                        <thead class="sticky top-0 z-10">
                            <tr>
                                <th class="text-center w-6 border-r border-[var(--border-sub)]">#</th>
                                <th class="border-r border-[var(--border-sub)]">Identitas / Username</th>
                                <th class="border-r border-[var(--border-sub)]">Profile</th>
                                <th class="text-center w-8 border-r border-[var(--border-sub)]">St</th>
                                <th class="text-center w-8 border-r border-[var(--border-sub)]">Online</th>
                                <th class="text-center w-8">Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>

                <!-- Mikrotik Pagination -->
                <div class="bg-[var(--bg-table-head)] border-t border-[var(--border-main)] px-2 py-0.5 flex justify-between items-center text-[9px] text-[var(--text-muted)] shrink-0 font-bold uppercase transition-colors">
                    <span id="mikrotik-info" class="text-[8px]">...</span>
                    <div id="mikrotik-paginate" class="flex gap-1"></div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- INVOICE DETAIL MODAL -->
<div id="invoice-detail-container" class="fixed inset-0 z-[100] flex items-center justify-center p-4 pointer-events-none">
  <div id="invoice-detail-overlay" onclick="closeInvoiceDetail()" class="absolute inset-0 bg-slate-900/60 opacity-0 transition-opacity duration-300"></div>
  <div id="invoice-detail-panel" class="relative w-full max-w-lg bg-white dark:bg-slate-900 shadow-2xl border border-slate-300 dark:border-slate-700 rounded-sm flex flex-col scale-95 opacity-0 transition-all duration-200 overflow-hidden text-slate-800 dark:text-slate-200">
    <div class="border-b border-slate-200 dark:border-slate-800 p-2 flex items-center justify-between bg-white dark:bg-slate-800">
      <h2 class="text-[12px] font-bold text-blue-800 dark:text-white">Detail Tagihan — <span id="det-inv-number-title" class="font-mono">...</span></h2>
      <button onclick="closeInvoiceDetail()" class="text-blue-800 dark:text-white hover:bg-slate-100 dark:hover:bg-slate-700 p-0.5 rounded">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="p-0 bg-white dark:bg-slate-900 overflow-auto">
       <div class="grid grid-cols-2 text-[10.5px]">
          <div class="p-2 border-r border-b border-slate-100 dark:border-slate-800">
             <div class="text-[8.5px] font-bold text-slate-400 mb-0.5">No. Invoice</div>
             <div class="font-bold text-[11px] font-mono leading-none" id="det-inv-number">...</div>
          </div>
          <div class="p-2 border-b border-slate-100 dark:border-slate-800">
             <div class="text-[8.5px] font-bold text-slate-400 mb-0.5">Status</div>
             <div id="det-inv-status" class="leading-none font-bold">...</div>
          </div>
          <div class="p-2 border-r border-b border-slate-100 dark:border-slate-800">
             <div class="text-[8.5px] font-bold text-slate-400 mb-0.5">Nama Pelanggan</div>
             <div class="font-bold text-[11px] leading-none" id="det-customer-name">...</div>
          </div>
          <div class="p-2 border-b border-slate-100 dark:border-slate-800">
             <div class="text-[8.5px] font-bold text-slate-400 mb-0.5">ID Pelanggan</div>
             <div class="font-bold text-[11px] leading-none" id="det-customer-code">...</div>
          </div>
          <div class="p-2 border-r border-b border-slate-100 dark:border-slate-800">
             <div class="text-[8.5px] font-bold text-slate-400 mb-0.5">Periode Tagihan</div>
             <div class="font-bold leading-none" id="det-period">...</div>
          </div>
          <div class="p-2 border-b border-slate-100 dark:border-slate-800">
             <div class="text-[8.5px] font-bold text-slate-400 mb-0.5">Jatuh Tempo</div>
             <div class="font-bold text-red-600 dark:text-red-400 leading-none" id="det-due-date">...</div>
          </div>
          <div class="p-2 border-r border-b border-slate-100 dark:border-slate-800">
             <div class="text-[8.5px] font-bold text-slate-400 mb-0.5">Paket / Profile</div>
             <div class="font-bold leading-none" id="det-package">...</div>
          </div>
          <div class="p-2 border-b border-slate-100 dark:border-slate-800">
             <div class="text-[8.5px] font-bold text-slate-400 mb-0.5">Kontak / WA</div>
             <div class="font-bold leading-none" id="det-whatsapp">...</div>
          </div>
          <div class="p-2 border-r border-b border-slate-100 dark:border-slate-800">
             <div class="text-[8.5px] font-bold text-slate-400 mb-0.5">Total Tagihan</div>
             <div class="font-black text-[13px] text-slate-900 dark:text-white leading-none" id="det-total-amount">...</div>
          </div>
          <div class="p-2 border-b border-slate-100 dark:border-slate-800">
             <div class="text-[8.5px] font-bold text-slate-400 mb-0.5">Metode Bayar</div>
             <div class="italic text-slate-500 dark:text-slate-400 leading-none">Transfer (VA) / Cash</div>
          </div>
          <div class="col-span-2 p-2 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/10">
             <div class="text-[8.5px] font-bold text-slate-400 mb-0.5">Alamat</div>
             <div class="text-slate-600 dark:text-slate-300 leading-tight" id="det-address">...</div>
          </div>
       </div>
    </div>
    <div class="bg-slate-50 dark:bg-slate-800 px-3 py-2 flex justify-end gap-1.5 border-t border-slate-200 dark:border-slate-700">
       <button id="btn-print-inv" class="px-3 py-1 border border-blue-600 text-blue-600 dark:text-white rounded-sm font-bold text-[10px] hover:bg-blue-50 dark:hover:bg-blue-900/30 flex items-center gap-1.5 transition-all active:scale-95">
          <svg class="w-3.5 h-3.5 opacity-60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
          <span>Cetak PDF</span>
       </button>
       <button onclick="closeInvoiceDetail()" class="px-3 py-1 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-200 rounded-sm font-bold text-[10px] hover:bg-slate-100 transition-all active:scale-95">Tutup</button>
    </div>
  </div>
</div>

<style>
/* Online Dot Indicator with Blinking Animation */
@keyframes dot-blink {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.4; transform: scale(1.2); }
}
.dot-online { 
    width: 6px; 
    height: 6px; 
    border-radius: 50%; 
    display: inline-block; 
    background-color: #22c55e; 
    box-shadow: 0 0 4px #22c55e;
    animation: dot-blink 1.5s infinite ease-in-out;
}

/* Dashboard Transitions */
.invoice-open #invoice-detail-container { pointer-events: auto; }
.invoice-open #invoice-detail-overlay { opacity: 1; pointer-events: auto; }
.invoice-open #invoice-detail-panel { scale: 1; opacity: 1; pointer-events: auto; }

/* Action Dropdown Styling */
.action-dropdown {
    display: none;
    position: absolute;
    right: 0;
    top: 100%;
    margin-top: 1px;
    width: 100px;
    background: var(--bg-card);
    border: 1px solid var(--border-main);
    z-index: 50;
    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    border-radius: 1px;
}
.action-dropdown.show { display: block; }
.action-item {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 3px 8px;
    width: 100%;
    text-align: left;
    font-size: 9px;
    font-weight: 800;
    text-transform: uppercase;
    border-bottom: 1px solid var(--border-sub);
}
.action-item:last-child { border-bottom: none; }
.action-item:hover { background-color: var(--nav-hover); color: #2563eb; }

/* Custom Pagination Buttons */
.cust-page-btn {
    padding: 1px 6px;
    border: 1px solid var(--border-main);
    background: var(--bg-card);
    color: var(--text-muted);
    border-radius: 1px;
    font-size: 8px;
    font-weight: bold;
    cursor: pointer;
    text-transform: uppercase;
}
.cust-page-btn.active { background: #2563eb; color: white; border-color: #2563eb; }
</style>

@push('scripts')
<script>
$(document).ready(function() {
    // Initialise Mikrotik Table
    const mikTable = $('#mikrotikTable').DataTable({
        processing: true,
        serverSide: true,
        ordering: false,
        ajax: "{{ route('admin.dashboard.mikrotik_data') }}",
        dom: 't',
        pageLength: 10,
        columns: [
            { data: null, render: (d,t,r,m) => `<span class="text-slate-400">${m.row + m.settings._iDisplayStart + 1}</span>`, className: 'text-center border-r border-[var(--border-sub)]' },
            { data: 'name', name: 'name', className: 'font-bold border-r border-[var(--border-sub)]', defaultContent: '-' },
            { data: 'profile', name: 'profile', className: 'font-mono text-[9px] border-r border-[var(--border-sub)]', defaultContent: '-' },
            { data: 'status', name: 'status', className: 'text-center border-r border-[var(--border-sub)]' },
            { data: 'online', name: 'online', className: 'text-center border-r border-[var(--border-sub)]' },
            { data: 'action', name: 'action', className: 'text-center' }
        ],
        drawCallback: function(settings) {
            drawCustomPagination(settings);
            $('#mikrotik-info').text(`REC: ${settings.fnRecordsDisplay()} | DISP: ${settings._iDisplayStart + 1}-${Math.min(settings._iDisplayStart + settings._iDisplayLength, settings.fnRecordsDisplay())}`);
        }
    });

    $('#mikrotik-length').on('change', function() { mikTable.page.len($(this).val()).draw(); });
    $('#mikrotik-search').on('keyup', function() { mikTable.search(this.value).draw(); });

    function drawCustomPagination(settings) {
        const info = mikTable.page.info();
        let html = '';
        
        // Prev button
        html += `<button class="cust-page-btn" ${info.page === 0 ? 'disabled' : ''} onclick="changePage('prev')">PREV</button>`;
        
        // Page numbers
        for (let i = 0; i < info.pages; i++) {
            // Show first, last, and around current page
            if (info.pages <= 5 || i === 0 || i === info.pages - 1 || Math.abs(i - info.page) <= 1) {
                html += `<button class="cust-page-btn ${i === info.page ? 'active' : ''}" onclick="changePage(${i})">${i + 1}</button>`;
            } else if (i === 1 || i === info.pages - 2) {
                html += `<span class="text-[8px] text-slate-400 px-0.5">..</span>`;
            }
        }
        
        // Next button
        html += `<button class="cust-page-btn" ${info.page === info.pages - 1 ? 'disabled' : ''} onclick="changePage('next')">NEXT</button>`;
        
        $('#mikrotik-paginate').html(html);
    }

    window.changePage = function(page) {
        if (page === 'prev') {
            mikTable.page('previous').draw('page');
        } else if (page === 'next') {
            mikTable.page('next').draw('page');
        } else {
            mikTable.page(page).draw('page');
        }
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

function openInvoiceDetail(tr) {
    const data = JSON.parse(tr.dataset.json);
    const customer = data.customer || {};
    const package = customer.package || {};

    $('#det-inv-number-title').text(data.invoice_number);
    $('#det-inv-number').text(data.invoice_number);
    $('#det-customer-name').text(customer.full_name || 'N/A');
    $('#det-customer-code').text(customer.customer_code || 'N/A');
    
    let pStart = data.period_start ? new Date(data.period_start) : new Date();
    $('#det-period').text(pStart.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' }));
    
    let dDate = data.due_date ? new Date(data.due_date) : null;
    $('#det-due-date').text(dDate ? dDate.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '-');
    
    $('#det-package').text(`${package.name || '-'} (${customer.username || '-'})`);
    $('#det-whatsapp').text(customer.whatsapp_number || '-');
    $('#det-total-amount').text('Rp ' + parseInt(data.total_amount).toLocaleString('id-ID'));
    $('#det-address').text(customer.address || '-');

    const statusContainer = $('#det-inv-status');
    let statusHtml = '<span class="px-2 border border-orange-500 text-orange-600 bg-orange-50 text-[9px] rounded-sm uppercase">Belum Bayar</span>';
    if (data.status === 'paid') statusHtml = '<span class="px-2 border border-green-600 text-green-700 bg-green-50 text-[9px] rounded-sm uppercase">Lunas</span>';
    else if (data.status === 'paid_cash') statusHtml = '<span class="px-2 border border-blue-600 text-blue-700 bg-blue-50 text-[9px] rounded-sm uppercase">Lunas (Cash)</span>';
    else if (data.status === 'overdue') statusHtml = '<span class="px-2 border border-red-500 text-red-600 bg-red-50 text-[9px] rounded-sm uppercase">Terlambat</span>';
    
    statusContainer.html(statusHtml);
    $('#btn-print-inv').attr('onclick', `window.open('/invoices/${data.id}/pdf', '_blank')`);

    $('body').addClass('invoice-open');
}

function closeInvoiceDetail() {
    $('body').removeClass('invoice-open');
}
</script>
@endpush
@endsection
