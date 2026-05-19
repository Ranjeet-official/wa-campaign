<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Client;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;


class TemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $templates = Template::with('client')->latest()->paginate(10);

        foreach ($templates as $template) {
            if ($template->meta_template_id) {
                $response = Http::withToken(env('WHATSAPP_TOKEN'))
                    ->get("https://graph.facebook.com/v25.0/{$template->meta_template_id}");

                if ($response->successful()) {
                    $metaStatus = strtolower($response->json('status'));

                    $status = match ($metaStatus) {
                        'approved' => 'approved',
                        'rejected' => 'rejected',
                        default    => 'pending',
                    };

                    if ($template->status !== $status) {
                        $template->update(['status' => $status]);
                    }
                }
            }
        }

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
    // public function store(Request $request)
    // {
    //     $validated = $request->validate([
    //         'client_id' => 'required|exists:clients,id',
    //         'name'      => 'required|string|max:255',
    //         'status'    => 'required|in:pending,approved,rejected',
    //         'message'   => 'required|string',
    //     ]);

    //     Template::create($validated);

    //     return response()->json([
    //         'status'  => true,
    //         'message' => 'Template created successfully!',
    //     ]);
    // }


    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'name'      => 'required|regex:/^[a-z0-9_]+$/',
            'category'  => 'required|in:MARKETING,UTILITY,AUTHENTICATION',
            'language'  => 'required',
            'message'   => 'required|string',
        ]);

        // {name} extract karo
        preg_match_all('/\{(\w+)\}/', $request->message, $matches);
        $variables = $matches[1] ?? [];

        // {name} → {{1}} Meta format me convert karo
        $metaMessage = $request->message;
        $index = 1;
        foreach ($variables as $var) {
            $metaMessage = str_replace('{' . $var . '}', '{{' . $index . '}}', $metaMessage);
            $index++;
        }
        // Meta API call
        $components = [];

        if (!empty($variables)) {
            $components[] = [
                'type'    => 'BODY',
                'text'    => $metaMessage,
                'example' => [
                    'body_text' => [
                        array_map(fn($v) => 'John', $variables)
                    ]
                ]
            ];
        } else {
            $components[] = [
                'type' => 'BODY',
                'text' => $metaMessage,
            ];
        }

        $response = Http::withToken(env('WHATSAPP_TOKEN'))
            ->post("https://graph.facebook.com/v25.0/" . env('WABA_ID') . "/message_templates", [
                'name'       => $request->name,
                'category'   => $request->category,
                'language'   => $request->language,
                'components' => $components,
            ]);

        if ($response->failed()) {
            return response()->json([
                'status'  => false,
                'message' => $response->json('error.error_user_msg') ?? $response->json('error.message') ?? 'Meta API error.',
            ], 422);
        }

        Template::create([
            'client_id'        => $request->client_id,
            'name'             => $request->name,
            'category'         => $request->category,
            'language'         => $request->language,
            'message'          => $request->message,
            'variables'        => $variables,
            'meta_template_id' => $response->json('id'),
            'status'           => 'pending',
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Template submitted to Meta for approval!',
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
            // 'status'    => 'required|in:pending,approved,rejected',
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
        $isUsed = Campaign::where('template_id', $template->id)->exists();

        if ($isUsed) {
            return response()->json([
                'status'  => false,
                'message' => 'Template is already used in campaigns.',
            ], 422);
        }

        if ($template->meta_template_id) {

            Http::withToken(env('WHATSAPP_TOKEN'))
                ->delete(
                    'https://graph.facebook.com/v25.0/' .
                        env('WABA_ID') .
                        '/message_templates',
                    ['name' => $template->name]
                );
        }

        $template->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Template deleted successfully!',
        ]);
    }
    public function getByClient($clientId)
    {
        $templates = Template::where('client_id', $clientId)
            ->where('status', 'approved')
            ->orderBy('name')
            ->get(['id', 'name', 'message']);

        return response()->json([
            'status' => true,
            'templates' => $templates,
        ]);
    }
}
