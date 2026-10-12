<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('invoices:generate-monthly')->monthlyOn(1, '00:01');
        $schedule->command('invoices:check-overdue')->dailyAt('01:00');
        $schedule->command('customers:backup')->dailyAt('01:30')->withoutOverlapping();
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectTo(
            guests: '/login',
            users: '/dashboard'
        );

        $middleware->validateCsrfTokens(except: [
            'api/payments/xendit/callback',
            'api/whatsapp/fonnte/*',
        ]);

        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminOnly::class,
            'permission' => \App\Http\Middleware\CheckPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
