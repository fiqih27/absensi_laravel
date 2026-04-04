<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WhatsAppConversation;
use App\Models\WhatsAppMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    protected $verifyToken;

    public function __construct()
    {
        // VERIFY TOKEN - harus SAMA PERSIS dengan yang di Meta Console
        $this->verifyToken = env('WHATSAPP_VERIFY_TOKEN', 'your_secret_token_here');
    }

    /**
     * GET /api/whatsapp/webhook
     * Untuk verifikasi webhook oleh Meta
     */
    public function verify(Request $request)
    {
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        Log::info('Webhook verification attempt', [
            'mode' => $mode,
            'token' => $token,
            'expected_token' => $this->verifyToken
        ]);

        // Meta akan mengirim mode 'subscribe' untuk verifikasi [citation:3]
        if ($mode === 'subscribe' && $token === $this->verifyToken) {
            Log::info('Webhook verified successfully!');
            return response($challenge, 200);
        }

        Log::warning('Webhook verification failed', [
            'received_token' => $token,
            'expected_token' => $this->verifyToken
        ]);

        return response('Forbidden', 403);
    }

    /**
     * POST /api/whatsapp/webhook
     * Menerima pesan masuk dari Meta
     */
    public function handleWebhook(Request $request)
    {
        $payload = $request->all();

        Log::info('Webhook received', ['payload' => $payload]);

        // Cek apakah ini pesan dari customer [citation:2][citation:3]
        if (isset($payload['entry'][0]['changes'][0]['value']['messages'][0])) {
            $message = $payload['entry'][0]['changes'][0]['value']['messages'][0];
            $from = $message['from']; // Nomor pengirim
            $text = $message['text']['body'] ?? ''; // Isi pesan
            $messageId = $message['id'] ?? null;
            $timestamp = $message['timestamp'] ?? time();

            Log::info('New message received', [
                'from' => $from,
                'message' => $text,
                'message_id' => $messageId
            ]);

            // Cari atau buat conversation
            $conversation = WhatsAppConversation::firstOrCreate(
                ['phone_number' => $from],
                [
                    'contact_name' => $from,
                    'last_message_at' => now(),
                    'unread_count' => 1
                ]
            );

            // Increment unread count (biar muncul notifikasi di dashboard)
            $conversation->increment('unread_count');

            // Simpan pesan masuk ke database
            WhatsAppMessage::create([
                'conversation_id' => $conversation->id,
                'message_id' => $messageId,
                'direction' => 'incoming',
                'message' => $text,
                'status' => 'delivered',
                'sent_at' => now(),
            ]);

            // Update last message di conversation
            $conversation->update([
                'last_message' => $text,
                'last_message_at' => now(),
            ]);
        }

        // WAJIB: return 200 OK [citation:3]
        // Meta akan timeout jika tidak dapat response dalam 20 detik
        return response('OK', 200);
    }
}
