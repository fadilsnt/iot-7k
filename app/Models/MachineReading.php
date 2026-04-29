<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MachineReading extends Model
{
    protected $fillable = [
        'machine_id',
        'recorded_at',
        'amp',
        'hm',
        'temp',
        'moist',
    ];
}
