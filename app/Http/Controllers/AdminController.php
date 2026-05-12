<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function dashboard()
    {
        $adminId = Auth::id();

        // ───────── TOTAL CLIENTS ─────────
        $totalClients = Client::count();

        // ───────── TOTAL CAMPAIGNS ─────────
        $totalCampaigns = Campaign::count();

        // ───────── STATUS WISE CAMPAIGNS ─────────
        $activeCampaigns = Campaign::where('status', 'running')->count();

        $pendingCampaigns = Campaign::where('status', 'draft')->count();

        $completedCampaigns = Campaign::where('status', 'completed')->count();

        $failedCampaigns = Campaign::where('status', 'failed')->count();

        // ───────── RECENT CAMPAIGNS ─────────
        $recentCampaigns = Campaign::with('client')
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalClients',
            'totalCampaigns',
            'activeCampaigns',
            'pendingCampaigns',
            'completedCampaigns',
            'failedCampaigns',
            'recentCampaigns'
        ));
    }
}
