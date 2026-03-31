@extends('layouts.app')

@section('title', 'Laporan Absensi')

@section('content')
<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-chart-bar me-2"></i> Laporan Absensi Siswa</h5>
    </div>
    <div class="card-body">
        <!-- Filter Form -->
        <form method="GET" action="{{ route('reports.attendance') }}" class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="form-label">Siswa</label>
                <select name="student_id" class="form-select">
                    <option value="">Semua Siswa</option>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}" {{ $studentId == $student->id ? 'selected' : '' }}>
                            {{ $student->name }} ({{ $student->class }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tanggal Mulai</label>
                <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Tanggal Akhir</label>
                <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-1"></i> Tampilkan
                </button>
            </div>
        </form>

        <!-- Rekap Per Siswa -->
        @if(!$studentId && count($rekap) > 0)
        <div class="mb-4">
            <h6>Rekapitulasi Absensi</h6>
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Siswa</th>
                            <th>Kelas</th>
                            <th>Total Hadir</th>
                            <th>Terlambat</th>
                            <th>Absen</th>
                            <th>Kehadiran</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rekap as $item)
                        <tr>
                            <td>{{ $item['student']->name }}</td>
                            <td>{{ $item['student']->class }}</td>
                            <td>{{ $item['present'] }}</td>
                            <td>{{ $item['late'] }}</td>
                            <td>{{ $item['absent'] }}</td>
                            <td>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-success" style="width: {{ $item['attendance_rate'] }}%">
                                        {{ $item['attendance_rate'] }}%
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Tabel Detail Absensi -->
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="attendanceReportTable">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal</th>
                        <th>Siswa</th>
                        <th>Kelas</th>
                        <th>Jam Masuk</th>
                        <th>Jam Pulang</th>
                        <th>Status</th>
                        <th>Device</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attendances as $attendance)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($attendance->date)->format('d/m/Y') }}</td>
                        <td>{{ $attendance->student->name ?? '-' }}</td>
                        <td>{{ $attendance->student->class ?? '-' }}</td>
                        <td>{{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i:s') : '-' }}</td>
                        <td>{{ $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i:s') : '-' }}</td>
                        <td>
                            @if($attendance->status == 'present')
                                <span class="badge bg-success">Hadir</span>
                            @elseif($attendance->status == 'late')
                                <span class="badge bg-warning text-dark">Terlambat</span>
                            @elseif($attendance->status == 'permission')
                                <span class="badge bg-info">Izin</span>
                            @else
                                <span class="badge bg-danger">Absen</span>
                            @endif
                        </td>
                        <td>{{ $attendance->device->device_name ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-end mt-3">
            {{ $attendances->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#attendanceReportTable').DataTable({
            order: [[0, 'desc']],
            pageLength: 25,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
            }
        });
    });
</script>
@endpush
