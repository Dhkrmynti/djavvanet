<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomerPortalAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->guard('customer')->check()) {
            return redirect()->route('portal.login')->with('error', 'Silakan masuk menggunakan Kode Pelanggan Anda.');
        }

        return $next($request);
    }
}
