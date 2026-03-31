<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Device extends Model
{
    protected $fillable = [
        'device_name', 'ip_address', 'port', 'serial_number', 'status', 'location'
    ];

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }
}
