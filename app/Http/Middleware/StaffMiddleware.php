<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StaffMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $role = auth()->user()?->role;
        if (! auth()->check() || ($role !== 'staff' && $role !== 'admin')) {
            abort(403, 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้ (เฉพาะพนักงานและผู้ดูแลระบบ)');
        }

        return $next($request);
    }
}
