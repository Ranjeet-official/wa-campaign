<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Template;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $templates = Template::latest()->paginate(10);
        return view('admin.templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $clients = Client::where('status', 'active')->orderBy('name')->get();
        return view('admin.templates.create', compact('clients'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'name'      => 'required|string|max:255',
            'status'    => 'required|in:pending,approved,rejected',
            'message'   => 'required|string',
        ]);

        Template::create($validated);

        return response()->json([
            'status'  => true,
            'message' => 'Template created successfully!',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Template $template)
    {
        return view('admin.templates.show', compact('template'));
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Template $template)
    {
        $clients = Client::where('status', 'active')->orderBy('name')->get();
        return view('admin.templates.edit', compact('template', 'clients'));
    }

    public function update(Request $request, Template $template)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'name'      => 'required|string|max:255',
            'status'    => 'required|in:pending,approved,rejected',
            'message'   => 'required|string',
        ]);

        $template->update($validated);

        return response()->json([
            'status'  => true,
            'message' => 'Template updated successfully!',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Template $template)
    {
        $template->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Template deleted successfully!',
        ]);
    }

    public function getByClient($clientId){
        $templates = Template::where('client_id',$clientId)
        ->where('status','approved')
        ->orderBy('name')
        ->get(['id','name','message']);

        return response()->json([
            'status' => true,
            'templates' => $templates,
        ]);
    }
}
