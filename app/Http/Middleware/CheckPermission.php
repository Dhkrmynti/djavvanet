<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (auth()->check() && auth()->user()->hasPermission($permission)) {
            return $next($request);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['ok' => false, 'message' => 'Anda tidak memiliki hak akses untuk tindakan ini.'], 403);
        }

        return redirect()->route('admin.dashboard.index')->with('error', 'Anda tidak memiliki akses ke fitur ini.');
    }
}
