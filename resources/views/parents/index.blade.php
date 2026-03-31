@extends('layouts.app')

@section('title', 'Data Orang Tua')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5><i class="fas fa-users me-2"></i> Data Orang Tua / Wali</h5>
        <a href="{{ route('parents.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Tambah Data
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="parentsTable">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Nama Orang Tua</th>
                        <th>No WhatsApp</th>
                        <th>Email</th>
                        <th>Nama Siswa</th>
                        <th>Kelas</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($parents as $index => $parent)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $parent->name }}</td>
                        <td>{{ $parent->phone }}</td>
                        <td>{{ $parent->email ?? '-' }}</td>
                        <td>{{ $parent->student->name ?? '-' }}</td>
                        <td>{{ $parent->student->class ?? '-' }}</td>
                        <td>
                            <a href="{{ route('parents.edit', $parent) }}" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('parents.destroy', $parent) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#parentsTable').DataTable({
            order: [[1, 'asc']],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
            }
        });
    });
</script>
@endpush
