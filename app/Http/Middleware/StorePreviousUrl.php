<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StorePreviousUrl
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (
            !$request->expectsJson() && 
            !$request->is('login') && 
            !$request->is('register') && 
            !$request->is('logout') && 
            !$request->is('dashboard') &&
            !$request->is('password/*') &&
            !$request->is('api/*')
        ) {
            session(['previous_url' => url()->full()]);
        }
        return $next($request);
    }
}
