<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StaffMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $role = auth()->user()?->role;
        if (! auth()->check() || ($role !== 'staff' && $role !== 'admin')) {
            abort(403);
        }

        return $next($request);
    }
}
