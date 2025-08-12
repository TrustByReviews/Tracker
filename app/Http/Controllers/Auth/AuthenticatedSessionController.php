<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Show the login page.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Evitar redirigir a endpoints API como URL pretendida tras el login
        $intended = $request->session()->pull('url.intended');
        if ($intended) {
            $path = parse_url($intended, PHP_URL_PATH) ?? '';
            if (function_exists('str_starts_with') ? str_starts_with($path, '/api/') : substr($path, 0, 5) === '/api/') {
                return redirect()->route('dashboard');
            }
            // Si la intended es la página de login u otra no deseada, también forzar dashboard
            if ($path === '/login') {
                return redirect()->route('dashboard');
            }
            // Restaurar intended para que redirect()->intended funcione si no es API
            $request->session()->put('url.intended', $intended);
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
