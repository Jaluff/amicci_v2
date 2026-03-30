<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetCompanySession
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // Si ya hay empresa en sesión, verificar que el usuario aún pertenece a ella
        if ($request->session()->has('company_id')) {
            $companyId = $request->session()->get('company_id');
            if ($user->companies()->whereKey($companyId)->exists()) {
                return $next($request);
            }
            // Ya no pertenece a esa empresa, limpiar sesión
            $request->session()->forget('company_id');
        }

        $companies = $user->companies;

        if ($companies->isEmpty()) {
            // Usuario sin empresa asignada
            abort(403, 'No tenés ninguna empresa asignada. Contactá al administrador.');
        }

        if ($companies->count() === 1) {
            // Solo una empresa, setearla automáticamente
            $request->session()->put('company_id', $companies->first()->id);
            return $next($request);
        }

        // Múltiples empresas: redirigir al selector si no está yendo ahí ya
        if (!$request->routeIs('company.select') && !$request->routeIs('company.switch')) {
            return redirect()->route('company.select');
        }

        return $next($request);
    }
}