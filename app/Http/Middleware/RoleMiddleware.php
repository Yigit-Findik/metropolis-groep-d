<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Accepts one or more allowed roles as arguments, e.g.:
     *   ->middleware('role:admin')
     *   ->middleware('role:admin,planner')
     *
     * If the user is not authenticated, has no role assigned, or their role
     * is not in the allowed list, the request is blocked with a 403.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // Block if not logged in
        if (!$user) {
            abort(403, 'Access denied.');
        }

        // Block if the user has no role assigned yet
        if (!$user->role) {
            abort(403, 'Access denied.');
        }

        // Block if the user's role is not in the list of allowed roles for this route
        if (!in_array($user->role->name, $roles)) {
            abort(403, 'Access denied.');
        }

        // User is authenticated and has an allowed role — let the request through
        return $next($request);
    }
}
