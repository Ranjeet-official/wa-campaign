<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;
use App\Models\Client;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::guard('web')->check()) {
            return redirect('/wa/dashboard');
        }

        if (Auth::guard('client')->check()) {
            return redirect('/wa/client/dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $email    = $request->email;
        $password = $request->password;

        // ─── ADMIN CHECK ───
        $admin = Admin::where('email', $email)->first();

        if ($admin && Hash::check($password, $admin->password)) {
            Auth::login($admin);
            session(['role' => 'admin']);
            return redirect('/wa/dashboard');
        }

        // ─── CLIENT CHECK ───
        $client = Client::where('email', $email)->first();

        if ($client && Hash::check($password, $client->password)) {

            // ✅ Inactive check
            if ($client->status === 'inactive') {
                return back()->withErrors([
                    'email' => 'Your account is inactive. Please contact administrator.',
                ])->withInput();
            }

            // ✅ Suspended check
            if ($client->status === 'suspended') {
                return back()->withErrors([
                    'email' => 'Your account has been suspended. Please contact administrator.',
                ])->withInput();
            }

            Auth::guard('client')->login($client);
            session(['role' => 'client']);
            return redirect('/wa/client/dashboard');
        }

        // ─── INVALID CREDENTIALS ───
        return back()->withErrors([
            'email' => 'Invalid email or password.',
        ])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        Auth::guard('client')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/wa/login');
    }
}
