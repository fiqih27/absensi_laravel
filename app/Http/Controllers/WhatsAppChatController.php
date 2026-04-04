<?php

namespace App\Http\Controllers;

use App\Models\WhatsAppConversation;
use App\Models\WhatsAppMessage;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppChatController extends Controller
{
    /**
     * Tampilkan dashboard chat (seperti WhatsApp Web)
     */
    public function index()
    {
        $conversations = WhatsAppConversation::orderBy('last_message_at', 'desc')
            ->orderBy('unread_count', 'desc')
            ->get();

        return view('whatsapp.chat', compact('conversations'));
    }

    /**
     * Ambil pesan dari conversation tertentu
     */
    public function getMessages($conversationId)
    {
        $conversation = WhatsAppConversation::findOrFail($conversationId);

        // Reset unread count
        $conversation->update(['unread_count' => 0]);

        $messages = WhatsAppMessage::where('conversation_id', $conversationId)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'conversation' => $conversation,
            'messages' => $messages
        ]);
    }

    /**
     * Kirim pesan balasan manual
     */
    public function sendMessage(Request $request, WhatsAppService $waService)
    {
        $request->validate([
            'conversation_id' => 'required|exists:whatsapp_conversations,id',
            'message' => 'required|string'
        ]);

        $conversation = WhatsAppConversation::findOrFail($request->conversation_id);

        // Kirim via WhatsApp API
        $result = $waService->sendMessage($conversation->phone_number, $request->message);

        // Simpan pesan outgoing ke database
        $message = WhatsAppMessage::create([
            'conversation_id' => $conversation->id,
            'message_id' => $result['message_id'] ?? null,
            'direction' => 'outgoing',
            'message' => $request->message,
            'status' => $result['success'] ? 'sent' : 'failed',
            'sent_at' => now(),
        ]);

        // Update last message di conversation
        $conversation->update([
            'last_message' => $request->message,
            'last_message_at' => now(),
        ]);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'error' => $result['error'] ?? 'Gagal mengirim'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }


    public function deleteConversation($id)
{
    $conversation = WhatsAppConversation::findOrFail($id);
    $conversation->delete();

    if (request()->ajax()) {
        return response()->json(['success' => true]);
    }
    return redirect()->route('whatsapp.chat')->with('success', 'Percakapan dihapus');
}

public function archiveConversation($id)
{
    $conversation = WhatsAppConversation::findOrFail($id);
    $conversation->update(['is_archived' => true]);

    if (request()->ajax()) {
        return response()->json(['success' => true]);
    }
    return redirect()->route('whatsapp.chat')->with('success', 'Percakapan diarsipkan');
}

public function getContacts()
{
    $conversations = WhatsAppConversation::orderBy('last_message_at', 'desc')
        ->orderBy('unread_count', 'desc')
        ->get();

    $totalUnread = $conversations->sum('unread_count');

    return response()->json([
        'success' => true,
        'conversations' => $conversations,
        'total_unread' => $totalUnread
    ]);
}

public function markAsRead($conversationId)
{
    $conversation = WhatsAppConversation::findOrFail($conversationId);
    $conversation->update(['unread_count' => 0]);

    $totalUnread = WhatsAppConversation::sum('unread_count');

    return response()->json([
        'success' => true,
        'total_unread' => $totalUnread
    ]);
}

}
