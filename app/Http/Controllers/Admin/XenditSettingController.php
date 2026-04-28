<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\XenditSetting;
use App\Models\XenditFee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class XenditSettingController extends Controller
{
    public function index()
    {
        $settings = XenditSetting::first();
        $fees = XenditFee::all();
        return view('admin.settings.xendit', compact('settings', 'fees'));
    }

    public function saveSettings(Request $request)
    {
        try {
            $validated = $request->validate([
                'api_key'        => 'required|string',
                'callback_token' => 'nullable|string',
                'mode'           => 'required|in:sandbox,production',
                'admin_fee'      => 'nullable|numeric|min:0',
                'fees'           => 'nullable|array',
                'fees.*.name'    => 'required|string|max:255',
                'fees.*.type'    => 'required|in:fixed,percent',
                'fees.*.value'   => 'required|numeric|min:0',
                'fees.*.active'  => 'nullable',
            ]);

            DB::beginTransaction();

            $settings = XenditSetting::first();
            $data = [
                'api_key'        => $request->api_key,
                'callback_token' => $request->callback_token,
                'mode'           => $request->mode,
                'admin_fee'      => $request->input('admin_fee', 0),
                'is_active'      => $request->has('is_active'),
            ];

            if ($settings) {
                $settings->update($data);
            } else {
                XenditSetting::create($data);
            }

            if ($request->has('fees')) {
                foreach ($request->fees as $id => $feeData) {
                    $fee = XenditFee::find($id);
                    if ($fee) {
                        $fee->update([
                            'name' => $feeData['name'],
                            'type' => $feeData['type'],
                            'value' => $feeData['value'],
                            'is_active' => isset($feeData['active']),
                        ]);
                    }
                }
            }

            DB::commit();
            return back()->with('success', 'Konfigurasi Xendit dan Biaya Admin berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal simpan setting Xendit: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }
}
