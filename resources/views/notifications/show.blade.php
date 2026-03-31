@extends('layouts.app')

@section('title', 'Detail Notifikasi')

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-bell me-2"></i> Detail Notifikasi #{{ $notification->id }}</h5>
                <a href="{{ route('notifications.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">ID Notifikasi</div>
                    <div class="col-md-8">: {{ $notification->id }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Tanggal</div>
                    <div class="col-md-8">: {{ $notification->created_at->format('d/m/Y H:i:s') }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Siswa</div>
                    <div class="col-md-8">: {{ $notification->attendance->student->name ?? '-' }} ({{ $notification->attendance->student->class ?? '-' }})</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Penerima</div>
                    <div class="col-md-8">: {{ $notification->recipient_phone }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Status</div>
                    <div class="col-md-8">
                        :
                        @if($notification->status == 'sent')
                            <span class="badge bg-success">Terkirim</span>
                        @elseif($notification->status == 'failed')
                            <span class="badge bg-danger">Gagal</span>
                        @else
                            <span class="badge bg-warning text-dark">Pending</span>
                        @endif
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Isi Pesan</div>
                    <div class="col-md-8">
                        <div class="bg-light p-3 rounded" style="white-space: pre-wrap;">{{ $notification->message }}</div>
                    </div>
                </div>
                @if($notification->wa_response)
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Response API</div>
                    <div class="col-md-8">
                        <pre class="bg-light p-3 rounded small">{{ json_encode($notification->wa_response, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                </div>
                @endif

                <hr>

                <div class="d-flex justify-content-between">
                    @if($notification->status == 'failed')
                        <form action="{{ route('notifications.resend', $notification) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-sync-alt me-1"></i> Kirim Ulang
                            </button>
                        </form>
                    @endif
                    <form action="{{ route('notifications.destroy', $notification) }}" method="POST" onsubmit="return confirm('Hapus notifikasi ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-1"></i> Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
