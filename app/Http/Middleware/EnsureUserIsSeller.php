<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserIsSeller
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        // Allow both 'seller' and 'admin' to access seller-only routes
        $role = $user->role ?? 'customer';
        if (!$user || !in_array($role, ['seller', 'admin'])) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Akses ditolak. Hanya untuk penjual.'], 403);
            }
            return redirect('/')->with('error', 'Akses ditolak. Hanya untuk penjual.');
        }

        return $next($request);
    }
}
