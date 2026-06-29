<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class TemplateController extends Controller
{
    public function index()
    {
        $client = Auth::guard('client')->user();

        $templates = Template::with('client')
            ->where('client_id', $client->id)
            ->latest()
            ->paginate(10);

        return view('admin.templates.index', compact('templates'));
    }

    public function create()
    {
        return view('admin.templates.create');
    }

    public function store(Request $request)
    {
        $client = Auth::guard('client')->user();

        $request->validate([
            'name'     => 'required|regex:/^[a-z0-9_]+$/',
            'category' => 'required|in:MARKETING,UTILITY,AUTHENTICATION',
            'language' => 'required',
            'message'  => 'required|string',
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
                'example' => [
                    'body_text' => [array_map(fn($v) => 'John', $variables)]
                ]
            ];
        } else {
            $components[] = ['type' => 'BODY', 'text' => $metaMessage];
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
                'message' => $response->json('error.error_user_msg')
                    ?? $response->json('error.message')
                    ?? 'Meta API error.',
            ], 422);
        }

        Template::create([
            'client_id'        => $client->id,
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

    public function show($id)
    {
        $client   = Auth::guard('client')->user();
        $template = Template::with('client')
            ->where('client_id', $client->id)
            ->findOrFail($id);

        return view('admin.templates.show', compact('template'));
    }

    public function edit($id)
    {
        $client   = Auth::guard('client')->user();
        $template = Template::where('client_id', $client->id)->findOrFail($id);

        $clients = collect(); // ← empty collection pass karo — blade mein foreach crash nahi karega

        return view('admin.templates.edit', compact('template', 'clients'));
    }
    public function update(Request $request, $id)
    {
        $client   = Auth::guard('client')->user();
        $template = Template::where('client_id', $client->id)->findOrFail($id);

        if (in_array(strtolower($template->status), ['approved', 'active'])) {
            return response()->json([
                'status'  => false,
                'message' => 'Approved templates cannot be edited.',
            ], 422);
        }

        $request->validate([
            'name'     => 'required|regex:/^[a-z0-9_]+$/',
            'category' => 'required|in:MARKETING,UTILITY,AUTHENTICATION',
            'language' => 'required',
            'message'  => 'required|string',
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
                'example' => [
                    'body_text' => [array_map(fn($v) => 'John', $variables)]
                ]
            ];
        } else {
            $components[] = ['type' => 'BODY', 'text' => $metaMessage];
        }

        $newTemplateName = $request->name . '_v' . time();

        $response = Http::withToken(env('WHATSAPP_TOKEN'))
            ->post("https://graph.facebook.com/v25.0/" . env('WABA_ID') . "/message_templates", [
                'name'       => $newTemplateName,
                'category'   => $request->category,
                'language'   => $request->language,
                'components' => $components,
            ]);

        if ($response->failed()) {
            return response()->json([
                'status'  => false,
                'message' => $response->json('error.error_user_msg')
                    ?? $response->json('error.message')
                    ?? 'Meta API error.',
            ], 422);
        }

        if (!empty($template->name)) {
            Http::withToken(env('WHATSAPP_TOKEN'))
                ->delete("https://graph.facebook.com/v25.0/" . env('WABA_ID') . "/message_templates", [
                    'name'     => $template->name,
                    'language' => $template->language,
                ]);
        }

        $template->update([
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
            'message' => 'Template updated and resubmitted to Meta!',
        ]);
    }

    public function destroy($id)
    {
        $client   = Auth::guard('client')->user();
        $template = Template::where('client_id', $client->id)->findOrFail($id);

        $isUsed = Campaign::where('template_id', $template->id)->exists();
        if ($isUsed) {
            return response()->json([
                'status'  => false,
                'message' => 'Template is already used in campaigns.',
            ], 422);
        }

        if ($template->meta_template_id) {
            Http::withToken(env('WHATSAPP_TOKEN'))
                ->delete("https://graph.facebook.com/v25.0/" . env('WABA_ID') . "/message_templates", [
                    'name' => $template->name,
                ]);
        }

        $template->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Template deleted successfully!',
        ]);
    }
}
