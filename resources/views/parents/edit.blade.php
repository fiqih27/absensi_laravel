@extends('layouts.app')

@section('title', 'Edit Data Orang Tua')

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-edit me-2"></i> Edit Data: {{ $parent->name }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('parents.update', $parent) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="student_id" class="form-label">Pilih Siswa <span class="text-danger">*</span></label>
                        <select class="form-select @error('student_id') is-invalid @enderror" id="student_id" name="student_id" required>
                            <option value="">-- Pilih Siswa --</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}" {{ old('student_id', $parent->student_id) == $student->id ? 'selected' : '' }}>
                                    {{ $student->nisn }} - {{ $student->name }} ({{ $student->class }})
                                </option>
                            @endforeach
                        </select>
                        @error('student_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Orang Tua/Wali <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name', $parent->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Nomor WhatsApp <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror"
                               id="phone" name="phone" value="{{ old('phone', $parent->phone) }}" required>
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email (Opsional)</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                               id="email" name="email" value="{{ old('email', $parent->email) }}">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('parents.index') }}" class="btn btn-secondary">
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
