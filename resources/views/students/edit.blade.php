@extends('layouts.app')

@section('title', 'Edit Siswa')

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-edit me-2"></i> Edit Siswa: {{ $student->name }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('students.update', $student) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="nisn" class="form-label">NISN <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nisn') is-invalid @enderror"
                               id="nisn" name="nisn" value="{{ old('nisn', $student->nisn) }}" required>
                        @error('nisn')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name', $student->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="class" class="form-label">Kelas <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('class') is-invalid @enderror"
                               id="class" name="class" value="{{ old('class', $student->class) }}" required>
                        @error('class')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="fingerprint_uid" class="form-label">Fingerprint UID</label>
                        <input type="text" class="form-control @error('fingerprint_uid') is-invalid @enderror"
                               id="fingerprint_uid" name="fingerprint_uid" value="{{ old('fingerprint_uid', $student->fingerprint_uid) }}">
                        @error('fingerprint_uid')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="device_user_id" class="form-label">Device User ID</label>
                        <input type="text" class="form-control @error('device_user_id') is-invalid @enderror"
                               id="device_user_id" name="device_user_id" value="{{ old('device_user_id', $student->device_user_id) }}">
                        @error('device_user_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                            <option value="active" {{ old('status', $student->status) == 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ old('status', $student->status) == 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('students.index') }}" class="btn btn-secondary">
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
