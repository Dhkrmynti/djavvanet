@extends('admin.layouts.app')

@section('title', 'Template WA')

@section('content')
<!-- Title Bar -->
<div class="bg-[var(--bg-card)] border border-[var(--border-main)] px-3 py-1.5 rounded-sm shadow-sm flex items-center justify-between shrink-0 mb-1 transition-colors">
  <h1 class="text-sm font-bold text-[var(--text-main)] uppercase tracking-tight">Konfigurasi WhatsApp</h1>
</div>

<div class="flex flex-col flex-1 overflow-hidden" id="wa-template-container">
    <!-- WebFig Style Tabs (jQuery version) -->
    <div class="bg-[var(--bg-card)] border border-[var(--border-main)] flex items-end shrink-0 transition-colors mb-1 rounded-sm shadow-sm">
        <button type="button" data-tab="invoice" class="tab-btn px-6 py-2 text-[10px] uppercase tracking-wider transition-all outline-none border-b-2 border-blue-600 text-blue-600 font-bold bg-blue-50/5">
            Template Invoice
        </button>
        <button type="button" data-tab="greeting" class="tab-btn px-6 py-2 text-[10px] uppercase tracking-wider transition-all outline-none border-b-2 border-transparent text-[var(--text-muted)] hover:bg-black/5 dark:hover:bg-white/5">
            Template Greeting
        </button>
        <button type="button" data-tab="maintenance" class="tab-btn px-6 py-2 text-[10px] uppercase tracking-wider transition-all outline-none border-b-2 border-transparent text-[var(--text-muted)] hover:bg-black/5 dark:hover:bg-white/5">
            Template Maintenance
        </button>
    </div>

    <form action="{{ route('admin.settings.whatsapp_template.save') }}" method="POST" enctype="multipart/form-data" class="flex-1 flex flex-col overflow-hidden gap-1">
        @csrf
        <div class="flex-1 overflow-auto bg-[var(--bg-card)] border border-[var(--border-main)] rounded-sm p-3 shadow-sm transition-colors">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
                <!-- Editor Area -->
                <div class="lg:col-span-8 flex flex-col gap-3">
                    
                    <!-- Invoice -->
                    <div id="tab-content-invoice" class="tab-content flex flex-col flex-1">
                        <div class="wf-section-title mb-1.5 uppercase text-[10px] font-black text-slate-500 tracking-widest">Konten Pesan Tagihan</div>
                        <textarea name="invoice_template" class="w-full bg-[var(--bg-page)] border border-[var(--border-main)] rounded-sm p-3 h-96 text-xs font-mono leading-relaxed text-[var(--text-main)] outline-none focus:ring-1 focus:ring-blue-500 transition-all resize-none shadow-inner" placeholder="Pesan tagihan...">{{ $invoiceTemplate }}</textarea>
                    </div>

                    <!-- Greeting -->
                    <div id="tab-content-greeting" class="tab-content hidden flex flex-col flex-1">
                        <div class="wf-section-title mb-1.5 uppercase text-[10px] font-black text-slate-500 tracking-widest">Konten Pesan Greeting</div>
                        <textarea name="greeting_template" class="w-full bg-[var(--bg-page)] border border-[var(--border-main)] rounded-sm p-3 h-96 text-xs font-mono leading-relaxed text-[var(--text-main)] outline-none focus:ring-1 focus:ring-blue-500 transition-all resize-none shadow-inner" placeholder="Pesan ucapan selamat datang...">{{ $greetingTemplate }}</textarea>
                    </div>

                    <!-- Maintenance -->
                    <div id="tab-content-maintenance" class="tab-content hidden flex flex-col flex-1 gap-4">
                        <div class="flex flex-col">
                            <div class="wf-section-title mb-1.5 uppercase text-[10px] font-black text-slate-500 tracking-widest">Konten Pesan Maintenance</div>
                            <textarea name="maintenance_template" class="w-full bg-[var(--bg-page)] border border-[var(--border-main)] rounded-sm p-3 h-64 text-xs font-mono leading-relaxed text-[var(--text-main)] outline-none focus:ring-1 focus:ring-blue-500 transition-all resize-none shadow-inner" placeholder="Pesan informasi gangguan...">{{ $maintenanceTemplate }}</textarea>
                        </div>

                        <div class="border border-[var(--border-main)] rounded-sm bg-[var(--bg-table-head)] p-3 flex flex-col gap-3 transition-colors">
                            <div class="flex items-center justify-between border-b border-[var(--border-main)] pb-2">
                                <span class="text-[10px] font-bold text-[var(--text-main)] uppercase tracking-widest">Maintenance Image Asset</span>
                                @if($maintenanceImage)
                                <span class="text-[9px] text-green-600 font-bold px-2 py-0.5 bg-green-50 rounded-sm border border-green-200 uppercase tracking-tighter">Status: Image Uploaded</span>
                                @endif
                            </div>
                            
                            <div class="flex items-start gap-4">
                                <div class="flex-1">
                                    <input type="file" name="maintenance_image_file" class="text-[10px] text-[var(--text-muted)] file:mr-3 file:py-1 file:px-3 file:rounded-sm file:border file:border-[var(--border-main)] file:text-[9px] file:font-bold file:bg-[var(--bg-card)] file:text-[var(--text-main)] hover:file:bg-black/5 cursor-pointer">
                                    <p class="text-[9px] text-slate-400 mt-2 font-medium leading-normal italic">ℹ️ Gambar ini disimpan di server dan akan dilampirkan pada setiap pesan maintenance sampai Anda mengunggah gambar baru.</p>
                                </div>
                                @if($maintenanceImage)
                                <div class="shrink-0 border border-[var(--border-main)] rounded-sm p-1 bg-white dark:bg-slate-800 shadow-sm overflow-hidden">
                                    <img src="{{ $maintenanceImage }}" class="h-20 w-auto object-contain">
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Info Area -->
                <div class="lg:col-span-4 flex flex-col gap-3">
                    <div class="flex flex-col border border-[var(--border-main)] rounded-sm bg-[var(--bg-page)] overflow-hidden transition-colors">
                        <div class="bg-[var(--bg-table-head)] border-b border-[var(--border-main)] px-3 py-1.5 text-[10px] font-bold text-[var(--text-main)] uppercase tracking-widest transition-colors">Dynamic Tokens</div>
                        <div class="p-3 space-y-3">
                            <!-- GLOBAL -->
                            <div class="flex flex-col gap-1.5 pt-1">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-0.5 border-b border-slate-100 dark:border-slate-800 pb-1">Data Pelanggan (Global)</p>
                                <div class="flex items-center justify-between text-[11px] font-mono border-b border-[var(--border-main)] pb-1 last:border-0 border-dashed">
                                    <code class="text-blue-600 font-bold">{customer_name}</code>
                                    <span class="text-[var(--text-muted)] text-[9px]">Nama Lengkap</span>
                                </div>
                                <div class="flex items-center justify-between text-[11px] font-mono border-b border-[var(--border-main)] pb-1 last:border-0 border-dashed">
                                    <code class="text-blue-600 font-bold">{customer_code}</code>
                                    <span class="text-[var(--text-muted)] text-[9px]">ID Pelanggan</span>
                                </div>
                                <div class="flex items-center justify-between text-[11px] font-mono border-b border-[var(--border-main)] pb-1 last:border-0 border-dashed">
                                    <code class="text-blue-600 font-bold">{customer_address}</code>
                                    <span class="text-[var(--text-muted)] text-[9px]">Alamat</span>
                                </div>
                                <div class="text-[8px] text-slate-400 italic">Alias: {address}</div>
                            </div>

                            <!-- PACKAGE INFO -->
                            <div class="flex flex-col gap-1.5 pt-2">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-0.5 border-b border-slate-100 dark:border-slate-800 pb-1">Data Layanan & Paket</p>
                                <div class="flex items-center justify-between text-[11px] font-mono border-b border-[var(--border-main)] pb-1 last:border-0 border-dashed">
                                    <code class="text-purple-600 font-bold">{package_name}</code>
                                    <span class="text-[var(--text-muted)] text-[9px]">Nama Paket</span>
                                </div>
                                <div class="flex items-center justify-between text-[11px] font-mono border-b border-[var(--border-main)] pb-1 last:border-0 border-dashed">
                                    <code class="text-purple-600 font-bold">{package_price}</code>
                                    <span class="text-[var(--text-muted)] text-[9px]">Harga Bulanan</span>
                                </div>
                                <div class="text-[8px] text-slate-400 italic">Alias: {package}, {customer_package}, {monthly_price}</div>
                            </div>

                            <!-- INVOICE INFO (Shown always in plain jQuery, or we toggle manually) -->
                            <div id="info-invoice" class="info-group flex flex-col gap-1.5 pt-2">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-0.5 border-b border-slate-100 dark:border-slate-800 pb-1">Informasi Tagihan (Khusus Invoice)</p>
                                <div class="flex items-center justify-between text-[11px] font-mono border-b border-[var(--border-main)] pb-1 last:border-0 border-dashed">
                                    <code class="text-indigo-600 font-bold">{period}</code>
                                    <span class="text-[var(--text-muted)] text-[9px]">Bulan Tagihan</span>
                                </div>
                                <div class="flex items-center justify-between text-[11px] font-mono border-b border-[var(--border-main)] pb-1 last:border-0 border-dashed">
                                    <code class="text-indigo-600 font-bold">{total_amount}</code>
                                    <span class="text-[var(--text-muted)] text-[9px]">Total Bayar</span>
                                </div>
                                <div class="flex items-center justify-between text-[11px] font-mono border-b border-[var(--border-main)] pb-1 last:border-0 border-dashed">
                                    <code class="text-indigo-600 font-bold">{due_date}</code>
                                    <span class="text-[var(--text-muted)] text-[9px]">Jatuh Tempo</span>
                                </div>
                                <div class="flex items-center justify-between text-[11px] font-mono border-b border-[var(--border-main)] pb-1 last:border-0 border-dashed">
                                    <code class="text-indigo-600 font-bold">{payment_link}</code>
                                    <span class="text-[var(--text-muted)] text-[9px]">Link Bayar</span>
                                </div>
                            </div>

                            <!-- GREETING INFO -->
                            <div id="info-greeting" class="info-group hidden flex flex-col gap-1.5 pt-2">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-0.5 border-b border-slate-100 dark:border-slate-800 pb-1">Akun Mikrotik (Khusus Greeting)</p>
                                <div class="flex items-center justify-between text-[11px] font-mono border-b border-[var(--border-main)] pb-1 last:border-0 border-dashed">
                                    <code class="text-emerald-600 font-bold">{username}</code>
                                    <span class="text-[var(--text-muted)] text-[9px]">PPPoE User</span>
                                </div>
                                <div class="flex items-center justify-between text-[11px] font-mono border-b border-[var(--border-main)] pb-1 last:border-0 border-dashed">
                                    <code class="text-emerald-600 font-bold">{password}</code>
                                    <span class="text-[var(--text-muted)] text-[9px]">PPPoE Pass</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col border border-[var(--border-main)] rounded-sm bg-[var(--bg-card)] overflow-hidden transition-colors">
                        <div class="bg-[var(--bg-table-head)] border-b border-[var(--border-main)] px-3 py-1.5 text-[10px] font-bold text-[var(--text-main)] uppercase tracking-widest transition-colors">Style Guide</div>
                        <div class="p-3 space-y-1.5 font-mono text-[10px]">
                            <div class="flex justify-between border-b border-[var(--border-main)] border-dashed pb-1">
                                <span class="text-blue-600">*Bold*</span> <span class="font-bold">Text</span>
                            </div>
                            <div class="flex justify-between border-b border-[var(--border-main)] border-dashed pb-1">
                                <span class="text-blue-600">_Italic_</span> <span class="italic">Text</span>
                            </div>
                            <div class="flex justify-between border-b border-[var(--border-main)] border-dashed pb-1">
                                <span class="text-blue-600">~Strike~</span> <span class="line-through">Text</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-blue-600">```Mono```</span> <span class="bg-black/5 dark:bg-white/10 px-1 rounded-sm">ABC</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Save Bar -->
        <div class="bg-[var(--bg-card)] border border-[var(--border-main)] p-2 rounded-sm shrink-0 flex items-center justify-between transition-colors shadow-sm">
            <div class="flex flex-col pl-1">
                <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest leading-tight">Simpan Perubahan</span>
                <span class="text-[9px] text-slate-400 font-medium italic">Konfigurasi akan diterapkan pada pengiriman berikutnya.</span>
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-1.5 text-[10px] font-bold uppercase rounded-sm shadow-sm transition-all active:scale-95 flex items-center gap-2">
                Save Configuration
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('.tab-btn').click(function() {
        const targetTab = $(this).data('tab');
        
        // Update tab buttons
        $('.tab-btn').removeClass('border-blue-600 text-blue-600 font-bold bg-blue-50/5')
                    .addClass('border-transparent text-[var(--text-muted)] hover:bg-black/5 dark:hover:bg-white/5');
        $(this).addClass('border-blue-600 text-blue-600 font-bold bg-blue-50/5')
               .removeClass('border-transparent text-[var(--text-muted)] hover:bg-black/5 dark:hover:bg-white/5');
        
        // Update contents
        $('.tab-content').addClass('hidden');
        $('#tab-content-' + targetTab).removeClass('hidden');

        // Toggle Dynamic Tokens Info
        $('.info-group').addClass('hidden');
        if (targetTab === 'invoice') {
            $('#info-invoice').removeClass('hidden');
        } else if (targetTab === 'greeting') {
            $('#info-greeting').removeClass('hidden');
        }
    });
});
</script>
@endpush