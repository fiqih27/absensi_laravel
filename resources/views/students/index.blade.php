@extends('layouts.app')

@section('title', 'Data Siswa')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5><i class="fas fa-user-graduate me-2"></i> Data Siswa</h5>
        <a href="{{ route('students.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Tambah Siswa
        </a>
    </div>
    <div class="card-body">
        <!-- Search Form -->
        <form method="GET" action="{{ route('students.index') }}" class="row g-3 mb-4">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Cari nama atau NISN..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-1"></i> Cari
                </button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('students.index') }}" class="btn btn-secondary w-100">
                    <i class="fas fa-sync-alt me-1"></i> Reset
                </a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>NISN</th>
                        <th>Nama Lengkap</th>
                        <th>Kelas</th>
                        <th>Fingerprint UID</th>
                        <th>Device User ID</th>
                        <th>Status</th>
                        <th>Orang Tua / Wali</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $index => $student)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $student->nisn }}</td>
                        <td>{{ $student->name }}</td>
                        <td>{{ $student->class }}</td>
                        <td>
                            @if($student->fingerprint_uid)
                                <code class="small">{{ $student->fingerprint_uid }}</code>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($student->device_user_id)
                                <code class="small">{{ $student->device_user_id }}</code>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($student->status == 'active')
                                <span class="badge bg-success"><i class="fas fa-check-circle"></i> Aktif</span>
                            @else
                                <span class="badge bg-secondary"><i class="fas fa-ban"></i> Tidak Aktif</span>
                            @endif
                        </td>
                        <td>
                            @if($student->parent)
                                <div>
                                    <strong>{{ $student->parent->name }}</strong><br>
                                    <small class="text-muted">
                                        <i class="fab fa-whatsapp"></i> {{ $student->parent->phone }}
                                    </small>
                                </div>
                            @else
                                <span class="text-muted">Belum ada data</span>
                                <a href="{{ route('parents.create', ['student_id' => $student->id]) }}" class="btn btn-sm btn-link p-0">
                                    <i class="fas fa-plus"></i> Tambah
                                </a>
                            @endif
                        </td>
                        <td>

                                <a href="{{ route('students.edit', $student) }}" class="btn btn-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('students.destroy', $student) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus siswa {{ $student->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                           
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            <i class="fas fa-user-graduate fa-2x mb-2 d-block"></i>
                            Belum ada data siswa. Silakan tambah siswa baru.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if(method_exists($students, 'links'))
            <div class="d-flex justify-content-end mt-3">
                {{ $students->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
