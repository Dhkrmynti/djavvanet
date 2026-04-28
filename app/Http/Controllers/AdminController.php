<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Package;
use App\Models\Invoice;
use App\Models\AppSetting;
use App\Models\CoverageArea;
use App\Support\CustomerExport;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Yajra\DataTables\Facades\DataTables;

class AdminController extends Controller
{
    protected $waService;

    public function __construct(WhatsAppService $waService)
    {
        $this->waService = $waService;
    }

    public function checkWhatsApp(Request $request)
    {
        $number = $this->waService->formatNumber($request->number);
        
        if (strlen($number) < 10) {
            return response()->json(['valid' => false, 'message' => 'Nomor terlalu pendek']);
        }

        $res = $this->waService->validateNumber($number);
        $isValid = is_array($res) ? ($res['valid'] ?? false) : $res;
        $message = is_array($res) ? ($res['message'] ?? 'Tidak terhubung ke WhatsApp') : ($isValid ? 'Terhubung ke WhatsApp' : 'Tidak terhubung ke WhatsApp');

        return response()->json([
            'valid' => $isValid,
            'message' => $message
        ]);
    }
    public function dashboard()
    {
        $stats = Cache::remember('admin_dashboard_stats', 600, function () {
            return [
                'customers'     => Customer::count(),
                'packages'      => Package::count(),
            ];
        });

        $recentCustomers = Customer::with('package')
            ->latest()
            ->take(8)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentCustomers'));
    }

    public function storeCustomer(Request $request)
    {
        $request->validate([
            'full_name'       => 'required|string|max:255',
            'package_id'      => 'required|exists:packages,id',
            'whatsapp_number' => 'required|string|max:20',
            'email'           => 'nullable|email|max:255',
            'address'         => 'required|string',
            'password'        => 'required|string|min:4',
            'domain'          => 'nullable|string|in:urban.wifi,djavva.net',
            'billing_cycle'   => 'required|integer|min:1|max:28',
            'customer_code'   => 'nullable|string|max:50',
            'username'        => 'nullable|string|max:255',
            'mikrotik_profile_override' => 'nullable|string|max:255',
        ]);

        $domain = $request->domain ?? 'djavva.net';
        $customerCode = $request->customer_code;
        $username = $request->username;

        if (!$customerCode) {
            if ($domain === 'urban.wifi') {
                $startCode = '12026010051';
            } else {
                $startCode = '188175700';
            }

            $maxCode = Customer::where('username', 'like', '%@' . $domain)->max('customer_code');
            $customerCode = ($maxCode && (int)$maxCode >= (int)$startCode)
                ? (string)((int)$maxCode + 1)
                : $startCode;
        }

        if (!$username) {
            $username = $customerCode . '@' . $domain;
        } elseif (!str_contains($username, '@')) {
            $username .= '@' . $domain;
        }

        $customer = Customer::create([
            'package_id'      => $request->package_id,
            'customer_code'   => $customerCode,
            'username'        => $username,
            'code_prefix'     => strtoupper(str_replace('.', '_', $domain)), 
            'password'        => bcrypt($request->password),
            'password_plain'   => $request->password,
            'full_name'       => $request->full_name,
            'whatsapp_number' => $request->whatsapp_number,
            'email'           => $request->email,
            'address'         => $request->address,
            'billing_cycle'   => 1,
            'mikrotik_profile' => $request->mikrotik_profile_override,
            'active_since'    => now(),
            'status'          => 'active',
        ]);

        try {
            $settings = \App\Models\MikrotikSetting::where('is_active', true)->first();
            if ($settings) {
                $mikrotik = new \App\Services\MikrotikService($settings->host, $settings->username, $settings->password, $settings->port);
                if ($mikrotik->connect()) {
                    $profile = $customer->mikrotik_profile;
                    if (!$profile) {
                        $package = \App\Models\Package::find($customer->package_id);
                        $profile = $package ? $package->profile : 'default';
                    }
                    $mikrotik->addPPPoESecret($customer->username, $request->password, $profile, $settings->default_service ?? 'pppoe');
                    $mikrotik->disconnect();
                    $customer->update(['is_synced' => true]);
                    Cache::forget('mikrotik_pppoe_secrets');
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to push customer to Mikrotik: ' . $e->getMessage());
        }

        return redirect()->route('admin.customers')->with('success', 'Pelanggan baru berhasil ditambahkan.');
    }

    public function getNextId(\Illuminate\Http\Request $request)
    {
        $domain = $request->domain;
        if ($domain === 'urban.wifi') {
            $startCode = '12026010051';
        } else {
            $startCode = '188175700';
        }

        $maxCode = \App\Models\Customer::where('username', 'like', '%@' . $domain)->max('customer_code');
        $nextCode = ($maxCode && (int)$maxCode >= (int)$startCode)
            ? (string)((int)$maxCode + 1)
            : $startCode;

        return response()->json([
            'next_id' => $nextCode,
            'username' => $nextCode . '@' . $domain,
            'password' => \Illuminate\Support\Str::random(6)
        ]);
    }

    public function checkCustomerCode(Request $request)
    {
        $code = $request->code;
        $domain = $request->domain;
        $excludeId = $request->exclude_id; 

        $query = Customer::where('customer_code', $code);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $exists = $query->exists();

        return response()->json([
            'exists' => $exists,
            'message' => $exists ? 'ID Pelanggan sudah terdaftar!' : 'ID tersedia.'
        ]);
    }

    public function getCustomerInvoices(Customer $customer)
    {
        $invoices = $customer->invoices()->latest()->take(5)->get()->map(function($inv) {
            return [
                'invoice_number' => $inv->invoice_number,
                'period' => $inv->period_start->translatedFormat('F Y'),
                'total' => 'Rp ' . number_format($inv->total_amount, 0, ',', '.'),
                'status' => $inv->status,
                'id' => $inv->id,
            ];
        });

        return response()->json([
            'customer_name' => $customer->full_name,
            'invoices' => $invoices
        ]);
    }

    public function showCustomer(Customer $customer)
    {
        $customer->load(['package', 'invoices' => function($q) {
            $q->latest();
        }]);
        
        return view('admin.customers.show', compact('customer'));
    }

    public function editCustomer(Customer $customer)
    {
        return view('admin.customers.edit', compact('customer'));
    }

    public function updateCustomer(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'full_name'       => 'required|string|max:255',
            'package_id'      => 'required|exists:packages,id',
            'whatsapp_number' => 'required|string|max:20',
            'email'           => 'nullable|email|max:255',
            'address'         => 'required|string',
            'status'          => 'required|in:active,inactive,suspend',
            'username'        => 'nullable|string|max:255',
            'customer_code'   => 'nullable|string|max:50',
            'mikrotik_profile_override' => 'nullable|string|max:255',
        ]);

        $validated['billing_cycle'] = 1;

        if ($request->filled('username')) {
            $username = $request->username;
            $domain = $request->domain ?? ($customer->username ? explode('@', $customer->username)[1] : 'djavva.net');
            if (!str_contains($username, '@')) {
                $validated['username'] = $username . '@' . $domain;
            }
        }

        if ($request->filled('password')) {
            $validated['password'] = bcrypt($request->password);
            $validated['password_plain'] = $request->password;
        }

        if ($request->has('mikrotik_profile_override')) {
            $validated['mikrotik_profile'] = $request->mikrotik_profile_override;
        }
 
        $oldUsername = $customer->username;
        $customer->update($validated);

        try {
            $settings = \App\Models\MikrotikSetting::where('is_active', true)->first();
            if ($settings) {
                $mikrotik = new \App\Services\MikrotikService($settings->host, $settings->username, $settings->password, $settings->port);
                if ($mikrotik->connect()) {
                    $params = [];
                    if ($request->filled('username')) $params['name'] = $customer->username;
                    if ($request->filled('password')) $params['password'] = $request->password;
                    
                    $profile = $customer->mikrotik_profile;
                    if (!$profile && $customer->package_id) {
                        $package = \App\Models\Package::find($customer->package_id);
                        $profile = $package ? $package->profile : 'default';
                    }

                    if ($customer->status === 'suspend') {
                        $params['profile'] = $profile ?: 'default';
                    }

                    $res = $mikrotik->updatePPPoESecret($oldUsername, $params);
                    
                    if ($res) {
                        $customer->update(['is_synced' => true]);
                        if ($oldUsername !== $customer->username || $request->filled('password') || ($customer->status === 'suspend')) {
                            $mikrotik->removeActivePPPoE($oldUsername);
                            if ($oldUsername !== $customer->username) {
                                $mikrotik->removeActivePPPoE($customer->username);
                            }
                        }
                    }
                    
                    $mikrotik->disconnect();
                    Cache::forget('mikrotik_pppoe_secrets');
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to sync customer update to Mikrotik: ' . $e->getMessage());
        }

        return back()->with('success', 'Data pelanggan berhasil diperbarui.');
    }

    public function destroyCustomer(Customer $customer)
    {
        $activeInvoices = $customer->invoices()->whereIn('status', ['unpaid', 'paid', 'paid_cash', 'overdue'])->count();
        
        if ($activeInvoices > 0) {
            return back()->with('error', 'Pelanggan tidak bisa dihapus karena memiliki tagihan Aktif/Lunas. Batalkan atau hapus tagihan terlebih dahulu.');
        }

        $username = $customer->username;
        
        try {
            DB::beginTransaction();

            $customer->invoices()->where('status', 'cancelled')->delete();
            $customer->delete();

            $settings = \App\Models\MikrotikSetting::where('is_active', true)->first();
            if ($settings && $settings->is_active) {
                $mikrotik = new \App\Services\MikrotikService($settings->host, $settings->username, $settings->password, $settings->port);
                if ($mikrotik->connect()) {
                    $mikrotik->removePPPoESecret($username);
                    $mikrotik->removeActivePPPoE($username);
                    $mikrotik->disconnect();
                    Cache::forget('mikrotik_pppoe_secrets');
                }
            }

            DB::commit();
            return back()->with('success', 'Pelanggan berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Failed to remove customer: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghapus pelanggan: ' . $e->getMessage());
        }
    }

    public function createCustomer()
    {
        return view('admin.customers.create');
    }

    public function customers(Request $request)
    {
        $packages = Package::orderBy('sort_order')->get();
        $totalCustomers = Customer::count();
        $backupSettings = [
            'mode' => AppSetting::get('customer_backup_mode', 'manual'),
            'frequency' => AppSetting::get('customer_backup_frequency', 'disabled'),
            'weekday' => AppSetting::get('customer_backup_weekday', 'monday'),
            'hour' => AppSetting::get('customer_backup_hour', '06'),
            'emails' => AppSetting::get('customer_backup_emails', ''),
        ];

        $mikrotikCache = Cache::get('mikrotik_pppoe_secrets');
        $routerUsernames = [];
        if ($mikrotikCache && isset($mikrotikCache['secrets'])) {
            $routerUsernames = collect($mikrotikCache['secrets'])->pluck('name')->toArray();
        }

        return view('admin.customers', compact('packages', 'routerUsernames', 'backupSettings', 'totalCustomers'));
    }

    public function customersData(Request $request)
    {
        $query = Customer::with('package')->select('customers.*');

        return DataTables::eloquent($query)
            ->addColumn('package_name', function (Customer $customer) {
                return $customer->package->name ?? 'N/A';
            })
            ->addColumn('profile_name', function (Customer $customer) {
                return $customer->mikrotik_profile ?: ($customer->package->profile ?? '-');
            })
            ->addColumn('monthly_price', function (Customer $customer) {
                return (float) ($customer->package->monthly_price ?? 0);
            })
            ->addColumn('raw_customer', function (Customer $customer) {
                return $customer->toArray();
            })
            ->filterColumn('package_name', function ($query, $keyword) {
                $query->whereHas('package', function ($packageQuery) use ($keyword) {
                    $packageQuery->where('name', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('profile_name', function ($query, $keyword) {
                $query->where(function ($scopeQuery) use ($keyword) {
                    $scopeQuery->where('mikrotik_profile', 'like', "%{$keyword}%")
                        ->orWhereHas('package', function ($packageQuery) use ($keyword) {
                            $packageQuery->where('profile', 'like', "%{$keyword}%");
                        });
                });
            })
            ->toJson();
    }

    public function exportCustomersCsv()
    {
        $rows = CustomerExport::rows();
        $filename = 'customers-backup-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, CustomerExport::headings());

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportCustomersExcel()
    {
        $rows = CustomerExport::rows();
        $filename = 'customers-backup-' . now()->format('Ymd-His') . '.xls';

        return response()->streamDownload(function () use ($rows) {
            echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
            echo '<head><meta charset="UTF-8"></head><body>';
            echo '<table border="1">';
            echo '<tr>';
            foreach (CustomerExport::headings() as $heading) {
                echo '<th style="background-color:#4CAF50;color:white;">' . htmlspecialchars($heading) . '</th>';
            }
            echo '</tr>';

            foreach ($rows as $row) {
                echo '<tr>';
                foreach ($row as $cell) {
                    echo '<td>' . htmlspecialchars((string) ($cell ?? '')) . '</td>';
                }
                echo '</tr>';
            }
            echo '</table></body></html>';
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
        ]);
    }

    public function updateBackupSettings(Request $request)
    {
        $validated = $request->validate([
            'backup_mode' => 'required|in:manual,automatic',
            'frequency' => 'required|in:disabled,daily,weekly',
            'weekday' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'hour' => 'required|string|in:00,01,02,03,04,05,06,07,08,09,10,11,12,13,14,15,16,17,18,19,20,21,22,23',
            'emails' => 'nullable|string|max:1000',
        ]);

        $emails = collect(explode(',', (string) ($validated['emails'] ?? '')))
            ->map(fn ($email) => trim($email))
            ->filter()
            ->unique()
            ->values();

        foreach ($emails as $email) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return back()->with('error', "Email backup tidak valid: {$email}");
            }
        }

        AppSetting::set('customer_backup_mode', $validated['backup_mode']);
        AppSetting::set('customer_backup_frequency', $validated['frequency']);
        AppSetting::set('customer_backup_weekday', $validated['weekday']);
        AppSetting::set('customer_backup_hour', $validated['hour']);
        AppSetting::set('customer_backup_emails', $emails->implode(','));

        return back()->with('success', 'Pengaturan backup pelanggan berhasil disimpan.');
    }

    public function packages()
    {
        $packages = Package::orderBy('sort_order')->orderBy('monthly_price')->get();
        return view('admin.packages', compact('packages'));
    }

    public function togglePackage(Package $package)
    {
        $package->update(['is_active' => !$package->is_active]);
        Cache::forget('active_packages');
        return back()->with('success', 'Status paket berhasil diperbarui.');
    }

    public function storePackage(Request $request)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'profile'          => 'nullable|string|max:255',
            'monthly_price'    => 'required|numeric|min:0',
            'installation_fee' => 'nullable|numeric|min:0',
            'description'      => 'nullable|string',
            'badge'            => 'nullable|string|max:50',
            'features'         => 'nullable|string',
            'is_active'        => 'nullable|boolean',
        ]);

        if ($request->filled('features')) {
            $validated['features'] = array_map('trim', explode(',', $request->features));
        } else {
            $validated['features'] = [];
        }

        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = Package::max('sort_order') + 1;

        Package::create($validated);
        Cache::forget('active_packages');

        return back()->with('success', 'Paket baru berhasil ditambahkan.');
    }

    public function updatePackage(Request $request, Package $package)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'profile'          => 'nullable|string|max:255',
            'monthly_price'    => 'required|numeric|min:0',
            'installation_fee' => 'nullable|numeric|min:0',
            'description'      => 'nullable|string',
            'badge'            => 'nullable|string|max:50',
            'features'         => 'nullable|string',
            'is_active'        => 'nullable|boolean',
            'sort_order'       => 'nullable|integer',
        ]);

        if ($request->filled('features')) {
            $validated['features'] = array_map('trim', explode(',', $request->features));
        } else {
            $validated['features'] = [];
        }

        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = $request->input('sort_order', $package->sort_order);

        $package->update($validated);
        Cache::forget('active_packages');

        return back()->with('success', 'Paket berhasil diperbarui.');
    }

    public function destroyPackage(Package $package)
    {
        if ($package->customers()->count() > 0) {
            return back()->with('error', 'Paket tidak bisa dihapus karena sedang digunakan oleh pelanggan.');
        }

        $package->delete();
        Cache::forget('active_packages');

        return back()->with('success', 'Paket berhasil dihapus.');
    }

    public function syncSingleCustomer(Customer $customer)
    {
        $settings = \App\Models\MikrotikSetting::where('is_active', true)->first();
        if (!$settings) {
            return response()->json(['ok' => false, 'message' => 'Konfigurasi Mikrotik belum diset atau tidak aktif.']);
        }

        try {
            $mikrotik = new \App\Services\MikrotikService($settings->host, $settings->username, $settings->password, $settings->port);
            if (!$mikrotik->connect()) {
                return response()->json(['ok' => false, 'message' => 'Gagal terkoneksi ke router.']);
            }

            $profile = $customer->mikrotik_profile;
            if (!$profile && $customer->package_id) {
                $package = \App\Models\Package::find($customer->package_id);
                $profile = $package ? $package->profile : 'default';
            }

            $mikrotik->addPPPoESecret($customer->username, $customer->password_plain ?? '12345678', $profile ?? 'default', $settings->default_service ?? 'pppoe');
            $mikrotik->disconnect();
            
            $customer->update(['is_synced' => true]);
            Cache::forget('mikrotik_pppoe_secrets');

            return response()->json(['ok' => true, 'message' => 'Data berhasil di-push ke Mikrotik!']);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Single sync failed: ' . $e->getMessage());
            return response()->json(['ok' => false, 'message' => 'Terjadi kesalahan sistem saat mencoba sinkronisasi.']);
        }
    }

    public function sendGreeting(Customer $customer)
    {
        $template = \App\Models\AppSetting::get('wa_greeting_template');
        if (!$template) {
            return response()->json(['ok' => false, 'message' => 'Template greeting belum diatur di Pengaturan WA.']);
        }

        $message = str_replace(
            ['{customer_name}', '{customer_code}', '{username}', '{password}', '{address}', '{customer_address}', '{package_name}', '{package}', '{customer_package}', '{package_price}', '{monthly_price}'],
            [
                $customer->full_name, 
                $customer->customer_code, 
                $customer->username, 
                $customer->password_plain,
                $customer->address ?? '-',
                $customer->address ?? '-',
                $customer->package->name ?? '-',
                $customer->package->name ?? '-',
                $customer->package->name ?? '-',
                $customer->package ? 'Rp ' . number_format($customer->package->monthly_price, 0, ',', '.') : 'Rp 0',
                $customer->package ? 'Rp ' . number_format($customer->package->monthly_price, 0, ',', '.') : 'Rp 0'
            ],
            $template
        );

        $res = $this->waService->sendMessage($customer->whatsapp_number, $message, null, true);
        
        if ($res === true) {
            return response()->json(['ok' => true, 'message' => 'Greeting berhasil dikirim ke ' . $customer->full_name]);
        }

        return response()->json(['ok' => false, 'message' => is_array($res) ? ($res['message'] ?? 'Gagal mengirim WA') : 'Gagal mengirim WA']);
    }

    public function blastMaintenance(Request $request)
    {
        $template = \App\Models\AppSetting::get('wa_maintenance_template');
        $imagePath = \App\Models\AppSetting::get('wa_maintenance_image');
        $imageUrl = null;
        
        if ($imagePath) {
            // Jika isinya adalah URL (migrasi lama), pakai langsung. Jika path, generate URL.
            $imageUrl = filter_var($imagePath, FILTER_VALIDATE_URL) ? $imagePath : \Illuminate\Support\Facades\Storage::disk('public')->url($imagePath);
        }
        
        if (!$template) {
            return response()->json(['ok' => false, 'message' => 'Template maintenance belum diatur.']);
        }

        $targets = [];
        if ($request->target === 'all') {
            $targets = Customer::where('status', 'active')->get();
        } else {
            $targets = Customer::whereIn('id', $request->customer_ids ?? [])->get();
        }

        if ($targets->isEmpty()) {
            return response()->json(['ok' => false, 'message' => 'Tidak ada target pelanggan ditemukan.']);
        }

        $successCount = 0;
        $failCount = 0;

        foreach ($targets as $customer) {
            $message = str_replace(
                ['{customer_name}', '{customer_code}', '{address}', '{customer_address}', '{package_name}', '{package}', '{customer_package}', '{package_price}', '{monthly_price}'],
                [
                    $customer->full_name, 
                    $customer->customer_code,
                    $customer->address ?? '-',
                    $customer->address ?? '-',
                    $customer->package->name ?? '-',
                    $customer->package->name ?? '-',
                    $customer->package->name ?? '-',
                    $customer->package ? 'Rp ' . number_format($customer->package->monthly_price, 0, ',', '.') : 'Rp 0',
                    $customer->package ? 'Rp ' . number_format($customer->package->monthly_price, 0, ',', '.') : 'Rp 0'
                ],
                $template
            );

            $res = $this->waService->sendMessage($customer->whatsapp_number, $message, $imageUrl);
            if ($res === true) {
                $successCount++;
            } else {
                $failCount++;
            }
        }

        return response()->json([
            'ok' => true, 
            'message' => "Blast selesai. Berhasil: {$successCount}, Gagal: {$failCount}"
        ]);
    }
}
