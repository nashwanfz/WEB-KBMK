<?php

namespace App\Http\Middleware;

use App\Models\API\Division;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsKoorMedia
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $mediaDivision = Division::where('nama', 'Media')->first();

        if (Auth::check() && Auth::user()->role === 'koor_divisi' && Auth::user()->division_id === $mediaDivision?->id) {
            return $next($request);
        }

        return response()->json(['message' => 'Anda tidak memiliki izin akses sebagai Koordinator Media.'], 403);
    }
}
