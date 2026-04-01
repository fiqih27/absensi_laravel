@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-broadcast-tower me-2"></i>
                    Detail Penerima Broadcast
                </h5>
                <div>
                    <span class="badge bg-info me-2">
                        <i class="fas fa-calendar me-1"></i>
                        {{ $broadcast->created_at->format('d/m/Y H:i') }}
                    </span>
                    <a href="{{ route('notifications.broadcast.history') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Kembali
                    </a>
                </div>
            </div>
        </div>

        <div class="card-body">
            <!-- Pesan Broadcast -->
            <div class="alert alert-secondary">
                <strong>Pesan:</strong><br>
                {{ $broadcast->message }}
            </div>

            <!-- Tabel Daftar Penerima -->
            <h6 class="mb-3">
                <i class="fas fa-users me-2"></i> Daftar Penerima
                <span class="badge bg-secondary">{{ count($broadcast->recipients_detail ?? []) + count($broadcast->failed_recipients ?? []) }}</span>
            </h6>

            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Nama Penerima</th>
                            <th>Nomor Telepon</th>
                            <th>Nama Siswa</th>
                            <th>Status</th>
                            <th>Waktu Kirim</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $no = 1; @endphp

                        {{-- Berhasil --}}
                        @foreach($broadcast->recipients_detail ?? [] as $recipient)
                        <tr>
                            <td>{{ $no++ }}</td>
                            <td>{{ $recipient['name'] ?? '-' }}</td>
                            <td>{{ $recipient['phone'] ?? '-' }}</td>
                            <td>{{ $recipient['student_name'] ?? '-' }}</td>
                            <td><span class="badge bg-success">✓ Berhasil</span></td>
                            <td>{{ isset($recipient['sent_at']) ? \Carbon\Carbon::parse($recipient['sent_at'])->format('d/m/Y H:i:s') : '-' }}</td>
                        </tr>
                        @endforeach

                        {{-- Gagal --}}
                        @foreach($broadcast->failed_recipients ?? [] as $recipient)
                        <tr class="table-danger">
                            <td>{{ $no++ }}</td>
                            <td>{{ $recipient['name'] ?? '-' }}</td>
                            <td>{{ $recipient['phone'] ?? '-' }}</td>
                            <td>{{ $recipient['student_name'] ?? '-' }}</td>
                            <td><span class="badge bg-danger">✗ Gagal</span></td>
                            <td>
                                {{ isset($recipient['sent_at']) ? \Carbon\Carbon::parse($recipient['sent_at'])->format('d/m/Y H:i:s') : '-' }}
                                @if(isset($recipient['error']))
                                    <br><small class="text-danger">{{ $recipient['error'] }}</small>
                                @endif
                            </td>
                        </tr>
                        @endforeach

                        @if(empty($broadcast->recipients_detail) && empty($broadcast->failed_recipients))
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                Tidak ada data penerima
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <!-- Ringkasan -->
            @if(!empty($broadcast->recipients_detail) || !empty($broadcast->failed_recipients))
            <div class="row mt-3">
                <div class="col-6 text-center">
                    <div class="border rounded p-2 bg-light">
                        <h6 class="mb-0 text-success">Berhasil</h6>
                        <h4 class="mb-0">{{ count($broadcast->recipients_detail ?? []) }}</h4>
                    </div>
                </div>
                <div class="col-6 text-center">
                    <div class="border rounded p-2 bg-light">
                        <h6 class="mb-0 text-danger">Gagal</h6>
                        <h4 class="mb-0">{{ count($broadcast->failed_recipients ?? []) }}</h4>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
