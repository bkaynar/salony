<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        // Check if user has admin role using Spatie Permission
        if (!$request->user()->hasRole('admin')) {
            abort(403, 'Unauthorized access');
        }

        return $next($request);
    }
}
