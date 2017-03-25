<?php

namespace App\Http\Middleware;

use Closure;

class AjaxMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (app()->environment() != 'local' && !$request->ajax()) {
            return response('Not Allowed.', 405);
        }

        return $next($request);
    }
}
