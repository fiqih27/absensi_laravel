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
            // Generate unique broadcast ID
            $broadcastId = 'BROADCAST_' . Str::upper(Str::random(8)) . '_' . time();

            // Ambil daftar parents
            $parentsQuery = ParentModel::query();

            if ($request->recipients === 'active_only') {
                $parentsQuery->whereHas('student', function($q) {
                    $q->where('status', 'active');
                });
            }

            $parents = $parentsQuery->get();

            if ($parents->isEmpty()) {
                return redirect()->route('notifications.broadcast.history')
                    ->with('error', 'Tidak ada penerima yang ditemukan.');
            }

            // Prepare data untuk broadcast
            $recipientsDetail = [];
            $failedRecipients = [];
            $sent = 0;
            $failed = 0;

            // Kirim pesan ke setiap parent
            foreach ($parents as $parent) {
                // Kirim pesan WhatsApp
                $result = $waService->sendMessage($parent->phone, $request->message);

                $recipientData = [
                    'id' => $parent->id,
                    'name' => $parent->name,
                    'phone' => $parent->phone,
                    'student_name' => $parent->student->name ?? 'N/A',
                    'status' => $result['success'] ? 'sent' : 'failed',
                    'sent_at' => now()->toDateTimeString(),
                ];

                if (!$result['success']) {
                    $recipientData['error'] = $result['message'] ?? 'Unknown error';
                    $failedRecipients[] = $recipientData;
                    $failed++;
                } else {
                    $recipientData['message_id'] = $result['message_id'] ?? null;
                    $recipientsDetail[] = $recipientData;
                    $sent++;
                }
            }

            // Simpan riwayat broadcast ke tabel broadcast_histories saja
            $broadcastHistory = BroadcastHistory::create([
                'broadcast_id' => $broadcastId,
                'message' => $request->message,
                'recipient_type' => $request->recipients,
                'total_recipients' => $parents->count(),
                'sent_count' => $sent,
                'failed_count' => $failed,
                'recipients_detail' => $recipientsDetail,
                'failed_recipients' => $failedRecipients,
                'status' => BroadcastHistory::STATUS_COMPLETED,
                'started_at' => now(),
                'completed_at' => now(),
                'notes' => "Broadcast selesai: {$sent} berhasil, {$failed} gagal",
            ]);

            DB::commit();

            $message = "Broadcast selesai: {$sent} berhasil, {$failed} gagal.";

            return redirect()->route('notifications.broadcast.history')
                ->with('success', $message)
                ->with('broadcast_id', $broadcastId);

        } catch (\Exception $e) {
            DB::rollBack();

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
     * Resend broadcast yang gagal
     */
    public function resendFailedBroadcast($broadcastId, WhatsAppService $waService)
    {
        $broadcast = BroadcastHistory::where('broadcast_id', $broadcastId)
            ->firstOrFail();

        if (empty($broadcast->failed_recipients) || !is_array($broadcast->failed_recipients)) {
            return redirect()->route('notifications.broadcast.detail', $broadcastId)
                ->with('info', 'Tidak ada pesan yang gagal untuk dikirim ulang.');
        }

        $resent = 0;
        $stillFailed = 0;
        $updatedFailedRecipients = [];
        $newSuccessRecipients = [];

        foreach ($broadcast->failed_recipients as $failedRecipient) {
            // Kirim ulang pesan
            $result = $waService->sendMessage($failedRecipient['phone'], $broadcast->message);

            if ($result['success']) {
                $resent++;
                // Pindahkan ke success recipients
                $newSuccessRecipients[] = [
                    'id' => $failedRecipient['id'],
                    'name' => $failedRecipient['name'],
                    'phone' => $failedRecipient['phone'],
                    'student_name' => $failedRecipient['student_name'],
                    'status' => 'sent',
                    'sent_at' => now()->toDateTimeString(),
                    'resend' => true,
                    'message_id' => $result['message_id'] ?? null,
                ];
            } else {
                $stillFailed++;
                // Tetap di failed recipients dengan informasi resend
                $updatedFailedRecipients[] = array_merge($failedRecipient, [
                    'last_resend_attempt' => now()->toDateTimeString(),
                    'resend_error' => $result['message'] ?? 'Unknown error',
                    'resend_count' => ($failedRecipient['resend_count'] ?? 0) + 1,
                ]);
            }
        }

        // Gabungkan recipients_detail yang sudah ada dengan yang baru berhasil
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
            'notes' => $broadcast->notes . " | Resend: {$resent} sukses, {$stillFailed} gagal",
        ]);

        $message = "Berhasil mengirim ulang {$resent} pesan";
        if ($stillFailed > 0) {
            $message .= ", {$stillFailed} masih gagal.";
        } else {
            $message .= ". Semua pesan berhasil terkirim!";
        }

        return redirect()->route('notifications.broadcast.detail', $broadcastId)
            ->with('success', $message);
    }

       public function deleteBroadcast($broadcastId)
    {
        try {
            // Log untuk debugging
            Log::info('Attempting to delete broadcast', ['broadcast_id' => $broadcastId]);

            // Cari broadcast dengan broadcast_id
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

            // Hapus broadcast
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

