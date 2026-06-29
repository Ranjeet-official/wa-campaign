<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureWhatsappEnabled
{
    public function handle(Request $request, Closure $next)
    {
        $client = Auth::guard('client')->user();

        if (!$client || !$client->whatsapp_enabled) {
            abort(403, 'WhatsApp service is not enabled for your account.');
        }

        return $next($request);
    }
}