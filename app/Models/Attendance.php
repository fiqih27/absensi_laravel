<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Attendance extends Model
{
    protected $fillable = [
        'student_id', 'device_id', 'date', 'check_in', 'check_out',
        'status', 'note', 'verification_method'
    ];

    protected $casts = [
        'date' => 'date',
        'check_in' => 'datetime',
        'check_out' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function notification(): HasOne
    {
        return $this->hasOne(Notification::class);
    }
}
