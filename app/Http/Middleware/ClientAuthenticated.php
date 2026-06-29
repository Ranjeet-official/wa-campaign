<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ClientAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        // ─── NOT LOGGED IN ───
        if (!Auth::guard('client')->check()) {
            return redirect('/wa/login')->withErrors([
                'email' => 'Please login to continue.',
            ]);
        }

        $client = Auth::guard('client')->user();

        // ─── INACTIVE ───
        if ($client->status === 'inactive') {
            Auth::guard('client')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/wa/login')->withErrors([
                'email' => 'Your account is inactive. Please contact administrator.',
            ]);
        }

        // ─── SUSPENDED ───
        if ($client->status === 'suspended') {
            Auth::guard('client')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/wa/login')->withErrors([
                'email' => 'Your account has been suspended. Please contact administrator.',
            ]);
        }

        return $next($request);
    }
}
