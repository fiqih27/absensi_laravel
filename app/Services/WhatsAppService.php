<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $apiUrl;
    protected $apiToken;

    public function __construct()
    {
        $this->apiUrl = env('WHATSAPP_API_URL');
        $this->apiToken = env('WHATSAPP_API_TOKEN');
    }

    public function sendMessage(string $phone, string $message): array
{
    if (empty($this->apiUrl) || empty($this->apiToken)) {
        return ['success' => false, 'error' => 'Config WA tidak lengkap'];
    }

    try {
        $formattedPhone = $this->formatPhoneNumber($phone);

      $response = Http::withHeaders([
    'Authorization' => $this->apiToken,
    ])
    ->withOptions([
              'verify' => false,
             'timeout' => 8,
             ])
    ->retry(3, 200)
    ->post($this->apiUrl, [
    'target' => $formattedPhone,
    'message' => $message,
    ]);

        $body = $response->json();

        if ($response->successful() && ($body['status'] ?? false)) {
            return [
                'success' => true,
                'message_id' => $body['id'] ?? null
            ];
        }

        return [
            'success' => false,
            'error' => $body['reason'] ?? 'Unknown error'
        ];

    } catch (\Exception $e) {
        Log::error('WA Error: ' . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

    private function formatPhoneNumber(string $phone): string
    {
        // Hapus semua karakter non-digit
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Jika diawali 0, ganti dengan 62
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }

        // Jika diawali 62, pastikan formatnya benar
        if (substr($phone, 0, 2) === '62') {
            $phone = $phone;
        }

        return $phone;
    }

    public function createAttendanceMessage($student, $attendance, $type): string
    {
        $time = $type === 'check_in' ? $attendance->check_in : $attendance->check_out;
        $date = $attendance->date;
        $status = $attendance->status === 'late' ? 'TERLAMBAT' : 'TEPAT WAKTU';

        if ($type === 'check_in') {
            return "📚 *Notifikasi Absensi Siswa* 📚\n\n" .
                   "Assalamu'alaikum Bpk/Ibu {$student->parent->name}\n\n" .
                   "Ananda *{$student->name}* (Kelas: {$student->class})\n" .
                   "Telah melakukan *ABSEN MASUK* pada:\n" .
                   "📅 Tanggal: {$date}\n" .
                   "⏰ Pukul: {$time}\n" .
                   "✅ Status: {$status}\n\n" .
                   "Terima kasih atas perhatiannya.\n" .
                   "Wassalamu'alaikum wr.wb.";
        } else {
            return "📚 *Notifikasi Absensi Siswa* 📚\n\n" .
                   "Assalamu'alaikum Bpk/Ibu {$student->parent->name}\n\n" .
                   "Ananda *{$student->name}* (Kelas: {$student->class})\n" .
                   "Telah melakukan *ABSEN PULANG* pada:\n" .
                   "📅 Tanggal: {$date}\n" .
                   "⏰ Pukul: {$time}\n\n" .
                   "Terima kasih atas perhatiannya.\n" .
                   "Wassalamu'alaikum wr.wb.";
        }
    }
}
