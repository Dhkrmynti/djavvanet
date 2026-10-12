<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;

class StaffController extends Controller
{
    public static $permissions = [
        'Dashboard' => [
            'dashboard.view' => 'Lihat Dashboard',
            'dashboard.mikrotik' => 'Lihat Real-time Mikrotik di Dashboard',
        ],
        'Pelanggan' => [
            'customers.view' => 'Lihat Daftar Pelanggan',
            'customers.create' => 'Tambah Pelanggan Baru',
            'customers.edit' => 'Edit Data Pelanggan',
            'customers.delete' => 'Hapus Pelanggan',
            'customers.sync' => 'Push ke Mikrotik',
            'customers.whatsapp' => 'Cek & Kirim WhatsApp Pelanggan',
        ],
        'Tagihan' => [
            'invoices.view' => 'Lihat Daftar Tagihan',
            'invoices.edit' => 'Edit Tagihan',
            'invoices.delete' => 'Hapus Tagihan',
            'invoices.generate' => 'Generate Tagihan Baru',
            'invoices.whatsapp' => 'Kirim WA Tagihan',
        ],
        'Paket' => [
            'packages.view' => 'Lihat Paket Internet',
            'packages.manage' => 'Kelola Paket (Add/Edit/Delete)',
        ],
        'Chat' => [
            'chat.manage' => 'Kelola Live Chat',
        ],
        'Sistem' => [
            'staff.manage' => 'Kelola Akun Staff',
            'settings.mikrotik' => 'Setting Mikrotik',
            'settings.xendit' => 'Setting Xendit',
            'settings.whatsapp' => 'Setting Template WA',
        ],
    ];

    public function index()
    {
        $totalStaff = User::count();
        $availablePermissions = self::$permissions;
        return view('admin.staff.index', compact('totalStaff', 'availablePermissions'));
    }

    public function staffData(Request $request)
    {
        $query = User::select('users.*');

        return DataTables::of($query)
            ->addColumn('raw_user', function (User $user) {
            return $user->toArray();
        })
            ->toJson();
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:4',
            'role' => 'required|in:admin,teknisi,sales',
            'permissions' => 'nullable|array',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'permissions' => $request->permissions ?? [],
        ]);

        return back()->with('success', 'Staff baru berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:4',
            'role' => 'required|in:admin,teknisi,sales',
            'permissions' => 'nullable|array',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'permissions' => $request->permissions ?? [],
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return back()->with('success', 'Data staff berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return back()->with('success', 'User staff berhasil dihapus.');
    }
}
