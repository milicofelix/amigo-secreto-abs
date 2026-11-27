<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ViewAmigoSecretoGroup
{
    public function handle(Request $request, Closure $next): Response
    {
        $group = $request->route('group');
        $participant = $request->attributes->get('pwa.participant');

        if ($request->user()?->can('manage', $group) || (int) $participant?->group_id === $group->id) {
            return $next($request);
        }

        if (!$request->user() && !$participant && !$request->expectsJson()) {
            return redirect()->guest(route('pwa.participant'));
        }

        abort($request->user() || $participant ? 403 : 401);
    }
}
