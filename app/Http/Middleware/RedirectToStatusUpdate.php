<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectToStatusUpdate
{
    /**
     * Redirects tenants to update their tenant_until property if it is in the past.
     * Also appears to users with no status whatsoever (neither collegist nor tenant).
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse) $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Ignore non-GET requests
        if ($request->method() != 'GET') {
            return $next($request);
        }
        // Ignore logout, language select routes, or if already updating tenant information
        if ($request->routeIs('setlocale', 'logout', 'users.tenant-update.show')) {
            return $next($request);
        }
        // Ignore non-verified users
        $user = $request->user();
        if (!($user && $user->verified)) {
            return $next($request);
        }
        // Ignore if update is not necessary
        if (!$user->needsUpdateTenantUntil()) {
            return $next($request);
        }
        // Ignore those having an application in progress
        if ($user->application) {
            return $next($request);
        }
        return redirect(route('users.tenant-update.show'));
    }
}
