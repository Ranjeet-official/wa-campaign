<?php

namespace App\Http\Controllers;

use App\Models\ChatHistory;
use App\Models\Client;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    // 📌 Email check karne wala function
    public function checkEmail(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if ($user) {
            return response()->json([
                'exists'     => true,
                'first_name' => $user->first_name,
                'last_name'  => $user->last_name,
                'user_id'    => $user->id,
            ]);
        }

        return response()->json(['exists' => false]);
    }

    // 📌 Naya user register karne wala function
    public function register(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'password' => 'required|min:4',
        ]);

        $user = User::create([
            'name' => $request->first_name . ' ' . $request->last_name,
            'email' => $request->email,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'success' => true,
            'name' => $user->name,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
        ]);
    }

    // 📌 Chatbot ka sawal-jawab wala function — ab Gemini AI use karta hai
    // public function ask(Request $request)
    // {
    //     $request->validate([
    //         'question' => 'required|string',
    //     ]);

    //     $question = $request->question;

    //     // Step 1: Client identify karo (session se, jo route /{slug} me set hua tha)
    //     $clientId = session('client_id');
    //     $client = $clientId ? Client::find($clientId) : null;

    //     // Step 2: Prompt + Knowledge Base fetch karo (agar client mila aur set kiya hua hai)
    //     $systemPrompt = $client?->chatbot_prompt
    //         ?: 'You are a helpful customer support assistant. Answer questions politely and concisely.';

    //     $knowledgeBase = $client?->chatbot_knowledge_base ?? '';

    //     // Step 3: Gemini ko bhejne wala final prompt banayein
    //     $fullPrompt = $systemPrompt;

    //     if (!empty($knowledgeBase)) {
    //         $fullPrompt .= "\n\nUse the following knowledge base to answer questions accurately:\n" . $knowledgeBase;
    //     }

    //     $fullPrompt .= "\n\nCustomer question: " . $question;

    //     // Step 4: Gemini API call karo
    //     try {
    //         $apiKey = config('services.gemini.api_key');

    //         $response = Http::timeout(20)->post(
    //             "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}",
    //             [
    //                 'contents' => [
    //                     [
    //                         'parts' => [
    //                             ['text' => $fullPrompt],
    //                         ],
    //                     ],
    //                 ],
    //             ]
    //         );

    //         if ($response->successful()) {
    //             $data = $response->json();
    //             $answer = $data['candidates'][0]['content']['parts'][0]['text']
    //                 ?? "Sorry, I couldn't generate a response right now.";
    //         } else {
    //             Log::error('Gemini API Error', ['body' => $response->body()]);
    //             $answer = "Sorry, I'm having trouble answering right now. Please try again shortly.";
    //         }
    //     } catch (\Exception $e) {
    //         Log::error('Gemini API Exception', ['message' => $e->getMessage()]);
    //         $answer = "Sorry, something went wrong while processing your question.";
    //     }

    //     return response()->json(['answer' => $answer]);
    // }



    public function ask(Request $request)
    {
        $request->validate([
            'question' => 'required|string|max:1000',
        ]);

        $question  = trim($request->question);
        $clientId  = session('client_id');
        $client    = $clientId ? Client::find($clientId) : null;

        // ── Active Prompt ──
        $activePrompt = $clientId
            ? \App\Models\ChatbotPrompt::where('client_id', $clientId)
            ->where('is_active', true)
            ->first()
            : null;

        $systemPrompt = $activePrompt?->prompt_text
            ?: 'You are a helpful customer support assistant. Answer questions politely and concisely.';

        // ── Active Knowledge Base ──
        $kbEntries = $clientId
            ? \App\Models\ChatbotKnowledgeBase::where('client_id', $clientId)
            ->where('is_active', true)
            ->get()
            : collect();

        $knowledgeBase = $kbEntries
            ->map(fn($kb) => "### {$kb->title}\n{$kb->content}")
            ->join("\n\n");

        // ── System Prompt Build ──
        $fullSystemPrompt = $systemPrompt;

        if (!empty($knowledgeBase)) {
            $fullSystemPrompt .= "\n\n---\n## Knowledge Base\n\n" . $knowledgeBase;
            $fullSystemPrompt .= "\n\n---\n## Instructions\n"
                . "- Answer ONLY based on the knowledge base provided above.\n"
                . "- Do NOT make up any information not present in the knowledge base.\n"
                . "- If the user asks something not covered in the knowledge base, respond with: 'I don't have information about that. Please contact us directly for assistance.'\n"
                . "- Keep responses clear, concise, and professional.\n"
                . "- Do not mention that you are using a knowledge base.";
        } else {
            $fullSystemPrompt .= "\n\n---\n## Instructions\n"
                . "- You have no business-specific knowledge base available.\n"
                . "- For any questions about specific services, pricing, timings, or business details, respond with: 'I don't have that information right now. Please contact us directly for assistance.'\n"
                . "- Keep responses polite and professional.";
        }

        // ── Groq API Call ──
        try {
            $response = Http::timeout(20)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . config('services.groq.api_key'),
                    'Content-Type'  => 'application/json',
                ])
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model'       => 'llama-3.3-70b-versatile',
                    'messages'    => [
                        ['role' => 'system', 'content' => $fullSystemPrompt],
                        ['role' => 'user',   'content' => $question],
                    ],
                    'max_tokens'  => 1024,
                    'temperature' => 0.3, // focused, less creative
                ]);

            if ($response->successful()) {
                $answer = trim(
                    $response->json()['choices'][0]['message']['content']
                        ?? "Sorry, I couldn't generate a response right now."
                );
            } else {
                Log::error('Groq API Error', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                $answer = "Sorry, I'm having trouble answering right now. Please try again shortly.";
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Groq Connection Error', ['message' => $e->getMessage()]);
            $answer = "Unable to connect to AI service. Please check your internet connection and try again.";
        } catch (\Exception $e) {
            Log::error('Groq Exception', ['message' => $e->getMessage()]);
            $answer = "Something went wrong while processing your question. Please try again.";
        }

        return response()->json(['answer' => $answer]);
    }

    // 📌 Central logger — frontend ka HAR message (user ya bot) yahin se save hota hai.
    public function logMessage(Request $request)
    {
        $request->validate([
            'sender'  => 'required|in:user,bot',
            'message' => 'required|string',
            'slug'    => 'nullable|string',
        ]);

        $clientId = null;

        if ($request->filled('slug')) {
            $client = \App\Models\Client::where('chatbot_slug', $request->slug)
                ->where('chatbot_enabled', 1)
                ->first();
            $clientId = $client?->id;
        }

        $clientId = $clientId ?? session('client_id', env('WHATSAPP_PANEL_CLIENT_ID'));

        ChatHistory::create([
            'client_id'  => $clientId,
            'session_id' => session()->getId(),
            'sender'     => $request->sender,
            'message'    => $request->message,
        ]);

        return response()->json(['status' => true]);
    }


    public function getWelcomeMessage()
    {
        $clientId = session('client_id');
        $client   = $clientId ? Client::find($clientId) : null;

        return response()->json([
            'welcome_message' => $client?->welcome_message ?? 'Welcome to our Chatbot!'
        ]);
    }
}










<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ChatHistoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ProductIssueController;
use App\Http\Controllers\ProductUserIssueController;
use App\Http\Controllers\OrderUserIssueController;
use App\Http\Controllers\ServiceUserIssueController;

Route::view('/', 'chat'); // Chat UI
Route::post('/check-email', [ChatbotController::class, 'checkEmail']);
Route::post('/ask', [ChatbotController::class, 'ask']);
Route::post('/register-user', [ChatbotController::class, 'register']);
Route::post('/log-message', [ChatbotController::class, 'logMessage']);
Route::post('/get-order-details', [OrderController::class, 'getOrderDetails']);
Route::post('/get-service-details', [ServiceController::class, 'getServiceDetails']);
Route::post('/get-product-issue', [ProductIssueController::class, 'getProductIssue']);
Route::post('/get-product-user-issue', [ProductUserIssueController::class, 'getProductIssue']);
Route::post('/submit-product-issue', [ProductUserIssueController::class, 'store']);
Route::post('/store-order-issue', [OrderUserIssueController::class, 'store']);
Route::post('/store-service-issue', [ServiceUserIssueController::class, 'store']);
Route::get('/welcome-message', [ChatbotController::class, 'getWelcomeMessage']);


Route::prefix('admin')->name('admin.')->group(function () {

    // Guest routes
    Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');

    // Protected routes
    Route::middleware('admin.auth')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/chat-history',        [ChatHistoryController::class, 'index'])->name('chat.index');
        Route::get('/chat-history/{user}', [ChatHistoryController::class, 'show'])->name('chat.show');
    });
});
Route::get('/{slug}', function ($slug) {
    $client = \App\Models\Client::where('chatbot_slug', $slug)
        ->where('chatbot_enabled', 1)
        ->firstOrFail();

    session(['client_id' => $client->id]);

    return view('chat', compact('client'));
})->where('slug', '^(?!admin).*$')
    ->name('chatbot.show');
