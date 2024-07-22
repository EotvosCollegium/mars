<?php

namespace App\Http\Middleware;

use App\Models\Role;
use Closure;
use Illuminate\Support\Facades\Redirect;

class EnsureVerified
{
    /**
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!$request->user()) {
            abort(403);
        }
        if (!$request->user()->verified) {
            if ($request->user()->hasRole(Role::TENANT)) {
                return Redirect::route('verification');
            } else {
                return Redirect::route('application');
            }
        }

        return $next($request);
    }
}
