<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ChatbotPrompt;
use App\Models\ChatbotKnowledgeBase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChatbotConfigController extends Controller
{
    // ── Full page dikhata hai: Welcome Message + Prompts + Knowledge Base ──
    public function edit($client_id)
    {
        $client = Client::findOrFail($client_id);

        $prompts = ChatbotPrompt::where('client_id', $client_id)
            ->orderByDesc('created_at')
            ->get();

        $knowledgeBases = ChatbotKnowledgeBase::where('client_id', $client_id)
            ->orderByDesc('created_at')
            ->get();

        return view('admin.chatbot.config', compact('client', 'prompts', 'knowledgeBases'))
            ->with('isAdmin', true);
    }

    // ════════════════════════════════════════
    //  WELCOME MESSAGE — Update (normal redirect, no AJAX)
    // ════════════════════════════════════════

    public function updateWelcomeMessage(Request $request, $client_id)
    {
        try {
            $client = Client::findOrFail($client_id);

            $request->validate([
                'welcome_message' => 'nullable|string|max:1000',
            ]);

            $client->update(['welcome_message' => $request->welcome_message]);

            return redirect()
                ->route('chatbot.config.edit', $client_id)
                ->with('success', 'Welcome message updated successfully.');
        } catch (\Exception $e) {
            Log::error('Welcome Message Update Error', ['message' => $e->getMessage()]);
            return redirect()
                ->route('chatbot.config.edit', $client_id)
                ->with('error', 'Something went wrong while updating the welcome message.');
        }
    }

    // ════════════════════════════════════════
    //  PROMPTS — CRUD (AJAX / JSON)
    // ════════════════════════════════════════

    public function storePrompt(Request $request, $client_id)
    {
        try {
            Client::findOrFail($client_id);

            $request->validate([
                'title'       => 'required|string|max:255',
                'prompt_text' => 'required|string',
            ]);

            ChatbotPrompt::create([
                'client_id'   => $client_id,
                'title'       => $request->title,
                'prompt_text' => $request->prompt_text,
                'is_active'   => false,
            ]);

            return $this->promptsJsonResponse($client_id, 'Prompt added successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['status' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Prompt Store Error', ['message' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Something went wrong while adding the prompt.'], 500);
        }
    }

    public function updatePrompt(Request $request, $client_id, $prompt_id)
    {
        try {
            $prompt = ChatbotPrompt::where('client_id', $client_id)->findOrFail($prompt_id);

            $request->validate([
                'title'       => 'required|string|max:255',
                'prompt_text' => 'required|string',
            ]);

            $prompt->update([
                'title'       => $request->title,
                'prompt_text' => $request->prompt_text,
            ]);

            return $this->promptsJsonResponse($client_id, 'Prompt updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['status' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Prompt Update Error', ['message' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Something went wrong while updating the prompt.'], 500);
        }
    }

    public function destroyPrompt($client_id, $prompt_id)
    {
        try {
            $prompt = ChatbotPrompt::where('client_id', $client_id)->findOrFail($prompt_id);
            $prompt->delete();

            return $this->promptsJsonResponse($client_id, 'Prompt deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Prompt Delete Error', ['message' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Something went wrong while deleting the prompt.'], 500);
        }
    }

    public function activatePrompt($client_id, $prompt_id)
    {
        try {
            ChatbotPrompt::where('client_id', $client_id)->update(['is_active' => false]);

            $prompt = ChatbotPrompt::where('client_id', $client_id)->findOrFail($prompt_id);
            $prompt->update(['is_active' => true]);

            return $this->promptsJsonResponse($client_id, 'Prompt activated successfully.');
        } catch (\Exception $e) {
            Log::error('Prompt Activate Error', ['message' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Something went wrong while activating the prompt.'], 500);
        }
    }

    // ════════════════════════════════════════
    //  KNOWLEDGE BASE — CRUD (AJAX / JSON)
    // ════════════════════════════════════════

    public function storeKb(Request $request, $client_id)
    {
        try {
            Client::findOrFail($client_id);

            $request->validate([
                'title'   => 'required|string|max:255',
                'content' => 'required|string',
            ]);

            ChatbotKnowledgeBase::create([
                'client_id' => $client_id,
                'title'     => $request->title,
                'content'   => $request->content,
                'is_active' => true,
            ]);

            return $this->kbJsonResponse($client_id, 'Knowledge base entry added successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['status' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('KB Store Error', ['message' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Something went wrong while adding the entry.'], 500);
        }
    }

    public function updateKb(Request $request, $client_id, $kb_id)
    {
        try {
            $kb = ChatbotKnowledgeBase::where('client_id', $client_id)->findOrFail($kb_id);

            $request->validate([
                'title'   => 'required|string|max:255',
                'content' => 'required|string',
            ]);

            $kb->update([
                'title'   => $request->title,
                'content' => $request->content,
            ]);

            return $this->kbJsonResponse($client_id, 'Knowledge base entry updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['status' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('KB Update Error', ['message' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Something went wrong while updating the entry.'], 500);
        }
    }

    public function destroyKb($client_id, $kb_id)
    {
        try {
            $kb = ChatbotKnowledgeBase::where('client_id', $client_id)->findOrFail($kb_id);
            $kb->delete();

            return $this->kbJsonResponse($client_id, 'Knowledge base entry deleted successfully.');
        } catch (\Exception $e) {
            Log::error('KB Delete Error', ['message' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Something went wrong while deleting the entry.'], 500);
        }
    }

    public function toggleKb($client_id, $kb_id)
    {
        try {
            $kb = ChatbotKnowledgeBase::where('client_id', $client_id)->findOrFail($kb_id);
            $kb->update(['is_active' => !$kb->is_active]);

            return $this->kbJsonResponse($client_id, $kb->is_active ? 'Entry activated.' : 'Entry deactivated.');
        } catch (\Exception $e) {
            Log::error('KB Toggle Error', ['message' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Something went wrong while updating the entry.'], 500);
        }
    }

    // ════════════════════════════════════════════════════════
    //  CLIENT-SIDE METHODS — client apna khud ka config edit karta hai
    //  (client_id route param se nahi, Auth::guard('client') se aata hai)
    // ════════════════════════════════════════════════════════

    public function clientEdit()
    {
        $client_id = Auth::guard('client')->id();
        $client = Client::findOrFail($client_id);

        $prompts = ChatbotPrompt::where('client_id', $client_id)
            ->orderByDesc('created_at')
            ->get();

        $knowledgeBases = ChatbotKnowledgeBase::where('client_id', $client_id)
            ->orderByDesc('created_at')
            ->get();

        return view('admin.chatbot.config', compact('client', 'prompts', 'knowledgeBases'))
            ->with('isAdmin', false);
    }

    public function clientUpdateWelcomeMessage(Request $request)
    {
        try {
            $client_id = Auth::guard('client')->id();
            $client = Client::findOrFail($client_id);

            $request->validate([
                'welcome_message' => 'nullable|string|max:1000',
            ]);

            $client->update(['welcome_message' => $request->welcome_message]);

            return redirect()
                ->route('client.chatbot.config.index')
                ->with('success', 'Welcome message updated successfully.');
        } catch (\Exception $e) {
            Log::error('Client Welcome Message Update Error', ['message' => $e->getMessage()]);
            return redirect()
                ->route('client.chatbot.config.index')
                ->with('error', 'Something went wrong while updating the welcome message.');
        }
    }

    public function clientStorePrompt(Request $request)
    {
        try {
            $client_id = Auth::guard('client')->id();

            $request->validate([
                'title'       => 'required|string|max:255',
                'prompt_text' => 'required|string',
            ]);

            ChatbotPrompt::create([
                'client_id'   => $client_id,
                'title'       => $request->title,
                'prompt_text' => $request->prompt_text,
                'is_active'   => false,
            ]);

            return $this->promptsJsonResponse($client_id, 'Prompt added successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['status' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Client Prompt Store Error', ['message' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Something went wrong while adding the prompt.'], 500);
        }
    }

    public function clientUpdatePrompt(Request $request, $prompt_id)
    {
        try {
            $client_id = Auth::guard('client')->id();
            $prompt = ChatbotPrompt::where('client_id', $client_id)->findOrFail($prompt_id);

            $request->validate([
                'title'       => 'required|string|max:255',
                'prompt_text' => 'required|string',
            ]);

            $prompt->update([
                'title'       => $request->title,
                'prompt_text' => $request->prompt_text,
            ]);

            return $this->promptsJsonResponse($client_id, 'Prompt updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['status' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Client Prompt Update Error', ['message' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Something went wrong while updating the prompt.'], 500);
        }
    }

    public function clientDestroyPrompt($prompt_id)
    {
        try {
            $client_id = Auth::guard('client')->id();
            $prompt = ChatbotPrompt::where('client_id', $client_id)->findOrFail($prompt_id);
            $prompt->delete();

            return $this->promptsJsonResponse($client_id, 'Prompt deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Client Prompt Delete Error', ['message' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Something went wrong while deleting the prompt.'], 500);
        }
    }

    public function clientActivatePrompt($prompt_id)
    {
        try {
            $client_id = Auth::guard('client')->id();

            ChatbotPrompt::where('client_id', $client_id)->update(['is_active' => false]);

            $prompt = ChatbotPrompt::where('client_id', $client_id)->findOrFail($prompt_id);
            $prompt->update(['is_active' => true]);

            return $this->promptsJsonResponse($client_id, 'Prompt activated successfully.');
        } catch (\Exception $e) {
            Log::error('Client Prompt Activate Error', ['message' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Something went wrong while activating the prompt.'], 500);
        }
    }

    public function clientStoreKb(Request $request)
    {
        try {
            $client_id = Auth::guard('client')->id();

            $request->validate([
                'title'   => 'required|string|max:255',
                'content' => 'required|string',
            ]);

            ChatbotKnowledgeBase::create([
                'client_id' => $client_id,
                'title'     => $request->title,
                'content'   => $request->content,
                'is_active' => true,
            ]);

            return $this->kbJsonResponse($client_id, 'Knowledge base entry added successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['status' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Client KB Store Error', ['message' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Something went wrong while adding the entry.'], 500);
        }
    }

    public function clientUpdateKb(Request $request, $kb_id)
    {
        try {
            $client_id = Auth::guard('client')->id();
            $kb = ChatbotKnowledgeBase::where('client_id', $client_id)->findOrFail($kb_id);

            $request->validate([
                'title'   => 'required|string|max:255',
                'content' => 'required|string',
            ]);

            $kb->update([
                'title'   => $request->title,
                'content' => $request->content,
            ]);

            return $this->kbJsonResponse($client_id, 'Knowledge base entry updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['status' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Client KB Update Error', ['message' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Something went wrong while updating the entry.'], 500);
        }
    }

    public function clientDestroyKb($kb_id)
    {
        try {
            $client_id = Auth::guard('client')->id();
            $kb = ChatbotKnowledgeBase::where('client_id', $client_id)->findOrFail($kb_id);
            $kb->delete();

            return $this->kbJsonResponse($client_id, 'Knowledge base entry deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Client KB Delete Error', ['message' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Something went wrong while deleting the entry.'], 500);
        }
    }

    public function clientToggleKb($kb_id)
    {
        try {
            $client_id = Auth::guard('client')->id();
            $kb = ChatbotKnowledgeBase::where('client_id', $client_id)->findOrFail($kb_id);
            $kb->update(['is_active' => !$kb->is_active]);

            return $this->kbJsonResponse($client_id, $kb->is_active ? 'Entry activated.' : 'Entry deactivated.');
        } catch (\Exception $e) {
            Log::error('Client KB Toggle Error', ['message' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Something went wrong while updating the entry.'], 500);
        }
    }

    // ════════════════════════════════════════════════════════
    //  HELPERS — fresh list JSON me return karte hain
    // ════════════════════════════════════════════════════════

    private function promptsJsonResponse($client_id, $message)
    {
        $prompts = ChatbotPrompt::where('client_id', $client_id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'status'  => true,
            'message' => $message,
            'prompts' => $prompts,
        ]);
    }

    private function kbJsonResponse($client_id, $message)
    {
        $knowledgeBases = ChatbotKnowledgeBase::where('client_id', $client_id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'status'         => true,
            'message'        => $message,
            'knowledgeBases' => $knowledgeBases,
        ]);
    }
}