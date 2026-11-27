<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;

class CountUniqueVisitor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('meu-amigo', 'meu-amigo/*', 'reveal/*', 'participant/status/*')) {
            return $next($request);
        }

        $ip = $request->ip();
        $now = Carbon::now();

        // verifica se o IP já foi contado nas últimas 24h
        $exists = \DB::table('visitors')
            ->where('ip', $ip)
            ->where('visited_at', '>=', $now->copy()->subHours(24))
            ->exists();

        // se não existir, registrar a visita
        if (!$exists) {
            \DB::table('visitors')->insert([
                'ip' => $ip,
                'visited_at' => $now,
            ]);
        }

        return $next($request);
    }
}
