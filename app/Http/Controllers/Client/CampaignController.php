<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CampaignContact;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CampaignExport;

class CampaignController extends Controller
{
    // ─────────────────────────────────────────
    // INDEX
    // ─────────────────────────────────────────
    public function index()
    {
        $client = Auth::guard('client')->user();

        $campaigns = Campaign::with('client')
            ->withCount('contacts')
            ->where('client_id', $client->id)
            ->latest()
            ->paginate(10);

        return view('admin.campaigns.index', compact('campaigns'));
    }

    // ─────────────────────────────────────────
    // CREATE
    // ─────────────────────────────────────────
    public function create()
    {
        $client = Auth::guard('client')->user();

        // Sirf apne approved templates
        $templates = Template::where('client_id', $client->id)
            ->where('status', 'approved')
            ->orderBy('name')
            ->get();

        return view('admin.campaigns.create', compact('templates'));
    }

    // ─────────────────────────────────────────
    // STORE
    // ─────────────────────────────────────────
    public function store(Request $request)
    {
        // ✅ PostTooLarge check
        if ($request->server('CONTENT_LENGTH') > 0 && empty($_FILES) && empty($_POST)) {
            return response()->json([
                'success' => false,
                'message' => 'File too large. Maximum allowed size is 20MB.',
            ], 422);
        }

        $client = Auth::guard('client')->user();

        $request->validate([
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
            'media_file.max'              => 'File size must not exceed 20MB.',
        ]);

        $contacts = $request->contacts;
        if (is_string($contacts)) {
            $contacts = json_decode($contacts, true);
        }

        $mediaPath         = null;
        $mediaOriginalName = null;

        if ($request->hasFile('media_file')) {
            $mediaOriginalName = $request->file('media_file')->getClientOriginalName();
            $mediaPath         = $request->file('media_file')->store('campaigns/media', 'public');
        }

        $campaign = Campaign::create([
            'client_id'           => $client->id,
            'name'                => $request->name,
            'message'             => $request->message,
            'media_file'          => $mediaPath,
            'media_original_name' => $mediaOriginalName,
            'start_date'          => $request->start_date,
            'end_date'            => $request->end_date,
            'status'              => 'draft',
            'total_contacts'      => !empty($contacts) ? count($contacts) : 0,
            'template_id'         => $request->template_id,
        ]);

        if (!empty($contacts)) {
            $insert = [];
            foreach ($contacts as $row) {
                if (!empty($row['name']) && !empty($row['phone'])) {
                    $insert[] = [
                        'campaign_id' => $campaign->id,
                        'name'        => $row['name'],
                        'phone'       => $row['phone'],
                        'status'      => 'pending', // ✅
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
            'redirect' => route('client.campaigns.index'),
        ]);
    }

    // ─────────────────────────────────────────
    // SHOW
    // ─────────────────────────────────────────
    public function show($id)
    {
        $client = Auth::guard('client')->user();

        $campaign = Campaign::with(['client', 'contacts'])
            ->where('client_id', $client->id)
            ->findOrFail($id);

        return view('admin.campaigns.show', compact('campaign'));
    }

    // ─────────────────────────────────────────
    // EDIT
    // ─────────────────────────────────────────
    public function edit($id)
    {
        $client = Auth::guard('client')->user();

        $campaign = Campaign::where('client_id', $client->id)
            ->findOrFail($id);

        $templates = Template::where('client_id', $client->id)
            ->where('status', 'approved')
            ->orderBy('name')
            ->get();

        return view('admin.campaigns.edit', compact('campaign', 'templates'));
    }

    // ─────────────────────────────────────────
    // UPDATE
    // ─────────────────────────────────────────
    public function update(Request $request, $id)
    {
        // ✅ PostTooLarge check
        if ($request->server('CONTENT_LENGTH') > 0 && empty($_FILES) && empty($_POST)) {
            return response()->json([
                'success' => false,
                'message' => 'File too large. Maximum allowed size is 20MB.',
            ], 422);
        }

        $client   = Auth::guard('client')->user();
        $campaign = Campaign::where('client_id', $client->id)->findOrFail($id);

        $request->validate([
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
        ]);

        $mediaPath         = $campaign->media_file;
        $mediaOriginalName = $campaign->media_original_name;

        if ($request->remove_media == 1) {
            if ($campaign->media_file && Storage::disk('public')->exists($campaign->media_file)) {
                Storage::disk('public')->delete($campaign->media_file);
            }
            $mediaPath         = null;
            $mediaOriginalName = null;
        }

        if ($request->hasFile('media_file')) {
            if ($campaign->media_file && Storage::disk('public')->exists($campaign->media_file)) {
                Storage::disk('public')->delete($campaign->media_file);
            }
            $mediaOriginalName = $request->file('media_file')->getClientOriginalName();
            $mediaPath         = $request->file('media_file')->store('campaigns/media', 'public');
        }

        $contacts = $request->contacts;
        if (is_string($contacts)) {
            $contacts = json_decode($contacts, true);
        }

        $campaign->update([
            'name'                => $request->name,
            'template_id'         => $request->template_id,
            'message'             => $request->message,
            'media_file'          => $mediaPath,
            'media_original_name' => $mediaOriginalName,
            'start_date'          => $request->start_date,
            'end_date'            => $request->end_date,
            'status'              => 'draft',
            'total_contacts'      => !empty($contacts) ? count($contacts) : 0,
            'sent_count'          => 0,
        ]);

        CampaignContact::where('campaign_id', $id)->delete();

        if (!empty($contacts)) {
            $insert = [];
            foreach ($contacts as $row) {
                if (!empty($row['name']) && !empty($row['phone'])) {
                    $insert[] = [
                        'campaign_id' => $id,
                        'name'        => $row['name'],
                        'phone'       => $row['phone'],
                        'status'      => 'pending', // ✅
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
            'redirect' => route('client.campaigns.index'),
        ]);
    }
    // ─────────────────────────────────────────
    // DESTROY
    // ─────────────────────────────────────────
    public function destroy($id)
    {
        $client   = Auth::guard('client')->user();
        $campaign = Campaign::where('client_id', $client->id)->findOrFail($id);

        CampaignContact::where('campaign_id', $id)->delete();
        $campaign->delete();

        return redirect()->route('client.campaigns.index')
            ->with('success', 'Campaign deleted successfully!');
    }

    // ─────────────────────────────────────────
    // EXPORT
    // ─────────────────────────────────────────
    public function export($id)
    {
        $client   = Auth::guard('client')->user();
        $campaign = Campaign::where('client_id', $client->id)->findOrFail($id);

        return Excel::download(new CampaignExport($id), 'campaign.xlsx');
    }

    // ─────────────────────────────────────────
    // SEND ALL (Run Campaign)
    // ─────────────────────────────────────────
    public function sendCampaign(Request $request, $id)
    {
        $client   = Auth::guard('client')->user();
        $campaign = Campaign::with('contacts')
            ->where('client_id', $client->id)
            ->findOrFail($id);

        if ($campaign->status == 'completed') {
            return response()->json([
                'status'  => false,
                'message' => 'Campaign already completed.',
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

            // ✅ Pehle se sent phones skip karo
            $alreadySentPhones = CampaignContact::where('campaign_id', $campaign->id)
                ->where('status', 'sent')
                ->pluck('phone')
                ->toArray();

            $contacts = collect($rows)
                ->filter(fn($row) => !empty($row[$phoneCol]))
                ->filter(fn($row) => !in_array($row[$phoneCol], $alreadySentPhones)) // ✅ sent skip
                ->map(fn($row) => (object) [
                    'name'  => $nameCol !== false ? ($row[$nameCol] ?? '') : '',
                    'phone' => $row[$phoneCol],
                ]);
        } else {
            // ✅ Sirf pending aur failed — sent skip
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
                        $uploadResponse = Http::withToken($client->wa_access_token)
                            ->attach('file', file_get_contents($filePath), basename($filePath))
                            ->post(
                                'https://graph.facebook.com/v25.0/' . $client->wa_phone_number_id . '/media',
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
                                $mediaPayload['document']['filename'] = $campaign->media_original_name
                                    ?? basename($campaign->media_file);
                            }

                            // Send media
                            $response = Http::withToken($client->wa_access_token)
                                ->post(
                                    'https://graph.facebook.com/v25.0/' . $client->wa_phone_number_id . '/messages',
                                    $mediaPayload
                                );

                            // Document/Audio → text alag se bhejo
                            if (
                                in_array($mediaType, ['document', 'audio']) &&
                                !empty($message) &&
                                $response->successful()
                            ) {
                                Http::withToken($client->wa_access_token)
                                    ->post(
                                        'https://graph.facebook.com/v25.0/' . $client->wa_phone_number_id . '/messages',
                                        $textPayload
                                    );
                            }
                        } else {
                            // Upload failed → fallback text
                            $response = Http::withToken($client->wa_access_token)
                                ->post(
                                    'https://graph.facebook.com/v25.0/' . $client->wa_phone_number_id . '/messages',
                                    $textPayload
                                );
                            \Log::error('Media Upload Failed', ['response' => $uploadResult]);
                        }
                    } else {
                        // File missing → fallback text
                        $response = Http::withToken($client->wa_access_token)
                            ->post(
                                'https://graph.facebook.com/v25.0/' . $client->wa_phone_number_id . '/messages',
                                $textPayload
                            );
                        \Log::error('Media File Not Found', ['path' => $filePath]);
                    }
                } else {
                    // ───────────────── TEXT ONLY ─────────────────
                    $response = Http::withToken($client->wa_access_token)
                        ->post(
                            'https://graph.facebook.com/v25.0/' . $client->wa_phone_number_id . '/messages',
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
        $totalSent     = CampaignContact::where('campaign_id', $campaign->id)->where('status', 'sent')->count();
        $totalFailed   = CampaignContact::where('campaign_id', $campaign->id)->where('status', 'failed')->count();
        $totalPending  = CampaignContact::where('campaign_id', $campaign->id)->where('status', 'pending')->count();
        $totalContacts = CampaignContact::where('campaign_id', $campaign->id)->count();

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

    // ─────────────────────────────────────────
    // SEND SINGLE (Manual)
    // ─────────────────────────────────────────
    public function sendSingle(Request $request, $campaignId, $contactId)
    {
        $client   = Auth::guard('client')->user();
        $campaign = Campaign::where('client_id', $client->id)->findOrFail($campaignId);
        $contact  = CampaignContact::where('id', $contactId)
            ->where('campaign_id', $campaignId)
            ->firstOrFail();

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

        try {
            $phone = preg_replace('/\D/', '', $contact->phone);
            if (strlen($phone) == 10) {
                $phone = '91' . $phone;
            }

            $message = str_replace('{name}', $contact->name ?? '', $campaign->message ?? '');

            $textPayload = [
                'messaging_product' => 'whatsapp',
                'to'                => $phone,
                'type'              => 'text',
                'text'              => ['body' => $message],
            ];

            if ($campaign->media_file) {
                $filePath = storage_path('app/public/' . $campaign->media_file);

                if (file_exists($filePath)) {
                    $uploadResponse = Http::withToken($client->wa_access_token)
                        ->attach('file', file_get_contents($filePath), basename($filePath))
                        ->post(
                            'https://graph.facebook.com/v25.0/' . $client->wa_phone_number_id . '/media',
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
                            $mediaPayload['document']['filename'] = $campaign->media_original_name
                                ?? basename($campaign->media_file);
                        }

                        $response = Http::withToken($client->wa_access_token)
                            ->post(
                                'https://graph.facebook.com/v25.0/' . $client->wa_phone_number_id . '/messages',
                                $mediaPayload
                            );

                        if (in_array($mediaType, ['document', 'audio']) && !empty($message) && $response->successful()) {
                            Http::withToken($client->wa_access_token)
                                ->post(
                                    'https://graph.facebook.com/v25.0/' . $client->wa_phone_number_id . '/messages',
                                    $textPayload
                                );
                        }
                    } else {
                        $response = Http::withToken($client->wa_access_token)
                            ->post(
                                'https://graph.facebook.com/v25.0/' . $client->wa_phone_number_id . '/messages',
                                $textPayload
                            );
                    }
                } else {
                    $response = Http::withToken($client->wa_access_token)
                        ->post(
                            'https://graph.facebook.com/v25.0/' . $client->wa_phone_number_id . '/messages',
                            $textPayload
                        );
                }
            } else {
                $response = Http::withToken($client->wa_access_token)
                    ->post(
                        'https://graph.facebook.com/v25.0/' . $client->wa_phone_number_id . '/messages',
                        $textPayload
                    );
            }

            if ($response->successful() && !empty($response->json('messages'))) {
                $contact->update(['status' => 'sent']);
                $campaign->increment('sent_count');

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
            Log::error('Single Send Error', ['contact_id' => $contactId, 'error' => $e->getMessage()]);
            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong: ' . $e->getMessage(),
            ]);
        }
    }
}
