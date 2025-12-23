<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user || ($user->role ?? 'customer') !== 'admin') {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Akses ditolak. Hanya untuk admin.'], 403);
            }
            return redirect('/')->with('error', 'Akses ditolak. Hanya untuk admin.');
        }

        return $next($request);
    }
}
