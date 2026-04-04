<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $apiUrl;
    protected $apiToken;
    protected $broadcastNumber; // Nomor WA kesiswaan untuk broadcast

    public function __construct()
    {
        $this->apiUrl = env('WHATSAPP_API_URL');
        $this->apiToken = env('WHATSAPP_API_TOKEN');
        $this->broadcastNumber = env('WHATSAPP_BROADCAST_NUMBER', '6281234567890'); // Nomor WA kesiswaan
    }

    public function sendMessage(string $phone, string $message): array
    {
        if (empty($this->apiUrl) || empty($this->apiToken)) {
            return ['success' => false, 'error' => 'Config WA tidak lengkap'];
        }

        try {
            $formattedPhone = $this->formatPhoneNumber($phone);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Content-Type' => 'application/json',
            ])
            ->withOptions([
                'verify' => false,
                'timeout' => 8,
            ])
            ->retry(3, 200)
            ->post($this->apiUrl, [
                'messaging_product' => 'whatsapp',
                'to' => $formattedPhone,
                'type' => 'text',
                'text' => [
                    'body' => $message
                ]
            ]);

            $body = $response->json();

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message_id' => $body['messages'][0]['id'] ?? null
                ];
            }

            return [
                'success' => false,
                'error' => $body['error']['message'] ?? 'Unknown error'
            ];

        } catch (\Exception $e) {
            Log::error('WA Error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Kirim notifikasi absensi ke nomor broadcast (kesiswaan)
     */
    public function sendAttendanceNotification($student, $attendance, $type): array
    {
        $message = $this->createAttendanceMessage($student, $attendance, $type);

        // Kirim ke nomor broadcast (kesiswaan) bukan ke orang tua
        return $this->sendMessage($this->broadcastNumber, $message);
    }

    /**
     * Kirim pesan test ke nomor broadcast
     */
    public function sendTestMessage(string $message): array
    {
        return $this->sendMessage($this->broadcastNumber, $message);
    }

    private function formatPhoneNumber(string $phone): string
    {
        // Hapus semua karakter non-digit
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Jika diawali 0, ganti dengan 62
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }

        return $phone;
    }

public function createAttendanceMessage($student, $attendance, $type): string
{
    $time = $type === 'check_in' ? $attendance->check_in : $attendance->check_out;
    $date = $attendance->date;
    $status = $attendance->status === 'late' ? 'TERLAMBAT' : 'TEPAT WAKTU';

    // Format nomor telepon orang tua
    $parentPhone = $student->parent->phone ?? 'Tidak tersedia';

    if ($type === 'check_in') {
        return "📚 *NOTIFIKASI ABSENSI SISWA* 📚\n\n" .
               "Nama Siswa: *{$student->name}*\n" .
               "Kelas: *{$student->class}*\n" .
               "Nama Orang Tua: *{$student->parent->name}*\n" .
               "No. Orang Tua: *{$parentPhone}*\n\n" .
               "Melakukan *ABSEN MASUK* pada:\n" .
               "📅 Tanggal: {$date}\n" .
               "⏰ Pukul: {$time}\n" .
               "✅ Status: {$status}\n\n" .
               "Terima kasih.";
    } else {
        return "📚 *NOTIFIKASI ABSENSI SISWA* 📚\n\n" .
               "Nama Siswa: *{$student->name}*\n" .
               "Kelas: *{$student->class}*\n" .
               "Nama Orang Tua: *{$student->parent->name}*\n" .
               "No. Orang Tua: *{$parentPhone}*\n\n" .
               "Melakukan *ABSEN PULANG* pada:\n" .
               "📅 Tanggal: {$date}\n" .
               "⏰ Pukul: {$time}\n\n" .
               "Terima kasih.";
    }
}
}
