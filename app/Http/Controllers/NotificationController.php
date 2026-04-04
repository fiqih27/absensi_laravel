<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BroadcastHistory;
use App\Models\ParentModel;
use App\Models\Notification;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NotificationController extends Controller
{
    /**
     * Menampilkan daftar notifikasi
     */
    public function index(Request $request)
    {
        $status = $request->status;
        $search = $request->search;

        $notifications = Notification::with('attendance.student')
            ->when($status, function($query) use ($status) {
                return $query->where('status', $status);
            })
            ->when($search, function($query) use ($search) {
                return $query->where('recipient_phone', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total' => Notification::count(),
            'sent' => Notification::where('status', 'sent')->count(),
            'failed' => Notification::where('status', 'failed')->count(),
            'pending' => Notification::where('status', 'pending')->count(),
        ];

        return view('notifications.index', compact('notifications', 'stats', 'status'));
    }

    /**
     * Menampilkan detail notifikasi
     */
    public function show(Notification $notification)
    {
        $notification->load('attendance.student', 'attendance.device');

        return view('notifications.show', compact('notification'));
    }

    /**
     * Mengirim ulang notifikasi yang gagal
     */
    public function resend(Notification $notification, WhatsAppService $waService)
    {
        if ($notification->status !== 'failed') {
            return redirect()->back()->with('error', 'Hanya notifikasi gagal yang dapat dikirim ulang');
        }

        $result = $notification->resend();

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        }

        return redirect()->back()->with('error', $result['message']);
    }

    /**
     * Mengirim ulang semua notifikasi yang gagal
     */
    public function resendAll(WhatsAppService $waService)
    {
        $failedNotifications = Notification::where('status', 'failed')->get();
        $count = 0;

        foreach ($failedNotifications as $notification) {
            $result = $notification->resend();
            if ($result['success']) {
                $count++;
            }
        }

        return redirect()->back()->with('success', "{$count} notifikasi sedang diproses ulang");
    }

    /**
     * Menghapus notifikasi
     */
    public function destroy(Notification $notification)
    {
        $notification->delete();

        return redirect()->route('notifications.index')->with('success', 'Notifikasi berhasil dihapus');
    }

    /**
     * Menghapus semua notifikasi yang sudah lama
     */
    public function deleteOld(Request $request)
    {
        $days = $request->days ?? 30;
        $deleted = Notification::where('created_at', '<', now()->subDays($days))->delete();

        return redirect()->back()->with('success', "{$deleted} notifikasi lama berhasil dihapus");
    }

    /**
     * Mengirim notifikasi broadcast ke semua orang tua
     */
    public function broadcast(Request $request)
    {
        return view('notifications.broadcast');
    }


  public function sendBroadcast(Request $request, WhatsAppService $waService)
    {
        $request->validate([
            'message' => 'required|string',
            'recipients' => 'required|in:all,active_only',
        ]);

        DB::beginTransaction();

        try {
            // Ambil nomor broadcast dari cache (sudah diatur di settings)
            $broadcastNumber = cache('whatsapp_broadcast_number');

            // Cek apakah nomor broadcast sudah diatur
            if (empty($broadcastNumber)) {
                return redirect()->route('notifications.broadcast.history')
                    ->with('error', 'Nomor penerima broadcast belum diatur. Silakan atur di menu Pengaturan > WhatsApp.');
            }

            // Generate unique broadcast ID
            $broadcastId = 'BROADCAST_' . Str::upper(Str::random(8)) . '_' . time();

            // Ambil daftar orang tua (untuk informasi/data saja, tidak untuk dikirimi WA)
            $parentsQuery = ParentModel::with('student');

            if ($request->recipients === 'active_only') {
                $parentsQuery->whereHas('student', function($q) {
                    $q->where('status', 'active');
                });
            }

            $parents = $parentsQuery->get();

            if ($parents->isEmpty()) {
                return redirect()->route('notifications.broadcast.history')
                    ->with('error', 'Tidak ada data orang tua yang ditemukan.');
            }

            // Buat ringkasan data orang tua untuk informasi
            $totalStudents = $parents->count();
            $activeStudents = $parents->filter(function($parent) {
                return $parent->student && $parent->student->status === 'active';
            })->count();

            // Siapkan pesan yang akan dikirim (bisa ditambah informasi ringkasan)
            $finalMessage = $request->message;

            // Opsional: Tambahkan footer informasi jumlah siswa
            if ($request->has('add_summary') && $request->add_summary) {
                $finalMessage .= "\n\n---\n📊 Ringkasan Data:\nTotal Siswa: {$totalStudents}\nSiswa Aktif: {$activeStudents}";
            }

            // Kirim pesan ke nomor broadcast (1 nomor WA saja)
            $result = $waService->sendMessage($broadcastNumber, $finalMessage);

            // Siapkan data untuk riwayat
            $sent = 0;
            $failed = 0;
            $recipientsDetail = [];
            $failedRecipients = [];

            if ($result['success']) {
                $sent = 1;
                $recipientsDetail = [
                    [
                        'id' => 'broadcast_number',
                        'name' => 'WhatsApp Kesiswaan',
                        'phone' => $broadcastNumber,
                        'student_name' => 'Broadcast',
                        'status' => 'sent',
                        'sent_at' => now()->toDateTimeString(),
                        'message_id' => $result['message_id'] ?? null,
                    ]
                ];
            } else {
                $failed = 1;
                $failedRecipients = [
                    [
                        'id' => 'broadcast_number',
                        'name' => 'WhatsApp Kesiswaan',
                        'phone' => $broadcastNumber,
                        'student_name' => 'Broadcast',
                        'status' => 'failed',
                        'error' => $result['error'] ?? 'Unknown error',
                        'sent_at' => now()->toDateTimeString(),
                    ]
                ];
            }

            // Simpan riwayat broadcast
            $broadcastHistory = BroadcastHistory::create([
                'broadcast_id' => $broadcastId,
                'message' => $request->message,
                'recipient_type' => $request->recipients,
                'total_recipients' => 1, // Hanya 1 nomor broadcast
                'sent_count' => $sent,
                'failed_count' => $failed,
                'recipients_detail' => $recipientsDetail,
                'failed_recipients' => $failedRecipients,
                'status' => $sent > 0 ? BroadcastHistory::STATUS_COMPLETED : BroadcastHistory::STATUS_FAILED,
                'started_at' => now(),
                'completed_at' => now(),
                'notes' => "Dikirim ke nomor broadcast: {$broadcastNumber}. Data mencakup {$totalStudents} siswa ({$activeStudents} aktif).",
            ]);

            DB::commit();

            if ($sent > 0) {
                return redirect()->route('notifications.broadcast.history')
                    ->with('success', "Broadcast berhasil dikirim ke nomor WhatsApp Kesiswaan: {$broadcastNumber}")
                    ->with('broadcast_id', $broadcastId);
            } else {
                return redirect()->route('notifications.broadcast.history')
                    ->with('error', "Broadcast gagal dikirim: " . ($result['error'] ?? 'Unknown error'));
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Broadcast Error: ' . $e->getMessage());

            return redirect()->route('notifications.broadcast.history')
                ->with('error', 'Gagal mengirim broadcast: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan riwayat broadcast
     */
    public function broadcastHistory()
    {
        $broadcasts = BroadcastHistory::orderBy('created_at', 'desc')
            ->paginate(20);

        return view('notifications.broadcast-history', compact('broadcasts'));
    }

    /**
     * Menampilkan detail broadcast
     */
    public function broadcastDetail($broadcastId)
    {
        $broadcast = BroadcastHistory::where('broadcast_id', $broadcastId)
            ->firstOrFail();

        return view('notifications.broadcast-detail', compact('broadcast'));
    }

    /**
     * Resend broadcast yang gagal (kirim ulang ke nomor broadcast)
     */
    public function resendFailedBroadcast($broadcastId, WhatsAppService $waService)
    {
        $broadcast = BroadcastHistory::where('broadcast_id', $broadcastId)
            ->firstOrFail();

        // Ambil nomor broadcast dari cache
        $broadcastNumber = cache('whatsapp_broadcast_number');

        if (empty($broadcastNumber)) {
            return redirect()->route('notifications.broadcast.detail', $broadcastId)
                ->with('error', 'Nomor broadcast belum diatur. Silakan atur di Pengaturan WhatsApp terlebih dahulu.');
        }

        // Cek apakah ada pesan yang gagal
        if (empty($broadcast->failed_recipients) || !is_array($broadcast->failed_recipients)) {
            return redirect()->route('notifications.broadcast.detail', $broadcastId)
                ->with('info', 'Tidak ada pesan yang gagal untuk dikirim ulang.');
        }

        // Kirim ulang pesan ke nomor broadcast
        $result = $waService->sendMessage($broadcastNumber, $broadcast->message);

        $resent = 0;
        $stillFailed = 0;
        $updatedFailedRecipients = [];
        $newSuccessRecipients = [];

        if ($result['success']) {
            $resent = 1;
            $newSuccessRecipients[] = [
                'id' => 'broadcast_number',
                'name' => 'WhatsApp Kesiswaan',
                'phone' => $broadcastNumber,
                'student_name' => 'Broadcast',
                'status' => 'sent',
                'sent_at' => now()->toDateTimeString(),
                'resend' => true,
                'message_id' => $result['message_id'] ?? null,
            ];
            $stillFailed = 0;
            $updatedFailedRecipients = [];
        } else {
            $resent = 0;
            $stillFailed = 1;
            foreach ($broadcast->failed_recipients as $failedRecipient) {
                $updatedFailedRecipients[] = array_merge($failedRecipient, [
                    'last_resend_attempt' => now()->toDateTimeString(),
                    'resend_error' => $result['error'] ?? 'Unknown error',
                    'resend_count' => ($failedRecipient['resend_count'] ?? 0) + 1,
                ]);
            }
        }

        // Update recipients_detail
        $existingRecipients = $broadcast->recipients_detail ?? [];
        $allRecipients = array_merge($existingRecipients, $newSuccessRecipients);

        // Update riwayat broadcast
        $newSentCount = $broadcast->sent_count + $resent;
        $newFailedCount = $stillFailed;

        $broadcast->update([
            'sent_count' => $newSentCount,
            'failed_count' => $newFailedCount,
            'recipients_detail' => $allRecipients,
            'failed_recipients' => $updatedFailedRecipients,
            'completed_at' => now(),
            'status' => $newFailedCount == 0 ? BroadcastHistory::STATUS_COMPLETED : BroadcastHistory::STATUS_PARTIAL,
            'notes' => $broadcast->notes . " | Resend: {$resent} sukses, {$stillFailed} gagal",
        ]);

        $message = "Berhasil mengirim ulang pesan ke nomor broadcast.";
        if ($stillFailed > 0) {
            $message = "Gagal mengirim ulang pesan. Error: " . ($result['error'] ?? 'Unknown error');
        }

        return redirect()->route('notifications.broadcast.detail', $broadcastId)
            ->with($stillFailed > 0 ? 'error' : 'success', $message);
    }

    /**
     * Hapus riwayat broadcast
     */
    public function deleteBroadcast($broadcastId)
    {
        try {
            Log::info('Attempting to delete broadcast', ['broadcast_id' => $broadcastId]);

            $broadcast = BroadcastHistory::where('broadcast_id', $broadcastId)->first();

            if (!$broadcast) {
                Log::warning('Broadcast not found', ['broadcast_id' => $broadcastId]);

                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Broadcast tidak ditemukan.'
                    ], 404);
                }

                return redirect()->route('notifications.broadcast.history')
                    ->with('error', 'Broadcast tidak ditemukan.');
            }

            $broadcast->delete();

            Log::info('Broadcast deleted successfully', ['broadcast_id' => $broadcastId]);

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Riwayat broadcast berhasil dihapus.'
                ]);
            }

            return redirect()->route('notifications.broadcast.history')
                ->with('success', 'Riwayat broadcast berhasil dihapus.');

        } catch (\Exception $e) {
            Log::error('Error deleting broadcast', [
                'broadcast_id' => $broadcastId,
                'error' => $e->getMessage()
            ]);

            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus broadcast: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('notifications.broadcast.history')
                ->with('error', 'Gagal menghapus broadcast: ' . $e->getMessage());
        }
    }
    /**
     * Delete selected broadcasts
     */
    public function deleteSelectedBroadcasts(Request $request)
    {
        $request->validate([
            'selected_broadcasts' => 'required|array',
            'selected_broadcasts.*' => 'string'
        ]);

        try {
            $deletedCount = 0;
            $notFoundCount = 0;

            foreach ($request->selected_broadcasts as $broadcastId) {
                $broadcast = BroadcastHistory::where('broadcast_id', $broadcastId)->first();

                if ($broadcast) {
                    $broadcast->delete();
                    $deletedCount++;
                } else {
                    $notFoundCount++;
                }
            }

            $message = "Berhasil menghapus {$deletedCount} riwayat broadcast.";
            if ($notFoundCount > 0) {
                $message .= " {$notFoundCount} data tidak ditemukan.";
            }

            return redirect()->route('notifications.broadcast.history')
                ->with('success', $message);

        } catch (\Exception $e) {
            return redirect()->route('notifications.broadcast.history')
                ->with('error', 'Gagal menghapus broadcast: ' . $e->getMessage());
        }
    }

    /**
     * Delete all broadcasts
     */
    public function deleteAllBroadcasts()
    {
        try {
            $total = BroadcastHistory::count();

            if ($total === 0) {
                return redirect()->route('notifications.broadcast.history')
                    ->with('info', 'Tidak ada data broadcast untuk dihapus.');
            }

            BroadcastHistory::truncate();

            return redirect()->route('notifications.broadcast.history')
                ->with('success', "Berhasil menghapus semua ({$total}) riwayat broadcast.");

        } catch (\Exception $e) {
            return redirect()->route('notifications.broadcast.history')
                ->with('error', 'Gagal menghapus semua broadcast: ' . $e->getMessage());
        }
    }
}

