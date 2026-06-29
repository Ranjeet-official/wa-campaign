<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Client;
use App\Models\CampaignContact;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CampaignExport;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CampaignController extends Controller
{


    // public function index(Request $request)
    // {
    //     $query = Campaign::with('client')->withCount('contacts');

    //     if ($request->filled('client_id')) {
    //         $query->where('client_id', $request->client_id);
    //         $client = Client::find($request->client_id);
    //     }

    //     $campaigns = $query->latest()->paginate(10);
    //     $client    = $client ?? null;

    //     return view('admin.campaigns.index', compact('campaigns', 'client'));
    // }


    public function index(Request $request)
    {
        $client = null; // ✅ Pehle hi null set karo

        $query = Campaign::with('client')
            ->withCount([
                'contacts',
                'contacts as failed_contacts_count' => fn($q) => $q->where('status', 'failed'),
            ]);

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
            $client = Client::find($request->client_id);
        }

        $campaigns = $query->latest()->paginate(10);

        return view('admin.campaigns.index', compact('campaigns', 'client'));
    }

    // public function create(Request $request)
    // {
    //     $clients = Client::where('status', 'active')->get();
    //     $client_id = $request->client_id;
    //     return view('admin.campaigns.create', compact('clients', 'client_id'));
    // }

         public function create(Request $request)
        {
            $clients = Client::where('status', 'active')
                ->where('whatsapp_enabled', true)
                ->orderBy('name')
                ->get();

            $client_id = $request->client_id;

            return view('admin.campaigns.create', compact('clients', 'client_id'));
        }


    public function store(Request $request)
    {
        // ✅ PHP level pe file too large check
        if ($request->server('CONTENT_LENGTH') > 0 && empty($_FILES) && empty($_POST)) {
            return response()->json([
                'success' => false,
                'message' => 'File too large. Maximum allowed size is 20MB.',
            ], 422);
        }

        $request->validate([
            'client_id'   => 'required|exists:clients,id',
            'name'        => 'required|string|max:255',
            'message'     => 'nullable|string|required_without:media_file',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'contacts'    => 'nullable',
            'media_file'  => 'nullable|file|max:20480|required_without:message',
            'template_id' => 'nullable|exists:templates,id',
        ], [
            'name.required'               => 'Campaign name is required.',
            'message.required_without'    => 'Message is required when no media file is uploaded.',
            'media_file.required_without' => 'Media file is required when message is empty.',
            'start_date.required'         => 'Start date is required.',
            'end_date.required'           => 'End date is required.',
            'end_date.after_or_equal'     => 'End date must be on or after start date.',
            'client_id.required'          => 'Please select a client.',
            'client_id.exists'            => 'Selected client is invalid.',
            'media_file.max'              => 'File size must not exceed 20MB.',
        ]);

        $contacts = $request->contacts;

        if (is_string($contacts)) {
            $contacts = json_decode($contacts, true);
        }

        $mediaPath         = null;
        $mediaOriginalName = null;

        if ($request->hasFile('media_file')) {
            $file              = $request->file('media_file');
            $mediaOriginalName = $file->getClientOriginalName();
            $mediaPath         = $file->store('campaigns/media', 'public');
        }

        $campaign = Campaign::create([
            'client_id'          => $request->client_id,
            'name'               => $request->name,
            'message'            => $request->message,
            'media_file'         => $mediaPath,
            'media_original_name' => $mediaOriginalName,
            'start_date'         => $request->start_date,
            'end_date'           => $request->end_date,
            'status'             => 'draft',
            'total_contacts'     => !empty($contacts) ? count($contacts) : 0,
            'template_id'        => $request->template_id,
        ]);

        if (!empty($contacts)) {
            $insert = [];

            foreach ($contacts as $row) {
                if (!empty($row['name']) && !empty($row['phone'])) {
                    $insert[] = [
                        'campaign_id' => $campaign->id,
                        'name'        => $row['name'],
                        'phone'       => $row['phone'],
                        'status'      => 'pending',
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ];
                }
            }

            if (!empty($insert)) {
                CampaignContact::insert($insert);
            }
        }

        return response()->json([
            'success'  => true,
            'message'  => 'Campaign created successfully!',
            'redirect' => route(
                'campaigns.index',
                $request->filled('client_filter')
                    ? ['client_id' => $request->client_filter]
                    : []
            )
        ]);
    }
    // SHOW
    public function show(Request $request, $id)
    {
        $campaign = Campaign::with(['client', 'contacts'])->findOrFail($id);
        $client_id = $request->client_id;
        return view('admin.campaigns.show', compact('campaign', 'client_id'));
    }

                // EDIT
        public function edit(Request $request, $id)
{
    $campaign = Campaign::findOrFail($id);

    $clients = Client::where('status', 'active')
        ->where('whatsapp_enabled', true)
        ->orderBy('name')
        ->get();

    $client_id = $request->client_id;

    return view('admin.campaigns.edit', compact('campaign', 'clients', 'client_id'));
}

    // UPDATE
    // public function update(Request $request, $id)
    // {
    //     $campaign = Campaign::findOrFail($id);


    //     $request->validate([

    //         'client_id' => 'required|exists:clients,id',

    //         'name' => 'required|string|max:255',

    //         'message' => [
    //             'nullable',
    //             'string',
    //             Rule::requiredIf(
    //                 !$campaign->media_file && !$request->hasFile('media_file')
    //             ),
    //         ],

    //         'media_file' => [
    //             'nullable',
    //             'file',
    //             'max:20480',
    //             Rule::requiredIf(
    //                 empty($request->message) && !$campaign->media_file
    //             ),
    //         ],

    //         'start_date' => 'required|date',

    //         'end_date' => 'required|date|after_or_equal:start_date',

    //         'contacts' => 'nullable',

    //     ], [

    //         'name.required' =>
    //         'Campaign name is required.',

    //         'message.required' =>
    //         'Message is required when no media file is uploaded.',

    //         'media_file.required' =>
    //         'Media file is required when message is empty.',

    //         'start_date.required' =>
    //         'Start date is required.',

    //         'end_date.required' =>
    //         'End date is required.',

    //         'end_date.after_or_equal' =>
    //         'End date must be on or after start date.',

    //         'client_id.required' =>
    //         'Please select a client.',

    //         'client_id.exists' =>
    //         'Selected client is invalid.',

    //         'media_file.max' =>
    //         'File size must not exceed 20MB.',

    //     ]);

    //     $mediaPath = $campaign->media_file;

    //     if ($request->hasFile('media_file')) {

    //         if ($campaign->media_file && Storage::disk('public')->exists($campaign->media_file)) {
    //             Storage::disk('public')->delete($campaign->media_file);
    //         }

    //         $mediaPath = $request->file('media_file')
    //             ->store('campaigns/media', 'public');
    //     }

    //     $campaign->update([
    //         'client_id'  => $request->client_id,
    //         'name'       => $request->name,
    //         'message'    => $request->message,
    //         'media_file' => $mediaPath,
    //         'start_date' => $request->start_date,
    //         'end_date'   => $request->end_date,
    //     ]);

    //     CampaignContact::where('campaign_id', $id)->delete();

    //     $contacts = $request->contacts;

    //     if (is_string($contacts)) {
    //         $contacts = json_decode($contacts, true);
    //     }

    //     if (!empty($contacts)) {
    //         $insert = [];

    //         foreach ($contacts as $row) {
    //             if (!empty($row['name']) && !empty($row['phone'])) {
    //                 $insert[] = [
    //                     'campaign_id' => $id,
    //                     'name'        => $row['name'],
    //                     'phone'       => $row['phone'],
    //                     'created_at'  => now(),
    //                     'updated_at'  => now(),
    //                 ];
    //             }
    //         }

    //         CampaignContact::insert($insert);
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Campaign updated successfully!',
    //         'redirect' => route('campaigns.index', [
    //             'client_id' => $request->client_id
    //         ])
    //     ]);
    // }



    public function update(Request $request, $id)
    {
        if ($request->server('CONTENT_LENGTH') > 0 && empty($_FILES) && empty($_POST)) {
            return response()->json([
                'success' => false,
                'message' => 'File too large. Maximum allowed size is 20MB.',
            ], 422);
        }

        $campaign = Campaign::findOrFail($id);

        $request->validate([
            'client_id'   => 'required|exists:clients,id',
            'name'        => 'required|string|max:255',
            'template_id' => 'nullable|exists:templates,id',
            'message'     => [
                'nullable',
                'string',
                Rule::requiredIf(
                    (!$campaign->media_file || $request->remove_media == 1) &&
                        !$request->hasFile('media_file')
                ),
            ],
            'media_file'  => [
                'nullable',
                'file',
                'max:20480',
                Rule::requiredIf(
                    empty($request->message) &&
                        (!$campaign->media_file || $request->remove_media == 1)
                ),
            ],
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'contacts'    => 'nullable',
        ], [
            'name.required'           => 'Campaign name is required.',
            'message.required'        => 'Message is required when no media file is uploaded.',
            'media_file.required'     => 'Media file is required when message is empty.',
            'start_date.required'     => 'Start date is required.',
            'end_date.required'       => 'End date is required.',
            'end_date.after_or_equal' => 'End date must be on or after start date.',
            'client_id.required'      => 'Please select a client.',
            'client_id.exists'        => 'Selected client is invalid.',
            'media_file.max'          => 'File size must not exceed 20MB.',
        ]);

        // ─── Existing values ───
        $mediaPath         = $campaign->media_file;
        $mediaOriginalName = $campaign->media_original_name; // ✅ existing keep

        // ─── Remove existing file ───
        if ($request->remove_media == 1) {
            if ($campaign->media_file && Storage::disk('public')->exists($campaign->media_file)) {
                Storage::disk('public')->delete($campaign->media_file);
            }
            $mediaPath         = null;
            $mediaOriginalName = null; // ✅
        }

        // ─── Upload new file ───
        if ($request->hasFile('media_file')) {
            if ($campaign->media_file && Storage::disk('public')->exists($campaign->media_file)) {
                Storage::disk('public')->delete($campaign->media_file);
            }
            $file              = $request->file('media_file');
            $mediaOriginalName = $file->getClientOriginalName(); // ✅ original naam
            $mediaPath         = $file->store('campaigns/media', 'public');
        }

        // ─── Contacts ───
        $contacts = $request->contacts;
        if (is_string($contacts)) {
            $contacts = json_decode($contacts, true);
        }

        // ─── Update campaign ───
        $campaign->update([
            'client_id'           => $request->client_id,
            'name'                => $request->name,
            'template_id'         => $request->template_id,
            'message'             => $request->message,
            'media_file'          => $mediaPath,
            'media_original_name' => $mediaOriginalName, // ✅
            'start_date'          => $request->start_date,
            'end_date'            => $request->end_date,
            'status'              => 'draft',
            'total_contacts'      => !empty($contacts) ? count($contacts) : 0,
            'sent_count'          => 0,
        ]);

        // ─── Contacts update ───
        CampaignContact::where('campaign_id', $id)->delete();

        if (!empty($contacts)) {
            $insert = [];
            foreach ($contacts as $row) {
                if (!empty($row['name']) && !empty($row['phone'])) {
                    $insert[] = [
                        'campaign_id' => $id,
                        'name'        => $row['name'],
                        'phone'       => $row['phone'],
                        'status'      => 'pending',
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ];
                }
            }
            CampaignContact::insert($insert);
        }

        return response()->json([
            'success'  => true,
            'message'  => 'Campaign updated successfully!',
            'redirect' => route(
                'campaigns.index',
                $request->filled('client_filter')
                    ? ['client_id' => $request->client_filter]
                    : []
            )
        ]);
    }
    public function destroy(Request $request, $id)
    {
        CampaignContact::where('campaign_id', $id)->delete();

        Campaign::findOrFail($id)->delete();

        return redirect()->route('campaigns.index', [
            'client_id' => $request->client_id,
            'success'   => 'Campaign deleted successfully!'
        ]);
    }

    public function export($id)
    {
        return Excel::download(new CampaignExport($id), 'campaign.xlsx');
    }



    // it my code working

    // public function sendCampaign(Request $request, $id)
    // {
    //     $campaign = Campaign::with('contacts')->findOrFail($id);
    //     if ($campaign->status == 'completed') {

    //         return response()->json([
    //             'status'            => false,
    //             'message'           => 'Campaign already completed.',
    //             'campaign_status'   => $campaign->status,
    //         ]);
    //     }

    //     // ───────────────── Sheet Upload Contacts ─────────────────
    //     if ($request->hasFile('sheet')) {

    //         $request->validate([
    //             'sheet' => 'required|file|mimes:xlsx,xls,csv'
    //         ]);

    //         $file = $request->file('sheet');

    //         $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load(
    //             $file->getPathname()
    //         );

    //         $rows = $spreadsheet->getActiveSheet()->toArray();

    //         // First row = header
    //         $header = array_map(
    //             'strtolower',
    //             array_map('trim', array_shift($rows))
    //         );

    //         $nameCol  = array_search('name', $header);
    //         $phoneCol = array_search('phone', $header);

    //         if ($phoneCol === false) {

    //             return response()->json([
    //                 'status'  => false,
    //                 'message' => 'Phone column not found in sheet.'
    //             ]);
    //         }

    //         $contacts = collect($rows)
    //             ->filter(fn($row) => !empty($row[$phoneCol]))
    //             ->map(fn($row) => (object) [
    //                 'name'  => $nameCol !== false
    //                     ? ($row[$nameCol] ?? '')
    //                     : '',
    //                 'phone' => $row[$phoneCol],
    //             ]);
    //     } else {

    //         // DB contacts
    //         $contacts = $campaign->contacts;
    //     }

    //     // ───────────────── Testing Mode ─────────────────
    //     if ($request->boolean('testing')) {
    //         $contacts = $contacts->take(2);
    //     }

    //     if ($contacts->isEmpty()) {

    //         return response()->json([
    //             'status'  => false,
    //             'message' => 'No contacts found.'
    //         ]);
    //     }

    //     $sent   = 0;
    //     $failed = 0;

    //     $campaign->update(['status' => 'running']);

    //     foreach ($contacts as $contact) {

    //         try {

    //             // ───────────── Phone Format ─────────────
    //             $phone = preg_replace('/\D/', '', $contact->phone);

    //             // India format
    //             if (strlen($phone) == 10) {
    //                 $phone = '91' . $phone;
    //             }

    //             // ───────────── Message Replace ─────────────
    //             $message = str_replace(
    //                 '{name}',
    //                 $contact->name,
    //                 $campaign->message
    //             );

    //             // ───────────────── TEMPLATE MESSAGE ─────────────────
    //             // $payload = [
    //             //     'messaging_product' => 'whatsapp',
    //             //     'to'                => $phone,
    //             //     'type'              => 'template',
    //             //     'template'          => [
    //             //         'name'     => 'hello_world',
    //             //         'language' => [
    //             //             'code' => 'en_US'
    //             //         ]
    //             //     ]
    //             // ];

    //             // ───────────────── TEXT MESSAGE ─────────────────

    //             $payload = [
    //                 'messaging_product' => 'whatsapp',
    //                 'to'                => $phone,
    //                 'type'              => 'text',
    //                 'text'              => [
    //                     'body' => $message
    //                 ]
    //             ];


    //             // ───────────────── MEDIA MESSAGE ─────────────────

    //             // if ($campaign->media_file) {

    //             //     $mediaUrl = asset('storage/' . $campaign->media_file);

    //             //     $extension = strtolower(
    //             //         pathinfo($campaign->media_file, PATHINFO_EXTENSION)
    //             //     );

    //             //     $mediaType = match (true) {

    //             //         in_array($extension, ['jpg', 'jpeg', 'png', 'webp'])
    //             //         => 'image',

    //             //         in_array($extension, ['mp4', '3gp'])
    //             //         => 'video',

    //             //         in_array($extension, ['mp3', 'ogg', 'aac'])
    //             //         => 'audio',

    //             //         default
    //             //         => 'document',
    //             //     };

    //             //     $payload = [
    //             //         'messaging_product' => 'whatsapp',
    //             //         'to'                => $phone,
    //             //         'type'              => $mediaType,
    //             //         $mediaType          => [
    //             //             'link' => $mediaUrl,
    //             //         ],
    //             //     ];

    //             //     // caption only for image/video/document
    //             //     if ($mediaType !== 'audio') {
    //             //         $payload[$mediaType]['caption'] = $message;
    //             //     }
    //             // }


    //             // ───────────────── API Request ─────────────────
    //             $response = Http::withToken(env('WHATSAPP_TOKEN'))
    //                 ->post(
    //                     'https://graph.facebook.com/v25.0/' .
    //                         env('PHONE_NUMBER_ID') .
    //                         '/messages',
    //                     $payload
    //                 );

    //             $result = $response->json();

    //             if ($response->successful() && !empty($result['messages'])) {

    //                 $sent++;

    //                 CampaignContact::where('campaign_id', $campaign->id)
    //                     ->where('phone', $contact->phone)
    //                     ->update([
    //                         'status' => 'sent',
    //                     ]);
    //             } else {

    //                 $failed++;

    //                 CampaignContact::where('campaign_id', $campaign->id)
    //                     ->where('phone', $contact->phone)
    //                     ->update([
    //                         'status'   => 'failed',
    //                     ]);

    //                 \Log::error('WhatsApp Failed', [
    //                     'phone'    => $phone,
    //                     'response' => $result
    //                 ]);
    //             }

    //             sleep(1);
    //         } catch (\Exception $e) {

    //             $failed++;

    //             \Log::error('Campaign Error', [
    //                 'phone' => $contact->phone,
    //                 'error' => $e->getMessage()
    //             ]);
    //         }
    //     }
    //     // $status = 'draft'; // default

    //     if ($sent == 0 && $failed > 0) {

    //         $status = 'failed';
    //     } elseif ($sent > 0 && $failed == 0) {

    //         $status = 'completed';
    //     } elseif ($sent > 0 && $failed > 0) {

    //         $status = 'partial';
    //     }
    //     $campaign->update([
    //         'status'         => $status,
    //         'total_contacts' => $contacts->count(),  // ← Add karo
    //         'sent_count'     => $sent,               // ← Add karo
    //     ]);

    //     return response()->json([
    //         'status'  => true,
    //         'message' => "✅ Sent: {$sent} | ❌ Failed: {$failed}",
    //         'campaign_status' => $status,
    //     ]);
    // }



    public function sendCampaign(Request $request, $id)
    {
        $campaign = Campaign::with(['contacts', 'client'])->findOrFail($id);

        if ($campaign->status == 'completed') {
            return response()->json([
                'status'  => false,
                'message' => 'Campaign already completed.',
            ]);
        }

        if (in_array($campaign->client->status, ['inactive', 'suspended'])) {
            return response()->json([
                'status'  => false,
                'message' => 'This client is ' . $campaign->client->status . '. Campaign cannot be run.',
            ]);
        }

        if (now()->toDateString() > $campaign->end_date) {
            return response()->json([
                'status'  => false,
                'message' => 'Campaign end date has passed. Cannot run this campaign.',
            ]);
        }

        // ───────────────── Sheet Upload Contacts ─────────────────
        if ($request->hasFile('sheet')) {

            $request->validate([
                'sheet' => 'required|file|mimes:xlsx,xls,csv'
            ]);

            $file        = $request->file('sheet');
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
            $rows        = $spreadsheet->getActiveSheet()->toArray();

            $header   = array_map('strtolower', array_map('trim', array_shift($rows)));
            $nameCol  = array_search('name', $header);
            $phoneCol = array_search('phone', $header);

            if ($phoneCol === false) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Phone column not found in sheet.'
                ]);
            }

            $contacts = collect($rows)
                ->filter(fn($row) => !empty($row[$phoneCol]))
                ->map(fn($row) => (object) [
                    'name'  => $nameCol !== false ? ($row[$nameCol] ?? '') : '',
                    'phone' => $row[$phoneCol],
                ]);
        } else {
            // ✅ pending aur failed dono ko lo, sent ko skip karo
            $contacts = CampaignContact::where('campaign_id', $campaign->id)
                ->whereIn('status', ['pending', 'failed'])
                ->get();
        }

        // ───────────────── Testing Mode ─────────────────
        if ($request->boolean('testing')) {
            $contacts = $contacts->take(2);
        }

        if ($contacts->isEmpty()) {
            return response()->json([
                'status'  => false,
                'message' => 'No contacts found.',
            ]);
        }

        $sent   = 0;
        $failed = 0;

        $campaign->update(['status' => 'running']);

        // ───────────────── LOOP CONTACTS ─────────────────
        foreach ($contacts as $contact) {

            try {

                // ── Phone Format ──
                $phone = preg_replace('/\D/', '', $contact->phone);
                if (strlen($phone) == 10) {
                    $phone = '91' . $phone;
                }

                // ── Message Replace ──
                $message = str_replace('{name}', $contact->name ?? '', $campaign->message ?? '');

                // ── Default text payload ──
                $textPayload = [
                    'messaging_product' => 'whatsapp',
                    'to'                => $phone,
                    'type'              => 'text',
                    'text'              => ['body' => $message],
                ];

                // ───────────────── MEDIA MESSAGE ─────────────────
                if ($campaign->media_file) {

                    $filePath = storage_path('app/public/' . $campaign->media_file);

                    if (file_exists($filePath)) {

                        // Upload to Meta
                        $uploadResponse = Http::withToken($campaign->client->wa_access_token)
                            ->attach('file', file_get_contents($filePath), basename($filePath))
                            ->post(
                                'https://graph.facebook.com/v25.0/' . $campaign->client->wa_phone_number_id . '/media',
                                ['messaging_product' => 'whatsapp']
                            );

                        $uploadResult = $uploadResponse->json();

                        if (!empty($uploadResult['id'])) {

                            $mediaId   = $uploadResult['id'];
                            $extension = strtolower(pathinfo($campaign->media_file, PATHINFO_EXTENSION));

                            $mediaType = match (true) {
                                in_array($extension, ['jpg', 'jpeg', 'png', 'webp']) => 'image',
                                in_array($extension, ['mp4', '3gp', 'mov'])          => 'video',
                                in_array($extension, ['mp3', 'ogg', 'aac', 'm4a'])   => 'audio',
                                default                                               => 'document',
                            };

                            $mediaPayload = [
                                'messaging_product' => 'whatsapp',
                                'to'                => $phone,
                                'type'              => $mediaType,
                                $mediaType          => ['id' => $mediaId],
                            ];

                            // Caption for image/video
                            if (in_array($mediaType, ['image', 'video']) && !empty($message)) {
                                $mediaPayload[$mediaType]['caption'] = $message;
                            }

                            // Filename for document
                            if ($mediaType === 'document') {
                                $mediaPayload['document']['filename'] = $campaign->media_original_name ?? basename($campaign->media_file);
                            }

                            // Send media
                            $response = Http::withToken($campaign->client->wa_access_token)
                                ->post(
                                    'https://graph.facebook.com/v25.0/' . $campaign->client->wa_phone_number_id . '/messages',
                                    $mediaPayload
                                );

                            // Document/Audio → text alag se bhejo
                            if (
                                in_array($mediaType, ['document', 'audio']) &&
                                !empty($message) &&
                                $response->successful()
                            ) {
                                Http::withToken($campaign->client->wa_access_token)
                                    ->post(
                                        'https://graph.facebook.com/v25.0/' . $campaign->client->wa_phone_number_id . '/messages',
                                        $textPayload
                                    );
                            }
                        } else {
                            // Upload failed → fallback text
                            $response = Http::withToken($campaign->client->wa_access_token)
                                ->post(
                                    'https://graph.facebook.com/v25.0/' . $campaign->client->wa_phone_number_id . '/messages',
                                    $textPayload
                                );
                            \Log::error('Media Upload Failed', ['response' => $uploadResult]);
                        }
                    } else {
                        // File missing → fallback text
                        $response = Http::withToken($campaign->client->wa_access_token)
                            ->post(
                                'https://graph.facebook.com/v25.0/' . $campaign->client->wa_phone_number_id . '/messages',
                                $textPayload
                            );
                        \Log::error('Media File Not Found', ['path' => $filePath]);
                    }
                } else {
                    // ───────────────── TEXT ONLY ─────────────────
                    $response = Http::withToken($campaign->client->wa_access_token)
                        ->post(
                            'https://graph.facebook.com/v25.0/' . $campaign->client->wa_phone_number_id . '/messages',
                            $textPayload
                        );
                }

                // ───────────────── STATUS CHECK ─────────────────
                if ($response->successful() && !empty($response->json('messages'))) {

                    $sent++;

                    CampaignContact::where('campaign_id', $campaign->id)
                        ->where('phone', $contact->phone)
                        ->update(['status' => 'sent']);
                } else {

                    $failed++;

                    CampaignContact::where('campaign_id', $campaign->id)
                        ->where('phone', $contact->phone)
                        ->update(['status' => 'failed']);

                    \Log::error('WhatsApp Failed', [
                        'phone'    => $phone,
                        'response' => $response->json(),
                    ]);
                }

                sleep(1);
            } catch (\Exception $e) {

                $failed++;

                CampaignContact::where('campaign_id', $campaign->id)
                    ->where('phone', $contact->phone)
                    ->update(['status' => 'failed']);

                \Log::error('Campaign Error', [
                    'phone' => $contact->phone,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // ───────────────── FINAL STATUS ─────────────────
        $totalContacts = $campaign->contacts()->count();
        $totalSent     = CampaignContact::where('campaign_id', $campaign->id)->where('status', 'sent')->count();
        $totalFailed   = CampaignContact::where('campaign_id', $campaign->id)->where('status', 'failed')->count();
        $totalPending  = CampaignContact::where('campaign_id', $campaign->id)->where('status', 'pending')->count();

        if ($totalSent > 0 && $totalFailed == 0 && $totalPending == 0) {
            $status = 'completed';
        } elseif ($totalSent > 0 && ($totalFailed > 0 || $totalPending > 0)) {
            $status = 'partial';
        } elseif ($totalSent == 0 && $totalFailed > 0) {
            $status = 'failed';
        } else {
            $status = 'draft';
        }

        $campaign->update([
            'status'         => $status,
            'total_contacts' => $totalContacts,
            'sent_count'     => $totalSent,
        ]);

        return response()->json([
            'status'          => true,
            'message'         => "Sent: {$sent} | Failed: {$failed}",
            'campaign_status' => $status,
        ]);
    }

































    public function sendSingle(Request $request, $campaignId, $contactId)
    {
        $campaign = Campaign::with('client')->findOrFail($campaignId);
        $contact  = CampaignContact::where('id', $contactId)
            ->where('campaign_id', $campaignId)
            ->firstOrFail();

        // Sirf failed ya pending ko send karo
        if ($contact->status === 'sent') {
            return response()->json([
                'status'  => false,
                'message' => 'Message already sent to this contact.',
            ]);
        }

        if (now()->toDateString() > $campaign->end_date) {
            return response()->json([
                'status'  => false,
                'message' => 'Campaign end date has passed. Cannot send message.',
            ]);
        }

        if (in_array($campaign->client->status, ['inactive', 'suspended'])) {
            return response()->json([
                'status'  => false,
                'message' => 'This client is ' . $campaign->client->status . '. Campaign cannot be run.',
            ]);
        }

        try {
            // Phone format
            $phone = preg_replace('/\D/', '', $contact->phone);
            if (strlen($phone) == 10) {
                $phone = '91' . $phone;
            }

            // Message replace
            $message = str_replace('{name}', $contact->name ?? '', $campaign->message ?? '');

            // Text payload
            $textPayload = [
                'messaging_product' => 'whatsapp',
                'to'                => $phone,
                'type'              => 'text',
                'text'              => ['body' => $message],
            ];

            // Media message
            if ($campaign->media_file) {

                $filePath = storage_path('app/public/' . $campaign->media_file);

                if (file_exists($filePath)) {

                    $uploadResponse = Http::withToken($campaign->client->wa_access_token)
                        ->attach('file', file_get_contents($filePath), basename($filePath))
                        ->post(
                            'https://graph.facebook.com/v25.0/' . $campaign->client->wa_phone_number_id . '/media',
                            ['messaging_product' => 'whatsapp']
                        );

                    $uploadResult = $uploadResponse->json();

                    if (!empty($uploadResult['id'])) {

                        $mediaId   = $uploadResult['id'];
                        $extension = strtolower(pathinfo($campaign->media_file, PATHINFO_EXTENSION));

                        $mediaType = match (true) {
                            in_array($extension, ['jpg', 'jpeg', 'png', 'webp']) => 'image',
                            in_array($extension, ['mp4', '3gp', 'mov'])          => 'video',
                            in_array($extension, ['mp3', 'ogg', 'aac', 'm4a'])   => 'audio',
                            default                                               => 'document',
                        };

                        $mediaPayload = [
                            'messaging_product' => 'whatsapp',
                            'to'                => $phone,
                            'type'              => $mediaType,
                            $mediaType          => ['id' => $mediaId],
                        ];

                        if (in_array($mediaType, ['image', 'video']) && !empty($message)) {
                            $mediaPayload[$mediaType]['caption'] = $message;
                        }

                        if ($mediaType === 'document') {
                            $mediaPayload['document']['filename'] = $campaign->media_original_name ?? basename($campaign->media_file);
                        }

                        $response = Http::withToken($campaign->client->wa_access_token)
                            ->post(
                                'https://graph.facebook.com/v25.0/' . $campaign->client->wa_phone_number_id . '/messages',
                                $mediaPayload
                            );

                        if (in_array($mediaType, ['document', 'audio']) && !empty($message) && $response->successful()) {
                            Http::withToken($campaign->client->wa_access_token)
                                ->post(
                                    'https://graph.facebook.com/v25.0/' . $campaign->client->wa_phone_number_id . '/messages',
                                    $textPayload
                                );
                        }
                    } else {
                        $response = Http::withToken($campaign->client->wa_access_token)
                            ->post(
                                'https://graph.facebook.com/v25.0/' . $campaign->client->wa_phone_number_id . '/messages',
                                $textPayload
                            );
                    }
                } else {
                    $response = Http::withToken($campaign->client->wa_access_token)
                        ->post(
                            'https://graph.facebook.com/v25.0/' . $campaign->client->wa_phone_number_id . '/messages',
                            $textPayload
                        );
                }
            } else {
                $response = Http::withToken($campaign->client->wa_access_token)
                    ->post(
                        'https://graph.facebook.com/v25.0/' . $campaign->client->wa_phone_number_id . '/messages',
                        $textPayload
                    );
            }

            // Status update
            if ($response->successful() && !empty($response->json('messages'))) {

                $contact->update(['status' => 'sent']);

                // Campaign sent_count increment
                $campaign->increment('sent_count');

                // Campaign status recalculate
                $total   = CampaignContact::where('campaign_id', $campaignId)->count();
                $sent    = CampaignContact::where('campaign_id', $campaignId)->where('status', 'sent')->count();
                $failed  = CampaignContact::where('campaign_id', $campaignId)->where('status', 'failed')->count();
                $pending = CampaignContact::where('campaign_id', $campaignId)->where('status', 'pending')->count();

                if ($sent == $total) {
                    $campaign->update(['status' => 'completed']);
                } elseif ($sent > 0 && ($failed > 0 || $pending > 0)) {
                    $campaign->update(['status' => 'partial']);
                }

                return response()->json([
                    'status'  => true,
                    'message' => 'Message sent successfully.',
                ]);
            } else {

                $contact->update(['status' => 'failed']);

                return response()->json([
                    'status'  => false,
                    'message' => 'Failed to send message.',
                ]);
            }
        } catch (\Exception $e) {

            $contact->update(['status' => 'failed']);

            \Log::error('Single Send Error', [
                'contact_id' => $contactId,
                'error'      => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong: ' . $e->getMessage(),
            ]);
        }
    }

    // second copy chatpgt and curront code
    // public function sendCampaign(Request $request, $id)
    // {
    //     $campaign = Campaign::with('contacts')->findOrFail($id);

    //     // Already completed
    //     if ($campaign->status == 'completed') {
    //         return response()->json([
    //             'status'          => false,
    //             'message'         => 'Campaign already completed.',
    //             'campaign_status' => $campaign->status,
    //         ]);
    //     }

    //     // ───────────────── Sheet Upload Contacts ─────────────────
    //     if ($request->hasFile('sheet')) {

    //         $request->validate([
    //             'sheet' => 'required|file|mimes:xlsx,xls,csv'
    //         ]);

    //         $file        = $request->file('sheet');
    //         $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
    //         $rows        = $spreadsheet->getActiveSheet()->toArray();

    //         $header   = array_map('strtolower', array_map('trim', array_shift($rows)));
    //         $nameCol  = array_search('name', $header);
    //         $phoneCol = array_search('phone', $header);

    //         if ($phoneCol === false) {
    //             return response()->json([
    //                 'status'  => false,
    //                 'message' => 'Phone column not found in sheet.'
    //             ]);
    //         }

    //         $contacts = collect($rows)
    //             ->filter(fn($row) => !empty($row[$phoneCol]))
    //             ->map(fn($row) => (object) [
    //                 'name'  => $nameCol !== false ? ($row[$nameCol] ?? '') : '',
    //                 'phone' => $row[$phoneCol],
    //             ]);
    //     } else {
    //         $contacts = $campaign->contacts;
    //     }

    //     // ───────────────── Testing Mode ─────────────────
    //     if ($request->boolean('testing')) {
    //         $contacts = $contacts->take(2);
    //     }

    //     if ($contacts->isEmpty()) {
    //         return response()->json([
    //             'status'  => false,
    //             'message' => 'No contacts found.'
    //         ]);
    //     }

    //     $sent   = 0;
    //     $failed = 0;

    //     $campaign->update(['status' => 'running']);

    //     // ───────────────── LOOP CONTACTS ─────────────────
    //     foreach ($contacts as $contact) {

    //         try {

    //             // ── Phone Format ──
    //             $phone = preg_replace('/\D/', '', $contact->phone);
    //             if (strlen($phone) == 10) {
    //                 $phone = '91' . $phone;
    //             }

    //             // ── Message Replace ──
    //             $message = str_replace('{name}', $contact->name, $campaign->message ?? '');

    //             // ───────────────── MEDIA MESSAGE ─────────────────
    //             if ($campaign->media_file) {

    //                 $filePath = storage_path('app/public/' . $campaign->media_file);

    //                 if (file_exists($filePath)) {

    //                     // Upload media to Meta
    //                     $uploadResponse = Http::withToken(env('WHATSAPP_TOKEN'))
    //                         ->attach('file', file_get_contents($filePath), basename($filePath))
    //                         ->post(
    //                             'https://graph.facebook.com/v25.0/' . env('PHONE_NUMBER_ID') . '/media',
    //                             ['messaging_product' => 'whatsapp']
    //                         );

    //                     $uploadResult = $uploadResponse->json();

    //                     if (!empty($uploadResult['id'])) {

    //                         $mediaId   = $uploadResult['id'];
    //                         $extension = strtolower(pathinfo($campaign->media_file, PATHINFO_EXTENSION));

    //                         $mediaType = match (true) {
    //                             in_array($extension, ['jpg', 'jpeg', 'png', 'webp']) => 'image',
    //                             in_array($extension, ['mp4', '3gp', 'mov'])          => 'video',
    //                             in_array($extension, ['mp3', 'ogg', 'aac', 'm4a'])   => 'audio',
    //                             default                                               => 'document',
    //                         };

    //                         // ── Media Payload ──
    //                         $payload = [
    //                             'messaging_product' => 'whatsapp',
    //                             'to'                => $phone,
    //                             'type'              => $mediaType,
    //                             $mediaType          => ['id' => $mediaId],
    //                         ];

    //                         // Image/Video → caption support hai
    //                         if (in_array($mediaType, ['image', 'video']) && !empty($message)) {
    //                             $payload[$mediaType]['caption'] = $message;
    //                         }

    //                         // Document → filename add karo
    //                         if ($mediaType === 'document') {
    //                             $payload['document']['filename'] = basename($campaign->media_file);
    //                         }

    //                         // ── Send Media ──
    //                         $response = Http::withToken(env('WHATSAPP_TOKEN'))
    //                             ->post(
    //                                 'https://graph.facebook.com/v25.0/' . env('PHONE_NUMBER_ID') . '/messages',
    //                                 $payload
    //                             );

    //                         $result = $response->json();

    //                         // Document/Audio → text message alag se bhejo
    //                         if (
    //                             in_array($mediaType, ['document', 'audio']) &&
    //                             !empty($message) &&
    //                             $response->successful()
    //                         ) {
    //                             Http::withToken(env('WHATSAPP_TOKEN'))
    //                                 ->post(
    //                                     'https://graph.facebook.com/v25.0/' . env('PHONE_NUMBER_ID') . '/messages',
    //                                     [
    //                                         'messaging_product' => 'whatsapp',
    //                                         'to'                => $phone,
    //                                         'type'              => 'text',
    //                                         'text'              => ['body' => $message],
    //                                     ]
    //                                 );
    //                         }
    //                     } else {
    //                         // Upload failed → fallback text
    //                         $response = Http::withToken(env('WHATSAPP_TOKEN'))
    //                             ->post(
    //                                 'https://graph.facebook.com/v25.0/' . env('PHONE_NUMBER_ID') . '/messages',
    //                                 [
    //                                     'messaging_product' => 'whatsapp',
    //                                     'to'                => $phone,
    //                                     'type'              => 'text',
    //                                     'text'              => ['body' => $message],
    //                                 ]
    //                             );

    //                         $result = $response->json();
    //                         \Log::error('Media Upload Failed', ['response' => $uploadResult]);
    //                     }
    //                 } else {
    //                     // File missing → fallback text
    //                     $response = Http::withToken(env('WHATSAPP_TOKEN'))
    //                         ->post(
    //                             'https://graph.facebook.com/v25.0/' . env('PHONE_NUMBER_ID') . '/messages',
    //                             [
    //                                 'messaging_product' => 'whatsapp',
    //                                 'to'                => $phone,
    //                                 'type'              => 'text',
    //                                 'text'              => ['body' => $message],
    //                             ]
    //                         );

    //                     $result = $response->json();
    //                     \Log::error('Media File Not Found', ['path' => $filePath]);
    //                 }
    //             } else {
    //                 // ───────────────── TEXT ONLY ─────────────────
    //                 $response = Http::withToken(env('WHATSAPP_TOKEN'))
    //                     ->post(
    //                         'https://graph.facebook.com/v25.0/' . env('PHONE_NUMBER_ID') . '/messages',
    //                         [
    //                             'messaging_product' => 'whatsapp',
    //                             'to'                => $phone,
    //                             'type'              => 'text',
    //                             'text'              => ['body' => $message],
    //                         ]
    //                     );

    //                 $result = $response->json();
    //             }

    //             // ───────────────── SUCCESS CHECK ─────────────────
    //             if ($response->successful() && !empty($result['messages'])) {

    //                 $sent++;

    //                 CampaignContact::where('campaign_id', $campaign->id)
    //                     ->where('phone', $contact->phone)
    //                     ->update(['status' => 'sent']);
    //             } else {

    //                 $failed++;

    //                 CampaignContact::where('campaign_id', $campaign->id)
    //                     ->where('phone', $contact->phone)
    //                     ->update(['status' => 'failed']);

    //                 \Log::error('WhatsApp Failed', ['phone' => $phone, 'response' => $result]);
    //             }

    //             sleep(1);
    //         } catch (\Exception $e) {

    //             $failed++;

    //             CampaignContact::where('campaign_id', $campaign->id)
    //                 ->where('phone', $contact->phone)
    //                 ->update(['status' => 'failed']);

    //             \Log::error('Campaign Error', ['phone' => $contact->phone, 'error' => $e->getMessage()]);
    //         }
    //     }

    //     // ───────────────── FINAL STATUS ─────────────────
    //     $status = 'draft';

    //     if ($sent == 0 && $failed > 0) {
    //         $status = 'failed';
    //     } elseif ($sent > 0 && $failed == 0) {
    //         $status = 'completed';
    //     } elseif ($sent > 0 && $failed > 0) {
    //         $status = 'partial';
    //     }

    //     $campaign->update([
    //         'status'         => $status,
    //         'total_contacts' => $contacts->count(),
    //         'sent_count'     => $sent,
    //     ]);

    //     return response()->json([
    //         'status'          => true,
    //         'message'         => "✅ Sent: {$sent} | ❌ Failed: {$failed}",
    //         'campaign_status' => $status,
    //     ]);
    // }













}
