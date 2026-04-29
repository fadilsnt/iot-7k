<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'device_id',
        'model',
        'description',
        'location',
        'status',
        'last_seen_at',
        'user_id'
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
    ];

    // Relationship: Device belongs to User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship: Device has many Sensors
    public function sensors()
    {
        return $this->hasMany(Sensor::class);
    }

    // Helper method: Check if device is online (seen in last 5 minutes)
    public function isOnline()
    {
        if (!$this->last_seen_at) {
            return false;
        }
        return $this->last_seen_at->diffInMinutes(now()) <= 5;
    }
}