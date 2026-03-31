<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Device;
use App\Models\Student;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Menampilkan dashboard utama
     */
    public function index()
    {
        // Statistik hari ini
        $today = Carbon::today();
        $todayAttendance = Attendance::whereDate('date', $today)->count();
        $todayPresent = Attendance::whereDate('date', $today)
            ->whereIn('status', ['present', 'late'])
            ->count();
        $todayLate = Attendance::whereDate('date', $today)
            ->where('status', 'late')
            ->count();

        // Statistik bulan ini
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();
        $monthAttendance = Attendance::whereBetween('date', [$monthStart, $monthEnd])->count();

        // Statistik device
        $totalDevices = Device::count();
        $onlineDevices = Device::where('status', 'online')->count();

        // Statistik notifikasi hari ini
        $todayNotifications = Notification::whereDate('created_at', $today)->count();
        $failedNotifications = Notification::where('status', 'failed')
            ->whereDate('created_at', $today)
            ->count();

        // Grafik absensi 7 hari terakhir
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $chartData[] = [
                'date' => $date->format('d/m'),
                'present' => Attendance::whereDate('date', $date)
                    ->whereIn('status', ['present', 'late'])
                    ->count(),
                'absent' => Attendance::whereDate('date', $date)
                    ->where('status', 'absent')
                    ->count(),
            ];
        }

        // 5 Absensi terbaru
        $recentAttendances = Attendance::with(['student', 'device'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // 5 Notifikasi terbaru
        $recentNotifications = Notification::with('attendance.student')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'todayAttendance',
            'todayPresent',
            'todayLate',
            'monthAttendance',
            'totalDevices',
            'onlineDevices',
            'todayNotifications',
            'failedNotifications',
            'chartData',
            'recentAttendances',
            'recentNotifications'
        ));
    }
}
