<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class NotificationLog extends Model
{
    protected $fillable = [
        'sensor_id',
        'sensor_code',
        'sensor_name',
        'sensor_type',
        'condition',
        'sensor_value',
        'message'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationship: NotificationLog belongs to Sensor
    public function sensor()
    {
        return $this->belongsTo(Sensor::class);
    }

    // Get condition name (Laravel 12 format)
    protected function conditionName(): Attribute
    {
        return Attribute::make(
            get: fn () => match($this->condition) {
                1 => 'Normal',
                2 => 'Waspada',
                3 => 'Bahaya',
                default => 'Unknown'
            }
        );
    }
}
