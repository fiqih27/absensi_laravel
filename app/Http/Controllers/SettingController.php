<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    /**
     * Menampilkan halaman pengaturan
     */
    public function index()
    {
        $settings = [
            'app_name' => env('APP_NAME', 'Absensi Alfaiz'),
            'whatsapp_api_url' => env('WHATSAPP_API_URL', ''),
            'whatsapp_api_token' => env('WHATSAPP_API_TOKEN', ''),
            'whatsapp_broadcast_number' => Cache::get('whatsapp_broadcast_number', ''), // Tambahkan ini
            'sync_interval' => Cache::get('sync_interval', 5),
            'auto_sync' => Cache::get('auto_sync', true),
            'send_notification' => Cache::get('send_notification', true),
            'late_threshold' => Cache::get('late_threshold', '07:00'),
            'school_name' => Cache::get('school_name', 'Privat Alfaiz'),
            'school_address' => Cache::get('school_address', ''),
            'school_phone' => Cache::get('school_phone', ''),
        ];

        return view('settings.index', compact('settings'));
    }

    /**
     * Update pengaturan umum
     */
    public function updateGeneral(Request $request)
    {
        $request->validate([
            'app_name' => 'required|string|max:100',
            'school_name' => 'required|string|max:100',
            'school_address' => 'nullable|string',
            'school_phone' => 'nullable|string|max:15',
            'late_threshold' => 'required|date_format:H:i',
        ]);

        // Update .env file untuk APP_NAME
        $this->updateEnv('APP_NAME', $request->app_name);

        // Update cache untuk pengaturan lainnya
        Cache::put('school_name', $request->school_name, 86400);
        Cache::put('school_address', $request->school_address, 86400);
        Cache::put('school_phone', $request->school_phone, 86400);
        Cache::put('late_threshold', $request->late_threshold, 86400);

        return redirect()->back()->with('success', 'Pengaturan umum berhasil disimpan');
    }

    /**
     * Update pengaturan WhatsApp
     */
    public function updateWhatsApp(Request $request)
    {
        $request->validate([
            'whatsapp_api_url' => 'required|url',
            'whatsapp_api_token' => 'required|string',
            'send_notification' => 'boolean',
            'whatsapp_broadcast_number' => 'nullable|string|regex:/^[0-9]{10,15}$/', // Validasi nomor HP
        ]);

        // Update .env file
        $this->updateEnv('WHATSAPP_API_URL', $request->whatsapp_api_url);
        $this->updateEnv('WHATSAPP_API_TOKEN', $request->whatsapp_api_token);

        // Update cache untuk pengaturan WhatsApp
        Cache::put('send_notification', $request->has('send_notification'), 86400);

        // Simpan nomor broadcast ke cache
        $broadcastNumber = $request->whatsapp_broadcast_number;
        if (!empty($broadcastNumber)) {
            // Format nomor broadcast
            $broadcastNumber = $this->formatPhoneNumber($broadcastNumber);
            Cache::put('whatsapp_broadcast_number', $broadcastNumber, 86400);
        } else {
            Cache::forget('whatsapp_broadcast_number');
        }

        return redirect()->back()->with('success', 'Pengaturan WhatsApp berhasil disimpan');
    }

    /**
     * Update pengaturan sinkronisasi
     */
    public function updateSync(Request $request)
    {
        $request->validate([
            'sync_interval' => 'required|integer|min:1|max:60',
            'auto_sync' => 'boolean',
        ]);

        Cache::put('sync_interval', $request->sync_interval, 86400);
        Cache::put('auto_sync', $request->has('auto_sync'), 86400);

        return redirect()->back()->with('success', 'Pengaturan sinkronisasi berhasil disimpan');
    }

    /**
     * Test koneksi WhatsApp
     */
    public function testWhatsApp(Request $request)
    {
        $request->validate([
            'test_phone' => 'nullable|string', // Ubah menjadi nullable
        ]);

        $waService = new \App\Services\WhatsAppService();

        // Jika test_phone kosong, gunakan nomor broadcast
        $testPhone = $request->test_phone;
        if (empty($testPhone)) {
            $broadcastNumber = Cache::get('whatsapp_broadcast_number');
            if (empty($broadcastNumber)) {
                return redirect()->back()->with('error', 'Mohon isi nomor WhatsApp tujuan test atau set nomor broadcast terlebih dahulu');
            }
            $testPhone = $broadcastNumber;
        }

        $result = $waService->sendMessage(
            $testPhone,
            "Bismillah fiqih disini Test koneksi WhatsApp dari Sistem Absensi Alfaiz\nWaktu: " . now()->format('d/m/Y H:i:s')
        );

        if ($result['success']) {
            return redirect()->back()->with('success', 'Test WhatsApp berhasil dikirim ke ' . $testPhone);
        }

        return redirect()->back()->with('error', 'Test WhatsApp gagal: ' . ($result['error'] ?? 'Unknown error'));
    }

    /**
     * Test koneksi device ZKTeco
     */
    public function testDevice(Request $request)
    {
        $request->validate([
            'device_id' => 'required|exists:devices,id',
        ]);

        $device = \App\Models\Device::find($request->device_id);
        $zkService = new \App\Services\ZKTecoService($device);

        $connected = $zkService->connect();

        if ($connected) {
            $info = $zkService->getDeviceInfo();
            $zkService->disconnect();
            return redirect()->back()->with('success', "Device {$device->device_name} online. " . json_encode($info));
        }

        return redirect()->back()->with('error', "Device {$device->device_name} offline/tidak terhubung");
    }

    /**
     * Clear cache sistem
     */
    public function clearCache()
    {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');

        return redirect()->back()->with('success', 'Cache sistem berhasil dibersihkan');
    }

    /**
     * Backup database
     */
    public function backup()
    {
        $database = env('DB_DATABASE');
        $username = env('DB_USERNAME');
        $password = env('DB_PASSWORD');
        $host = env('DB_HOST');

        $backupFile = storage_path('backups/backup-' . date('Y-m-d-H-i-s') . '.sql');

        // Pastikan folder backup ada
        if (!is_dir(storage_path('backups'))) {
            mkdir(storage_path('backups'), 0755, true);
        }

        $command = "mysqldump --host={$host} --user={$username} --password={$password} {$database} > {$backupFile}";
        system($command, $output);

        if (file_exists($backupFile)) {
            return redirect()->back()->with('success', 'Backup database berhasil dibuat: ' . basename($backupFile));
        }

        return redirect()->back()->with('error', 'Backup database gagal');
    }

    /**
     * Helper untuk update .env file
     */
    private function updateEnv($key, $value)
    {
        $path = base_path('.env');

        if (file_exists($path)) {
            // Escape value jika mengandung spasi atau karakter khusus
            if (strpos($value, ' ') !== false || strpos($value, '#') !== false) {
                $value = '"' . $value . '"';
            }

            // Cek apakah key sudah ada di .env
            $content = file_get_contents($path);
            if (preg_match("/^{$key}=.*/m", $content)) {
                // Update existing key
                file_put_contents($path, preg_replace(
                    "/^{$key}=.*/m",
                    "{$key}={$value}",
                    $content
                ));
            } else {
                // Add new key
                file_put_contents($path, "\n{$key}={$value}", FILE_APPEND);
            }
        }
    }

    /**
     * Format nomor telepon
     */
    private function formatPhoneNumber($phone)
    {
        // Hapus semua karakter non-digit
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Jika diawali 0, ganti dengan 62
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }

        return $phone;
    }
}
