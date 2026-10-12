<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\ChatController;
use App\Http\Controllers\SupportChatController;
use Illuminate\Support\Facades\Route;

$staffDomain   = config('app.staff_domain', 'cm.djavva.net');
$paymentDomain = config('app.payment_domain', 'payment.djavva.net');
$isolatedDomain = config('app.isolated_domain', 'isolir.djavva.net');

Route::domain($paymentDomain)->group(function () {
    Route::get('/', function () {
        return redirect()->route('portal.login');
    });

    Route::get('/pay/{hash}', [\App\Http\Controllers\PaymentController::class, 'showPublicPayment'])->name('payment.public')->middleware('throttle:60,1');
    Route::post('/pay/{hash}/checkout', [\App\Http\Controllers\PaymentController::class, 'checkout'])->name('payment.checkout')->middleware('throttle:10,1');

    Route::get('/login', [\App\Http\Controllers\CustomerPortalController::class, 'login'])->name('portal.login')->middleware('throttle:10,1');
    Route::post('/login', [\App\Http\Controllers\CustomerPortalController::class, 'authenticate'])->name('portal.authenticate')->middleware('throttle:5,1');

    Route::middleware([\App\Http\Middleware\CustomerPortalAuth::class])->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\CustomerPortalController::class, 'index'])->name('portal.dashboard');
        Route::get('/invoice/{invoice}', [\App\Http\Controllers\CustomerPortalController::class, 'showInvoice'])->name('portal.invoice.show');
        Route::get('/invoice/{invoice}/pdf', [\App\Http\Controllers\CustomerPortalController::class, 'downloadPdf'])->name('portal.invoice.pdf');
        Route::post('/support/messages', [SupportChatController::class, 'customerSend'])->name('portal.support.messages')->middleware('throttle:30,1');
        Route::post('/support/{conversation}/read', [SupportChatController::class, 'customerRead'])->name('portal.support.read')->middleware('throttle:60,1');
        Route::post('/support/typing', [SupportChatController::class, 'customerTyping'])->name('portal.support.typing')->middleware('throttle:60,1');
        Route::post('/logout', [\App\Http\Controllers\CustomerPortalController::class, 'logout'])->name('portal.logout');
    });
});

Route::domain($isolatedDomain)->group(function () {
    Route::get('/{any?}', function () {
        return view('portal.isolated');
    })->where('any', '.*')->name('portal.isolated');
});

Route::get('/portal/{any?}', function ($any = null) {
    $subdomain = config('app.payment_domain', 'payment.djavva.net');
    $path = $any ? '/' . $any : '/login';
    if ($any == 'dashboard') $path = '/dashboard';
    return redirect()->away('https://' . $subdomain . $path);
})->where('any', '.*');

Route::get('/pay/{hash}', [\App\Http\Controllers\PaymentController::class, 'showPublicPayment'])->name('payment.public.fallback')->middleware('throttle:60,1');
Route::post('/pay/{hash}/checkout', [\App\Http\Controllers\PaymentController::class, 'checkout'])->name('payment.checkout.fallback')->middleware('throttle:10,1');
Route::get('/support/bootstrap', [SupportChatController::class, 'publicBootstrap'])->name('support.public.bootstrap')->middleware('throttle:60,1');
Route::post('/support/start', [SupportChatController::class, 'publicStart'])->name('support.public.start')->middleware('throttle:5,1');
Route::post('/support/send', [SupportChatController::class, 'publicSend'])->name('support.public.send')->middleware('throttle:30,1');
Route::post('/support/typing', [SupportChatController::class, 'publicTyping'])->name('support.public.typing')->middleware('throttle:60,1');

Route::domain($staffDomain)->group(function () {
    Route::get('/', function () {
        return redirect()->route('admin.login');
    });

    Route::get('/login', [\App\Http\Controllers\Auth\AdminLoginController::class, 'showLoginForm'])->name('admin.login')->middleware('throttle:10,1');
    Route::post('/login', [\App\Http\Controllers\Auth\AdminLoginController::class, 'login'])->name('admin.login.submit')->middleware('throttle:5,1');
    Route::post('/logout', [\App\Http\Controllers\Auth\AdminLoginController::class, 'logout'])->name('admin.logout');

    Route::middleware('auth')->group(function () {
        Route::post('/customers/check-whatsapp', [AdminController::class, 'checkWhatsApp'])->name('admin.customers.check_whatsapp')->middleware('permission:customers.whatsapp');

        Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('admin.dashboard.index');
        Route::middleware('permission:dashboard.view')->group(function() {
            Route::get('/dashboard/mikrotik-data', [\App\Http\Controllers\Admin\DashboardController::class, 'mikrotikData'])->name('admin.dashboard.mikrotik_data');
            Route::get('/dashboard/pdf', [\App\Http\Controllers\Admin\DashboardController::class, 'exportPdf'])->name('admin.dashboard.pdf');
            Route::get('/dashboard/excel', [\App\Http\Controllers\Admin\DashboardController::class, 'exportExcel'])->name('admin.dashboard.excel');
        });

        Route::get('/customers', [AdminController::class, 'customers'])->name('admin.customers')->middleware('permission:customers.view');
        Route::get('/customers/data', [AdminController::class, 'customersData'])->name('admin.customers.data')->middleware('permission:customers.view');
        Route::get('/customers/export/csv', [AdminController::class, 'exportCustomersCsv'])->name('admin.customers.export.csv')->middleware('permission:customers.view');
        Route::get('/customers/export/excel', [AdminController::class, 'exportCustomersExcel'])->name('admin.customers.export.excel')->middleware('permission:customers.view');
        Route::post('/customers/backup-settings', [AdminController::class, 'updateBackupSettings'])->name('admin.customers.backup_settings')->middleware('permission:customers.edit');
        Route::get('/customers/next-id', [AdminController::class, 'getNextId'])->name('admin.customers.next_id')->middleware('permission:customers.view');
        Route::get('/customers/{customer}/invoices', [AdminController::class, 'getCustomerInvoices'])->name('admin.customers.invoices')->middleware('permission:customers.view');
        
        Route::post('/customers', [AdminController::class, 'storeCustomer'])->name('admin.customers.store')->middleware('permission:customers.create');
        Route::get('/customers/{customer}', [AdminController::class, 'showCustomer'])->name('admin.customers.show')->middleware('permission:customers.view');
        Route::put('/customers/{customer}', [AdminController::class, 'updateCustomer'])->name('admin.customers.update')->middleware('permission:customers.edit');
        Route::post('/customers/{customer}/sync', [AdminController::class, 'syncSingleCustomer'])->name('admin.customers.sync')->middleware('permission:customers.sync');
        Route::delete('/customers/{customer}', [AdminController::class, 'destroyCustomer'])->name('admin.customers.destroy')->middleware('permission:customers.delete');
        Route::post('/customers/{customer}/greeting', [AdminController::class, 'sendGreeting'])->name('admin.customers.greeting')->middleware('permission:customers.whatsapp');
        Route::post('/customers/blast-maintenance', [AdminController::class, 'blastMaintenance'])->name('admin.customers.blast_maintenance')->middleware('permission:customers.whatsapp');

        Route::get('/packages', [AdminController::class, 'packages'])->name('admin.packages')->middleware('permission:packages.view');
        Route::middleware('permission:packages.manage')->group(function() {
            Route::post('/packages', [AdminController::class, 'storePackage'])->name('admin.packages.store');
            Route::put('/packages/{package}', [AdminController::class, 'updatePackage'])->name('admin.packages.update');
            Route::delete('/packages/{package}', [AdminController::class, 'destroyPackage'])->name('admin.packages.destroy');
            Route::patch('/packages/{package}/toggle', [AdminController::class, 'togglePackage'])->name('admin.packages.toggle');
        });

        Route::get('/invoices', [\App\Http\Controllers\Admin\InvoiceController::class, 'index'])->name('admin.invoices')->middleware('permission:invoices.view');
        Route::get('/invoices/data', [\App\Http\Controllers\Admin\InvoiceController::class, 'invoicesData'])->name('admin.invoices.data')->middleware('permission:invoices.view');
        Route::get('/invoices/export', [\App\Http\Controllers\Admin\InvoiceController::class, 'exportCsv'])->name('admin.invoices.export')->middleware('permission:invoices.view');
        Route::get('/invoices/{invoice}', [\App\Http\Controllers\Admin\InvoiceController::class, 'show'])->name('admin.invoices.show')->middleware('permission:invoices.view');
        Route::get('/invoices/{invoice}/pdf', [\App\Http\Controllers\Admin\InvoiceController::class, 'downloadPdf'])->name('admin.invoices.pdf')->middleware('permission:invoices.view');

        Route::middleware('permission:chat.manage')->group(function () {
            Route::get('/chat', [ChatController::class, 'index'])->name('admin.chat.index');
            Route::post('/chat/conversations', [ChatController::class, 'storeConversation'])->name('admin.chat.conversations.store');
            Route::post('/chat/mark-all-read', [ChatController::class, 'markAllAsRead'])->name('admin.chat.mark_all_read');
            Route::post('/chat/{conversation}/messages', [ChatController::class, 'storeMessage'])->name('admin.chat.messages.store');
            Route::post('/chat/{conversation}/read', [ChatController::class, 'markAsRead'])->name('admin.chat.read');
            Route::put('/chat/{conversation}', [ChatController::class, 'updateConversation'])->name('admin.chat.update');
            Route::delete('/chat/{conversation}', [ChatController::class, 'destroyConversation'])->name('admin.chat.destroy');
            Route::post('/chat/{conversation}/typing', [ChatController::class, 'typing'])->name('admin.chat.typing');
        });
        
        Route::post('/invoices/{invoice}/whatsapp', [\App\Http\Controllers\Admin\InvoiceController::class, 'sendWhatsApp'])->name('admin.invoices.whatsapp')->middleware('permission:invoices.whatsapp');
        Route::post('/invoices/bulk-whatsapp', [\App\Http\Controllers\Admin\InvoiceController::class, 'sendBulkWhatsApp'])->name('admin.invoices.bulk_whatsapp')->middleware('permission:invoices.whatsapp');
        
        Route::post('/invoices/{invoice}/pay', [\App\Http\Controllers\Admin\InvoiceController::class, 'markAsPaid'])->name('admin.invoices.pay')->middleware('permission:invoices.edit');
        Route::put('/invoices/{invoice}', [\App\Http\Controllers\Admin\InvoiceController::class, 'update'])->name('admin.invoices.update')->middleware('permission:invoices.edit');
        Route::delete('/invoices/{invoice}', [\App\Http\Controllers\Admin\InvoiceController::class, 'destroy'])->name('admin.invoices.destroy')->middleware('permission:invoices.delete');
        Route::post('/invoices/generate', [\App\Http\Controllers\Admin\InvoiceController::class, 'generateInvoices'])->name('admin.invoices.generate')->middleware('permission:invoices.generate');

        Route::group(['prefix' => 'settings'], function () {
            Route::middleware('permission:settings.mikrotik')->group(function() {
                Route::get('/mikrotik', [\App\Http\Controllers\Admin\ApiServerController::class, 'index'])->name('admin.settings.mikrotik');
                Route::post('/mikrotik/settings', [\App\Http\Controllers\Admin\ApiServerController::class, 'saveSettings'])->name('admin.settings.mikrotik.save');
                Route::post('/mikrotik/test', [\App\Http\Controllers\Admin\ApiServerController::class, 'testConnection'])->name('admin.settings.mikrotik.test');
                Route::post('/mikrotik/sync', [\App\Http\Controllers\Admin\ApiServerController::class, 'syncToMikrotik'])->name('admin.settings.mikrotik.sync');
                Route::post('/mikrotik/toggle-user', [\App\Http\Controllers\Admin\ApiServerController::class, 'toggleUser'])->name('admin.settings.mikrotik.toggle');
                Route::post('/mikrotik/disconnect-user', [\App\Http\Controllers\Admin\ApiServerController::class, 'disconnectUser'])->name('admin.settings.mikrotik.disconnect');
                Route::post('/mikrotik/isolir-user', [\App\Http\Controllers\Admin\ApiServerController::class, 'isolirUser'])->name('admin.settings.mikrotik.isolir');
                Route::post('/mikrotik/restore-user', [\App\Http\Controllers\Admin\ApiServerController::class, 'restoreUser'])->name('admin.settings.mikrotik.restore');
                Route::get('/mikrotik/data', [\App\Http\Controllers\Admin\ApiServerController::class, 'mikrotikData'])->name('admin.settings.mikrotik.data');
            });
            
            Route::post('/mikrotik/check-id', [\App\Http\Controllers\AdminController::class, 'checkCustomerCode'])->name('admin.customers.check_id')->middleware('permission:customers.create');
            Route::get('/mikrotik/profiles', [\App\Http\Controllers\Admin\ApiServerController::class, 'getProfiles'])->name('admin.settings.mikrotik.profiles');
            Route::get('/mikrotik/secrets', [\App\Http\Controllers\Admin\ApiServerController::class, 'listSecrets'])->name('admin.settings.mikrotik.secrets')->middleware('permission:settings.mikrotik');

            Route::middleware('permission:settings.xendit')->group(function() {
                Route::get('/xendit', [\App\Http\Controllers\Admin\XenditSettingController::class, 'index'])->name('admin.settings.xendit');
                Route::post('/xendit', [\App\Http\Controllers\Admin\XenditSettingController::class, 'saveSettings'])->name('admin.settings.xendit.save');
            });

            Route::middleware('permission:settings.whatsapp')->group(function() {
                Route::get('/whatsapp-template', [\App\Http\Controllers\Admin\WhatsAppTemplateController::class, 'index'])->name('admin.settings.whatsapp_template');
                Route::post('/whatsapp-template', [\App\Http\Controllers\Admin\WhatsAppTemplateController::class, 'save'])->name('admin.settings.whatsapp_template.save');
            });

            Route::middleware('permission:staff.manage')->group(function() {
                Route::get('/staff', [\App\Http\Controllers\Admin\StaffController::class, 'index'])->name('admin.staff.index');
                Route::get('/staff/data', [\App\Http\Controllers\Admin\StaffController::class, 'staffData'])->name('admin.staff.data');
                Route::post('/staff', [\App\Http\Controllers\Admin\StaffController::class, 'store'])->name('admin.staff.store');
                Route::put('/staff/{user}', [\App\Http\Controllers\Admin\StaffController::class, 'update'])->name('admin.staff.update');
                Route::delete('/staff/{user}', [\App\Http\Controllers\Admin\StaffController::class, 'destroy'])->name('admin.staff.destroy');
            });
        });
    });
});


Route::get('/', function () {
    $packages = \Illuminate\Support\Facades\Cache::rememberForever('active_packages', function () {
        return \App\Models\Package::where('is_active', true)->orderBy('sort_order')->orderBy('monthly_price')->get();
    });

    return view('welcome', compact('packages'));
});
