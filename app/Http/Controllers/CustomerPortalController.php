<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CustomerPortalController extends Controller
{
    public function login()
    {
        if (auth()->guard('customer')->check()) {
            return redirect()->route('portal.dashboard');
        }
        return view('portal.login');
    }

    public function authenticate(Request $request)
    {
        $request->validate([
            'login' => 'required|string|max:255',
        ]);

        $customer = Customer::where('username', $request->login)
            ->orWhere('customer_code', $request->login)
            ->first();

        if (!$customer) {
            return back()
                ->withErrors(['login' => 'ID pelanggan atau username tidak ditemukan.'])
                ->onlyInput('login');
        }

        auth()->guard('customer')->login($customer);
        $request->session()->regenerate();

        return redirect()->route('portal.dashboard');
    }

    public function index()
    {
        $customer = auth()->guard('customer')->user();
        
        $invoices = Invoice::where('customer_id', $customer->id)
            ->where('period_start', '>=', Carbon::now()->subMonths(6))
            ->orderBy('period_start', 'desc')
            ->get();

        $supportConversation = ChatConversation::with([
                'messages' => fn ($query) => $query->oldest(),
                'messages.senderAdmin',
                'messages.senderCustomer',
            ])
            ->firstOrCreate(
                [
                    'customer_id' => $customer->id,
                    'source_type' => 'customer',
                    'status' => 'open',
                ],
                [
                    'subject' => 'Customer support portal',
                ]
            );

        $supportConversation->messages()
            ->where('sender_type', 'admin')
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        if ($supportConversation->unread_for_customer_count > 0) {
            $supportConversation->forceFill([
                'unread_for_customer_count' => 0,
            ])->save();
        }

        return view('portal.dashboard', compact('customer', 'invoices', 'supportConversation'));
    }

    public function showInvoice(Invoice $invoice)
    {
        if ($invoice->customer_id != auth()->guard('customer')->id()) {
            abort(403);
        }

        $invoice->load(['customer', 'customer.package']);
        return view('portal.invoice.show', compact('invoice'));
    }

    public function downloadPdf(Invoice $invoice)
    {
        if ($invoice->customer_id != auth()->guard('customer')->id()) {
            abort(403);
        }

        $invoice->load(['customer', 'customer.package']);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('common.invoice-pdf', compact('invoice'));
        
        $filename = str_replace(['/', '\\'], '-', "Invoice_{$invoice->invoice_number}.pdf");
        return $pdf->download($filename);
    }

    public function logout(Request $request)
    {
        auth()->guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('portal.login')->with('success', 'Anda telah keluar dari portal.');
    }
}
