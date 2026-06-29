<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Client;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;



class TemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $templates = Template::with('client')->latest()->paginate(10);

        return view('admin.templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new resource.
     */
            public function create()
            {
                $clients = Client::where('status', 'active')
                    ->where('whatsapp_enabled', true)
                    ->orderBy('name')
                    ->get();

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
            'name'      => [
                'required',
                'regex:/^[a-z0-9_]+$/',
                \Illuminate\Validation\Rule::unique('templates', 'name')
                    ->where('client_id', $request->client_id), // ✅ same client check
            ],
            'category'  => 'required|in:MARKETING,UTILITY,AUTHENTICATION',
            'language'  => 'required',
            'message'   => 'required|string',
        ], [
            'name.unique' => 'This template name already exists for this client.',
        ]);

        preg_match_all('/\{(\w+)\}/', $request->message, $matches);
        // $variables = $matches[1] ?? [];
        $variables = array_values(array_unique($matches[1] ?? []));


        $metaMessage = $request->message;
        $index = 1;
        foreach ($variables as $var) {
            $metaMessage = str_replace('{' . $var . '}', '{{' . $index . '}}', $metaMessage);
            $index++;
        }

        $components = [];
        if (!empty($variables)) {
            $components[] = [
                'type'    => 'BODY',
                'text'    => $metaMessage,
                'example' => ['body_text' => [array_map(fn($v) => 'John', $variables)]]
            ];
        } else {
            $components[] = ['type' => 'BODY', 'text' => $metaMessage];
        }

        // ✅ Meta ke liye unique — DB ke liye original
        $metaTemplateName = 'c' . $request->client_id . '_' . $request->name;


        $response = Http::withToken(env('WHATSAPP_TOKEN'))
            ->post("https://graph.facebook.com/v25.0/" . env('WABA_ID') . "/message_templates", [
                'name'       => $metaTemplateName, // ✅ "c2_account_created"
                'category'   => $request->category,
                'language'   => $request->language,
                'components' => $components,
            ]);




        if ($response->failed()) {
            $error = $response->json('error');
            return response()->json([
                'status'  => false,
                'message' => $error['error_user_msg'] ?? $error['error_user_title'] ?? $error['message'] ?? 'Meta API error.',
            ], 422);
        }



        // ✅ DB mein original name — koi column change nahi
        Template::create([
            'client_id'        => $request->client_id,
            'name'             => $request->name,        // "account_created"
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
            $clients = Client::where('status', 'active')
                ->where('whatsapp_enabled', true)
                ->orderBy('name')
                ->get();

            return view('admin.templates.edit', compact('template', 'clients'));
        }


//  public function edit(Template $template)
// {
//     $clients = Client::where('status', 'active')
//         ->where(function ($query) use ($template) {
//             $query->where('whatsapp_enabled', true)
//                   ->orWhere('id', $template->client_id);
//         })
//         ->orderBy('name')
//         ->get();

//     return view('admin.templates.edit', compact('template', 'clients'));
// }

    public function update(Request $request, Template $template)
    {
        // ───────────────── APPROVED BLOCK ─────────────────
        if (in_array(strtolower($template->status), ['approved', 'active'])) {

            return response()->json([
                'status'  => false,
                'message' => 'Approved templates cannot be edited.',
            ], 422);
        }

        // ───────────────── VALIDATION ─────────────────
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'name'      => 'required|regex:/^[a-z0-9_]+$/',
            'category'  => 'required|in:MARKETING,UTILITY,AUTHENTICATION',
            'language'  => 'required',
            'message'   => 'required|string',
        ]);

        // ───────────────── VARIABLES EXTRACT ─────────────────
        preg_match_all('/\{(\w+)\}/', $request->message, $matches);

        // $variables = $matches[1] ?? [];
        $variables = array_values(array_unique($matches[1] ?? []));


        // ───────────────── META FORMAT ─────────────────
        $metaMessage = $request->message;

        $index = 1;

        foreach ($variables as $var) {

            $metaMessage = str_replace(
                '{' . $var . '}',
                '{{' . $index . '}}',
                $metaMessage
            );

            $index++;
        }

        // ───────────────── COMPONENTS ─────────────────
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

        // ───────────────── UNIQUE TEMPLATE NAME ─────────────────
        // Meta same name instantly allow nahi karta
        $newTemplateName = $request->name . '_v' . time();

        // ───────────────── CREATE NEW TEMPLATE ─────────────────
        $response = Http::withToken(env('WHATSAPP_TOKEN'))
            ->post(
                "https://graph.facebook.com/v25.0/" . env('WABA_ID') . "/message_templates",
                [
                    'name'       => $newTemplateName,
                    'category'   => $request->category,
                    'language'   => $request->language,
                    'components' => $components,
                ]
            );

        // ───────────────── META ERROR ─────────────────
        if ($response->failed()) {

            return response()->json([
                'status'  => false,
                'message' =>
                $response->json('error.error_user_msg')
                    ?? $response->json('error.message')
                    ?? 'Meta API error.',
            ], 422);
        }

        // ───────────────── DELETE OLD TEMPLATE (OPTIONAL) ─────────────────
        if (!empty($template->name) && !empty($template->language)) {

            Http::withToken(env('WHATSAPP_TOKEN'))
                ->delete(
                    "https://graph.facebook.com/v25.0/" . env('WABA_ID') . "/message_templates",
                    [
                        'name'     => $template->name,
                        'language' => $template->language,
                    ]
                );
        }

        // ───────────────── UPDATE DATABASE ─────────────────
        $template->update([
            'client_id'        => $request->client_id,
            'name'             => $newTemplateName,
            'category'         => $request->category,
            'language'         => $request->language,
            'message'          => $request->message,
            'variables'        => $variables,
            'meta_template_id' => $response->json('id'),
            'status'           => 'pending',
            'approved_at'      => null,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Template updated and resubmitted to Meta successfully!',
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
