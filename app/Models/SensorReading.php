<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SensorReading extends Model
{
    use HasFactory;

    protected $fillable = [
        'sensor_id',
        'value',
        'recorded_at',
        'metadata'
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'recorded_at' => 'datetime',
        'metadata' => 'array',
    ];

    // Relationship: Reading belongs to Sensor
    public function sensor()
    {
        return $this->belongsTo(Sensor::class);
    }
}