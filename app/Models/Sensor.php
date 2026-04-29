<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sensor extends Model
{
    use HasFactory;

    protected $fillable = [
        'sensor_type_id',
        'sensor_code',
        'name',
        'location',
        'description',
        'status',
        'user_id',
        'threshold_plus_1',
        'threshold_plus_2',
        'threshold_min_1',
        'threshold_min_2',
        'condition'
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
    ];

    // Relationship: Sensor belongs to User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship: Sensor belongs to SensorType
    public function sensorType()
    {
        return $this->belongsTo(SensorType::class);
    }

    // Relationship: Sensor has many Readings
    public function readings()
    {
        return $this->hasMany(SensorReading::class);
    }

    // Relationship: Sensor has many Threshold Settings
    public function thresholds()
    {
        return $this->hasMany(ThresholdSetting::class);
    }

    // Helper: Get latest reading
    public function latestReading()
    {
        return $this->hasOne(SensorReading::class)->latestOfMany('recorded_at');
    }

    // Helper method: Check if sensor is online (seen in last 5 minutes)
    public function isOnline()
    {
        if (!$this->last_seen_at) {
            return false;
        }
        return $this->last_seen_at->diffInMinutes(now()) <= 5;
    }

    // Helper method: Update condition based on latest reading and thresholds
    public function updateCondition()
    {
        $latestReading = $this->latestReading;
        
        if (!$latestReading) {
            $this->update(['condition' => 1]); // Default to normal if no reading
            return 1;
        }

        $value = $latestReading->value;
        $condition = 1; // Default: Normal

        // Check danger conditions
        if (($this->threshold_plus_2 !== null && $value > $this->threshold_plus_2) ||
            ($this->threshold_min_2 !== null && $value < $this->threshold_min_2)) {
            $condition = 3; // Danger
        }
        // Check warning conditions
        elseif (($this->threshold_plus_1 !== null && $this->threshold_plus_2 !== null && 
                 $value > $this->threshold_plus_1 && $value <= $this->threshold_plus_2) ||
                ($this->threshold_min_1 !== null && $this->threshold_min_2 !== null && 
                 $value < $this->threshold_min_1 && $value >= $this->threshold_min_2)) {
            $condition = 2; // Warning
        }

        $this->update(['condition' => $condition]);
        return $condition;
    }

    // Helper method: Get condition name
    public function getConditionName()
    {
        return match($this->condition) {
            1 => 'Normal',
            2 => 'Waspada',
            3 => 'Bahaya',
            default => 'Unknown'
        };
    }
}
