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
        if (!$user || ($user->role ?? 'customer') !== 'seller') {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Akses ditolak. Hanya untuk penjual.'], 403);
            }
            return redirect('/')->with('error', 'Akses ditolak. Hanya untuk penjual.');
        }

        return $next($request);
    }
}
