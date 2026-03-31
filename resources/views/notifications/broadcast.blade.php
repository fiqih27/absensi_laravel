@extends('layouts.app')

@section('title', 'Broadcast Notifikasi')

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-broadcast-tower me-2"></i> Broadcast Notifikasi WhatsApp</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('notifications.send-broadcast') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Penerima <span class="text-danger">*</span></label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="recipients" id="all" value="all" checked>
                            <label class="form-check-label" for="all">
                                Semua Orang Tua ({{ \App\Models\ParentModel::count() }} penerima)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="recipients" id="active_only" value="active_only">
                            <label class="form-check-label" for="active_only">
                                Siswa Aktif Saja ({{ \App\Models\ParentModel::whereHas('student', function($q) { $q->where('status', 'active'); })->count() }} penerima)
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="message" class="form-label">Pesan Broadcast <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('message') is-invalid @enderror"
                                  id="message" name="message" rows="8" required
                                  placeholder="Tulis pesan broadcast di sini...&#10;&#10;Contoh:&#10;Assalamu'alaikum Wr. Wb.&#10;&#10;Diberitahukan kepada seluruh orang tua/wali siswa, bahwa ..."></textarea>
                        @error('message')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Pesan akan dikirim ke semua nomor WhatsApp yang terdaftar</small>
                    </div>

                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Perhatian:</strong> Broadcast akan mengirim pesan ke semua penerima. Pastikan pesan sudah benar.
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('notifications.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-primary" onclick="return confirm('Kirim broadcast ke semua penerima?')">
                            <i class="fas fa-paper-plane me-1"></i> Kirim Broadcast
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
