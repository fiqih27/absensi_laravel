<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    /**
     * Nama tabel yang digunakan
     */
    protected $table = 'notifications';

    /**
     * Atribut yang dapat diisi secara massal
     */
    protected $fillable = [
        'attendance_id',
        'recipient_phone',
        'message',
        'status',
        'wa_response',
    ];

    /**
     * Atribut yang harus di-cast
     */
    protected $casts = [
        'wa_response' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Status notifikasi
     */
    const STATUS_PENDING = 'pending';
    const STATUS_SENT = 'sent';
    const STATUS_FAILED = 'failed';

    // ==================== RELATIONS ====================

    /**
     * Relasi ke tabel attendances
     */
    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class, 'attendance_id');
    }

    // ==================== SCOPES ====================

    /**
     * Scope untuk notifikasi pending
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope untuk notifikasi yang berhasil
     */
    public function scopeSent($query)
    {
        return $query->where('status', self::STATUS_SENT);
    }

    /**
     * Scope untuk notifikasi yang gagal
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    // ==================== METHODS ====================

    /**
     * Cek apakah notifikasi berhasil
     */
    public function isSuccess(): bool
    {
        return $this->status === self::STATUS_SENT;
    }

    /**
     * Cek apakah notifikasi gagal
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Cek apakah notifikasi pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Update status notifikasi
     */
    public function updateStatus(string $status, ?array $response = null): bool
    {
        $this->status = $status;

        if ($response) {
            $this->wa_response = $response;
        }

        return $this->save();
    }

    /**
     * Tandai sebagai sukses
     */
    public function markAsSent(?array $response = null): bool
    {
        return $this->updateStatus(self::STATUS_SENT, $response);
    }

    /**
     * Tandai sebagai gagal
     */
    public function markAsFailed(?array $response = null): bool
    {
        return $this->updateStatus(self::STATUS_FAILED, $response);
    }
}
