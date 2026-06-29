<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class ClientController extends Controller
{
    public function index(Request $request)
    {
        $clients = Client::withCount('campaigns')
            ->latest()
            ->paginate(10);

        return view('admin.clients.index', compact('clients'));
    }

    public function create()
    {
        return view('admin.clients.create');
    }

    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'name'                => 'required|max:150',
    //         'email'               => 'required|email|unique:clients,email',
    //         'phone'               => 'nullable|digits:10',
    //         'company'             => 'required|max:150',
    //         'wa_sender_number'    => 'required|digits:10',
    //         'wa_phone_number_id'  => 'nullable',
    //         'wa_access_token'     => 'nullable',
    //         'wa_waba_id'          => 'nullable',
    //         'address'             => 'nullable',
    //         'city'                => 'nullable|max:100',
    //         'state'               => 'nullable|max:100',
    //         'pincode'             => 'nullable|max:10',
    //         'status'              => 'required|in:active,inactive,suspended',
    //         'password'            => 'required|min:6',
    //     ]);

    //     $client = Client::create([
    //         'name'               => $request->name,
    //         'email'              => $request->email,
    //         'phone'              => $request->phone,
    //         'company'            => $request->company,
    //         'wa_sender_number'   => $request->wa_sender_number,
    //         'wa_phone_number_id' => $request->wa_phone_number_id,
    //         'wa_access_token'    => $request->wa_access_token,
    //         'wa_waba_id'         => $request->wa_waba_id,
    //         'address'            => $request->address,
    //         'city'               => $request->city,
    //         'state'              => $request->state,
    //         'pincode'            => $request->pincode,
    //         'status'             => $request->status,

    //         // HASH PASSWORD
    //         'password' => Hash::make($request->password),
    //     ]);

    //     return response()->json([
    //         'status'  => true,
    //         'message' => 'Client created successfully!',
    //         'data'    => $client,
    //     ]);
    // }





    public function store(Request $request)
    {
        $request->validate([
            'name'                => 'required|max:150',
            'email'               => 'required|email|unique:clients,email',
            'phone'               => 'nullable|digits:10',
            'company'             => 'required|max:150',
            'wa_sender_number'    => 'required|digits:10',
            'wa_phone_number_id'  => 'nullable',
            'wa_access_token'     => 'nullable',
            'wa_waba_id'          => 'nullable',
            'address'             => 'nullable',
            'city'                => 'nullable|max:100',
            'state'               => 'nullable|max:100',
            'pincode'             => 'nullable|max:10',
            'status'              => 'required|in:active,inactive,suspended',
            'password'            => 'required|min:6',
        ]);

        $client = Client::create([
            'name'               => $request->name,
            'email'              => $request->email,
            'phone'              => $request->phone,
            'company'            => $request->company,
            'wa_sender_number'   => $request->wa_sender_number,
            'wa_phone_number_id' => $request->wa_phone_number_id,
            'wa_access_token'    => $request->wa_access_token,
            'wa_waba_id'         => $request->wa_waba_id,
            'address'            => $request->address,
            'city'               => $request->city,
            'state'              => $request->state,
            'pincode'            => $request->pincode,
            'status'             => $request->status,
            'password'           => Hash::make($request->password),

            // ── Services ──
            // 'chatbot_slug'     => Str::slug($request->company) . '-' . time(),
            'chatbot_slug' => Str::slug($request->company) . '-' . Str::random(6),

            'chatbot_enabled'  => $request->boolean('chatbot_enabled'),
            'whatsapp_enabled' => $request->boolean('whatsapp_enabled'),
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Client created successfully!',
            'data'    => $client,
        ]);
    }
    public function edit($id)
    {
        $client = Client::findOrFail($id);
        return view('admin.clients.edit', compact('client'));
    }


    public function update(Request $request, $id)
{
    $client = Client::findOrFail($id);

    $request->validate([
        'name'               => 'required|max:150',
        'email'              => 'required|email|unique:clients,email,' . $id,
        'phone'              => 'nullable|digits:10',
        'company'            => 'required|max:150',
        'wa_sender_number'   => 'required|digits:10',
        'wa_phone_number_id' => 'nullable',
        'wa_access_token'    => 'nullable',
        'wa_waba_id'         => 'nullable',
        'address'            => 'nullable',
        'city'               => 'nullable|max:100',
        'state'              => 'nullable|max:100',
        'pincode'            => 'nullable|max:10',
        'status'             => 'required|in:active,inactive,suspended',
        'password'           => 'nullable|min:6',
    ]);

    $data = $request->only([
        'name',
        'email',
        'phone',
        'company',
        'wa_sender_number',
        'wa_phone_number_id',
        'wa_access_token',
        'wa_waba_id',
        'address',
        'city',
        'state',
        'pincode',
        'status',
    ]);

    // ✅ Checkbox values explicitly handle karo
    $data['whatsapp_enabled'] = $request->boolean('whatsapp_enabled');
    $data['chatbot_enabled']  = $request->boolean('chatbot_enabled');

            if ($data['chatbot_enabled'] && empty($client->chatbot_slug)) {
                do {
                    $slug = Str::slug($request->company) . '-' . Str::random(6);
                } while (Client::where('chatbot_slug', $slug)->exists());

                $data['chatbot_slug'] = $slug;
            }

    if ($request->filled('password')) {
        $data['password'] = Hash::make($request->password);
    }

    $client->update($data);

    return response()->json([
        'status'  => true,
        'message' => 'Client updated successfully!',
        'data'    => $client->fresh(),
    ]);
}

    // public function update(Request $request, $id)
    // {
    //     $client = Client::findOrFail($id);

    //     $request->validate([
    //         'name'               => 'required|max:150',
    //         'email'              => 'required|email|unique:clients,email,' . $id,
    //         'phone'              => 'nullable|digits:10',
    //         'company'            => 'required|max:150',
    //         'wa_sender_number'   => 'required|digits:10',
    //         'wa_phone_number_id' => 'nullable',
    //         'wa_access_token'    => 'nullable',
    //         'wa_waba_id'         => 'nullable',
    //         'address'            => 'nullable',
    //         'city'               => 'nullable|max:100',
    //         'state'              => 'nullable|max:100',
    //         'pincode'            => 'nullable|max:10',
    //         'status'             => 'required|in:active,inactive,suspended',

    //         // optional on update
    //         'password'           => 'nullable|min:6',
    //     ]);

    //     $data = $request->only([
    //         'name',
    //         'email',
    //         'phone',
    //         'company',
    //         'wa_sender_number',
    //         'wa_phone_number_id',
    //         'wa_access_token',
    //         'wa_waba_id',
    //         'address',
    //         'city',
    //         'state',
    //         'pincode',
    //         'status',
    //     ]);

    //     // Update password only if entered
    //     if ($request->filled('password')) {

    //         $data['password'] = Hash::make($request->password);
    //     }

    //     $client->update($data);

    //     return response()->json([
    //         'status'  => true,
    //         'message' => 'Client updated successfully!',
    //         'data'    => $client->fresh(),
    //     ]);
    // }

    public function destroy($id)
    {
        $client = Client::findOrFail($id);

        if ($client->campaigns()->exists()) {
            return response()->json([
                'status'  => false,
                'message' => 'Client is assigned to a campaign. Cannot delete.',
            ], 422);
        }

        $client->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Client deleted successfully!',
        ]);
    }
}
