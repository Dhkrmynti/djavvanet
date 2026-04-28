@extends('admin.layouts.app')

@section('title', 'Xendit Pay')

@section('content')
<div class="bg-[var(--bg-card)] border border-[var(--border-main)] px-3 py-1.5 rounded-sm shadow-sm flex items-center justify-between shrink-0 mb-1 transition-colors">
  <div class="flex items-center gap-10">
    <div class="flex flex-col gap-0.5">
      <span class="text-[9px] font-bold text-blue-700 dark:text-white uppercase tracking-widest opacity-80 leading-none">Gateway Status</span>
      <div class="flex items-center gap-2">
        <span class="h-2 w-2 rounded-full {{ $settings?->is_active ? 'bg-green-500 animate-pulse' : 'bg-red-500' }}"></span>
        <span class="text-[10px] font-black leading-none uppercase {{ $settings?->is_active ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
            {{ $settings?->is_active ? 'ACTIVE (READY)' : 'INACTIVE' }}
        </span>
      </div>
    </div>
  </div>
  <h1 class="text-xs font-bold text-[var(--text-main)] uppercase tracking-wider">Xendit Payment Gateway</h1>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-2 flex-1 min-h-0">
  <!-- Left Column: Config -->
  <div class="lg:col-span-6 flex flex-col gap-2">
    <div class="flex flex-col border border-[var(--border-main)] rounded-sm bg-[var(--bg-card)] overflow-hidden shadow-sm transition-colors">
      <div class="wf-section-title">Xendit API Config</div>
      <form action="{{ route('admin.settings.xendit.save') }}" method="POST" class="p-3 space-y-4">
         @csrf
         <div class="flex items-center justify-between p-2 bg-[var(--bg-page)] border border-[var(--border-main)] rounded-sm">
            <span class="text-[10px] font-bold text-[var(--text-main)]">Aktifkan Pembayaran</span>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" name="is_active" value="1" class="sr-only peer" {{ $settings?->is_active ? 'checked' : '' }}>
                <div class="w-7 h-4 bg-gray-200 peer-focus:outline-none dark:bg-gray-700 peer-checked:bg-blue-600 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-3 after:w-3 after:transition-all"></div>
            </label>
         </div>
          <div class="space-y-1">
            <label class="text-[9px] font-black text-[var(--text-muted)] uppercase">Secret API Key</label>
            <input name="api_key" type="password" required class="w-full bg-[var(--bg-page)] border border-[var(--border-main)] rounded p-1.5 text-xs text-[var(--text-main)] font-mono outline-none focus:border-blue-500" value="{{ $settings->api_key ?? '' }}" placeholder="xnd_development_...">
          </div>
          <div class="space-y-1">
            <label class="text-[9px] font-black text-[var(--text-muted)] uppercase">Verification Token</label>
            <input name="callback_token" type="password" required class="w-full bg-[var(--bg-page)] border border-[var(--border-main)] rounded p-1.5 text-xs text-[var(--text-main)] font-mono outline-none focus:border-blue-500" value="{{ $settings->callback_token ?? '' }}">
          </div>
         <div class="space-y-1">
            <label class="text-[9px] font-black text-[var(--text-muted)] uppercase">Environment Mode</label>
            <select name="mode" class="w-full bg-[var(--bg-page)] border border-[var(--border-main)] rounded p-1.5 text-xs text-[var(--text-main)] outline-none font-bold">
                <option value="sandbox" {{ ($settings->mode ?? 'sandbox') == 'sandbox' ? 'selected' : '' }}>Sandbox (Testing)</option>
                <option value="production" {{ ($settings->mode ?? '') == 'production' ? 'selected' : '' }}>Production (Live)</option>
            </select>
         </div>
         <div class="space-y-1">
            <label class="text-[9px] font-black text-[var(--text-muted)] uppercase">Biaya Admin Global (Backup)</label>
            <div class="relative">
                <span class="absolute left-2 top-1.5 text-[10px] font-bold text-slate-400">Rp</span>
                <input name="admin_fee" type="number" class="w-full bg-[var(--bg-page)] border border-[var(--border-main)] rounded py-1.5 pl-7 pr-1.5 text-xs text-[var(--text-main)] font-bold outline-none focus:border-blue-500" value="{{ $settings->admin_fee ?? 0 }}" placeholder="Contoh: 5000">
            </div>
            <p class="text-[8px] text-[var(--text-muted)] italic leading-tight mt-1">Gunakan ini jika biaya per metode tidak terdefinisi.</p>
         </div>

         <!-- Per-Method Fees Table -->
         <div class="pt-2">
            <label class="text-[9px] font-black text-blue-700 dark:text-blue-400 uppercase tracking-widest mb-2 block">Biaya per Metode (Xendit Base Fee)</label>
            <div class="border border-[var(--border-main)] rounded-sm overflow-hidden bg-[var(--bg-page)]">
                <table class="w-full text-[10px] border-collapse">
                    <thead class="bg-gray-50 dark:bg-slate-800">
                        <tr class="text-left border-b border-[var(--border-main)]">
                            <th class="p-2 text-slate-500 uppercase tracking-tighter text-[8px]">Metode</th>
                            <th class="p-2 text-slate-500 uppercase tracking-tighter text-[8px]">Base Fee</th>
                            <th class="p-2 text-slate-500 uppercase tracking-tighter text-[8px] text-center">Aktif</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($fees as $fee)
                        <tr class="border-b border-[var(--border-main)] last:border-0 hover:bg-white dark:hover:bg-slate-700 transition-colors">
                            <td class="p-2">
                                <input name="fees[{{ $fee->id }}][name]" type="text" class="w-full bg-white dark:bg-slate-900 border border-[var(--border-main)] rounded px-1.5 py-0.5 text-[10px] font-bold text-slate-700 dark:text-slate-200 outline-none" value="{{ $fee->name }}">
                            </td>
                            <td class="p-2">
                                <div class="flex items-center gap-1">
                                    <select name="fees[{{ $fee->id }}][type]" class="bg-white dark:bg-slate-900 border border-[var(--border-main)] rounded px-1 py-0.5 text-[9px] font-bold text-slate-500 outline-none">
                                        <option value="fixed" {{ $fee->type === 'fixed' ? 'selected' : '' }}>Rp</option>
                                        <option value="percent" {{ $fee->type === 'percent' ? 'selected' : '' }}>%</option>
                                    </select>
                                    <input name="fees[{{ $fee->id }}][value]" type="number" step="0.01" class="w-16 bg-white dark:bg-slate-900 border border-[var(--border-main)] rounded px-1.5 py-0.5 text-[10px] font-bold text-blue-600 outline-none" value="{{ $fee->value }}">
                                </div>
                            </td>
                            <td class="p-2 text-center text-[10px]">
                                <input type="checkbox" name="fees[{{ $fee->id }}][active]" value="1" {{ $fee->is_active ? 'checked' : '' }} class="rounded border-slate-300">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="text-[8px] text-[var(--text-muted)] italic mt-2 uppercase font-bold text-orange-600">* HARGA DI ATAS BELUM TERMASUK PPN 11% (OTOMATIS DITAMBAHKAN SISTEM)</p>
         </div>

         <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 text-[10px] font-black uppercase rounded shadow-sm transition-colors mt-2 active:scale-95">
            Simpan Konfigurasi Xendit →
         </button>
      </form>
    </div>

    <div class="bg-[var(--bg-card)] border border-[var(--border-main)] rounded-sm p-3 shadow-sm transition-colors">
        <h5 class="text-[9px] font-black text-blue-700 dark:text-white uppercase tracking-widest mb-2">Webhook / Callback URL</h5>
        <div class="flex gap-2">
            <input id="callback_url_input" readonly value="{{ url('/api/payment/xendit/callback') }}" class="flex-1 bg-[var(--bg-page)] border border-[var(--border-main)] rounded px-2 py-1 text-[10px] font-mono text-[var(--text-muted)] outline-none">
            <button onclick="copyUrl()" class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-white px-2 rounded text-[9px] font-bold uppercase transition-all active:scale-95 hover:bg-blue-100">COPY</button>
        </div>
        <p class="text-[9px] text-[var(--text-muted)] mt-2 italic">Salin URL ke Dashboard Xendit > Settings > Callbacks.</p>
    </div>
  </div>

  <!-- Right Column: Guide -->
  <div class="lg:col-span-6 flex flex-col gap-2">
    <div class="flex flex-col border border-[var(--border-main)] rounded-sm bg-[var(--bg-card)] overflow-hidden shadow-sm h-full transition-colors">
      <div class="wf-section-title">Panduan Integrasi</div>
      <div class="p-4 space-y-6">
         <div class="flex gap-3">
            <span class="h-6 w-6 shrink-0 flex items-center justify-center rounded-full bg-blue-600 text-white font-black text-[10px]">1</span>
            <div>
                <h6 class="text-[11px] font-bold text-[var(--text-main)] mb-1">Dapatkan API Key</h6>
                <p class="text-[10px] text-[var(--text-muted)] leading-relaxed">Masuk ke Dashboard Xendit > Settings > API Keys. Buat secret key baru dengan permission Write untuk "Invoices" dan Read untuk "Transactions".</p>
            </div>
         </div>
         <div class="flex gap-3">
            <span class="h-6 w-6 shrink-0 flex items-center justify-center rounded-full bg-blue-600 text-white font-black text-[10px]">2</span>
            <div>
                <h6 class="text-[11px] font-bold text-[var(--text-main)] mb-1">Pengaturan Callback</h6>
                <p class="text-[10px] text-[var(--text-muted)] leading-relaxed">Tempelkan Verification Token dari dashboard Xendit ke form di sebelah kiri untuk keamanan data transaksi.</p>
            </div>
         </div>
         <div class="flex gap-3 border-t border-[var(--border-sub)] pt-4">
            <div class="bg-blue-50 dark:bg-blue-900/10 p-3 rounded-sm border border-blue-100 dark:border-blue-800 w-full">
                <p class="text-[10px] text-blue-800 dark:text-white font-medium italic leading-relaxed">
                    "Otomatisasi: Setelah diaktifkan, pelanggan akan melihat tombol pembayaran instan (E-Wallet, VA, QRIS) langsung di portal tagihan mereka."
                </p>
            </div>
         </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
    function copyUrl() {
        const input = document.getElementById('callback_url_input');
        input.select();
        document.execCommand('copy');
        alert('URL disalin!');
    }
</script>
@endpush