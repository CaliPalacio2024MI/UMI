<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
       // 1. Aceptar los roles permitidos como argumentos variables
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // 2. Obtener el nombre del rol activo de la sesión
        $activeRoleName = $request->session()->get('active_role_name');
        
        // --- FILTRADO DE SEGURIDAD BASADO EN EL CONTEXTO ---

        // A. Verificar que el usuario tenga un rol activo en la sesión
        if (!$activeRoleName) {
            // Si el usuario está autenticado pero no tiene contexto, forzar la inicialización
            return redirect()->route('context.set'); 
        }

        $activeRoleName = strtoupper($activeRoleName);
        $roles = array_map('strtoupper', $roles);

        if (!in_array($activeRoleName, $roles)) {
            abort(403, 'Acceso denegado. Tu rol activo (' . $activeRoleName . ') no tiene permisos para esta sección.');
        }


        // Esto reemplaza la lógica antigua de $user->hasAnyRole($roles)
        return $next($request);
    }
}

