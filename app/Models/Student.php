<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Student extends Model
{
    protected $fillable = [
        'nisn', 'name', 'class', 'fingerprint_uid', 'device_user_id', 'status'
    ];

    public function parent(): HasOne
    {
        return $this->hasOne(ParentModel::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }
}
