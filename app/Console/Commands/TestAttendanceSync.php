<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Device;
use App\Services\ZKTecoService;

class TestAttendanceSync extends Command
{
    protected $signature = 'attendance:test {device_id?}';
    protected $description = 'Test attendance sync from ZKTeco devices';

    public function handle()
    {
        $deviceId = $this->argument('device_id');

        $devices = Device::query();
        if ($deviceId) {
            $devices->where('id', $deviceId);
        }

        foreach ($devices->get() as $device) {
            $this->info("Testing device: {$device->device_name} ({$device->ip_address})");

            $zkService = new ZKTecoService($device);
            $connected = $zkService->connect();

            if ($connected) {
                $this->info("✓ Connected to device");
                $info = $zkService->getDeviceInfo();
                $this->info("Device Info: " . json_encode($info));
                $zkService->disconnect();
            } else {
                $this->error("✗ Failed to connect to device");
            }
        }

        return Command::SUCCESS;
    }
}
