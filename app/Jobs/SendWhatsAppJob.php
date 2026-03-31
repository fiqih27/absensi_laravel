<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendWhatsAppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 15;

    public function __construct(
        public $student,
        public $attendance,
        public $type
    ) {}

    public function handle(): void
    {
        if (!$this->student->parent) return;

        // 🔥 anti duplicate
        if (Notification::where('attendance_id', $this->attendance->id)->exists()) {
            return;
        }

        $wa = new WhatsAppService();

        $message = $wa->createAttendanceMessage(
            $this->student,
            $this->attendance,
            $this->type
        );

        $result = $wa->sendMessage(
            $this->student->parent->phone,
            $message
        );

        Notification::create([
            'attendance_id' => $this->attendance->id,
            'recipient_phone' => $this->student->parent->phone,
            'message' => $message,
            'status' => $result['success'] ? 'sent' : 'failed',
            'wa_response' => json_encode($result),
        ]);

        usleep(300000); // 🔥 rate limit
    }
}
