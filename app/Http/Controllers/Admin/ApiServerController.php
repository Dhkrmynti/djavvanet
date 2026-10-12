<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MikrotikSetting;
use App\Models\Customer;
use App\Models\Package;
use App\Services\MikrotikService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Yajra\DataTables\Facades\DataTables;

class ApiServerController extends Controller
{
    public function index()
    {
        $settings = MikrotikSetting::first();
        $routerInfo = Cache::get('mikrotik_router_info', []);
        $stats = [
            'customers' => Customer::count(),
            'secrets'   => count(Cache::get('mikrotik_pppoe_secrets')['secrets'] ?? [])
        ];

        return view('admin.api_server', compact('settings', 'routerInfo', 'stats'));
    }

    public function saveSettings(Request $request)
    {
        $validated = $request->validate([
            'label'           => 'required|string|max:100',
            'host'            => 'required|string|max:255',
            'port'            => 'required|integer|min:1|max:65535',
            'username'        => 'required|string|max:100',
            'password'        => 'nullable|string',
            'default_profile' => 'required|string|max:100',
            'isolir_profile'  => 'required|string|max:100',
            'default_service' => 'required|string|in:pppoe,pptp,l2tp,sstp,any',
        ]);

        $settings = MikrotikSetting::first();
        if ($settings) {
            if (empty($validated['password'])) unset($validated['password']);
            
            $validated['is_active'] = false;
            $settings->update($validated);
        } else {
            $validated['is_active'] = false;
            $validated['password'] = $validated['password'] ?? '';
            MikrotikSetting::create($validated);
        }

        Cache::forget('mikrotik_router_info');
        Cache::forget('mikrotik_pppoe_secrets');

        return back()->with('success', 'Konfigurasi Mikrotik berhasil disimpan.');
    }

    public function testConnection()
    {
        $settings = MikrotikSetting::first();
        if (!$settings) {
            return response()->json(['ok' => false, 'message' => 'Konfigurasi belum disimpan.']);
        }

        $debug = [
            'host' => $settings->host,
            'port' => $settings->port,
            'username' => $settings->username,
            'password_length' => strlen($settings->password ?? ''),
            'password_preview' => substr($settings->password ?? '', 0, 3) . '***',
        ];

        $socketTest = @fsockopen($settings->host, $settings->port, $errno, $errstr, 5);
        $debug['socket_open'] = $socketTest ? true : false;
        $debug['socket_error'] = $socketTest ? null : "$errstr ($errno)";
        if ($socketTest) fclose($socketTest);

        $testPorts = [80, 443, 8728, 8291, $settings->port];
        $debug['port_tests'] = [];
        foreach (array_unique($testPorts) as $tp) {
            $t = @fsockopen($settings->host, $tp, $e2, $s2, 3);
            $debug['port_tests'][$tp] = $t ? 'OPEN' : "CLOSED ($s2)";
            if ($t) fclose($t);
        }

        \Log::info('MikroTik Test Connection', $debug);

        if (!$debug['socket_open']) {
            return response()->json([
                'ok' => false,
                'message' => "Server tidak bisa membuka koneksi ke {$settings->host}:{$settings->port}. Error: {$debug['socket_error']}",
                'debug' => $debug,
            ]);
        }

        $mikrotik = new MikrotikService($settings->host, $settings->username, $settings->password, $settings->port, 5);
        $connected = $mikrotik->connect();

        \Log::info('MikroTik Connect Result', ['connected' => $connected]);

        if ($connected) {
            $identity  = $mikrotik->getSystemIdentity();
            $resource  = $mikrotik->getSystemResource();
            $mikrotik->disconnect();

            $settings->update([
                'is_active' => true,
                'last_connected_at' => now(),
            ]);

            $resData = [
                'ok'        => true,
                'message'   => 'Berhasil terhubung ke ' . $identity,
                'identity'  => $identity,
                'uptime'    => $resource['uptime'] ?? '-',
                'cpu_load'  => $resource['cpu-load'] ?? '-',
                'memory'    => $resource['total-memory'] ?? '-',
                'free_mem'  => $resource['free-memory'] ?? '-',
                'version'   => $resource['version'] ?? '-',
                'board'     => $resource['board-name'] ?? '-',
            ];

            Cache::put('mikrotik_router_info', $resData, 3600);
            return response()->json($resData);
        }

        $mikrotik->disconnect();
        $settings->update(['is_active' => false]);
        return response()->json([
            'ok' => false,
            'message' => 'Gagal terhubung. Periksa IP, port, username, dan password.',
            'debug' => $debug,
        ]);
    }

    public function listSecrets()
    {
        $settings = MikrotikSetting::first();
        if (!$settings) {
            return response()->json(['ok' => false, 'message' => 'Konfigurasi belum disimpan.']);
        }

        $mikrotik = new MikrotikService($settings->host, $settings->username, $settings->password, $settings->port, 60);
        if (!$mikrotik->connect()) {
            return response()->json(['ok' => false, 'message' => 'Tidak dapat terhubung ke Mikrotik.']);
        }

        $secrets = $mikrotik->getPPPoESecrets();
        $active  = $mikrotik->getActivePPPoE();
        $mikrotik->disconnect();

        $routerUsernamesList = array_column($secrets, 'name');
        if (!empty($routerUsernamesList)) {
            \App\Models\Customer::whereIn('username', $routerUsernamesList)->update(['is_synced' => true]);
            \App\Models\Customer::whereNotIn('username', $routerUsernamesList)->update(['is_synced' => false]);
        }

        $activeNames = array_column($active, 'name');

        foreach ($secrets as &$s) {
            $s['online'] = in_array($s['name'] ?? '', $activeNames);
        }

        $data = ['ok' => true, 'secrets' => $secrets, 'active_count' => count($active)];
        
        Cache::put('mikrotik_pppoe_secrets', $data, 3600);

        return response()->json($data);
    }

    public function syncToMikrotik()
    {
        $settings = MikrotikSetting::first();
        if (!$settings) {
            return response()->json(['ok' => false, 'message' => 'Konfigurasi belum disimpan.']);
        }

        $mikrotik = new MikrotikService($settings->host, $settings->username, $settings->password, $settings->port);
        if (!$mikrotik->connect()) {
            return response()->json(['ok' => false, 'message' => 'Tidak dapat terhubung ke Mikrotik.']);
        }

        $customers = Customer::whereNotNull('username')->with('package')->get();
        $existingSecrets = $mikrotik->getPPPoESecrets();
        $existingMap     = [];
        foreach ($existingSecrets as $s) {
            if (isset($s['name'])) $existingMap[$s['name']] = $s;
        }

        $profiles     = $mikrotik->getPPPoEProfiles();
        $profileNames = array_column($profiles, 'name');

        $created = 0; $updated = 0; $errors = 0; $log = [];

        foreach ($customers as $cust) {
            $username = $cust->username;
            $isActive = ($cust->status === 'active');
            $profile  = $cust->mikrotik_profile ?? $settings->default_profile;
            
            if (!$cust->mikrotik_profile && $cust->package) {
                $pkgProfile = $cust->package->profile;
                if (in_array($pkgProfile, $profileNames)) $profile = $pkgProfile;
            }

            if (!isset($existingMap[$username])) {
                if ($isActive) {
                    $res = $mikrotik->addPPPoESecret($username, $cust->password ?? '12345678', $profile, $settings->default_service);
                    if ($res) { 
                        $created++; 
                        $cust->update(['is_synced' => true]);
                        $log[] = ['user' => $username, 'status' => 'created', 'note' => "New User + Profile: $profile"]; 
                    }
                    else { $errors++; $log[] = ['user' => $username, 'status' => 'error', 'note' => 'Failed to create']; }
                }
                continue;
            }
            
            $cust->update(['is_synced' => true]);
            $secret   = $existingMap[$username];
            $disabled = ($secret['disabled'] ?? 'false') === 'true';
            $currentProfile = $secret['profile'] ?? '';

            if ($cust->status === 'active') {
                if ($disabled) {
                    $mikrotik->enablePPPoESecret($username);
                    $updated++; $log[] = ['user' => $username, 'status' => 'updated', 'note' => 'Status Enabled (Active in Web)'];
                }
                if ($currentProfile !== $profile) {
                    $mikrotik->updatePPPoESecret($username, ['profile' => $profile]);
                    $mikrotik->removeActivePPPoE($username);
                    $updated++; $log[] = ['user' => $username, 'status' => 'updated', 'note' => "Profile Restored to: $profile"];
                }
            } elseif ($cust->status === 'suspend') {
                if ($disabled) {
                    $mikrotik->enablePPPoESecret($username);
                    $updated++; $log[] = ['user' => $username, 'status' => 'updated', 'note' => 'Status Enabled for Isolir'];
                }
                if ($currentProfile !== $settings->isolir_profile) {
                    $mikrotik->updatePPPoESecret($username, ['profile' => $settings->isolir_profile]);
                    $mikrotik->removeActivePPPoE($username);
                    $updated++; $log[] = ['user' => $username, 'status' => 'updated', 'note' => "Isolated (Profile: {$settings->isolir_profile})"];
                }
            } elseif ($cust->status === 'inactive' && !$disabled) {
                if ($mikrotik->disablePPPoESecret($username)) {
                    $updated++; $log[] = ['user' => $username, 'status' => 'updated', 'note' => 'Status Disabled (Inactive in Web)'];
                    $mikrotik->removeActivePPPoE($username);
                }
            }
        }

        $mikrotik->disconnect();

        return response()->json([
            'ok'      => true,
            'created' => $created,
            'updated' => $updated,
            'errors'  => $errors,
            'log'     => $log,
            'message' => "Sinkronisasi selesai: $created dibuat, $updated diperbarui, $errors error.",
        ]);
    }

    public function toggleUser(Request $request)
    {
        $request->validate(['username' => 'required|string', 'action' => 'required|in:enable,disable']);

        $settings = MikrotikSetting::first();
        if (!$settings) {
            return response()->json(['ok' => false, 'message' => 'Konfigurasi belum disimpan.']);
        }

        $mikrotik = new MikrotikService($settings->host, $settings->username, $settings->password, $settings->port);
        if (!$mikrotik->connect()) {
            return response()->json(['ok' => false, 'message' => 'Tidak dapat terhubung ke Mikrotik.']);
        }

        $result = $request->action === 'enable'
            ? $mikrotik->enablePPPoESecret($request->username)
            : $mikrotik->disablePPPoESecret($request->username);

        if ($result && $request->action === 'disable') {
            $mikrotik->removeActivePPPoE($request->username);
        }

        $mikrotik->disconnect();

        return response()->json([
            'ok'      => $result,
            'message' => $result ? "User {$request->username} berhasil di-{$request->action}." : 'Gagal mengubah status user.',
        ]);
    }

    public function disconnectUser(Request $request)
    {
        $request->validate(['username' => 'required|string']);

        $settings = MikrotikSetting::first();
        if (!$settings) return response()->json(['ok' => false, 'message' => 'Konfigurasi belum disimpan.']);

        $mikrotik = new MikrotikService($settings->host, $settings->username, $settings->password, $settings->port);
        if (!$mikrotik->connect()) return response()->json(['ok' => false, 'message' => 'Tidak dapat terhubung.']);

        $result = $mikrotik->removeActivePPPoE($request->username);
        $mikrotik->disconnect();

        return response()->json([
            'ok'      => $result,
            'message' => $result ? "User {$request->username} berhasil diputus koneksinya." : "User {$request->username} tidak sedang online.",
        ]);
    }

    public function isolirUser(Request $request)
    {
        $request->validate(['username' => 'required|string']);

        $settings = MikrotikSetting::first();
        if (!$settings) return response()->json(['ok' => false, 'message' => 'Konfigurasi belum disimpan.']);

        $mikrotik = new MikrotikService($settings->host, $settings->username, $settings->password, $settings->port);
        if (!$mikrotik->connect()) return response()->json(['ok' => false, 'message' => 'Tidak dapat terhubung.']);

        $result = $mikrotik->setPPPoESecretProfile($request->username, $settings->isolir_profile);
        
        if ($result) {
            $mikrotik->removeActivePPPoE($request->username);
        }

        $mikrotik->disconnect();

        return response()->json([
            'ok'      => $result,
            'message' => $result ? "User {$request->username} berhasil di-Isolir (Profil: {$settings->isolir_profile})." : "Gagal mengisolir user.",
        ]);
    }

    public function restoreUser(Request $request)
    {
        $request->validate(['username' => 'required|string']);

        $settings = MikrotikSetting::first();
        if (!$settings) return response()->json(['ok' => false, 'message' => 'Konfigurasi belum disimpan.']);

        $customer = Customer::where('username', $request->username)->with('package')->first();
        $targetProfile = $settings->default_profile;
        
        if ($customer) {
            $targetProfile = $customer->mikrotik_profile ?? $settings->default_profile;
            if (!$customer->mikrotik_profile && $customer->package) {
                $targetProfile = $customer->package->profile;
            }
        }

        $mikrotik = new MikrotikService($settings->host, $settings->username, $settings->password, $settings->port);
        if (!$mikrotik->connect()) return response()->json(['ok' => false, 'message' => 'Tidak dapat terhubung.']);

        $result = $mikrotik->setPPPoESecretProfile($request->username, $targetProfile);
        
        if ($result) {
            $mikrotik->removeActivePPPoE($request->username);
        }

        $mikrotik->disconnect();

        return response()->json([
            'ok'      => $result,
            'message' => $result ? "User {$request->username} berhasil dipulihkan (Profil: {$targetProfile})." : "Gagal memulihkan user.",
        ]);
    }
 
    public function getProfiles()
    {
        $settings = MikrotikSetting::first();
        if (!$settings) return response()->json(['ok' => false, 'message' => 'Konfigurasi belum disimpan.']);
 
        $mikrotik = new MikrotikService($settings->host, $settings->username, $settings->password, $settings->port);
        if (!$mikrotik->connect()) {
            return response()->json(['ok' => false, 'message' => 'Tidak dapat terhubung ke Mikrotik.']);
        }
 
        $profiles = $mikrotik->getPPPoEProfiles();
        $mikrotik->disconnect();
 
        $profileNames = array_column($profiles, 'name');
         return response()->json([
            'ok' => true,
            'profiles' => $profileNames
        ]);
    }

    public function mikrotikData()
    {
        try {
            $cached = Cache::get('mikrotik_pppoe_secrets');
            $raw_data = $cached['secrets'] ?? [];
            $data = array_values($raw_data);

            $isolirProfile = MikrotikSetting::first()?->isolir_profile ?? 'isolir';
            $customers = Customer::select('username', 'customer_code', 'full_name')->get()->keyBy('username');

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
                ->editColumn('profile', function($s) { return $s['profile'] ?? '-'; })
                ->rawColumns(['status', 'db_customer', 'online', 'action'])
                ->make(true);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
