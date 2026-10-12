<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Package;
use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Yajra\DataTables\Facades\DataTables;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->hasPermission('dashboard.view')) {
            return redirect()->route('admin.customers')->with('info', 'Akses dashboard dibatasi untuk Teknisi.');
        }

        $targetMonth = $request->input('month', now()->subMonth()->month);
        $targetYear  = $request->input('year', now()->subMonth()->year);
        $targetDate  = Carbon::create($targetYear, $targetMonth, 1);

        $cacheKey = "dashboard_stats_{$targetYear}_{$targetMonth}";
        $stats = Cache::remember($cacheKey, 600, function() use ($targetYear, $targetMonth) {
            $baseQuery = Invoice::whereYear('period_start', $targetYear)
                                ->whereMonth('period_start', $targetMonth);

            return [
                'total_customers' => Customer::count(),
                'total_invoice_amount' => (clone $baseQuery)->sum('total_amount'),
                
                'paid_count'    => (clone $baseQuery)->whereIn('status', ['paid', 'paid_transfer', 'paid_cash', 'paid_gateway'])->count(),
                'paid_amount'   => (clone $baseQuery)->whereIn('status', ['paid', 'paid_transfer', 'paid_cash', 'paid_gateway'])->sum('total_amount'),
                
                'unpaid_count'  => (clone $baseQuery)->where('status', 'unpaid')->count(),
                'unpaid_amount' => (clone $baseQuery)->where('status', 'unpaid')->sum('total_amount'),
            ];
        });

        // Data for Chart.js (Last 6 Months)
        $chartData = Cache::remember("dashboard_chart_{$targetYear}_{$targetMonth}", 3600, function() {
            $data = [];
            for ($i = 5; $i >= 0; $i--) {
                $d = now()->subMonths($i);
                $m = $d->month;
                $y = $d->year;
                $income = Invoice::whereYear('period_start', $y)
                                 ->whereMonth('period_start', $m)
                                 ->whereIn('status', ['paid', 'paid_transfer', 'paid_cash', 'paid_gateway'])
                                 ->sum('total_amount');
                $data[] = [
                    'label' => $d->translatedFormat('M Y'),
                    'income' => (int) $income
                ];
            }
            return $data;
        });

        $cachedSecrets = Cache::get('mikrotik_pppoe_secrets');
        return view('admin.dashboard.index', compact('stats', 'targetMonth', 'targetYear', 'chartData', 'cachedSecrets'));
    }

    public function mikrotikData()
    {
        try {
            $cached = Cache::get('mikrotik_pppoe_secrets');
            $raw_data = $cached['secrets'] ?? [];
            
            // Pastikan data adalah array numerik
            $data = array_values($raw_data);

            $isolirProfile = \App\Models\MikrotikSetting::first()?->isolir_profile ?? 'isolir';
            $customers = Customer::select('username', 'customer_code', 'full_name', 'address')->get()->keyBy('username');

            return DataTables::of($data)
                ->addColumn('status', function($s) {
                    $disabled = ($s['disabled'] ?? 'false') === 'true';
                    return $disabled 
                        ? '<span class="text-red-600 dark:text-red-400 font-black text-[10px]">OFF</span>'
                        : '<span class="text-green-600 dark:text-green-400 font-black text-[10px]">ON</span>';
                })
                ->addColumn('db_customer', function($s) use ($customers) {
                    $username = $s['name'] ?? '';
                    $cust = $customers->get($username);
                    if ($cust) {
                        return '<div class="flex flex-col"><span class="font-bold text-slate-800 dark:text-slate-100">'.$cust->full_name.'</span><span class="text-[9px] text-slate-500 uppercase">'.$cust->customer_code.'</span></div>';
                    }
                    return '<span class="text-[9px] text-slate-400">Tidak Terdaftar</span>';
                })
                ->addColumn('online', function($s) {
                    return (isset($s['online']) && $s['online']) 
                        ? '<div class="dot-online mx-auto"></div>' 
                        : '<div class="h-1.5 w-1.5 rounded-full bg-slate-300 dark:bg-slate-700 mx-auto"></div>';
                })
                ->addColumn('action', function($s) use ($isolirProfile) {
                    $name = $s['name'] ?? '';
                    $isIsolated = ($s['profile'] ?? '') === $isolirProfile;
                    $disabled = ($s['disabled'] ?? 'false') === 'true';

                    $btnKick = '<button onclick="disconnectUser(\''.$name.'\')" class="action-item text-orange-500">Putus Sesi</button>';
                    $btnToggle = $disabled 
                        ? '<button onclick="toggleUser(\''.$name.'\', \'enable\')" class="action-item text-blue-500">Aktifkan</button>'
                        : '<button onclick="toggleUser(\''.$name.'\', \'disable\')" class="action-item text-slate-500">Nonaktifkan</button>';
                    $btnIsolir = $isIsolated
                        ? '<button onclick="restoreUser(\''.$name.'\')" class="action-item text-green-500">Buka Isolir</button>'
                        : '<button onclick="isolirUser(\''.$name.'\')" class="action-item text-red-500">Isolir</button>';

                    return '<div class="relative flex justify-center">
                        <button onclick="toggleActionMenu(this)" class="p-1 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-sm">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 10c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0-6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 12c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg>
                        </button>
                        <div class="action-dropdown shadow-xl absolute right-0 top-full mt-1 w-32 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 z-50 overflow-hidden">
                            '.$btnKick.$btnToggle.$btnIsolir.'
                        </div>
                    </div>';
                })
                ->editColumn('profile', function($s) {
                    return $s['profile'] ?? '-';
                })
                ->rawColumns(['status', 'db_customer', 'online', 'action'])
                ->make(true);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'draw' => 0,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ], 500);
        }
    }


    private function getReportData($startDate, $endDate)
    {
        return Invoice::with(['customer', 'customer.package'])
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->where('status', 'paid')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function exportPdf(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');
        $invoices  = $this->getReportData($startDate, $endDate);
        
        $totalRevenue = $invoices->sum('total_amount');
        $companyName  = 'DJAVVA Net';

        $data = [
            'invoices'     => $invoices,
            'totalRevenue' => $totalRevenue,
            'startDate'    => $startDate,
            'endDate'      => $endDate,
            'companyName'  => $companyName,
        ];

        $pdf = Pdf::loadView('admin.dashboard.pdf', $data);

        return $pdf->download("Laporan_Pendapatan_{$startDate}_sampai_{$endDate}.pdf");
    }

    public function exportExcel(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');
        $invoices  = $this->getReportData($startDate, $endDate);

        $fileName = "Laporan_Pendapatan_{$startDate}_sampai_{$endDate}.csv";

        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = ['Tanggal', 'Nomor Invoice', 'Kode Pelanggan', 'Nama Pelanggan', 'Paket Terdaftar', 'Total Bayar (Rp)'];

        $callback = function() use($invoices, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            $total = 0;
            foreach ($invoices as $inv) {
                fputcsv($file, [
                    $inv->created_at->format('d/m/Y H:i'),
                    $inv->invoice_number,
                    $inv->customer->customer_code ?? '-',
                    $inv->customer->name ?? '-',
                    $inv->customer->package->name ?? '-',
                    $inv->total_amount
                ]);
                $total += $inv->total_amount;
            }

            fputcsv($file, ['', '', '', '', 'TOTAL PENDAPATAN', $total]);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
