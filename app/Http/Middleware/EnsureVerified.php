<?php

namespace App\Http\Middleware;

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
        if (!$request->user()?->verified) {
            if ($request->user()?->isCollegist()) {
                //if an applicant
                return Redirect::route('application');
            }
            return Redirect::route('verification');
        }

        return $next($request);
    }
}
