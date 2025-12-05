<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleAdminOrCoordinator
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            $userRole = auth()->user()->role;

            // Izinkan akses jika role-nya superadmin, admin, atau koor_divisi
            if (in_array($userRole, ['superadmin', 'admin', 'koor_divisi'])) {
                return $next($request);
            }
        }

        // Jika tidak memiliki izin, tampilkan error 403
        abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
    }
}