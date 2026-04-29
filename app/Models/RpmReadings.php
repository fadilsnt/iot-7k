<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RpmReadings extends Model
{
    protected $fillable = [
        'rpm_id',
        'recorded_at',
        'rpm',
        'hm',
        'temp',
        'humi',
    ];
}
