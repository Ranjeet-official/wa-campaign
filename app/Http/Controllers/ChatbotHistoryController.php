<?php

namespace App\Http\Controllers;

use App\Models\ChatbotConversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;


class ChatbotHistoryController extends Controller
{
    // ── ADMIN METHODS (already existing) ──
    public function index($client_id)
    {
        $sessions = ChatbotConversation::where('client_id', $client_id)
                        ->selectRaw('session_id, MAX(created_at) as last_message_at, COUNT(*) as message_count')
                        ->groupBy('session_id')
                        ->orderByDesc('last_message_at')
                        ->paginate(20);

        return view('admin.chatbot.history', compact('sessions', 'client_id'));
    }

    public function show($client_id, $session_id)
    {
        $conversations = ChatbotConversation::where('client_id', $client_id)
                            ->where('session_id', $session_id)
                            ->orderBy('created_at', 'asc')
                            ->get();

        $userInfo = $conversations->first();

        return view('admin.chatbot.show', compact('conversations', 'userInfo', 'client_id', 'session_id'));
    }

    //  NEW: CLIENT METHODS
    public function clientIndex()
    {
        $client_id = Auth::guard('client')->id();

        $sessions = ChatbotConversation::where('client_id', $client_id)
                        ->selectRaw('session_id, MAX(created_at) as last_message_at, COUNT(*) as message_count, MAX(user_name) as user_name, MAX(user_email) as user_email')
                        ->groupBy('session_id')
                        ->orderByDesc('last_message_at')
                        ->paginate(20);

        return view('admin.chatbot.history', compact('sessions'));
    }

    public function clientShow($session_id)
    {
        $client_id = Auth::guard('client')->id();

        $conversations = ChatbotConversation::where('client_id', $client_id)
                            ->where('session_id', $session_id)
                            ->orderBy('created_at', 'asc')
                            ->get();

        if ($conversations->isEmpty()) {
            abort(404);
        }

        $userInfo = $conversations->first();

        return view('admin.chatbot.show', compact('conversations', 'userInfo', 'session_id'));
    }

                    
                  //admin
                public function downloadPdf($client_id, $session_id)
                {
                    $conversations = ChatbotConversation::where('client_id', $client_id)
                                        ->where('session_id', $session_id)
                                        ->orderBy('created_at', 'asc')
                                        ->get();

                    $userInfo = $conversations->first();

                    $pdf = Pdf::loadView('admin.chatbot.pdf', compact('conversations', 'userInfo'));

                    return $pdf->download('chat-' . $session_id . '.pdf');
                }

                // Client ke liye download
                public function clientDownloadPdf($session_id)
                {
                    $client_id = Auth::guard('client')->id();

                    $conversations = ChatbotConversation::where('client_id', $client_id)
                                        ->where('session_id', $session_id)
                                        ->orderBy('created_at', 'asc')
                                        ->get();

                    if ($conversations->isEmpty()) {
                        abort(404);
                    }

                    $userInfo = $conversations->first();

                    $pdf = Pdf::loadView('admin.chatbot.pdf', compact('conversations', 'userInfo'));

                    return $pdf->download('chat-' . $session_id . '.pdf');
                }
}