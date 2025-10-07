<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Usage: ->middleware('role:client') ili 'role:provider'
     *        ->middleware('role:client,provider')  // bilo koja od navedenih
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // ako nema specificiranih rola, propuštamo  
        if (empty($roles)) {
            return $next($request);
        }

        if (!in_array($user->role, $roles, true)) {
            return response()->json(['message' => 'Forbidden (role)'], 403);
        }

        return $next($request);
    }
}
