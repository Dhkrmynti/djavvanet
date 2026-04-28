<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->role === 'admin') {
            return $next($request);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['ok' => false, 'message' => 'Hanya Admin yang dapat melakukan aksi ini.'], 403);
        }

        return back()->with('error', 'Hanya Admin yang dapat melakukan aksi ini.');
    }
}
