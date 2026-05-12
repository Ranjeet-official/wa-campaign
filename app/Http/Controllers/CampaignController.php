<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Client;
use App\Models\CampaignContact;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CampaignExport;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;


class CampaignController extends Controller
{


    public function index(Request $request)
    {
        $query = Campaign::with('client');

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
            $client = Client::find($request->client_id);
        }

        $campaigns = $query->latest()->paginate(10);
        $client    = $client ?? null;

        return view('admin.campaigns.index', compact('campaigns', 'client'));
    }

    public function create(Request $request)
    {
        $clients = Client::where('status', 'active')->get();
        $client_id = $request->client_id;
        return view('admin.campaigns.create', compact('clients', 'client_id'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'client_id'  => 'required|exists:clients,id',
            'name'       => 'required|string|max:255',
            'message'    => 'nullable|string|required_without:media_file',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'contacts'   => 'nullable',
            'media_file' => 'nullable|file|max:20480|required_without:message',
        ], [
            'name.required'           => 'Campaign name is required.',

            'message.required_without' =>
            'Message is required when no media file is uploaded.',

            'media_file.required_without' =>
            'Media file is required when message is empty.',

            'start_date.required'     => 'Start date is required.',
            'end_date.required'       => 'End date is required.',
            'end_date.after_or_equal' => 'End date must be on or after start date.',

            'client_id.required'      => 'Please select a client.',
            'client_id.exists'        => 'Selected client is invalid.',

            'media_file.max'          => 'File size must not exceed 20MB.',
        ]);

        $mediaPath = null;

        if ($request->hasFile('media_file')) {
            $mediaPath = $request->file('media_file')
                ->store('campaigns/media', 'public');
        }
        $campaign = Campaign::create([
            'client_id'  => $request->client_id,
            'name'       => $request->name,
            'message'    => $request->message,
            'media_file' => $mediaPath,
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
            'status'     => 'draft',
        ]);

        $contacts = $request->contacts;

        if (is_string($contacts)) {
            $contacts = json_decode($contacts, true);
        }

        if (!empty($contacts)) {
            $insert = [];

            foreach ($contacts as $row) {
                if (!empty($row['name']) && !empty($row['phone'])) {
                    $insert[] = [
                        'campaign_id' => $campaign->id,
                        'name'        => $row['name'],
                        'phone'       => $row['phone'],
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ];
                }
            }

            CampaignContact::insert($insert);
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
        // return response()->json([

        //     'success' => true,

        //     'message' => 'Campaign created successfully!',

        //     'redirect' => route(
        //         'campaigns.index',

        //         $request->filled('client_filter')
        //             ? [
        //                 'client_id' => $request->client_filter,
        //                 'success'   => 'Campaign created successfully!'
        //             ]
        //             : [
        //                 'success' => 'Campaign created successfully!'
        //             ]
        //     )

        // ]);
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
        $clients = Client::where('status', 'active')->get();
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
        $campaign = Campaign::findOrFail($id);

        $request->validate([

            'client_id' => 'required|exists:clients,id',

            'name' => 'required|string|max:255',

            'message' => [
                'nullable',
                'string',
                Rule::requiredIf(
                    (
                        !$campaign->media_file ||
                        $request->remove_media == 1
                    ) &&
                        !$request->hasFile('media_file')
                ),
            ],

            'media_file' => [
                'nullable',
                'file',
                'max:20480',
                Rule::requiredIf(
                    empty($request->message) &&
                        (
                            !$campaign->media_file ||
                            $request->remove_media == 1
                        )
                ),
            ],

            'start_date' => 'required|date',

            'end_date' => 'required|date|after_or_equal:start_date',

            'contacts' => 'nullable',

        ], [

            'name.required' =>
            'Campaign name is required.',

            'message.required' =>
            'Message is required when no media file is uploaded.',

            'media_file.required' =>
            'Media file is required when message is empty.',

            'start_date.required' =>
            'Start date is required.',

            'end_date.required' =>
            'End date is required.',

            'end_date.after_or_equal' =>
            'End date must be on or after start date.',

            'client_id.required' =>
            'Please select a client.',

            'client_id.exists' =>
            'Selected client is invalid.',

            'media_file.max' =>
            'File size must not exceed 20MB.',

        ]);

        // Existing media path
        $mediaPath = $campaign->media_file;

        // Remove existing file
        if ($request->remove_media == 1) {

            if (
                $campaign->media_file &&
                Storage::disk('public')->exists($campaign->media_file)
            ) {

                Storage::disk('public')->delete($campaign->media_file);
            }

            $mediaPath = null;
        }

        // Upload new file
        if ($request->hasFile('media_file')) {

            // Delete old file first
            if (
                $campaign->media_file &&
                Storage::disk('public')->exists($campaign->media_file)
            ) {

                Storage::disk('public')->delete($campaign->media_file);
            }

            $mediaPath = $request->file('media_file')
                ->store('campaigns/media', 'public');
        }

        // Update campaign
        $campaign->update([
            'client_id'  => $request->client_id,
            'name'       => $request->name,
            'message'    => $request->message,
            'media_file' => $mediaPath,
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
        ]);

        // Delete old contacts
        CampaignContact::where('campaign_id', $id)->delete();

        $contacts = $request->contacts;

        // JSON decode
        if (is_string($contacts)) {

            $contacts = json_decode($contacts, true);
        }

        // Insert new contacts
        if (!empty($contacts)) {

            $insert = [];

            foreach ($contacts as $row) {

                if (
                    !empty($row['name']) &&
                    !empty($row['phone'])
                ) {

                    $insert[] = [
                        'campaign_id' => $id,
                        'name'        => $row['name'],
                        'phone'       => $row['phone'],
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




    public function sendCampaign(Request $request, $id)
    {
        $campaign = Campaign::with('contacts')->findOrFail($id);

        // ── Sheet upload se contacts lo ──────────────────────────
        if ($request->hasFile('sheet')) {

            $request->validate([
                'sheet' => 'file|mimes:xlsx,xls,csv'
            ]);

            $file        = $request->file('sheet');
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
            $rows        = $spreadsheet->getActiveSheet()->toArray();

            // First row = header
            $header   = array_map('strtolower', array_map('trim', array_shift($rows)));
            $nameCol  = array_search('name', $header);
            $phoneCol = array_search('phone', $header);

            if ($phoneCol === false) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Sheet mein "phone" column nahi mila.'
                ]);
            }

            $contacts = collect($rows)
                ->filter(fn($row) => !empty($row[$phoneCol]))
                ->map(fn($row) => (object)[
                    'name'  => $nameCol !== false ? ($row[$nameCol] ?? '') : '',
                    'phone' => $row[$phoneCol],
                ]);
        } else {
            // Sheet nahi hai toh DB contacts use karo
            $contacts = $campaign->contacts;
        }

        // ── Testing mode: sirf 2 contacts ───────────────────────
        if ($request->boolean('testing')) {
            $contacts = $contacts->take(2);
        }

        if ($contacts->isEmpty()) {
            return response()->json([
                'status'  => false,
                'message' => 'No contacts found.'
            ]);
        }

        $sent   = 0;
        $failed = 0;

        foreach ($contacts as $contact) {

            // Phone clean karo
            $phone = preg_replace('/\D/', '', $contact->phone);
            $phone = '91' . substr($phone, -10);

            // {name} replace karo
            $message = str_replace('{name}', $contact->name, $campaign->message);

            // ── Media file hai toh image/video/doc bhejo ────────
            if ($campaign->media_file) {

                $mediaUrl  = Storage::disk('public')->url($campaign->media_file);
                $extension = strtolower(pathinfo($campaign->media_file, PATHINFO_EXTENSION));

                $mediaType = match (true) {
                    in_array($extension, ['jpg', 'jpeg', 'png', 'webp']) => 'image',
                    in_array($extension, ['mp4', '3gp'])               => 'video',
                    in_array($extension, ['mp3', 'ogg', 'aac'])         => 'audio',
                    default                                            => 'document',
                };

                $payload = [
                    'messaging_product' => 'whatsapp',
                    'to'                => $phone,
                    'type'              => $mediaType,
                    $mediaType          => [
                        'link'    => $mediaUrl,
                        'caption' => $message,   // caption mein message
                    ],
                ];
            } else {
                // Sirf text
                $payload = [
                    'messaging_product' => 'whatsapp',
                    'to'                => $phone,
                    'type'              => 'text',
                    'text'              => ['body' => $message],
                ];
            }

            $response = Http::withToken(env('WHATSAPP_TOKEN'))
                ->post(
                    'https://graph.facebook.com/v25.0/' . env('PHONE_NUMBER_ID') . '/messages',
                    $payload
                );

            isset($response->json()['messages']) ? $sent++ : $failed++;

            sleep(1);

            // $result = $response->json();

            // dd([
            //     'phone'    => $phone,
            //     'status'   => $response->status(),
            //     'response' => $result,
            // ]);
        }

        $campaign->update(['status' => 'running']);

        return response()->json([
            'status'  => true,
            'message' => "✅ Sent: {$sent} | ❌ Failed: {$failed}"
        ]);
    }
}
