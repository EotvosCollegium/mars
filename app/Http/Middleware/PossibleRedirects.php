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
        if (!$request->is('logout')) {
            if(!$request->routeIs('secretariat.status-update.*')
            && $request->user()
            && $request->user()->isCollegist()
            && !$request->user()->hasActivated()){
                return redirect(route('secretariat.status-update.show'));
            }
            // if(!$request->routeIs('secretariat.tenant-update')
            // && $request->user()
            // && !$request->user()->isCollegist()
            // //&& $request->user()->isCurrentTenant()//TODO: uncomment this line when the tenant update is implemented
            // ){
            //     return redirect(route('secretariat.tenant-update'));
            // }
        }
        return $next($request);
    }
}
