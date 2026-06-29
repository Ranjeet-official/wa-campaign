<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Template;
use Illuminate\Support\Facades\Http;

class SyncTemplateStatuses extends Command
{
    protected $signature   = 'templates:sync-status';
    protected $description = 'Sync template statuses from Meta API';

    public function handle()
    {
        $templates = Template::with('client')
            ->whereNotNull('meta_template_id')
            ->get();

        $expiredClients  = [];
        $failedTemplates = [];

        foreach ($templates as $template) {
            $token = env('WHATSAPP_TOKEN');

            $response = Http::withToken($token)
                ->get("https://graph.facebook.com/v25.0/{$template->meta_template_id}");

            if ($response->successful()) {
                $metaStatus = strtolower($response->json('status'));

                $status = match ($metaStatus) {
                    'approved', 'active', 'quality_pending'            => 'approved',
                    'rejected', 'paused', 'disabled', 'limit_exceeded' => 'rejected',
                    'pending', 'in_appeal'                             => 'pending',
                    default                                            => 'pending',
                };

                if ($template->status !== $status) {
                    $template->update([
                        'status'      => $status,
                        'approved_at' => $status === 'approved' && !$template->approved_at ? now() : $template->approved_at,
                    ]);
                }
            } else {
                $errorCode = $response->json('error.code');
                $errorMsg  = $response->json('error.message') ?? 'Unknown error';

                if (in_array($errorCode, [190, 102, 104, 463, 467])) {
                    $expiredClients[] = "Client: {$template->client->name} | Template: {$template->name}";
                } else {
                    $failedTemplates[] = "Template: {$template->name} | Error: {$errorMsg}";
                }
            }
        }

        if (!empty($expiredClients)) {
            $this->error('⚠️  Token Expired:');
            foreach ($expiredClients as $msg) {
                $this->error("   → {$msg}");
            }
        }

        if (!empty($failedTemplates)) {
            $this->warn('❌ Sync Failed:');
            foreach ($failedTemplates as $msg) {
                $this->warn("   → {$msg}");
            }
        }

        $this->info('Template statuses synced successfully!');
    }
}
