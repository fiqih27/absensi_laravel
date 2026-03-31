<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Attendance;
use App\Models\Student;
use App\Services\ZKTecoService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $attendances = Attendance::with(['student', 'device'])
            ->whereDate('date', $today)
            ->orderBy('check_in', 'asc')
            ->get();

        $devices = Device::all();

        return view('attendance.index', compact('attendances', 'devices', 'today'));
    }

    public function sync(Request $request)
    {
        // Set timeout untuk proses yang lama
        if (function_exists('set_time_limit')) {
            set_time_limit(300);
        }

        $deviceId = $request->device_id;
        $totalSynced = 0;
        $totalFailed = 0;

        if ($deviceId) {
            $device = Device::findOrFail($deviceId);
            $zkService = new ZKTecoService($device);
            // Gunakan syncNewAttendance untuk efisiensi
            $result = $zkService->syncNewAttendance();
            $totalSynced = $result['synced'];
            $totalFailed = $result['failed'];
        } else {
            foreach (Device::all() as $device) {
                $zkService = new ZKTecoService($device);
                $result = $zkService->syncNewAttendance();
                $totalSynced += $result['synced'];
                $totalFailed += $result['failed'];

                // Jeda antar device
                sleep(1);
            }
        }

        return redirect()->back()->with('success',
            "Sinkronisasi selesai: {$totalSynced} data berhasil, {$totalFailed} gagal"
        );
    }

    public function checkDevice($id)
    {
        $device = Device::findOrFail($id);
        $zkService = new ZKTecoService($device);

        $connected = $zkService->connect();

        if ($connected) {
            $info = $zkService->getDeviceInfo();
            $zkService->disconnect();
            return response()->json([
                'status' => 'online',
                'info' => $info
            ]);
        }

        return response()->json([
            'status' => 'offline',
            'info' => null
        ]);
    }

    public function report(Request $request)
    {
        $studentId = $request->student_id;
        $startDate = $request->start_date ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->end_date ?? Carbon::now()->format('Y-m-d');

        $query = Attendance::with('student', 'device')
            ->when($studentId, function($query) use ($studentId) {
                return $query->where('student_id', $studentId);
            })
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc');

        $attendances = $query->paginate(20);
        $students = Student::where('status', 'active')->orderBy('name')->get();

        // Rekap per siswa
        $rekap = [];
        if (!$studentId) {
            $allStudents = Student::where('status', 'active')->get();
            foreach ($allStudents as $student) {
                $total = Attendance::where('student_id', $student->id)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->count();
                $present = Attendance::where('student_id', $student->id)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->whereIn('status', ['present', 'late'])
                    ->count();
                $late = Attendance::where('student_id', $student->id)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->where('status', 'late')
                    ->count();

                $rekap[$student->id] = [
                    'name' => $student->name,
                    'class' => $student->class,
                    'total' => $total,
                    'present' => $present,
                    'late' => $late,
                    'absent' => $total - $present,
                    'percentage' => $total > 0 ? round(($present / $total) * 100, 1) : 0
                ];
            }
        }

        return view('reports.attendance', compact('attendances', 'students', 'studentId', 'startDate', 'endDate', 'rekap'));
    }
}
