<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectClientToDashboard
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        
        // Verificar si el usuario tiene rol de cliente
        if ($user->roles()->where('value', 'client')->exists()) {
            // Si está intentando acceder al dashboard principal, redirigir al dashboard de cliente
            if ($request->routeIs('dashboard')) {
                return redirect()->route('client.dashboard');
            }
            
            // Si está intentando acceder a rutas principales que no son de cliente, redirigir al dashboard de cliente
            // Pero permitir rutas específicas como logout, profile, etc.
            if ($request->is('/') || 
                $request->routeIs('projects.index') || 
                $request->routeIs('tasks.index') || 
                $request->routeIs('sprints.index') ||
                $request->routeIs('bugs.index') ||
                $request->routeIs('reports.*') ||
                $request->routeIs('payments.*') ||
                $request->routeIs('users.*') ||
                $request->routeIs('permissions.*')) {
                return redirect()->route('client.dashboard');
            }
        }

        return $next($request);
    }
}
