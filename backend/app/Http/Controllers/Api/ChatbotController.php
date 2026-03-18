<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ChatbotController extends Controller
{
    private string $systemPrompt = <<<PROMPT
You are a helpful AI assistant for Barangay Smart Connect, a barangay management system in the Philippines.
You help residents with questions about barangay services, document requirements, fees, and processes.
You can answer questions about:
- Barangay Clearance (requirements: valid ID, proof of residency — processing: 1-2 days — free)
- Certificate of Residency (requirements: valid ID, utility bill — processing: 1 day — free)
- Certificate of Indigency (requirements: valid ID, proof of residency, sworn statement — processing: 1 day — free)
- Business Permit Clearance (requirements: valid ID, DTI/SEC registration, business sketch — processing: 2-3 days — free)
- Certificate of Good Moral (requirements: valid ID, proof of residency — processing: 1 day — free)
- Blotter Report (requirements: valid ID, written incident report — processing: same day — free)
Office hours: Monday to Friday, 8:00 AM - 5:00 PM
Location: Barangay Hall
Always be friendly, helpful, and respond in English or Filipino depending on what the user uses.
Keep responses concise and practical.
If asked about something outside barangay services, politely redirect to relevant barangay topics.
PROMPT;

    public function chat(Request $request)
    {
        $request->validate([
            'message'           => 'required|string|max:500',
            'history'           => 'nullable|array',
            'history.*.role'    => 'required|in:user,assistant',
            'history.*.content' => 'required|string',
        ]);

        // Build conversation history (Groq/OpenAI format)
        $messages = [
            ['role' => 'system', 'content' => $this->systemPrompt],
        ];

        foreach ($request->history ?? [] as $msg) {
            $messages[] = [
                'role'    => $msg['role'],
                'content' => $msg['content'],
            ];
        }

        $messages[] = [
            'role'    => 'user',
            'content' => $request->message,
        ];

        // Call Groq API (OpenAI-compatible)
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.claude.api_key'),
            'Content-Type'  => 'application/json',
        ])->post('https://api.groq.com/openai/v1/chat/completions', [
            'model'      => config('services.claude.model'),
            'max_tokens' => 1024,
            'messages'   => $messages,
        ]);

        if ($response->failed()) {
            \Log::error('Groq API error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return response()->json([
                'message' => 'AI service temporarily unavailable. Please try again.',
            ], 503);
        }

        $reply = $response->json('choices.0.message.content');

        return response()->json([
            'reply'   => $reply,
            'message' => $request->message,
        ]);
    }
}