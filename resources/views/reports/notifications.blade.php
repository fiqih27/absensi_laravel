@extends('layouts.app')

@section('title', 'Laporan Notifikasi')

@section('content')
<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-bell me-2"></i> Laporan Notifikasi WhatsApp</h5>
    </div>
    <div class="card-body">
        <!-- Filter Form -->
        <form method="GET" action="{{ route('reports.notifications') }}" class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">Semua</option>
                    <option value="sent" {{ $status == 'sent' ? 'selected' : '' }}>Terkirim</option>
                    <option value="failed" {{ $status == 'failed' ? 'selected' : '' }}>Gagal</option>
                    <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>Pending</option>
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

        <!-- Statistik Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h3>{{ $stats['total'] }}</h3>
                        <small>Total Notifikasi</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h3>{{ $stats['sent'] }}</h3>
                        <small>Terkirim</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body text-center">
                        <h3>{{ $stats['failed'] }}</h3>
                        <small>Gagal</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body text-center">
                        <h3>{{ $stats['pending'] }}</h3>
                        <small>Pending</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Notifikasi -->
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="notificationTable">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal</th>
                        <th>Siswa</th>
                        <th>Penerima</th>
                        <th>Pesan</th>
                        <th>Status</th>
                        <th>Response</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($notifications as $notification)
                    <tr>
                        <td>{{ $notification->created_at->format('d/m/Y H:i:s') }}</td>
                        <td>{{ $notification->attendance->student->name ?? '-' }}</td>
                        <td>{{ $notification->recipient_phone }}</td>
                        <td>{{ Str::limit($notification->message, 50) }}</td>
                        <td>
                            @if($notification->status == 'sent')
                                <span class="badge bg-success">Terkirim</span>
                            @elseif($notification->status == 'failed')
                                <span class="badge bg-danger">Gagal</span>
                            @else
                                <span class="badge bg-warning text-dark">Pending</span>
                            @endif
                        </td>
                        <td>
                            @if($notification->wa_response)
                                <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#responseModal{{ $notification->id }}">
                                    <i class="fas fa-eye"></i> Lihat
                                </button>

                                <!-- Modal Response -->
                                <div class="modal fade" id="responseModal{{ $notification->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h6 class="modal-title">Response API</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <pre class="small">{{ json_encode($notification->wa_response, JSON_PRETTY_PRINT) }}</pre>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-end mt-3">
            {{ $notifications->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#notificationTable').DataTable({
            order: [[0, 'desc']],
            pageLength: 25,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
            }
        });
    });
</script>
@endpush
