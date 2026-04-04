<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;
use App\Models\Device;
use App\Services\ZKTecoService;
use App\Services\WhatsAppService;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )

        ->withMiddleware(function (Middleware $middleware) {
        // ⬇️ TAMBAHKAN INI ⬇️
        $middleware->validateCsrfTokens(except: [
            'api/whatsapp/webhook',  // Exclude webhook dari CSRF
        ]);
    })
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->withSchedule(function (Schedule $schedule) {

        /**
         * ===============================
         * 🔥 SYNC ABSENSI (ANTI NGGANTUNG)
         * ===============================
         */
        $schedule->call(function () {

            $devices = Device::all();

            // ✅ 1. Jika tidak ada device → stop
            if ($devices->isEmpty()) {
                Log::info('⛔ Tidak ada device terdaftar. Skip sync.');
                return;
            }

            foreach ($devices as $device) {
                try {

                    Log::info("🔄 Sync device: {$device->name}");

                    $zkService = new ZKTecoService($device);

                    // ✅ 2. Cek koneksi dulu
                    if (!$zkService->connect()) {
                        Log::warning("⚠️ Device {$device->name} tidak terhubung.");
                        continue;
                    }

                    // ⏱️ Timeout biar ga ngegantung
                    set_time_limit(10);

                    // ✅ 3. Ambil data absensi
                    $result = $zkService->syncAttendance();

                    // Disconnect setelah selesai
                    $zkService->disconnect();

                    // ✅ 4. Jika tidak ada data → skip
                    if (empty($result['results'])) {
                        Log::info("ℹ️ Tidak ada data baru dari {$device->name}");
                        continue;
                    }

                    foreach ($result['results'] as $record) {

                        $attendance = $record['attendance'];
                        $student = $record['student'];
                        $type = $record['type'];

                        // ✅ 5. Pastikan ada orang tua
                        if (!$student->parent) {
                            continue;
                        }

                        try {
                            $waService = new WhatsAppService();

                            $message = $waService->createAttendanceMessage(
                                $student,
                                $attendance,
                                $type
                            );

                            $sendResult = $waService->sendMessage(
                                $student->parent->phone,
                                $message
                            );

                            Notification::create([
                                'attendance_id'   => $attendance->id,
                                'recipient_phone' => $student->parent->phone,
                                'message'         => $message,
                                'status'          => $sendResult['success'] ? 'sent' : 'failed',
                                'wa_response'     => json_encode($sendResult),
                            ]);

                        } catch (\Exception $e) {
                            Log::error("❌ WA Error: " . $e->getMessage());
                        }
                    }

                } catch (\Exception $e) {
                    Log::error("❌ Sync Error Device {$device->name}: " . $e->getMessage());
                    continue;
                }
            }

        })
        ->everyFiveMinutes()
        ->name('sync-attendance')
        ->withoutOverlapping();



        /**
         * ===============================
         * 🔍 CEK STATUS DEVICE
         * ===============================
         */
        $schedule->call(function () {

            $devices = Device::all();

            if ($devices->isEmpty()) {
                return;
            }

            foreach ($devices as $device) {
                try {
                    $zkService = new ZKTecoService($device);

                    if ($zkService->connect()) {
                        Log::info("✅ Device {$device->name} online");
                        $zkService->disconnect();
                    } else {
                        Log::warning("⚠️ Device {$device->name} offline");
                    }

                } catch (\Exception $e) {
                    Log::error("❌ Device check error {$device->name}: " . $e->getMessage());
                }
            }

        })
        ->everyTenMinutes()
        ->name('check-device-status');



        /**
         * ===============================
         * 🧹 HAPUS NOTIFIKASI LAMA
         * ===============================
         */
        $schedule->call(function () {

            try {
                $deleted = Notification::where('created_at', '<', now()->subDays(30))->delete();
                Log::info("🧹 Hapus {$deleted} notifikasi lama");
            } catch (\Exception $e) {
                Log::error("❌ Cleanup error: " . $e->getMessage());
            }

        })
        ->daily()
        ->name('clean-old-notifications');

    })
    ->create();
