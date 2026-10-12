@extends('admin.layouts.app')

@section('content')
<div class="space-y-4">
    <!-- Top Filter Bar -->
    <div class="card p-2 flex items-center justify-between transition-colors bg-white dark:bg-slate-800 border border-[var(--border-main)] rounded-sm">
        <form action="{{ route('admin.dashboard.index') }}" method="GET" class="flex items-center gap-2">
            <div class="flex items-center bg-slate-50 dark:bg-slate-900 border border-[var(--border-main)] rounded-sm overflow-hidden text-[11px]">
                <span class="px-2 text-slate-500 font-bold uppercase text-[10px]">Filter Periode</span>
                <select name="month" class="bg-slate-50 dark:bg-slate-800 py-1 px-2 border-l border-[var(--border-main)] font-bold text-slate-700 dark:text-slate-300 outline-none cursor-pointer">
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" {{ $targetMonth == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(2000, $m, 1)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
                <select name="year" class="bg-slate-50 dark:bg-slate-800 py-1 px-2 border-l border-[var(--border-main)] font-bold text-slate-700 dark:text-slate-300 outline-none cursor-pointer">
                    @foreach(range(now()->year - 2, now()->year + 1) as $y)
                        <option value="{{ $y }}" {{ $targetYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-1 rounded-sm text-[11px] font-bold uppercase tracking-tight shadow-sm flex items-center gap-1.5 transition-all active:scale-95">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                Terapkan Filter
            </button>
        </form>
        <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic">Live Dashboard Monitoring</div>
    </div>

    <!-- Stats Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <!-- Panel 1: Total Pelanggan & Tagihan -->
        <div class="card overflow-hidden p-0 bg-white dark:bg-slate-800 border border-[var(--border-main)] rounded-sm shadow-sm">
            <div class="p-3">
                <div class="text-[9px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2 flex justify-between items-center">
                    <span>Pelanggan & Total Tagihan</span>
                    <span class="bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 px-1.5 py-0.5 rounded text-[8px]">TOTAL</span>
                </div>
                <div class="flex items-center justify-between border-t border-slate-50 dark:border-slate-700 pt-2">
                    <div class="flex flex-col">
                        <span class="text-2xl font-black text-slate-800 dark:text-white font-mono leading-none">{{ number_format($stats['total_customers']) }}</span>
                        <span class="text-[9px] font-bold text-slate-400 uppercase mt-1">Qty Pelanggan</span>
                    </div>
                    <div class="text-right flex flex-col">
                        <span class="text-xl font-black text-blue-600 dark:text-blue-400 font-mono leading-none">Rp {{ number_format($stats['total_invoice_amount'], 0, ',', '.') }}</span>
                        <span class="text-[9px] font-bold text-slate-400 uppercase mt-1">Nominal Tagihan</span>
                    </div>
                </div>
            </div>
            <div class="h-1 w-full bg-blue-600"></div>
        </div>

        <!-- Panel 2: Total Lunas -->
        <div class="card overflow-hidden p-0 bg-white dark:bg-slate-800 border border-[var(--border-main)] rounded-sm shadow-sm">
            <div class="p-3">
                <div class="text-[9px] font-black text-green-600 dark:text-green-500 uppercase tracking-widest mb-2 flex justify-between items-center">
                    <span>Tagihan Lunas</span>
                    <span class="bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 px-1.5 py-0.5 rounded text-[8px]">SUCCESS</span>
                </div>
                <div class="flex items-center justify-between border-t border-slate-50 dark:border-slate-700 pt-2">
                    <div class="flex flex-col">
                        <span class="text-2xl font-black text-slate-800 dark:text-white font-mono leading-none">{{ number_format($stats['paid_count']) }}</span>
                        <span class="text-[9px] font-bold text-slate-400 uppercase mt-1">Qty Invoice</span>
                    </div>
                    <div class="text-right flex flex-col">
                        <span class="text-xl font-black text-green-600 dark:text-green-400 font-mono leading-none">Rp {{ number_format($stats['paid_amount'], 0, ',', '.') }}</span>
                        <span class="text-[9px] font-bold text-slate-400 uppercase mt-1">Total Terbayar</span>
                    </div>
                </div>
            </div>
            <div class="h-1 w-full bg-green-500"></div>
        </div>

        <!-- Panel 3: Belum Lunas -->
        <div class="card overflow-hidden p-0 bg-white dark:bg-slate-800 border border-[var(--border-main)] rounded-sm shadow-sm">
            <div class="p-3">
                <div class="text-[9px] font-black text-orange-600 dark:text-orange-500 uppercase tracking-widest mb-2 flex justify-between items-center">
                    <span>Belum Terbayar</span>
                    <span class="bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300 px-1.5 py-0.5 rounded text-[8px]">PENDING</span>
                </div>
                <div class="flex items-center justify-between border-t border-slate-50 dark:border-slate-700 pt-2">
                    <div class="flex flex-col">
                        <span class="text-2xl font-black text-slate-800 dark:text-white font-mono leading-none">{{ number_format($stats['unpaid_count']) }}</span>
                        <span class="text-[9px] font-bold text-slate-400 uppercase mt-1">Qty Invoice</span>
                    </div>
                    <div class="text-right flex flex-col">
                        <span class="text-xl font-black text-orange-600 dark:text-orange-400 font-mono leading-none">Rp {{ number_format($stats['unpaid_amount'], 0, ',', '.') }}</span>
                        <span class="text-[9px] font-bold text-slate-400 uppercase mt-1">Total Piutang</span>
                    </div>
                </div>
            </div>
            <div class="h-1 w-full bg-orange-500"></div>
        </div>
    </div>

    <!-- Monthly Income Overview Chart -->
    <div class="card bg-white dark:bg-slate-800 border border-[var(--border-main)] rounded-sm shadow-sm overflow-hidden">
        <div class="bg-slate-50 dark:bg-slate-900/50 border-b border-[var(--border-main)] px-4 py-2 flex items-center justify-between">
            <h3 class="text-[11px] font-black text-slate-700 dark:text-slate-300 uppercase tracking-widest italic">Overview Pendapatan Bulanan</h3>
            <div class="flex items-center gap-2">
                <div class="flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                    <span class="text-[9px] font-bold text-slate-400">PENDAPATAN (LUNAS)</span>
                </div>
            </div>
        </div>
        <div class="p-4">
            <div class="relative h-[300px]">
                <canvas id="incomeChart"></canvas>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('incomeChart').getContext('2d');
        const chartData = @json($chartData);
        
        const labels = chartData.map(item => item.label);
        const data = chartData.map(item => item.income);

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Pendapatan (Rp)',
                    data: data,
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    borderWidth: 3,
                    pointBackgroundColor: '#2563eb',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleFont: { size: 10, weight: 'bold' },
                        bodyFont: { size: 12, weight: 'black' },
                        padding: 10,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)',
                            drawBorder: false
                        },
                        ticks: {
                            font: { size: 9, weight: 'bold' },
                            color: '#94a3b8',
                            callback: function(value) {
                                if (value >= 1000000) return (value / 1000000) + 'M';
                                if (value >= 1000) return (value / 1000) + 'k';
                                return value;
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: { size: 9, weight: 'bold' },
                            color: '#94a3b8'
                        }
                    }
                }
            }
        });
    });
</script>
@endpush
@endsection
