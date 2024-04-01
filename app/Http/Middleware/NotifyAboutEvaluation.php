<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Secretariat\SemesterEvaluationController;
use App\Models\Semester;
use Closure;
use Illuminate\Http\Request;

class NotifyAboutEvaluation
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse) $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (
            $user
            && $user->verified
            && $request->method() == 'GET'
            && !$request->routeIs('secretariat.evaluation.*')
            && $user->isCollegist(alumni: false)
            && $user->getStatus(Semester::next()) == null
            && SemesterEvaluationController::isEvaluationAvailable()
        ) {
            $request->session()->flash('message', 'Töltsd ki a szemeszter végi kérdőívet a profilod alatt!');
        }

        return $next($request);
    }
}
