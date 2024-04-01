<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log as SystemLog;

class LogRequests
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(Request): (Response) $next
     * @throws AuthenticationException
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && $request->isMethod('post')) {
            SystemLog::debug('User #' . user()->id . ' sent request: path='
                . $request->path() . '&'
                . http_build_query($request->input()));
        }

        return $next($request);
    }
}
