<?php

namespace App\Http\Controllers\Api;

use App\Events\NewMessage;
use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function conversations(Request $request)
    {
        $userId = $request->user()->id;

        $conversations = Message::with(['sender', 'receiver'])
            ->where('barangay_id', $request->user()->barangay_id)
            ->where(fn($q) => $q
                ->where('sender_id', $userId)
                ->orWhere('receiver_id', $userId)
            )
            ->latest()
            ->get()
            ->groupBy(fn($m) => $m->sender_id === $userId
                ? $m->receiver_id
                : $m->sender_id
            )
            ->map(fn($msgs, $otherId) => [
                'user'         => User::find($otherId)?->only(['id','name','email','role']),
                'last_message' => $msgs->first()->body,
                'time'         => $msgs->first()->created_at->diffForHumans(),
                'unread'       => $msgs->where('receiver_id', $userId)
                                       ->where('is_read', false)->count(),
            ])
            ->values();

        return response()->json(['conversations' => $conversations]);
    }

    public function thread(Request $request, $userId)
    {
        $authId = $request->user()->id;

        Message::where('sender_id', $userId)
            ->where('receiver_id', $authId)
            ->update(['is_read' => true]);

        $messages = Message::with('sender')
            ->where('barangay_id', $request->user()->barangay_id)
            ->where(fn($q) => $q
                ->where(fn($q2) => $q2
                    ->where('sender_id', $authId)
                    ->where('receiver_id', $userId))
                ->orWhere(fn($q2) => $q2
                    ->where('sender_id', $userId)
                    ->where('receiver_id', $authId))
            )
            ->oldest()
            ->get()
            ->map(fn($m) => [
                'id'          => $m->id,
                'body'        => $m->body,
                'sender_id'   => $m->sender_id,
                'sender_name' => $m->sender?->name,
                'is_mine'     => $m->sender_id === $authId,
                'time'        => $m->created_at->format('h:i A'),
                'date'        => $m->created_at->format('M d, Y'),
            ]);

        return response()->json(['messages' => $messages]);
    }

    public function send(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'body'        => 'required|string|max:1000',
        ]);

        $message = Message::create([
            'barangay_id' => $request->user()->barangay_id,
            'sender_id'   => $request->user()->id,
            'receiver_id' => $request->receiver_id,
            'body'        => $request->body,
        ]);

        $message->load('sender');
        broadcast(new NewMessage($message))->toOthers();

        return response()->json([
            'message' => [
                'id'          => $message->id,
                'body'        => $message->body,
                'sender_id'   => $message->sender_id,
                'sender_name' => $message->sender->name,
                'is_mine'     => true,
                'time'        => $message->created_at->format('h:i A'),
                'date'        => $message->created_at->format('M d, Y'),
            ],
        ], 201);
    }

    public function contacts(Request $request)
    {
        $contacts = User::where('barangay_id', $request->user()->barangay_id)
            ->where('id', '!=', $request->user()->id)
            ->whereIn('role', ['admin', 'staff'])
            ->get()
            ->map(fn($u) => $u->only(['id', 'name', 'email', 'role']));

        return response()->json(['contacts' => $contacts]);
    }
}