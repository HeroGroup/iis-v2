<?php

namespace App\Http\Middleware;

use Closure;

class SessionAuth
{
    public function handle($request, Closure $next)
    {
        if(session('logged_in'))
            return $next($request);
        else
            return redirect(route('site.getLogin'));
    }
}
