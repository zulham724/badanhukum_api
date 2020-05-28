<?php

namespace App\Http\Middleware;

use Closure;

class HeadMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request request
     * @param \Closure                 $next    next
     * 
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        app('db')->enableQueryLog();

        // Return the response
        return $next($request);
    }
}
