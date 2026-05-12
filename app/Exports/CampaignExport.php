<?php

namespace App\Exports;

use App\Models\Campaign;
use App\Models\CampaignContact;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CampaignExport implements FromCollection, WithHeadings, WithStyles
{
    protected $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    // ───────── HEADINGS ─────────
    public function headings(): array
    {
        return [
            'Campaign Name',
            'Message',
            'Client',
            'Contact Name',
            'Phone',
            'Start Date',
            'End Date',
            'Status',
        ];
    }

    // ───────── DATA ─────────
    public function collection()
    {
        $campaign = Campaign::with('client')
            ->findOrFail($this->id);

        $contacts = CampaignContact::where('campaign_id', $this->id)->get();

        $data = [];

        // if contacts exist
        if ($contacts->count() > 0) {

            foreach ($contacts as $contact) {
                $data[] = [
                    'Campaign Name' => $campaign->name,
                    'Message'       => $campaign->message,
                    'Client'        => $campaign->client->name ?? '-',
                    'Contact Name'  => $contact->name,
                    'Phone'         => $contact->phone,
                    'Start Date'    => $campaign->start_date,
                    'End Date'      => $campaign->end_date,
                    'Status'        => ucfirst($campaign->status),
                ];
            }

        } else {

            // fallback if no contacts
            $data[] = [
                'Campaign Name' => $campaign->name,
                'Message'       => $campaign->message,
                'Client'        => $campaign->client->name ?? '-',
                'Contact Name'  => '-',
                'Phone'         => '-',
                'Start Date'    => $campaign->start_date,
                'End Date'      => $campaign->end_date,
                'Status'        => ucfirst($campaign->status),
            ];
        }

        return collect($data);
    }

    // ───────── STYLES ─────────
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
