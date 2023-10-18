<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TeamsPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(!empty(auth()->user())){
            setPermissionsTeamId(auth()->user()->company_id);
        }
        // if(!empty(auth('api')->user())){
        //     setPermissionsTeamId(auth('api')->user()->getTeamIdFromToken());
        // }

        return $next($request);
    }
}
