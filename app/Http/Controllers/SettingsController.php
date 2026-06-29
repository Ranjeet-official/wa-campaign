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
            'name'            => ['required', 'string', 'max:100'],
            'email'           => ['required', 'email', 'max:150', 'unique:users,email,' . $user->id],
            'whatsapp_number' => ['nullable', 'regex:/^[0-9]+$/', 'digits_between:10,15'],
        ]);

        $user->update($validated);

        return response()->json([
            'status'  => true,
            'message' => 'Profile updated successfully.',
        ]);
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => ['required'],
            'password'         => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status'  => false,
                'message' => 'Current password is incorrect.',
                'errors'  => ['current_password' => ['Current password is incorrect.']],
            ], 422);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return response()->json([
            'status'  => true,
            'message' => 'Password updated successfully.',
        ]);
    }

    public function updateApp(Request $request)
    {
        $request->validate([
            'site_name' => ['required', 'string', 'max:15'],
            'site_icon' => ['required', 'string', 'max:50', 'regex:/^bi bi-[a-z0-9\-]+$/'],
        ], [
            'site_name.required' => 'Site name is required.',
            'site_name.max'      => 'Site name must not exceed 15 characters.',
            'site_icon.required' => 'Site icon is required.',
            'site_icon.regex'    => 'Enter a valid Bootstrap icon (e.g. bi bi-whatsapp).',
        ]);

        $settings = Setting::first() ?? Setting::create([]);

        $settings->update([
            'site_name' => $request->site_name,
            'site_icon' => $request->site_icon,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'App settings updated successfully.',
        ]);
    }
}
