<?php

use Illuminate\Support\Facades\Schedule;
use App\Services\ZKTecoService;
use App\Services\WhatsAppService;
use App\Models\Device;
use App\Models\Attendance;
use App\Models\Notification;

// Sinkronisasi absensi setiap 5 menit
Schedule::call(function () {
    $devices = Device::all();
    $totalSynced = 0;
    $totalFailed = 0;

    foreach ($devices as $device) {
        $zkService = new ZKTecoService($device);
        $result = $zkService->syncAttendance();

        $totalSynced += $result['synced'];
        $totalFailed += $result['failed'];

        // Kirim notifikasi WhatsApp untuk setiap absensi baru
        foreach ($result['results'] as $record) {
            $attendance = $record['attendance'];
            $student = $record['student'];
            $type = $record['type'];

            if ($student->parent) {
                $waService = new WhatsAppService();
                $message = $waService->createAttendanceMessage($student, $attendance, $type);

                $sendResult = $waService->sendMessage($student->parent->phone, $message);

                Notification::create([
                    'attendance_id' => $attendance->id,
                    'recipient_phone' => $student->parent->phone,
                    'message' => $message,
                    'status' => $sendResult['success'] ? 'sent' : 'failed',
                    'wa_response' => $sendResult,
                ]);
            }
        }
    }

    \Illuminate\Support\Facades\Log::info("Attendance sync completed: {$totalSynced} synced, {$totalFailed} failed");

})->everyFiveMinutes()->name('sync-attendance')->withoutOverlapping();

// Cek status device setiap 10 menit
Schedule::call(function () {
    $devices = Device::all();

    foreach ($devices as $device) {
        $zkService = new ZKTecoService($device);
        $connected = $zkService->connect();

        if ($connected) {
            $zkService->disconnect();
        }
    }
})->everyTenMinutes()->name('check-device-status');
