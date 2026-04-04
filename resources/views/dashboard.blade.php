@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<style>
    /* Maroon Text Theme with White Background */
    :root {
        --maroon-primary: #800020;
        --maroon-dark: #5C0016;
        --maroon-light: #A8324A;
        --maroon-soft: #B85C6E;
        --maroon-pale: #F5E6EA;
        --gold-accent: #C4A035;
        --gray-light: #F8F9FA;
        --gray-soft: #E9ECEF;
    }

    body {
        background: #e5caca !important;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    /* Card Styles */
    .modern-card {
        background: #ffffff;
        border: 1px solid var(--gray-soft);
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        transition: all 0.3s ease;
    }

    .modern-card:hover {
        box-shadow: 0 4px 16px rgba(128, 0, 32, 0.08);
        transform: translateY(-2px);
    }

    /* Stat Card */
    .stat-card {
        background: #ffffff;
        border: 1px solid var(--gray-soft);
        border-radius: 16px;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .stat-card:hover {
        border-color: var(--maroon-pale);
        box-shadow: 0 4px 20px rgba(128, 0, 32, 0.08);
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--maroon-primary), var(--gold-accent));
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }

    .stat-card:hover::before {
        transform: scaleX(1);
    }

    /* Text Colors */
    .text-maroon {
        color: var(--maroon-primary) !important;
    }

    .text-maroon-dark {
        color: var(--maroon-dark) !important;
    }

    .text-maroon-light {
        color: var(--maroon-light) !important;
    }

    .text-gold {
        color: var(--gold-accent) !important;
    }

    /* Stats Number */
    .stats-number {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--maroon-primary);
        line-height: 1.2;
    }

    /* Table Styles */
    .modern-table {
        background: #ffffff;
    }

    .modern-table thead th {
        background: var(--gray-light);
        color: var(--maroon-dark);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.85rem;
        border-bottom: 2px solid var(--maroon-pale);
        padding: 12px 16px;
    }

    .modern-table tbody tr {
        border-bottom: 1px solid var(--gray-soft);
        transition: all 0.2s ease;
    }

    .modern-table tbody tr:hover {
        background: var(--gray-light);
    }

    .modern-table tbody td {
        padding: 12px 16px;
        color: #4a5568;
        vertical-align: middle;
    }

    /* Badge Styles */
    .badge-modern {
        padding: 6px 12px;
        border-radius: 20px;
        font-weight: 500;
        font-size: 0.75rem;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .badge-present {
        background: #E8F5E9;
        color: #2E7D32;
    }

    .badge-late {
        background: #FFF3E0;
        color: #E65100;
    }

    .badge-absent {
        background: #FFEBEE;
        color: #C62828;
    }

    .badge-sent {
        background: #E8F5E9;
        color: #2E7D32;
    }

    .badge-failed {
        background: #FFEBEE;
        color: #C62828;
    }

    .badge-pending {
        background: #FFF3E0;
        color: #E65100;
    }

    /* Button Styles */
    .btn-maroon {
        background: var(--maroon-primary);
        border: none;
        border-radius: 10px;
        padding: 8px 20px;
        color: white;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-maroon:hover {
        background: var(--maroon-dark);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(128, 0, 32, 0.2);
        color: white;
    }

    .btn-outline-maroon {
        background: transparent;
        border: 1px solid var(--maroon-primary);
        border-radius: 10px;
        padding: 8px 20px;
        color: var(--maroon-primary);
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-outline-maroon:hover {
        background: var(--maroon-primary);
        color: white;
        transform: translateY(-1px);
    }

    /* Icon Styles */
    .icon-circle {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: var(--maroon-pale);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--maroon-primary);
    }

    .icon-sm {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: var(--maroon-pale);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--maroon-primary);
    }

    /* Header/Welcome Section */
    .welcome-section {
        background: linear-gradient(135deg, #ffffff 0%, var(--maroon-pale) 100%);
        border-radius: 20px;
        padding: 24px;
        border: 1px solid var(--gray-soft);
    }

    /* Progress Bar */
    .progress-modern {
        background: var(--gray-soft);
        border-radius: 10px;
        height: 6px;
    }

    .progress-bar-maroon {
        background: linear-gradient(90deg, var(--maroon-primary), var(--gold-accent));
        border-radius: 10px;
    }

    /* Chart Container */
    .chart-container {
        background: #ffffff;
        border-radius: 16px;
        padding: 16px;
    }

    /* Section Title */
    .section-title {
        color: var(--maroon-dark);
        font-weight: 600;
        font-size: 1.1rem;
        margin-bottom: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* Divider */
    .divider {
        height: 1px;
        background: linear-gradient(90deg, var(--maroon-pale), transparent);
        margin: 16px 0;
    }

    /* Card Header */
    .card-header-custom {
        background: transparent;
        border-bottom: 1px solid var(--gray-soft);
        padding: 16px 20px;
    }

    /* Hover Effects */
    .hover-lift {
        transition: all 0.3s ease;
    }

    .hover-lift:hover {
        transform: translateY(-2px);
    }

    /* Stats Label */
    .stats-label {
        font-size: 0.85rem;
        color: #6c757d;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    /* Live Clock */
    .live-clock {
        font-family: monospace;
        font-size: 1rem;
        color: var(--maroon-primary);
        font-weight: 500;
    }
</style>

<div class="container-fluid px-4">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="welcome-section">
                <div class="d-flex align-items-center justify-content-between flex-wrap">
                    <div class="d-flex align-items-center">
                        <div class="icon-circle me-3">
                            <i class="fas fa-robot fs-3"></i>
                        </div>
                        <div>
                            <h1 class="text-maroon" style="font-size: 1.8rem; font-weight: 600; margin-bottom: 8px;">
                                Selamat Datang di Sistem Absensi Alfaiz
                            </h1>
                            <p class="text-muted mb-0">
                                <i class="fas fa-calendar-alt me-2 text-maroon"></i>{{ now()->format('l, d F Y') }}
                                <span class="mx-2">•</span>
                                <i class="fas fa-clock me-2 text-maroon"></i>
                                <span class="live-clock" id="liveClock"></span>
                            </p>
                        </div>
                    </div>
                    <div class="mt-3 mt-md-0">
                        <span class="badge badge-present">
                            <i class="fas fa-check-circle"></i> Sistem Online
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistik Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="stat-card p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stats-label">
                            <i class="fas fa-chart-line text-maroon"></i>
                            Absensi Hari Ini
                        </div>
                        <div class="stats-number">{{ $todayAttendance }}</div>
                        <small class="text-muted">Total absensi terkonfirmasi</small>
                    </div>
                    <div class="icon-sm">
                        <i class="fas fa-fingerprint"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stats-label">
                            <i class="fas fa-user-check text-maroon"></i>
                            Hadir / Terlambat
                        </div>
                        <div class="stats-number">{{ $todayPresent }}</div>
                        <small class="text-muted">Dari {{ $todayAttendance }} siswa</small>
                    </div>
                    <div class="icon-sm">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stats-label">
                            <i class="fas fa-hourglass-half text-maroon"></i>
                            Terlambat
                        </div>
                        <div class="stats-number">{{ $todayLate }}</div>
                        <small class="text-muted">Siswa terlambat masuk</small>
                    </div>
                    <div class="icon-sm">
                        <i class="fas fa-stopwatch"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stats-label">
                            <i class="fas fa-microchip text-maroon"></i>
                            Device Online
                        </div>
                        <div class="stats-number">{{ $onlineDevices }}/{{ $totalDevices }}</div>
                        <small class="text-muted">Device terhubung aktif</small>
                    </div>
                    <div class="icon-sm">
                        <i class="fas fa-wifi"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Grafik dan Statistik -->
    <div class="row mb-4">
        <div class="col-md-8 mb-3">
            <div class="modern-card">
                <div class="card-header-custom">
                    <h6 class="section-title">
                        <i class="fas fa-chart-line text-maroon"></i>
                        Grafik Absensi 7 Hari Terakhir
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="attendanceChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="modern-card">
                <div class="card-header-custom">
                    <h6 class="section-title">
                        <i class="fas fa-chart-pie text-maroon"></i>
                        Statistik Bulan Ini
                    </h6>
                </div>
                <div class="card-body text-center">
                    <div>
                        <div class="stats-number" style="font-size: 3rem;">{{ $monthAttendance }}</div>
                        <p class="text-muted mb-3">Total Absensi</p>
                        <div class="progress-modern mb-4">
                            <div class="progress-bar-maroon" style="width: {{ $monthAttendance > 0 ? min(($monthAttendance / 1000) * 100, 100) : 0 }}%; height: 6px;"></div>
                        </div>
                    </div>
                    <div class="divider"></div>
                    <div class="row mt-3">
                        <div class="col-6">
                            <div class="stats-number" style="font-size: 1.8rem;">{{ $todayNotifications }}</div>
                            <small class="text-muted">
                                <i class="fas fa-bell me-1 text-maroon"></i>Notifikasi Hari Ini
                            </small>
                        </div>
                        <div class="col-6">
                            <div class="stats-number" style="font-size: 1.8rem; color: var(--maroon-light);">{{ $failedNotifications }}</div>
                            <small class="text-muted">
                                <i class="fas fa-exclamation-triangle me-1 text-maroon-light"></i>Gagal Terkirim
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Terbaru -->
    <div class="row">
        <div class="col-md-6 mb-3">
            <div class="modern-card">
                <div class="card-header-custom d-flex justify-content-between align-items-center">
                    <h6 class="section-title mb-0">
                        <i class="fas fa-history text-maroon"></i>
                        Absensi Terbaru
                    </h6>
                    <a href="{{ route('attendance.index') }}" class="btn-outline-maroon btn-sm">
                        <i class="fas fa-chart-line me-1"></i>Lihat Semua
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table modern-table mb-0">
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
                                    <td>
                                        <i class="fas fa-user-graduate me-2 text-maroon"></i>
                                        {{ $attendance->student->name ?? '-' }}
                                    </td>
                                    <td>
                                        <i class="far fa-clock me-1 text-muted"></i>
                                        {{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i:s') : '--:--' }}
                                    </td>
                                    <td>
                                        @if($attendance->status == 'present')
                                            <span class="badge-modern badge-present">
                                                <i class="fas fa-check-circle"></i> Hadir
                                            </span>
                                        @elseif($attendance->status == 'late')
                                            <span class="badge-modern badge-late">
                                                <i class="fas fa-exclamation-circle"></i> Terlambat
                                            </span>
                                        @else
                                            <span class="badge-modern badge-absent">
                                                <i class="fas fa-times-circle"></i> Absen
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">
                                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                        Belum ada data
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="modern-card">
                <div class="card-header-custom d-flex justify-content-between align-items-center">
                    <h6 class="section-title mb-0">
                        <i class="fas fa-bell text-maroon"></i>
                        Notifikasi Terbaru
                    </h6>
                    <a href="{{ route('notifications.index') }}" class="btn-outline-maroon btn-sm">
                        <i class="fas fa-bell me-1"></i>Lihat Semua
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table modern-table mb-0">
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
                                    <td>
                                        <i class="fas fa-mobile-alt me-2 text-maroon"></i>
                                        {{ $notification->recipient_phone }}
                                    </td>
                                    <td>
                                        @if($notification->status == 'sent')
                                            <span class="badge-modern badge-sent">
                                                <i class="fas fa-paper-plane"></i> Terkirim
                                            </span>
                                        @elseif($notification->status == 'failed')
                                            <span class="badge-modern badge-failed">
                                                <i class="fas fa-ban"></i> Gagal
                                            </span>
                                        @else
                                            <span class="badge-modern badge-pending">
                                                <i class="fas fa-spinner"></i> Pending
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <i class="far fa-clock me-1 text-muted"></i>
                                        {{ $notification->created_at->diffForHumans() }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">
                                        <i class="fas fa-bell-slash fa-2x mb-2 d-block"></i>
                                        Belum ada notifikasi
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Live Clock
    function updateClock() {
        const now = new Date();
        const time = now.toLocaleTimeString('id-ID', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
        document.getElementById('liveClock').innerHTML = time;
    }
    setInterval(updateClock, 1000);
    updateClock();

    // Chart Absensi dengan tema Maroon
    const ctx = document.getElementById('attendanceChart').getContext('2d');
    const chartData = @json($chartData);

    // Gradient colors for maroon theme
    const gradientPresent = ctx.createLinearGradient(0, 0, 0, 400);
    gradientPresent.addColorStop(0, 'rgba(128, 0, 32, 0.3)');
    gradientPresent.addColorStop(1, 'rgba(128, 0, 32, 0.02)');

    const gradientAbsent = ctx.createLinearGradient(0, 0, 0, 400);
    gradientAbsent.addColorStop(0, 'rgba(184, 92, 110, 0.3)');
    gradientAbsent.addColorStop(1, 'rgba(184, 92, 110, 0.02)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.map(item => item.date),
            datasets: [
                {
                    label: 'Hadir',
                    data: chartData.map(item => item.present),
                    borderColor: '#800020',
                    backgroundColor: gradientPresent,
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#800020',
                    pointBorderColor: '#ffffff',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBorderWidth: 2
                },
                {
                    label: 'Absen',
                    data: chartData.map(item => item.absent),
                    borderColor: '#B85C6E',
                    backgroundColor: gradientAbsent,
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#B85C6E',
                    pointBorderColor: '#ffffff',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBorderWidth: 2
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        color: '#5C0016',
                        font: {
                            size: 12,
                            weight: '500'
                        },
                        usePointStyle: true,
                        boxWidth: 10
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: '#ffffff',
                    titleColor: '#800020',
                    bodyColor: '#4a5568',
                    borderColor: '#800020',
                    borderWidth: 1,
                    callbacks: {
                        label: function(context) {
                            return `${context.dataset.label}: ${context.raw} siswa`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    grid: {
                        color: '#E9ECEF',
                        drawBorder: true
                    },
                    ticks: {
                        color: '#6c757d',
                        stepSize: 1
                    },
                    title: {
                        display: true,
                        text: 'Jumlah Siswa',
                        color: '#800020'
                    }
                },
                x: {
                    grid: {
                        color: '#E9ECEF'
                    },
                    ticks: {
                        color: '#6c757d'
                    }
                }
            },
            interaction: {
                mode: 'nearest',
                axis: 'x',
                intersect: false
            },
            animation: {
                duration: 1500,
                easing: 'easeInOutQuart'
            }
        }
    });
</script>
@endpush
