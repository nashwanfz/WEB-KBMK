<?php

namespace App\Http\Middleware;

use App\Models\API\Division; // Tambahkan ini
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleAdminOrCoordinator
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $user = auth()->user();

        if (in_array($user->role, ['admin', 'superadmin'])) {
            return $next($request);
        }

        $mediaDivision = Division::where('nama', 'Media')->first();

        if ($user->role === 'koor_divisi' && $user->division_id === $mediaDivision?->id) {
            return $next($request);
        }

        return response()->json(['message' => 'Anda tidak memiliki izin akses.'], 403);
    }
}