<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/payments/xendit/callback', [\App\Http\Controllers\PaymentController::class, 'callback'])->name('xendit.callback');
Route::match(['get', 'post'], '/whatsapp/fonnte/webhook', [\App\Http\Controllers\FonnteWebhookController::class, 'handle'])->name('api.whatsapp.fonnte.webhook');
Route::post('/whatsapp/fonnte/chaining', [\App\Http\Controllers\FonnteWebhookController::class, 'handle'])->name('api.whatsapp.fonnte.chaining');
Route::post('/whatsapp/fonnte/connect', [\App\Http\Controllers\FonnteWebhookController::class, 'connect'])->name('api.whatsapp.fonnte.connect');
Route::post('/whatsapp/fonnte/status', [\App\Http\Controllers\FonnteWebhookController::class, 'status'])->name('api.whatsapp.fonnte.status');
