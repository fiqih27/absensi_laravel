@extends('layouts.app')

@section('title', 'Manajemen Notifikasi')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
        <h5><i class="fas fa-bell me-2"></i> Riwayat Notifikasi WhatsApp</h5>
        <div>
          <!--  <a href="{{ route('notifications.broadcast') }}" class="btn btn-success btn-sm">
                <i class="fas fa-broadcast-tower me-1"></i> Broadcast
            </a>
             <a href="{{ route('notifications.broadcast.history') }}" class="btn btn-success btn-sm">
                <i class="fas fa-sync-alt me-1"></i> riwayat Broadcast
            </a>-->
            <form action="{{ route('notifications.resend-all') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-warning btn-sm" onclick="return confirm('Kirim ulang semua notifikasi yang gagal?')">
                    <i class="fas fa-sync-alt me-1"></i> Kirim Ulang Gagal
                </button>
            </form>
            <form action="{{ route('notifications.delete-old') }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Hapus notifikasi lama (>30 hari)?')">
                    <i class="fas fa-trash me-1"></i> Hapus Lama
                </button>
            </form>
        </div>
    </div>
    <div class="card-body">
        <!-- Statistik -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center py-2">
                        <h4>{{ $stats['total'] }}</h4>
                        <small>Total</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center py-2">
                        <h4>{{ $stats['sent'] }}</h4>
                        <small>Terkirim</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body text-center py-2">
                        <h4>{{ $stats['failed'] }}</h4>
                        <small>Gagal</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body text-center py-2">
                        <h4>{{ $stats['pending'] }}</h4>
                        <small>Pending</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter -->
        <form method="GET" action="{{ route('notifications.index') }}" class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="form-label">Filter Status</label>
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua</option>
                    <option value="sent" {{ $status == 'sent' ? 'selected' : '' }}>Terkirim</option>
                    <option value="failed" {{ $status == 'failed' ? 'selected' : '' }}>Gagal</option>
                    <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>Pending</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Cari (Nomor HP)</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Cari nomor HP...">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-1"></i> Cari
                </button>
            </div>
        </form>

        <!-- Tabel -->
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Tanggal</th>
                        <th>Siswa</th>
                        <th>Penerima</th>
                        <th>Pesan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notifications as $notification)
                    <tr>
                        <td>{{ $notification->id }}</td>
                        <td>{{ $notification->created_at->format('d/m/Y H:i:s') }}</td>
                        <td>{{ $notification->attendance->student->name ?? '-' }}</td>
                        <td>{{ $notification->recipient_phone }}</td>
                        <td>{{ Str::limit($notification->message, 60) }} <button type="button" class="btn btn-sm btn-link" data-bs-toggle="modal" data-bs-target="#messageModal{{ $notification->id }}">Lihat</button></td>
                        <td>
                            @if($notification->status == 'sent')
                                <span class="badge bg-success">Terkirim</span>
                            @elseif($notification->status == 'failed')
                                <span class="badge bg-danger">Gagal</span>
                            @else
                                <span class="badge bg-warning text-dark">Pending</span>
                            @endif
                        \n
                        <td>
                            <a href="{{ route('notifications.show', $notification) }}" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if($notification->status == 'failed')
                                <form action="{{ route('notifications.resend', $notification) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Kirim ulang notifikasi ini?')">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </form>
                            @endif
                            <form action="{{ route('notifications.destroy', $notification) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus notifikasi ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>

                    <!-- Modal Pesan -->
                    <div class="modal fade" id="messageModal{{ $notification->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h6 class="modal-title">Isi Pesan Notifikasi</h6>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <pre class="small bg-light p-3 rounded" style="white-space: pre-wrap;">{{ $notification->message }}</pre>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                            Tidak ada data notifikasi
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-end mt-3">
            {{ $notifications->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection
