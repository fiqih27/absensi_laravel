<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;

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

    /**
     * Proses broadcast notifikasi
     */
    public function sendBroadcast(Request $request, WhatsAppService $waService)
    {
        $request->validate([
            'message' => 'required|string',
            'recipients' => 'required|in:all,active_only',
        ]);

        $parents = \App\Models\ParentModel::query();

        if ($request->recipients === 'active_only') {
            $parents->whereHas('student', function($q) {
                $q->where('status', 'active');
            });
        }

        $parents = $parents->get();
        $sent = 0;
        $failed = 0;

        foreach ($parents as $parent) {
            $result = $waService->sendMessage($parent->phone, $request->message);

            if ($result['success']) {
                $sent++;
            } else {
                $failed++;
            }
        }

        return redirect()->route('notifications.index')->with('success',
            "Broadcast selesai: {$sent} berhasil, {$failed} gagal"
        );
    }
}
