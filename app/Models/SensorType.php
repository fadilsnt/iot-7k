<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SensorType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'unit',
        'icon',
        'min_value',
        'max_value',
        'description'
    ];

    protected $casts = [
        'min_value' => 'decimal:2',
        'max_value' => 'decimal:2',
    ];

    // Relationship: SensorType has many Sensors
    public function sensors()
    {
        return $this->hasMany(Sensor::class);
    }
}