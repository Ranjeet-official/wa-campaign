<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::first();
        return view('admin.settings', compact('settings'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();


        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:100'],
            'email'            => ['required', 'email', 'max:150', 'unique:users,email,' . $user->id],
            'whatsapp_number'  => ['nullable', 'string', 'max:20'],
        ]);

        $user->update($validated);

        return redirect()->route('settings.index')->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'password'         => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => 'Current password is incorrect.',
            ]);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('settings.index')->with('success', 'Password updated successfully.');
    }

    public function updateApp(Request $request)
    {
        $request->validate([
            'site_name' => ['required', 'string', 'max:15'],

            'site_icon' => [
                'required',
                'string',
                'max:50',
                'regex:/^bi bi-[a-z0-9\-]+$/'
            ],
        ], [
            'site_name.required' => 'Site name is required.',
            'site_name.max'      => 'Site name must not exceed 15 characters.',

            'site_icon.required' => 'Site icon is required.',
            'site_icon.regex'    => 'Enter a valid Bootstrap icon (e.g. bi bi-whatsapp).',
        ]);

        $settings = Setting::first();

        if (!$settings) {
            $settings = Setting::create([]);
        }

        $settings->update([
            'site_name' => $request->site_name,
            'site_icon' => $request->site_icon,
        ]);

        return redirect()->route('settings.index')
            ->with('success', 'App settings updated successfully.');
    }
}
