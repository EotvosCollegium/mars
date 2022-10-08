<?php

namespace App\Http\Middleware;

use App\Models\Semester;
use App\Models\SemesterStatus;

use App\Http\Controllers\Secretariat\SemesterController;

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
        // Enable the user to logout, change language and check if the users exists and is verified
        if (!($request->is('logout') || $request->routeIs('setlocale')) && $user && $user->verified) {
            // Redirect the user if they are a collegist and their semester is not active.
            // The user is not redirected if they are already on the page to change their semester.
            if (!$request->routeIs('secretariat.status-update.*')
            && $user->isCollegist()
            && !$user->hasActivated()) {
                return redirect(route('secretariat.status-update.show'));
            }
            /**
             * Redirects teants to update their tenant_until property if it is in the past.
             * This way we can distinguish between the active and inactive tenants. 
             * Active collegists living in the dormitory as tenants are not affected
             *    as their tenant_until is set automatically until the end of the semester
             * The user is not redirected if they are already on the page to update the tenant_until.
            */
            if (!$request->is('users/tenant_update/*') && $user->needsUpdateTenantUntil()) {
                return redirect(route('users.tenant-update.show'));
            }
        }
        return $next($request);
    }
}
