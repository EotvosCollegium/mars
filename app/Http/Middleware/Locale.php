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
        $appLocales = array_keys(config('app.locales'));
        $acceptedByUser = $request->getLanguages();
        $default = config('app.locale');

        /* If the user speaks the site's main language, we should always select that, even if others have a higher priority. */
        $selected = in_array($default, $acceptedByUser) ? $default : $request->getPreferredLanguage($appLocales);
        App::setLocale($request->cookie('locale', $selected));
        return $next($request);
    }
}
