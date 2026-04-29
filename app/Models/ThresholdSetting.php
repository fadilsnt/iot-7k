<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThresholdSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'sensor_id',
        'name',
        'min_value',
        'max_value',
        'operator',
        'is_active',
        'notification_method',
        'notification_email',
        'notification_phone',
        'cooldown_minutes',
        'last_triggered_at'
    ];

    protected $casts = [
        'min_value' => 'decimal:2',
        'max_value' => 'decimal:2',
        'is_active' => 'boolean',
        'last_triggered_at' => 'datetime',
    ];

    // Relationship: Threshold belongs to Sensor
    public function sensor()
    {
        return $this->belongsTo(Sensor::class);
    }

    // Helper: Check if value violates threshold
    public function isViolated($value)
    {
        switch ($this->operator) {
            case 'between':
                return $value < $this->min_value || $value > $this->max_value;
            case 'less_than':
                return $value < $this->min_value;
            case 'greater_than':
                return $value > $this->max_value;
            case 'equal_to':
                return $value == $this->min_value;
            default:
                return false;
        }
    }
}