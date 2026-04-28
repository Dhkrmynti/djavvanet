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
use Barryvdh\DomPDF\Facade\Pdf;
use Yajra\DataTables\Facades\DataTables;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->hasPermission('dashboard.view')) {
            return redirect()->route('admin.customers')->with('info', 'Akses dashboard dibatasi untuk Teknisi.');
        }

        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate   = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));

        $query = Invoice::with(['customer', 'customer.package'])
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        $invoices = Invoice::with('customer.package')->latest()->take(10)->get();

        $cacheKey = "dashboard_stats_{$startDate}_{$endDate}";
        $stats = Cache::remember($cacheKey, 600, function() use ($startDate, $endDate) {
            return [
                'total_customers' => Customer::count(),
                'total_invoice_amount' => Invoice::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])->sum('total_amount'),
                'paid_count'    => Invoice::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])->where('status', 'paid')->count(),
                'unpaid_count'  => Invoice::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])->where('status', 'unpaid')->count(),
            ];
        });

        $cachedSecrets = Cache::get('mikrotik_pppoe_secrets');
        return view('admin.dashboard.index', compact('invoices', 'stats', 'startDate', 'endDate', 'cachedSecrets'));
    }

    public function mikrotikData()
    {
        try {
            $cached = Cache::get('mikrotik_pppoe_secrets');
            $data = $cached['secrets'] ?? [];

            $data = array_values($data);

            $isolirProfile = \App\Models\MikrotikSetting::first()?->isolir_profile ?? 'isolir';
            
            // Ambil data customer dari database untuk di-map ke mikrotik secrets berdasarkan username
            $customers = Customer::select('customer_code', 'username', 'full_name', 'address')->get()->keyBy('username');

            return DataTables::of(collect($data))
                ->addColumn('status', function($s) {
                    $disabled = ($s['disabled'] ?? 'false') === 'true';
                    if ($disabled) {
                        return '<span class="text-red-600 dark:text-red-400 font-black uppercase tracking-tighter text-[10px]">OFF</span>';
                    }
                    return '<span class="text-green-600 dark:text-green-400 font-black uppercase tracking-tighter text-[10px]">ON</span>';
                })
                ->addColumn('db_customer', function($s) use ($customers) {
                    $name = $s['name'] ?? '';
                    $customer = $customers->get($name);
                    
                    if ($customer) {
                        return '
                            <div class="flex flex-col">
                                <span class="font-bold text-slate-800 dark:text-slate-100">'.$customer->full_name.'</span>
                                <span class="text-xs text-slate-500 dark:text-slate-400 uppercase font-medium tracking-wider">'.$customer->customer_code.'</span>
                                <span class="text-[10px] text-slate-400 dark:text-slate-500 truncate max-w-[200px]" title="'.$customer->address.'">'.$customer->address.'</span>
                            </div>
                        ';
                    } else {
                        return '<span class="bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 border border-red-200 dark:border-red-800 text-[8px] font-bold uppercase tracking-wider py-0.5 px-1.5 rounded-[2px] block w-max">Belum Terdaftar</span>';
                    }
                })
                ->addColumn('online', function($s) {
                    $isOnline = isset($s['online']) && $s['online'];
                    if ($isOnline) {
                        return '<div class="dot-online mx-auto"></div>';
                    }
                    return '<div class="h-1.5 w-1.5 rounded-full bg-slate-700 mx-auto"></div>';
                })
                ->addColumn('action', function($s) use ($isolirProfile) {
                    $name = $s['name'] ?? '';
                    $currentProfile = $s['profile'] ?? '';
                    $disabled = ($s['disabled'] ?? 'false') === 'true';
                    $isIsolated = $currentProfile === $isolirProfile;

                    $btnKick = '<button onclick="disconnectUser(\''.$name.'\')" class="action-item text-orange-600 dark:text-orange-400 flex items-center gap-2">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg> Putus Sesi</button>';
                    
                    if ($disabled) {
                        $btnToggle = '<button onclick="toggleUser(\''.$name.'\', \'enable\')" class="action-item text-blue-600 dark:text-blue-400 flex items-center gap-2">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Aktifkan</button>';
                    } else {
                        $btnToggle = '<button onclick="toggleUser(\''.$name.'\', \'disable\')" class="action-item text-slate-500 flex items-center gap-2">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg> Nonaktifkan</button>';
                    }

                    if ($isIsolated) {
                        $btnIsolir = '<button onclick="restoreUser(\''.$name.'\')" class="action-item text-green-600 dark:text-green-400 flex items-center gap-2 border-t border-slate-100 dark:border-slate-700">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg> Buka Isolir</button>';
                    } else {
                        $btnIsolir = '<button onclick="isolirUser(\''.$name.'\')" class="action-item text-red-600 dark:text-red-400 flex items-center gap-2 border-t border-slate-100 dark:border-slate-700">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg> Isolir</button>';
                    }

                    return '
                    <div class="relative flex justify-center">
                        <button onclick="toggleActionMenu(this)" class="hover:bg-[var(--nav-hover)] w-6 h-6 inline-flex items-center justify-center rounded-sm transition-colors text-[var(--text-muted)] border border-transparent hover:border-[var(--border-main)]">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 10c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0-6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 12c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg>
                        </button>
                        <div class="action-dropdown shadow-lg absolute right-0 top-full mt-1 w-32 bg-white dark:bg-slate-800 border border-[var(--border-main)] rounded-sm z-50 overflow-hidden">
                            '.$btnKick.'
                            '.$btnToggle.'
                            '.$btnIsolir.'
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
