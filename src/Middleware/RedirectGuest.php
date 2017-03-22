<?php

namespace VivienLN\Pilot\Middleware;

use Closure;
use VivienLN\Pilot\PilotRole;

class RedirectGuest
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
        if($request->user() && PilotRole::contains($request->user())){
            return $next($request);
        }

        return redirect(sprintf('%s/login', config('pilot.prefix')));
    }
}
