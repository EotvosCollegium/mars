<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;

class Locale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $locales = array_keys(config('app.locales'));
        App::setLocale($request->cookie('locale', $request->getPreferredLanguage($locales)));
        return $next($request);
    }
}
