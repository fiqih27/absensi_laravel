@extends('layouts.app')

@section('title', 'Absensi Hari Ini')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                <h5><i class="fas fa-calendar-day me-2"></i> Absensi Hari Ini - {{ $today->format('d F Y') }}</h5>
                <form action="{{ route('attendance.sync') }}" method="POST" class="d-inline-flex gap-2">
                    @csrf
                    <select name="device_id" class="form-select form-select-sm w-auto">
                        <option value="">Semua Device</option>
                        @foreach($devices as $device)
                            <option value="{{ $device->id }}">{{ $device->device_name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-sync-alt me-1"></i> Sinkronisasi
                    </button>
                </form>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="attendanceTable">
                        <thead class="table-light">
                            应用
                                <th>No</th>
                                <th>NISN</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th>Jam Masuk</th>
                                <th>Jam Pulang</th>
                                <th>Status</th>
                                <th>Device</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($attendances as $index => $attendance)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $attendance->student->nisn ?? '-' }}</td>
                                <td>{{ $attendance->student->name ?? '-' }}</td>
                                <td>{{ $attendance->student->class ?? '-' }}</td>
                                <td>{{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i:s') : '-' }}</td>
                                <td>{{ $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i:s') : '-' }}</td>
                                <td>
                                    @if($attendance->status == 'present')
                                        <span class="badge bg-success"><i class="fas fa-check-circle"></i> Hadir</span>
                                    @elseif($attendance->status == 'late')
                                        <span class="badge bg-warning text-dark"><i class="fas fa-clock"></i> Terlambat</span>
                                    @elseif($attendance->status == 'permission')
                                        <span class="badge bg-info"><i class="fas fa-envelope"></i> Izin</span>
                                    @else
                                        <span class="badge bg-danger"><i class="fas fa-times-circle"></i> Absen</span>
                                    @endif
                                </td>
                                <td>{{ $attendance->device->device_name ?? '-' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-info-circle me-2"></i> Belum ada data absensi hari ini
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

<!-- Device Status Cards (remain same) -->
<div class="row mt-4">
    <div class="col-12">
        <h6><i class="fas fa-microchip me-2"></i> Status Device</h6>
    </div>
    @foreach($devices as $device)
    <div class="col-md-4 mt-2">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">{{ $device->device_name }}</h6>
                        <small class="text-muted">{{ $device->ip_address }}:{{ $device->port }}</small>
                        @if($device->location)
                            <br><small class="text-muted"><i class="fas fa-map-marker-alt"></i> {{ $device->location }}</small>
                        @endif
                    </div>
                    <div>
                        <span id="status-{{ $device->id }}">
                            @if($device->status == 'online')
                                <span class="badge bg-success"><i class="fas fa-plug"></i> Online</span>
                            @else
                                <span class="badge bg-danger"><i class="fas fa-plug"></i> Offline</span>
                            @endif
                        </span>
                    </div>
                </div>
                <div class="mt-2">
                    <button onclick="checkDevice({{ $device->id }})" class="btn btn-sm btn-outline-primary w-100">
                        <i class="fas fa-search"></i> Cek Status
                    </button>
                </div>
                <div id="info-{{ $device->id }}" class="mt-2 small text-muted"></div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection

@push('scripts')
<script>
    function checkDevice(deviceId) {
        const statusSpan = document.getElementById(`status-${deviceId}`);
        const infoDiv = document.getElementById(`info-${deviceId}`);

        statusSpan.innerHTML = '<span class="badge bg-warning text-dark"><i class="fas fa-spinner fa-spin"></i> Checking...</span>';

        fetch(`/attendance/check-device/${deviceId}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'online') {
                    statusSpan.innerHTML = '<span class="badge bg-success"><i class="fas fa-plug"></i> Online</span>';
                    infoDiv.innerHTML = `
                        <i class="fas fa-info-circle"></i> Version: ${data.info.version || '-'}<br>
                        <i class="fas fa-microchip"></i> Platform: ${data.info.platform || '-'}<br>
                        <i class="fas fa-barcode"></i> Serial: ${data.info.serial || '-'}<br>
                        <i class="fas fa-clock"></i> Time: ${data.info.time || '-'}
                    `;
                } else {
                    statusSpan.innerHTML = '<span class="badge bg-danger"><i class="fas fa-plug"></i> Offline</span>';
                    infoDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Device tidak terhubung';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                statusSpan.innerHTML = '<span class="badge bg-danger"><i class="fas fa-exclamation-triangle"></i> Error</span>';
                infoDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> Gagal mengecek status';
            });
    }

    $(document).ready(function() {
        // Pastikan tabel memiliki data sebelum menginisialisasi DataTables
        var $table = $('#attendanceTable');
        var $tbody = $table.find('tbody');
        var hasRealData = $tbody.find('tr').length > 0 && $tbody.find('td[colspan]').length === 0;

        if (hasRealData) {
            $table.DataTable({
                order: [[4, 'desc']],
                pageLength: 25,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
                }
            });
        } else {
            // Jika tidak ada data, kita hanya menampilkan tabel biasa tanpa inisialisasi DataTables
            // Menambahkan class untuk styling tetap rapi
            $table.addClass('table');
        }
    });

    // Auto refresh status device setiap 30 detik
    setInterval(function() {
        @foreach($devices as $device)
        checkDevice({{ $device->id }});
        @endforeach
    }, 30000);
</script>
@endpush
