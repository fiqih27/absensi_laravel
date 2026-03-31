<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\Device;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Laporan absensi per siswa
     */
    public function attendance(Request $request)
    {
        $studentId = $request->student_id;
        $startDate = $request->start_date ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->end_date ?? Carbon::now()->format('Y-m-d');

        $attendances = Attendance::with(['student', 'device'])
            ->when($studentId, function($query) use ($studentId) {
                return $query->where('student_id', $studentId);
            })
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc')
            ->orderBy('check_in', 'desc')
            ->paginate(20);

        $students = Student::where('status', 'active')->orderBy('name')->get();

        // Rekap per siswa
        $rekap = [];
        if (!$studentId) {
            $rekap = Student::with(['attendances' => function($query) use ($startDate, $endDate) {
                $query->whereBetween('date', [$startDate, $endDate]);
            }])
            ->where('status', 'active')
            ->get()
            ->map(function($student) {
                $total = $student->attendances->count();
                $present = $student->attendances->whereIn('status', ['present', 'late'])->count();
                $late = $student->attendances->where('status', 'late')->count();
                $absent = $student->attendances->where('status', 'absent')->count();

                return [
                    'student' => $student,
                    'total' => $total,
                    'present' => $present,
                    'late' => $late,
                    'absent' => $absent,
                    'attendance_rate' => $total > 0 ? round(($present / $total) * 100, 1) : 0,
                ];
            });
        }

        return view('reports.attendance', compact(
            'attendances',
            'students',
            'studentId',
            'startDate',
            'endDate',
            'rekap'
        ));
    }

    /**
     * Laporan notifikasi
     */
    public function notifications(Request $request)
    {
        $status = $request->status;
        $startDate = $request->start_date ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->end_date ?? Carbon::now()->format('Y-m-d');

        $notifications = Notification::with('attendance.student')
            ->when($status, function($query) use ($status) {
                return $query->where('status', $status);
            })
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total' => Notification::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])->count(),
            'sent' => Notification::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])->where('status', 'sent')->count(),
            'failed' => Notification::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])->where('status', 'failed')->count(),
            'pending' => Notification::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])->where('status', 'pending')->count(),
        ];

        return view('reports.notifications', compact('notifications', 'status', 'startDate', 'endDate', 'stats'));
    }

    /**
     * Laporan device (status dan aktivitas)
     */
    public function devices(Request $request)
    {
        $startDate = $request->start_date ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->end_date ?? Carbon::now()->format('Y-m-d');

        $devices = Device::with(['attendances' => function($query) use ($startDate, $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        }])->get();

        $deviceStats = [];
        foreach ($devices as $device) {
            $deviceStats[] = [
                'device' => $device,
                'total_attendance' => $device->attendances->count(),
                'unique_students' => $device->attendances->groupBy('student_id')->count(),
                'peak_hour' => $this->getPeakHour($device->attendances),
            ];
        }

        return view('reports.devices', compact('deviceStats', 'startDate', 'endDate'));
    }

    /**
     * Laporan rekapitulasi bulanan
     */
    public function monthly(Request $request)
    {
        $month = $request->month ?? Carbon::now()->format('Y-m');
        $year = substr($month, 0, 4);
        $monthNum = substr($month, 5, 2);

        $startDate = Carbon::createFromDate($year, $monthNum, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, $monthNum, 1)->endOfMonth();

        // Rekap per siswa
        $students = Student::where('status', 'active')
            ->with(['attendances' => function($query) use ($startDate, $endDate) {
                $query->whereBetween('date', [$startDate, $endDate]);
            }])
            ->get();

        $rekap = [];
        foreach ($students as $student) {
            $total = $student->attendances->count();
            $present = $student->attendances->whereIn('status', ['present', 'late'])->count();
            $late = $student->attendances->where('status', 'late')->count();
            $absent = $student->attendances->where('status', 'absent')->count();

            $rekap[] = [
                'student' => $student,
                'total' => $total,
                'present' => $present,
                'late' => $late,
                'absent' => $absent,
                'attendance_rate' => $total > 0 ? round(($present / $total) * 100, 1) : 0,
            ];
        }

        // Rekap harian
        $dailyRekap = [];
        $date = clone $startDate;
        while ($date <= $endDate) {
            $dailyRekap[] = [
                'date' => $date->format('Y-m-d'),
                'date_display' => $date->format('d/m/Y'),
                'present' => Attendance::whereDate('date', $date)->whereIn('status', ['present', 'late'])->count(),
                'absent' => Attendance::whereDate('date', $date)->where('status', 'absent')->count(),
            ];
            $date->addDay();
        }

        return view('reports.monthly', compact('rekap', 'dailyRekap', 'month', 'startDate', 'endDate'));
    }

    /**
     * Export laporan ke Excel/PDF
     */
    public function export(Request $request)
    {
        $type = $request->type; // excel, pdf
        $reportType = $request->report_type; // attendance, notifications, monthly
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $studentId = $request->student_id;

        // Logika export sesuai kebutuhan
        // Bisa menggunakan Laravel Excel atau DomPDF

        return redirect()->back()->with('success', 'Laporan berhasil diekspor');
    }

    /**
     * Helper: Mendapatkan jam tersibuk
     */
    private function getPeakHour($attendances)
    {
        $hours = [];
        foreach ($attendances as $attendance) {
            if ($attendance->check_in) {
                $hour = date('H', strtotime($attendance->check_in));
                $hours[$hour] = ($hours[$hour] ?? 0) + 1;
            }
            if ($attendance->check_out) {
                $hour = date('H', strtotime($attendance->check_out));
                $hours[$hour] = ($hours[$hour] ?? 0) + 1;
            }
        }

        if (empty($hours)) return '-';

        arsort($hours);
        return key($hours) . ':00 - ' . (key($hours) + 1) . ':00';
    }
}
