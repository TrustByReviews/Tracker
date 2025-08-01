<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * TEMPORARILY DISABLED - Login as User functionality
 * Uncomment to reactivate the middleware
 */
class CheckUserPermissions
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $requiredRole = null): Response
    {
        $user = Auth::user();

        if (!$user) {
            abort(401, 'Unauthorized');
        }

        // TEMPORARILY DISABLED - Login as User functionality
        // Verificar si el usuario está en una sesión de impersonación
        // $adminOriginalUserId = $request->session()->get('admin_original_user_id');
        
        // if ($adminOriginalUserId) {
        //     // Si está impersonando, verificar que el usuario original sea admin
        //     $originalAdmin = \App\Models\User::find($adminOriginalUserId);
        //     if (!$originalAdmin || !$originalAdmin->hasRole('admin')) {
        //         // Limpiar sesión corrupta
        //         $request->session()->forget('admin_original_user_id');
        //         abort(403, 'Invalid impersonation session');
        //     }
        // }

        // Verificar rol requerido si se especifica
        if ($requiredRole && !$user->hasRole($requiredRole)) {
            abort(403, 'Insufficient permissions');
        }

        return $next($request);
    }
} 