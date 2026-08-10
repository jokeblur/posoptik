<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $role)
    {
        if (!Auth::check()) {
            abort(403, 'Unauthorized action.');
        }

        $normalizedUserRole = strtolower(trim((string) $request->user()->role));
        $normalizedRequiredRole = strtolower(trim((string) $role));

        if ($normalizedUserRole !== $normalizedRequiredRole) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
