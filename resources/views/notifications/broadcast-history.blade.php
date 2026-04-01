@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="mb-0">Riwayat Broadcast WhatsApp</h3>
            <div>
                <button type="button" class="btn btn-danger btn-sm" id="deleteSelectedBtn" style="display: none;">
                    <i class="fas fa-trash-alt me-1"></i> Hapus Terpilih
                </button>
                <button type="button" class="btn btn-warning btn-sm" id="deleteAllBtn">
                    <i class="fas fa-trash-alt me-1"></i> Hapus Semua
                </button>
            </div>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form id="deleteForm" action="{{ route('notifications.broadcast.delete-selected') }}" method="POST">
                @csrf
                @method('DELETE')

                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th width="50">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="selectAll">
                                        <label class="form-check-label" for="selectAll"></label>
                                    </div>
                                </th>
                                <th>ID Broadcast</th>
                                <th>Pesan</th>
                                <th>Tipe</th>
                                <th>Total</th>
                                <th>Berhasil</th>
                                <th>Gagal</th>
                                <th>Status</th>
                                <th>Waktu</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($broadcasts as $broadcast)
                            <tr>
                                <td class="text-center">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input broadcast-checkbox"
                                               name="selected_broadcasts[]" value="{{ $broadcast->broadcast_id }}">
                                    </div>
                                </td>
                                <td>
                                    <code>{{ Str::limit($broadcast->broadcast_id, 20) }}</code>
                                </td>
                                <td>
                                    <span title="{{ $broadcast->message }}">
                                        {{ Str::limit($broadcast->message, 50) }}
                                    </span>
                                </td>
                                <td>
                                    @if($broadcast->recipient_type == 'all')
                                        <span class="badge bg-primary">Semua</span>
                                    @else
                                        <span class="badge bg-info">Aktif Saja</span>
                                    @endif
                                </td>
                                <td class="text-center">{{ $broadcast->total_recipients }}</td>
                                <td class="text-center text-success">
                                    <i class="fas fa-check-circle"></i> {{ $broadcast->sent_count }}
                                </td>
                                <td class="text-center text-danger">
                                    <i class="fas fa-times-circle"></i> {{ $broadcast->failed_count }}
                                </td>
                                <td class="text-center">
                                    @if($broadcast->status == 'completed')
                                        <span class="badge bg-success">Selesai</span>
                                    @elseif($broadcast->status == 'processing')
                                        <span class="badge bg-warning">Diproses</span>
                                    @else
                                        <span class="badge bg-danger">Gagal</span>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ $broadcast->created_at->format('d/m/Y H:i') }}</small>
                                    <br>
                                    <small class="text-muted">{{ $broadcast->created_at->diffForHumans() }}</small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('notifications.broadcast.detail', $broadcast->broadcast_id) }}"
                                           class="btn btn-sm btn-info" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($broadcast->failed_count > 0)
                                        <form action="{{ route('notifications.broadcast.resend', $broadcast->broadcast_id) }}"
                                              method="POST" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-warning" title="Kirim Ulang Gagal">
                                                <i class="fas fa-paper-plane"></i>
                                            </button>
                                        </form>
                                        @endif
                                        <button type="button" class="btn btn-sm btn-danger delete-single-btn"
                                                data-id="{{ $broadcast->broadcast_id }}"
                                                data-name="{{ $broadcast->broadcast_id }}"
                                                title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>

                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>

            @if($broadcasts->isNotEmpty())
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        <span class="text-muted">
                            Menampilkan {{ $broadcasts->firstItem() }} - {{ $broadcasts->lastItem() }}
                            dari {{ $broadcasts->total() }} data
                        </span>
                    </div>
                    <div>
                        {{ $broadcasts->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Hapus Semua -->
<div class="modal fade" id="deleteAllModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus Semua</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus <strong>SEMUA</strong> riwayat broadcast?</p>
                <p class="text-danger mb-0">Tindakan ini tidak dapat dibatalkan!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form action="{{ route('notifications.broadcast.delete-all') }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Ya, Hapus Semua</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Hapus Single -->
<div class="modal fade" id="deleteSingleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus riwayat broadcast ini?</p>
                <p class="text-muted mb-2"><strong>ID:</strong> <span id="deleteBroadcastId"></span></p>
                <p class="text-danger mb-0">Tindakan ini tidak dapat dibatalkan!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteSingle">Ya, Hapus</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    let currentBroadcastId = null;
    let currentBroadcastName = null;

    // Select All checkbox
    $('#selectAll').on('change', function() {
        $('.broadcast-checkbox').prop('checked', $(this).prop('checked'));
        toggleDeleteButton();
    });

    // Individual checkbox change
    $('.broadcast-checkbox').on('change', function() {
        toggleDeleteButton();
        $('#selectAll').prop('checked', $('.broadcast-checkbox:checked').length === $('.broadcast-checkbox').length);
    });

    // Toggle delete selected button visibility
    function toggleDeleteButton() {
        var checkedCount = $('.broadcast-checkbox:checked').length;
        if (checkedCount > 0) {
            $('#deleteSelectedBtn').show();
            $('#deleteSelectedBtn').html('<i class="fas fa-trash-alt me-1"></i> Hapus Terpilih (' + checkedCount + ')');
        } else {
            $('#deleteSelectedBtn').hide();
        }
    }

    // Delete selected broadcasts
    $('#deleteSelectedBtn').on('click', function() {
        var checkedCount = $('.broadcast-checkbox:checked').length;
        if (checkedCount === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Tidak ada data terpilih',
                text: 'Silakan pilih setidaknya satu broadcast untuk dihapus.',
                confirmButtonColor: '#3085d6'
            });
            return;
        }

        Swal.fire({
            title: 'Konfirmasi Hapus',
            text: `Apakah Anda yakin ingin menghapus ${checkedCount} riwayat broadcast yang dipilih?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#deleteForm').submit();
            }
        });
    });

    // Delete all broadcasts
    $('#deleteAllBtn').on('click', function() {
        $('#deleteAllModal').modal('show');
    });

    // Delete single broadcast - menggunakan AJAX
    $(document).on('click', '.delete-single-btn', function() {
        currentBroadcastId = $(this).data('id');
        currentBroadcastName = $(this).data('name');
        $('#deleteBroadcastId').text(currentBroadcastName || currentBroadcastId);
        $('#deleteSingleModal').modal('show');
    });

    // Confirm delete single
    $('#confirmDeleteSingle').on('click', function() {
        if (!currentBroadcastId) {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'ID broadcast tidak valid.'
            });
            $('#deleteSingleModal').modal('hide');
            return;
        }

        // Tampilkan loading
        Swal.fire({
            title: 'Menghapus...',
            text: 'Mohon tunggu',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: '/notifications/broadcast/' + currentBroadcastId,
            type: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                Swal.close();
                $('#deleteSingleModal').modal('hide');

                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: response.message || 'Riwayat broadcast berhasil dihapus.',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            },
            error: function(xhr) {
                Swal.close();
                $('#deleteSingleModal').modal('hide');

                let errorMessage = 'Gagal menghapus broadcast.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.status === 404) {
                    errorMessage = 'Broadcast tidak ditemukan. Mungkin sudah dihapus sebelumnya.';
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: errorMessage,
                    confirmButtonColor: '#d33'
                }).then(() => {
                    // Reload untuk refresh data
                    location.reload();
                });
            }
        });
    });

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});
</script>
@endpush
