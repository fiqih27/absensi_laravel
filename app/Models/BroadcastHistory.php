<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BroadcastHistory extends Model
{
    use HasFactory;

    protected $table = 'broadcast_histories';

    protected $fillable = [
        'broadcast_id',
        'message',
        'recipient_type',
        'total_recipients',
        'sent_count',
        'failed_count',
        'recipients_detail',
        'failed_recipients',
        'status',
        'notes',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'recipients_detail' => 'array',
        'failed_recipients' => 'array',
        'total_recipients' => 'integer',
        'sent_count' => 'integer',
        'failed_count' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    /**
     * Relasi ke tabel notifications (opsional)
     * Jika ingin menghubungkan dengan notifikasi individual
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'broadcast_id', 'broadcast_id');
    }

    /**
     * Scope untuk broadcast yang sedang diproses
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }

    /**
     * Scope untuk broadcast yang selesai
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Hitung persentase keberhasilan
     */
    public function getSuccessRateAttribute(): float
    {
        if ($this->total_recipients == 0) {
            return 0;
        }

        return round(($this->sent_count / $this->total_recipients) * 100, 2);
    }

    /**
     * Cek apakah broadcast selesai
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Tandai sebagai selesai
     */
    public function markAsCompleted(): bool
    {
        $this->status = self::STATUS_COMPLETED;
        $this->completed_at = now();

        return $this->save();
    }

    /**
     * Update statistik broadcast
     */
    public function updateStats(int $sent, int $failed): bool
    {
        $this->sent_count = $sent;
        $this->failed_count = $failed;

        if ($this->sent_count + $this->failed_count >= $this->total_recipients) {
            $this->status = self::STATUS_COMPLETED;
            $this->completed_at = now();
        }

        return $this->save();
    }
}
