<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\ChatbotConversation;
use Illuminate\Support\Facades\Auth;

class ClientDashboardController extends Controller
{
    public function dashboard()
    {
        $client = Auth::guard('client')->user();

        $totalCampaigns = $activeCampaigns = $pendingCampaigns = $completedCampaigns = $failedCampaigns = null;
        $recentCampaigns = collect();

        $totalChatSessions = $totalChatMessages = $todayChatSessions = null;

        
        if ($client->whatsapp_enabled) {
            $totalCampaigns     = Campaign::where('client_id', $client->id)->count();
            $activeCampaigns    = Campaign::where('client_id', $client->id)->where('status', 'running')->count();
            $pendingCampaigns   = Campaign::where('client_id', $client->id)->where('status', 'draft')->count();
            $completedCampaigns = Campaign::where('client_id', $client->id)->where('status', 'completed')->count();
            $failedCampaigns    = Campaign::where('client_id', $client->id)->where('status', 'failed')->count();

            $recentCampaigns = Campaign::where('client_id', $client->id)
                ->latest()
                ->take(5)
                ->get();
        }

        if ($client->chatbot_enabled) {
            $totalChatSessions = ChatbotConversation::where('client_id', $client->id)
                ->distinct('session_id')
                ->count('session_id');

            $totalChatMessages = ChatbotConversation::where('client_id', $client->id)->count();

            $todayChatSessions = ChatbotConversation::where('client_id', $client->id)
                ->whereDate('created_at', today())
                ->distinct('session_id')
                ->count('session_id');
        }

        return view('admin.dashboard', compact(
            'totalCampaigns',
            'activeCampaigns',
            'pendingCampaigns',
            'completedCampaigns',
            'failedCampaigns',
            'recentCampaigns',
            'totalChatSessions',
            'totalChatMessages',
            'todayChatSessions',
        ));
    }
}