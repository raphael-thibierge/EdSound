<?php

namespace App\Http\Middleware;

use Closure;

class VerifyBotMan
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

        if ($request->input('hub_mode') === 'subscribe'
            && $request->input('hub_verify_token') === env('FACEBOOK_VERIFICATION'))
        {
            return response($request->input('hub_challenge'), 200);
        }

        return $next($request);
    }
}
