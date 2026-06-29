<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ClientSettingsController extends Controller
{
    public function index()
    {
        return view('admin.settings');
    }

    public function updateProfile(Request $request)
    {
        try {
            $client = Auth::guard('client')->user();

            if (!$client) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Client not authenticated.',
                ], 401);
            }

            $request->validate([
                'name'            => 'required|string|max:255',
                'email'           => 'required|email|max:255|unique:clients,email,' . $client->id,
                'whatsapp_number' => 'nullable|string|max:20',
            ]);

            $client->update([
                'name'            => $request->name,
                'email'           => $request->email,
                'whatsapp_number' => $request->whatsapp_number,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Profile updated successfully.',
                'data'    => [
                    'id'              => $client->id,
                    'name'            => $client->fresh()->name,
                    'email'           => $client->fresh()->email,
                    'whatsapp_number' => $client->fresh()->whatsapp_number,
                ]
            ], 200);
        } catch (\Exception $e) {

            \Log::error('Client Profile Update Error', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function updatePassword(Request $request)
    {
        $client = Auth::guard('client')->user();

        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|min:8|confirmed',
        ]);

        if (!Hash::check($request->current_password, $client->password)) {
            return response()->json([
                'status'  => false,
                'message' => 'Current password is incorrect.',
                'errors'  => ['current_password' => ['Current password is incorrect.']],
            ], 422);
        }

        $client->update(['password' => Hash::make($request->password)]);

        return response()->json([
            'status'  => true,
            'message' => 'Password updated successfully!',
        ]);
    }
}
