@extends('layouts.app')

@section('title', 'Pengaturan Sistem')

@section('content')
<div class="row">
    <div class="col-md-12">
        <ul class="nav nav-tabs mb-4" id="settingsTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">
                    <i class="fas fa-cog me-1"></i> Umum
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="whatsapp-tab" data-bs-toggle="tab" data-bs-target="#whatsapp" type="button" role="tab">
                    <i class="fab fa-whatsapp me-1"></i> WhatsApp
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="sync-tab" data-bs-toggle="tab" data-bs-target="#sync" type="button" role="tab">
                    <i class="fas fa-sync-alt me-1"></i> Sinkronisasi
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="system-tab" data-bs-toggle="tab" data-bs-target="#system" type="button" role="tab">
                    <i class="fas fa-database me-1"></i> Sistem
                </button>
            </li>
        </ul>

        <div class="tab-content">
            <!-- Tab Umum -->
            <div class="tab-pane fade show active" id="general" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h6>Pengaturan Umum</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('settings.update-general') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label class="form-label">Nama Aplikasi</label>
                                <input type="text" name="app_name" class="form-control" value="{{ $settings['app_name'] }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nama Sekolah/Instansi</label>
                                <input type="text" name="school_name" class="form-control" value="{{ $settings['school_name'] }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Alamat</label>
                                <textarea name="school_address" class="form-control" rows="2">{{ $settings['school_address'] }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nomor Telepon Sekolah</label>
                                <input type="text" name="school_phone" class="form-control" value="{{ $settings['school_phone'] }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Batas Waktu Terlambat</label>
                                <input type="time" name="late_threshold" class="form-control w-50" value="{{ $settings['late_threshold'] }}">
                                <small class="text-muted">Jam yang ditentukan sebagai batas keterlambatan</small>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Simpan Pengaturan
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Tab WhatsApp -->
            <div class="tab-pane fade" id="whatsapp" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h6>Pengaturan WhatsApp API</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('settings.update-whatsapp') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label class="form-label">API URL</label>
                                <input type="url" name="whatsapp_api_url" class="form-control" value="{{ $settings['whatsapp_api_url'] }}" required>
                                <small class="text-muted">URL endpoint WhatsApp API (Wablas/Fonnte/Meta Cloud)</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">API Token</label>
                                <input type="text" name="whatsapp_api_token" class="form-control" value="{{ $settings['whatsapp_api_token'] }}" required>
                                <small class="text-muted">Token API dari provider WhatsApp</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-bell me-1"></i> Nomor Penerima Notifikasi
                                </label>
                                <input type="text" name="whatsapp_broadcast_number" class="form-control" value="{{ $settings['whatsapp_broadcast_number'] ?? '' }}" placeholder="6281234567890">
                                <small class="text-muted">
                                    <strong>Nomor WhatsApp yang akan menerima semua notifikasi absensi.</strong><br>
                                    Format: 628xxxxxxxxxx (tanpa tanda + atau spasi). Kosongkan jika ingin mengirim ke masing-masing nomor orang tua.
                                </small>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="send_notification" class="form-check-input" id="send_notification" {{ ($settings['send_notification'] ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="send_notification">
                                        Aktifkan Pengiriman Notifikasi WhatsApp
                                    </label>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Simpan Pengaturan
                            </button>
                        </form>

                        <hr>

                        <h6>Test Koneksi WhatsApp</h6>
                        <form action="{{ route('settings.test-whatsapp') }}" method="POST" class="row g-3">
                            @csrf
                            <div class="col-md-8">
                                <input type="text" name="test_phone" class="form-control" placeholder="Nomor WhatsApp untuk test (contoh: 081234567890)">
                                <small class="text-muted">Biarkan kosong untuk mengirim test ke nomor penerima notifikasi</small>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fab fa-whatsapp me-1"></i> Test Kirim Pesan
                                </button>
                            </div>
                        </form>

                        <!-- Informasi Nomor Penerima Notifikasi Saat Ini -->
                        @if(!empty($settings['whatsapp_broadcast_number']))
                        <div class="alert alert-info mt-3 mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Mode Broadcast Aktif:</strong> Semua notifikasi absensi akan dikirim ke nomor
                            <strong>{{ $settings['whatsapp_broadcast_number'] }}</strong>
                        </div>
                        @else
                        <div class="alert alert-warning mt-3 mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Mode Individual:</strong> Notifikasi akan dikirim ke nomor WhatsApp masing-masing orang tua siswa.
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Tab Sinkronisasi -->
            <div class="tab-pane fade" id="sync" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h6>Pengaturan Sinkronisasi Device</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('settings.update-sync') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label class="form-label">Interval Sinkronisasi (menit)</label>
                                <input type="number" name="sync_interval" class="form-control w-25" value="{{ $settings['sync_interval'] }}" min="1" max="60">
                                <small class="text-muted">Frekuensi sinkronisasi data dari device fingerprint</small>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="auto_sync" class="form-check-input" id="auto_sync" {{ $settings['auto_sync'] ? 'checked' : '' }}>
                                    <label class="form-check-label" for="auto_sync">
                                        Aktifkan Sinkronisasi Otomatis
                                    </label>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Simpan Pengaturan
                            </button>
                        </form>

                        <hr>

                        <h6>Test Koneksi Device</h6>
                        <form action="{{ route('settings.test-device') }}" method="POST" class="row g-3">
                            @csrf
                            <div class="col-md-8">
                                <select name="device_id" class="form-select" required>
                                    <option value="">-- Pilih Device --</option>
                                    @foreach(\App\Models\Device::all() as $device)
                                        <option value="{{ $device->id }}">{{ $device->device_name }} ({{ $device->ip_address }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fas fa-microchip me-1"></i> Test Koneksi
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Tab Sistem -->
            <div class="tab-pane fade" id="system" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h6>Pengaturan Sistem</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h6><i class="fas fa-database me-2"></i> Database</h6>
                                        <hr>
                                        <p class="small mb-2">
                                            <strong>Database:</strong> {{ env('DB_DATABASE') }}<br>
                                            <strong>Host:</strong> {{ env('DB_HOST') }}
                                        </p>
                                        <form action="{{ route('settings.backup') }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-warning btn-sm w-100">
                                                <i class="fas fa-database me-1"></i> Backup Database
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h6><i class="fas fa-broom me-2"></i> Cache Sistem</h6>
                                        <hr>
                                        <p class="small text-muted">Membersihkan cache aplikasi, konfigurasi, dan view</p>
                                        <form action="{{ route('settings.clear-cache') }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-danger btn-sm w-100" onclick="return confirm('Bersihkan cache sistem?')">
                                                <i class="fas fa-broom me-1"></i> Clear Cache
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <h6><i class="fas fa-info-circle me-2"></i> Informasi Sistem</h6>
                                <hr>
                                <table class="table table-sm">
                                    <tr>
                                        <td width="200">Versi</td>
                                        <td>: Rnd V1</td>
                                    </tr>
                                    <tr>
                                        <td>Server Time</td>
                                        <td>: {{ now()->format('d/m/Y H:i:s') }}</td>
                                    </tr>
                                    <tr>
                                        <td>Timezone</td>
                                        <td>: {{ config('app.timezone') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
