@extends('layouts.app')

@section('title', 'Edit Device')

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-edit me-2"></i> Edit Device: {{ $device->device_name }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('devices.update', $device) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="device_name" class="form-label">Nama Device <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('device_name') is-invalid @enderror"
                               id="device_name" name="device_name" value="{{ old('device_name', $device->device_name) }}" required>
                        @error('device_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="ip_address" class="form-label">IP Address <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('ip_address') is-invalid @enderror"
                               id="ip_address" name="ip_address" value="{{ old('ip_address', $device->ip_address) }}" required>
                        @error('ip_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="port" class="form-label">Port <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('port') is-invalid @enderror"
                               id="port" name="port" value="{{ old('port', $device->port) }}" required>
                        @error('port')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="location" class="form-label">Lokasi Pemasangan</label>
                        <input type="text" class="form-control @error('location') is-invalid @enderror"
                               id="location" name="location" value="{{ old('location', $device->location) }}">
                        @error('location')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status Device</label>
                        <div class="form-control-plaintext">
                            @if($device->status == 'online')
                                <span class="badge bg-success"><i class="fas fa-circle"></i> Online</span>
                            @else
                                <span class="badge bg-danger"><i class="fas fa-circle"></i> Offline</span>
                            @endif
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('devices.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
