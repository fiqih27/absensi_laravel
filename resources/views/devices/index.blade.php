@extends('layouts.app')

@section('title', 'Manajemen Device')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5><i class="fas fa-microchip me-2"></i> Daftar Device Fingerprint</h5>
        <a href="{{ route('devices.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Tambah Device
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="devicesTable">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Nama Device</th>
                        <th>IP Address</th>
                        <th>Port</th>
                        <th>Lokasi</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($devices as $index => $device)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $device->device_name }}</td>
                        <td>{{ $device->ip_address }}</td>
                        <td>{{ $device->port }}</td>
                        <td>{{ $device->location ?? '-' }}</td>
                        <td>
                            @if($device->status == 'online')
                                <span class="badge bg-success"><i class="fas fa-circle"></i> Online</span>
                            @else
                                <span class="badge bg-danger"><i class="fas fa-circle"></i> Offline</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('devices.edit', $device) }}" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('devices.destroy', $device) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus device ini?')">
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
        $('#devicesTable').DataTable({
            order: [[0, 'asc']],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
            }
        });
    });
</script>
@endpush
