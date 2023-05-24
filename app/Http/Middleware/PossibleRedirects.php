<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Secretariat\SemesterController;
use App\Http\Controllers\Secretariat\SemesterEvaluationController;
use App\Models\Semester;
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
        if (!($request->is('logout') || $request->routeIs('setlocale')) && $user && $user->verified && $request->method() == 'GET') {

            // Show a message if the semester evaluation can be filled out.
            if (!$request->routeIs('secretariat.evaluation.*')
            && $user->isCollegist()
            && !$user->getStatusFor(Semester::next())
            && SemesterEvaluationController::isEvaluationAvailable()) {
               $request->session()->now('message', 'Töltsd ki a szemeszter végi kérdőívet a profilod alatt!');
            }
            /**
             * Redirects teants to update their tenant_until property if it is in the past.
             * This way we can distinguish between the active and inactive tenants.
             * The user is not redirected if they are already on the page to update the tenant_until.
            */
            if(!$request->is('users/tenant_update/*')
            && $user->needsUpdateTenantUntil()) {
                return redirect(route('users.tenant-update.show'));
            }
        }
        return $next($request);
    }
}
