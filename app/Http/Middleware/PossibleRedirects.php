<?php

namespace App\Http\Middleware;

use App\Models\Semester;
use App\Models\SemesterStatus;

use Closure;
use Illuminate\Http\Request;

class PossibleRedirects
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user=$request->user();
        if (!($request->is('logout') || $request->routeIs('setlocale')) && $user && $user->verified) {
            if (!$request->routeIs('secretariat.status-update.*')
            && $request->user()->isCollegist()
            && !$request->user()->hasActivated()){
                return redirect(route('secretariat.status-update.show'));
            }
            /** Active collegists living in
            *   the dormitory as tenants are not affected as their tenant_until is set automatically until the end of the semester
            */
            if (!$request->is('users/tenant_update/*') && $request->user()->needsUpdateTenantUntil()) {
                return redirect(route('users.tenant-update.show'));
            }
        }
        return $next($request);
    }
}
