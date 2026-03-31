<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Device;
use App\Services\ZKTecoService;
use App\Jobs\SendWhatsAppJob;
use Illuminate\Support\Facades\Log;

class RealtimeAttendance extends Command
{
    protected $signature = 'attendance:realtime';
    protected $description = 'Realtime attendance system';

    public function handle()
    {
        $this->info('🚀 Realtime attendance running...');

        while (true) {

            try {
                $devices = Device::all();

                if ($devices->isEmpty()) {
                    sleep(5);
                    continue;
                }

                foreach ($devices as $device) {
                    try {

                        $zk = new ZKTecoService($device);

                        if (!$zk->connect()) {
                            continue;
                        }

                        set_time_limit(10);

                        $result = $zk->syncAttendance();

                        $zk->disconnect();

                        if (empty($result['results'])) {
                            continue;
                        }

                        foreach ($result['results'] as $record) {

                            SendWhatsAppJob::dispatch(
                                $record['student'],
                                $record['attendance'],
                                $record['type']
                            );
                        }

                    } catch (\Exception $e) {
                        Log::error("Device error: " . $e->getMessage());
                        continue;
                    }
                }

                sleep(7); // realtime interval

            } catch (\Exception $e) {
                Log::error("Loop error: " . $e->getMessage());
                sleep(5);
            }
        }
    }
}
