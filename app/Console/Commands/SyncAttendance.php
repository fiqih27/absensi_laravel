<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Device;
use App\Services\ZKTecoService;
use App\Services\WhatsAppService;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;

class SyncAttendance extends Command
{
    protected $signature = 'attendance:sync {device_id?}';
    protected $description = 'Sync attendance from ZKTeco devices and send WhatsApp notifications';

    public function handle()
    {
        $deviceId = $this->argument('device_id');

        $devices = Device::query();
        if ($deviceId) {
            $devices->where('id', $deviceId);
        }

        foreach ($devices->get() as $device) {
            $this->info("Syncing device: {$device->device_name} ({$device->ip_address})");

            $zkService = new ZKTecoService($device);

            // Gunakan syncNewAttendance untuk efisiensi (hanya data baru)
            $result = $zkService->syncNewAttendance();

            $this->info("Synced: {$result['synced']} records, Failed: {$result['failed']}");

            // Kirim notifikasi untuk setiap absensi baru
            foreach ($result['results'] as $record) {
                $this->sendNotificationForAttendance($record);
            }
        }

        return Command::SUCCESS;
    }

    private function sendNotificationForAttendance($record)
    {
        try {
            // Langsung ambil dari record yang sudah tersedia
            $attendance = $record['attendance'];
            $student = $record['student'];
            $type = $record['type']; // 'check_in' atau 'check_out'

            if ($student && $student->parent) {
                $waService = new WhatsAppService();
                $message = $waService->createAttendanceMessage($student, $attendance, $type);

                $result = $waService->sendMessage($student->parent->phone, $message);

                Notification::create([
                    'attendance_id' => $attendance->id,
                    'recipient_phone' => $student->parent->phone,
                    'message' => $message,
                    'status' => $result['success'] ? 'sent' : 'failed',
                    'wa_response' => json_encode($result),
                ]);

                if ($result['success']) {
                    $this->info("WhatsApp sent to: {$student->parent->phone}");
                } else {
                    $this->error("WhatsApp failed to: {$student->parent->phone} - " . ($result['error'] ?? 'Unknown'));
                }
            }
        } catch (\Exception $e) {
            Log::error('Notification error: ' . $e->getMessage());
            $this->error("Notification error: " . $e->getMessage());
        }
    }
}
