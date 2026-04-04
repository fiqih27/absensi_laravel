<?php

namespace App\Services;

use Jmrashed\Zkteco\Lib\ZKTeco;
use App\Models\Device;
use App\Models\Student;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ZKTecoService
{
    protected $device;
    protected $zk;

    public function __construct(Device $device)
    {
        $this->device = $device;
        $this->zk = new ZKTeco($device->ip_address, $device->port);
    }

    /**
     * Connect ke device ZKTeco
     */
    public function connect(): bool
    {
        try {
            $connected = $this->zk->connect();
            if ($connected) {
                $this->device->update(['status' => 'online']);
                return true;
            }
            $this->device->update(['status' => 'offline']);
            return false;
        } catch (\Exception $e) {
            Log::error('ZKTeco Connection Error: ' . $e->getMessage());
            $this->device->update(['status' => 'offline']);
            return false;
        }
    }

    /**
     * Disconnect dari device
     */
    public function disconnect(): bool
    {
        try {
            return $this->zk->disconnect();
        } catch (\Exception $e) {
            Log::error('ZKTeco Disconnect Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Disable device untuk operasi
     */
    public function disableDevice(): bool
    {
        try {
            return $this->zk->disableDevice();
        } catch (\Exception $e) {
            Log::error('Disable Device Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Enable device setelah operasi
     */
    public function enableDevice(): bool
    {
        try {
            return $this->zk->enableDevice();
        } catch (\Exception $e) {
            Log::error('Enable Device Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Ambil semua user dari device
     */
    public function getUsers(): array
    {
        try {
            $this->connect();
            $this->disableDevice();
            $users = $this->zk->getUser();
            $this->enableDevice();
            $this->disconnect();
            return $users;
        } catch (\Exception $e) {
            Log::error('Get Users Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Ambil semua log absensi dari device
     */
    public function getAttendance(): array
    {
        try {
            $this->connect();
            $this->disableDevice();
            $attendance = $this->zk->getAttendance();
            $this->enableDevice();
            $this->disconnect();
            return $attendance;
        } catch (\Exception $e) {
            Log::error('Get Attendance Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Sinkronisasi data absensi ke database (OPTIMIZED - tanpa setTimeout)
     */
    public function syncAttendance(): array
    {
        // Reset time limit untuk proses ini
        if (function_exists('set_time_limit')) {
            set_time_limit(300);
        }

        $logs = $this->getAttendance();

        // Batasi jumlah log yang diproses (jika terlalu banyak)
        $logs = array_slice($logs, 0, 500);

        $synced = 0;
        $failed = 0;
        $results = [];

        // Ambil semua student ID sekali untuk caching
        $studentsMap = Student::where('status', 'active')
            ->pluck('id', 'device_user_id')
            ->toArray();

        foreach ($logs as $log) {
            // Reset time limit setiap 10 iterasi
            if ($synced % 10 === 0 && function_exists('set_time_limit')) {
                set_time_limit(30);
            }

            try {
                $deviceUserId = (string)$log['id'];

                // Cek dari cache array
                if (!isset($studentsMap[$deviceUserId])) {
                    $failed++;
                    continue;
                }

                $studentId = $studentsMap[$deviceUserId];
                $timestamp = Carbon::parse($log['timestamp']);
                $date = $timestamp->toDateString();
                $time = $timestamp->toTimeString();

                // Tentukan apakah check_in atau check_out
                $isCheckIn = $timestamp->hour < 12;

                // Gunakan firstOrNew untuk efisiensi
                $attendance = Attendance::firstOrNew([
                    'student_id' => $studentId,
                    'date' => $date,
                ]);

                $isNewData = false;

                if ($isCheckIn && is_null($attendance->check_in)) {
                    $attendance->check_in = $time;
                    $attendance->status = $timestamp->hour > 7 ? 'late' : 'present';
                    $isNewData = true;
                } elseif (!$isCheckIn && is_null($attendance->check_out)) {
                    $attendance->check_out = $time;
                    $isNewData = true;
                }

                if ($isNewData) {
                    $attendance->device_id = $this->device->id;
                    $attendance->verification_method = $this->getVerificationMethod($log['state']);
                    $attendance->save();

                    $results[] = [
                        'attendance' => $attendance,
                        'student' => Student::find($studentId),
                        'type' => $isCheckIn ? 'check_in' : 'check_out'
                    ];
                }

                $synced++;

            } catch (\Exception $e) {
                Log::error('Sync Attendance Error: ' . $e->getMessage());
                $failed++;
            }
        }

        return [
            'synced' => $synced,
            'failed' => $failed,
            'results' => $results
        ];
    }

    /**
     * Sinkronisasi hanya data baru (lebih cepat)
     */
    public function syncNewAttendance(): array
    {
        if (function_exists('set_time_limit')) {
            set_time_limit(300);
        }

        $logs = $this->getAttendance();

        // Ambil timestamp sinkronisasi terakhir
        $lastSyncTime = Attendance::max('created_at');

        // Filter log yang lebih baru dari lastSyncTime
        if ($lastSyncTime) {
            $logs = array_filter($logs, function($log) use ($lastSyncTime) {
                return Carbon::parse($log['timestamp']) > $lastSyncTime;
            });
        }

        // Batasi jumlah
        $logs = array_slice($logs, 0, 200);

        $synced = 0;
        $failed = 0;
        $results = [];

        $studentsMap = Student::where('status', 'active')
            ->pluck('id', 'device_user_id')
            ->toArray();

        foreach ($logs as $log) {
            try {
                $deviceUserId = (string)$log['id'];

                if (!isset($studentsMap[$deviceUserId])) {
                    $failed++;
                    continue;
                }

                $studentId = $studentsMap[$deviceUserId];
                $timestamp = Carbon::parse($log['timestamp']);
                $date = $timestamp->toDateString();
                $time = $timestamp->toTimeString();
                $isCheckIn = $timestamp->hour < 12;

                $attendance = Attendance::firstOrNew([
                    'student_id' => $studentId,
                    'date' => $date,
                ]);

                if ($isCheckIn && is_null($attendance->check_in)) {
                    $attendance->check_in = $time;
                    $attendance->status = $timestamp->hour > 7 ? 'late' : 'present';
                    $attendance->device_id = $this->device->id;
                    $attendance->verification_method = $this->getVerificationMethod($log['state']);
                    $attendance->save();

                    $results[] = [
                        'attendance' => $attendance,
                        'student' => Student::find($studentId),
                        'type' => 'check_in'
                    ];
                    $synced++;
                } elseif (!$isCheckIn && is_null($attendance->check_out)) {
                    $attendance->check_out = $time;
                    $attendance->save();

                    $results[] = [
                        'attendance' => $attendance,
                        'student' => Student::find($studentId),
                        'type' => 'check_out'
                    ];
                    $synced++;
                }

            } catch (\Exception $e) {
                Log::error('Sync New Attendance Error: ' . $e->getMessage());
                $failed++;
            }
        }

        return [
            'synced' => $synced,
            'failed' => $failed,
            'results' => $results
        ];
    }

    /**
     * Sinkronisasi dengan chunk (untuk data sangat besar)
     */
    public function syncAttendanceChunked(int $chunkSize = 50): array
    {
        if (function_exists('set_time_limit')) {
            set_time_limit(300);
        }

        $logs = $this->getAttendance();
        $totalLogs = count($logs);

        $synced = 0;
        $failed = 0;
        $results = [];

        $studentsMap = Student::where('status', 'active')
            ->pluck('id', 'device_user_id')
            ->toArray();

        // Proses dalam chunk
        for ($i = 0; $i < $totalLogs; $i += $chunkSize) {
            $chunk = array_slice($logs, $i, $chunkSize);

            foreach ($chunk as $log) {
                try {
                    $deviceUserId = (string)$log['id'];

                    if (!isset($studentsMap[$deviceUserId])) {
                        $failed++;
                        continue;
                    }

                    $studentId = $studentsMap[$deviceUserId];
                    $timestamp = Carbon::parse($log['timestamp']);
                    $date = $timestamp->toDateString();
                    $time = $timestamp->toTimeString();
                    $isCheckIn = $timestamp->hour < 12;

                    $attendance = Attendance::updateOrCreate(
                        [
                            'student_id' => $studentId,
                            'date' => $date,
                        ],
                        [
                            'device_id' => $this->device->id,
                            'verification_method' => $this->getVerificationMethod($log['state']),
                        ]
                    );

                    if ($isCheckIn && !$attendance->check_in) {
                        $attendance->check_in = $time;
                        $attendance->status = $timestamp->hour > 7 ? 'late' : 'present';
                        $attendance->save();

                        $results[] = [
                            'attendance' => $attendance,
                            'student' => Student::find($studentId),
                            'type' => 'check_in'
                        ];
                        $synced++;
                    } elseif (!$isCheckIn && !$attendance->check_out) {
                        $attendance->check_out = $time;
                        $attendance->save();

                        $results[] = [
                            'attendance' => $attendance,
                            'student' => Student::find($studentId),
                            'type' => 'check_out'
                        ];
                        $synced++;
                    }

                } catch (\Exception $e) {
                    Log::error('Sync Chunk Error: ' . $e->getMessage());
                    $failed++;
                }
            }

            // Jeda antar chunk
            if ($i + $chunkSize < $totalLogs) {
                usleep(100000); // 0.1 detik
            }
        }

        return [
            'synced' => $synced,
            'failed' => $failed,
            'results' => $results
        ];
    }

    /**
     * Konversi metode verifikasi
     */
    private function getVerificationMethod($state): string
    {
        return match ($state) {
            0 => 'password',
            1 => 'fingerprint',
            2 => 'rfid',
            3 => 'face',
            default => 'unknown',
        };
    }

    /**
     * Dapatkan informasi device
     */
    public function getDeviceInfo(): array
    {
        try {
            $this->connect(); 
            $this->disableDevice();

            $info = [
                'version' => $this->zk->version(),
                'platform' => $this->zk->platform(),
                'serial' => $this->zk->serialNumber(),
                'device_name' => $this->zk->deviceName(),
                'time' => $this->zk->getTime(),
            ];

            $this->enableDevice();
            $this->disconnect();

            return $info;
        } catch (\Exception $e) {
            Log::error('Get Device Info Error: ' . $e->getMessage());
            return [];
        }
    }
}
