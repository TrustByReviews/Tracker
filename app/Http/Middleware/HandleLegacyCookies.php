<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class HandleLegacyCookies
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar si hay cookies de remember me legacy
        $rememberCookie = $request->cookie('remember_web_59ba36addc2b2f9401580f014c7f58ea4e30989d');
        
        if ($rememberCookie) {
            try {
                // Intentar decodificar el cookie
                $decoded = json_decode(base64_decode($rememberCookie), true);
                
                // Si el cookie contiene un ID numérico (legacy), eliminarlo
                if (isset($decoded['value']) && is_numeric($decoded['value'])) {
                    // Eliminar el cookie legacy
                    Cookie::queue(
                        Cookie::forget('remember_web_59ba36addc2b2f9401580f014c7f58ea4e30989d')
                    );
                    
                    // También eliminar otros cookies de sesión que puedan estar causando problemas
                    Cookie::queue(
                        Cookie::forget('laravel_session')
                    );
                    
                    Cookie::queue(
                        Cookie::forget('XSRF-TOKEN')
                    );
                }
            } catch (\Exception $e) {
                // Si hay error al decodificar, eliminar el cookie de todas formas
                Cookie::queue(
                    Cookie::forget('remember_web_59ba36addc2b2f9401580f014c7f58ea4e30989d')
                );
            }
        }
        
        return $next($request);
    }
} 