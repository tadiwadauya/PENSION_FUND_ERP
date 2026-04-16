<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HrAccessMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            abort(403, 'Unauthorised access.');
        }

        $user = auth()->user();

        if (!$user->is_admin && !$user->is_hr) {
            abort(403, 'Only HR or Admin can access this page.');
        }

        return $next($request);
    }
}