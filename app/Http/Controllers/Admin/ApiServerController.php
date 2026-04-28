<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MikrotikSetting;
use App\Models\Customer;
use App\Models\Package;
use App\Services\MikrotikService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ApiServerController extends Controller
{
    public function index()
    {
        $settings      = MikrotikSetting::first();
        $stats         = ['customers' => Customer::count(), 'active' => Customer::where('status', 'active')->count()];
        $cachedSecrets = Cache::get('mikrotik_pppoe_secrets');
        $routerInfo    = Cache::get('mikrotik_router_info');
        
        return view('admin.api_server', compact('settings', 'stats', 'cachedSecrets', 'routerInfo'));
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

        $mikrotik = new MikrotikService($settings->host, $settings->username, $settings->password, $settings->port, 5);
        $connected = $mikrotik->connect();

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
        return response()->json(['ok' => false, 'message' => 'Gagal terhubung. Periksa IP, port, username, dan password.']);
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
}
