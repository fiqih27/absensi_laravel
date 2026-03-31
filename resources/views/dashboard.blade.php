@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="row">
    <div class="col-md-12 mb-4">
        <h3>Selamat Datang di Sistem Absensi Alfaiz</h3>
        <p class="text-muted">{{ now()->format('l, d F Y') }}</p>
    </div>
</div>

<!-- Statistik Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card card-stats card-stats-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Absensi Hari Ini</h6>
                        <h3 class="mb-0">{{ $todayAttendance }}</h3>
                        <small class="text-muted">Total absensi</small>
                    </div>
                    <div class="fs-1 text-primary">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card card-stats card-stats-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Hadir / Terlambat</h6>
                        <h3 class="mb-0">{{ $todayPresent }}</h3>
                        <small class="text-muted">Dari {{ $todayAttendance }} siswa</small>
                    </div>
                    <div class="fs-1 text-success">
                        <i class="fas fa-user-check"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card card-stats card-stats-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Terlambat</h6>
                        <h3 class="mb-0">{{ $todayLate }}</h3>
                        <small class="text-muted">Siswa terlambat</small>
                    </div>
                    <div class="fs-1 text-warning">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card card-stats card-stats-danger">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Device Online</h6>
                        <h3 class="mb-0">{{ $onlineDevices }}/{{ $totalDevices }}</h3>
                        <small class="text-muted">Device terhubung</small>
                    </div>
                    <div class="fs-1 text-danger">
                        <i class="fas fa-microchip"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Grafik dan Statistik -->
<div class="row mb-4">
    <div class="col-md-8 mb-3">
        <div class="card">
            <div class="card-header">
                <h6><i class="fas fa-chart-line me-2"></i> Grafik Absensi 7 Hari Terakhir</h6>
            </div>
            <div class="card-body">
                <canvas id="attendanceChart" height="300"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card">
            <div class="card-header">
                <h6><i class="fas fa-chart-pie me-2"></i> Statistik Bulan Ini</h6>
            </div>
            <div class="card-body">
                <div class="text-center">
                    <h2 class="mb-3">{{ $monthAttendance }}</h2>
                    <p class="text-muted">Total Absensi</p>
                    <hr>
                    <div class="row">
                        <div class="col-6">
                            <h5>{{ $todayNotifications }}</h5>
                            <small class="text-muted">Notifikasi Hari Ini</small>
                        </div>
                        <div class="col-6">
                            <h5 class="text-danger">{{ $failedNotifications }}</h5>
                            <small class="text-muted">Gagal Terkirim</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabel Terbaru -->
<div class="row">
    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6><i class="fas fa-history me-2"></i> Absensi Terbaru</h6>
                <a href="{{ route('attendance.index') }}" class="btn btn-sm btn-primary">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Siswa</th>
                                <th>Jam Masuk</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentAttendances as $attendance)
                            <tr>
                                <td>{{ $attendance->student->name ?? '-' }}</td>
                                <td>{{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i') : '-' }}</td>
                                <td>
                                    @if($attendance->status == 'present')
                                        <span class="badge bg-success">Hadir</span>
                                    @elseif($attendance->status == 'late')
                                        <span class="badge bg-warning text-dark">Terlambat</span>
                                    @else
                                        <span class="badge bg-danger">Absen</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">Belum ada data</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6><i class="fas fa-bell me-2"></i> Notifikasi Terbaru</h6>
                <a href="{{ route('notifications.index') }}" class="btn btn-sm btn-primary">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Penerima</th>
                                <th>Status</th>
                                <th>Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentNotifications as $notification)
                            <tr>
                                <td>{{ $notification->recipient_phone }}</td>
                                <td>
                                    @if($notification->status == 'sent')
                                        <span class="badge bg-success">Terkirim</span>
                                    @elseif($notification->status == 'failed')
                                        <span class="badge bg-danger">Gagal</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @endif
                                </td>
                                <td>{{ $notification->created_at->diffForHumans() }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">Belum ada notifikasi</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Chart Absensi
    const ctx = document.getElementById('attendanceChart').getContext('2d');
    const chartData = @json($chartData);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.map(item => item.date),
            datasets: [
                {
                    label: 'Hadir',
                    data: chartData.map(item => item.present),
                    borderColor: '#198754',
                    backgroundColor: 'rgba(25, 135, 84, 0.1)',
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Absen',
                    data: chartData.map(item => item.absent),
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    fill: true,
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'top',
                }
            }
        }
    });
</script>
@endpush
