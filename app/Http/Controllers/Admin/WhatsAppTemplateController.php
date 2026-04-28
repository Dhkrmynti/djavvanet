<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class WhatsAppTemplateController extends Controller
{
    private function defaultTemplate($key = 'wa_invoice_template'): string
    {
        if ($key === 'wa_greeting_template') {
            return "*SELAMAT DATANG DI DJAVVA NETWORK*\n\n" .
                "Halo *{customer_name}*,\n\n" .
                "Terima kasih telah memilih DJAVVA NETWORK sebagai layanan internet Anda. Berikut adalah detail akun Anda:\n" .
                "ID Pelanggan: *{customer_code}*\n" .
                "Username: *{username}*\n" .
                "Password: *{password}*\n\n" .
                "Simpan data ini dengan baik. Jika ada kendala, hubungi kami di nomor ini.";
        }

        if ($key === 'wa_maintenance_template') {
            return "*INFORMASI PEMELIHARAAN DJAVVA NETWORK*\n\n" .
                "Pelanggan yang terhormat,\n\n" .
                "Kami informasikan bahwa akan dilakukan pemeliharaan rutin pada jaringan kami untuk meningkatkan kualitas layanan. Selama proses ini berlangsung, kemungkinan akan terjadi gangguan sementara.\n\n" .
                "Mohon maaf atas ketidaknyamanan Anda.\n\n" .
                "Salam,\n" .
                "DJAVVA NETWORK";
        }

        return "*DJAVVA NETWORK*\n\n" .
            "Yth. Pelanggan / Dear Customer\n" .
            "*{customer_name}* (ID: *{customer_code}*)\n\n" .
            "Tagihan Anda periode *{period}* sebesar *Rp{total_amount}* mohon lakukan pembayaran sebelum *{due_date}* agar terhindar dari pemutusan internet atau isolir.\n\n" .
            "_Your internet bill for the period of *{period}* is *Rp{total_amount}*. Please make your payment before *{due_date}* to avoid internet disconnection or isolation._\n\n" .
            "Link pembayaran / Payment link:\n" .
            "{payment_link}\n\n" .
            "Terima Kasih / Thank You\n\n" .
            "Layanan Pelanggan / Customer Service\n" .
            "WhatsApp chat: 6282246468335\n" .
            "Email: djavvanetwork@gmail.com";
    }

    public function index()
    {
        $invoiceTemplate = AppSetting::get('wa_invoice_template', $this->defaultTemplate('wa_invoice_template'));
        $greetingTemplate = AppSetting::get('wa_greeting_template', $this->defaultTemplate('wa_greeting_template'));
        $maintenanceTemplate = AppSetting::get('wa_maintenance_template', $this->defaultTemplate('wa_maintenance_template'));
        
        $imagePath = AppSetting::get('wa_maintenance_image', '');
        $maintenanceImage = '';
        if ($imagePath) {
            // Jika isinya adalah URL (migrasi lama), pakai langsung. Jika path, generate URL.
            $maintenanceImage = filter_var($imagePath, FILTER_VALIDATE_URL) ? $imagePath : \Illuminate\Support\Facades\Storage::disk('public')->url($imagePath);
        }

        return view('admin.settings.whatsapp_template', compact('invoiceTemplate', 'greetingTemplate', 'maintenanceTemplate', 'maintenanceImage'));
    }

    public function save(Request $request)
    {
        $request->validate([
            'invoice_template' => 'nullable|string|max:8000',
            'greeting_template' => 'nullable|string|max:8000',
            'maintenance_template' => 'nullable|string|max:8000',
            'maintenance_image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->filled('invoice_template')) {
            AppSetting::updateOrCreate(['key' => 'wa_invoice_template'], ['value' => $request->invoice_template, 'label' => 'Template WhatsApp Tagihan', 'group' => 'whatsapp']);
            Cache::forget('app_setting_wa_invoice_template');
        }

        if ($request->filled('greeting_template')) {
            AppSetting::updateOrCreate(['key' => 'wa_greeting_template'], ['value' => $request->greeting_template, 'label' => 'Template WhatsApp Greeting', 'group' => 'whatsapp']);
            Cache::forget('app_setting_wa_greeting_template');
        }

        if ($request->filled('maintenance_template')) {
            AppSetting::updateOrCreate(['key' => 'wa_maintenance_template'], ['value' => $request->maintenance_template, 'label' => 'Template WhatsApp Maintenance', 'group' => 'whatsapp']);
            Cache::forget('app_setting_wa_maintenance_template');
        }

        if ($request->hasFile('maintenance_image_file')) {
            $file = $request->file('maintenance_image_file');
            $filename = 'maintenance_' . time() . '.' . $file->getClientOriginalExtension();
            
            // Hapus gambar lama jika ada untuk menghemat storage
            $oldPath = AppSetting::get('wa_maintenance_image');
            if ($oldPath && !\Illuminate\Support\Facades\Storage::disk('public')->exists($oldPath)) {
                // Jika isinya URL (migrasi lama), jangan dihapus via Storage.
                // Tapi jika itu file path, kita hapus.
                if (!filter_var($oldPath, FILTER_VALIDATE_URL)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
                }
            }

            // Simpan ke storage/app/public/uploads/whatsapp
            $path = $file->storeAs('uploads/whatsapp', $filename, 'public');
            
            // Simpan PATH saja ke database agar fleksibel ganti domain/env
            AppSetting::updateOrCreate(['key' => 'wa_maintenance_image'], ['value' => $path, 'label' => 'Gambar WhatsApp Maintenance', 'group' => 'whatsapp']);
            Cache::forget('app_setting_wa_maintenance_image');
        }

        return back()->with('success', 'Template dan pengaturan WhatsApp berhasil diperbarui.');
    }
}
