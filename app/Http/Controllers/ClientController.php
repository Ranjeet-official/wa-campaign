<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;

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

    public function store(Request $request)
    {
        $request->validate([
            'name'             => 'required|max:150',
            'email'            => 'required|email|unique:clients,email',
            'phone'            => 'nullable|digits:10',
            'company'          => 'required|max:150',
            'wa_sender_number' => 'required|digits:10',
            'wa_api_key'       => 'nullable',
            'wa_api_url'       => 'nullable|url',
            'address'          => 'nullable',
            'city'             => 'nullable|max:100',
            'state'            => 'nullable|max:100',
            'pincode'          => 'nullable|max:10',
            'status'           => 'required|in:active,inactive,suspended',
        ]);

        $client = Client::create($request->only([
            'name',
            'email',
            'phone',
            'company',
            'wa_sender_number',
            'wa_api_key',
            'wa_api_url',
            'address',
            'city',
            'state',
            'pincode',
            'status',
        ]));

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
            'name'             => 'required|max:150',
            'email'            => 'required|email|unique:clients,email,' . $id,
            'phone'            => 'nullable|digits:10',
            'company'          => 'required|max:150',
            'wa_sender_number' => 'required|digits:10',
            'wa_api_key'       => 'nullable',
            'wa_api_url'       => 'nullable|url',
            'address'          => 'nullable',
            'city'             => 'nullable|max:100',
            'state'            => 'nullable|max:100',
            'pincode'          => 'nullable|max:10',
            'status'           => 'required|in:active,inactive,suspended',
        ]);

        $client->update($request->only([
            'name',
            'email',
            'phone',
            'company',
            'wa_sender_number',
            'wa_api_key',
            'wa_api_url',
            'address',
            'city',
            'state',
            'pincode',
            'status',
        ]));

        return response()->json([
            'status'  => true,
            'message' => 'Client updated successfully!',
            'data'    => $client->fresh(),
        ]);
    }

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
