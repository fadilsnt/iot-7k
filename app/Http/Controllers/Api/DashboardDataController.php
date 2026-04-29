<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SensorReading;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardDataController extends Controller
{
    /**
     * Check if new data exists (ULTRA LIGHTWEIGHT - < 1KB response)
     * Hanya query timestamp, tidak load full data
     */
    public function checkNewData(Request $request)
    {
        $lastTimestamp = $request->input('last_timestamp');
        $lastCheck = $lastTimestamp ? Carbon::parse($lastTimestamp) : Carbon::now()->subMinutes(5);
        
        // Query paling ringan - hanya cek MAX timestamp
        $latestTimestamp = SensorReading::max('created_at');
        
        if (!$latestTimestamp || Carbon::parse($latestTimestamp) <= $lastCheck) {
            return response()->json([
                'has_new_data' => false,
                'last_timestamp' => $lastCheck->toIso8601String()
            ]);
        }
        
        return response()->json([
            'has_new_data' => true,
            'last_timestamp' => Carbon::parse($latestTimestamp)->toIso8601String()
        ]);
    }
    
    /**
     * Get latest sensor data - hanya dipanggil jika checkNewData return true
     */
    public function getLatestData(Request $request)
    {
        $lastTimestamp = $request->input('last_timestamp');
        $lastCheck = $lastTimestamp ? Carbon::parse($lastTimestamp) : Carbon::now()->subMinutes(5);
        
        // Get temperature data (sensor_type_id = 1)
        $temperatureReadings = SensorReading::whereHas('sensor', function($query) {
                $query->where('sensor_type_id', 1);
            })
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();
        
        // Get moisture data (sensor_type_id = 2)
        $moistureReadings = SensorReading::whereHas('sensor', function($query) {
                $query->where('sensor_type_id', 2);
            })
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();
        
        $latestTimestamp = SensorReading::max('created_at');
        
        return response()->json([
            'has_new_data' => true,
            'last_timestamp' => $latestTimestamp ? Carbon::parse($latestTimestamp)->toIso8601String() : now()->toIso8601String(),
            'temperature' => [
                'latest' => $temperatureReadings->first()?->value ?? 0,
                'data' => $temperatureReadings->map(fn($r) => [
                    'value' => $r->value,
                    'timestamp' => $r->created_at->toIso8601String(),
                ])->toArray()
            ],
            'moisture' => [
                'latest' => $moistureReadings->first()?->value ?? 0,
                'data' => $moistureReadings->map(fn($r) => [
                    'value' => $r->value,
                    'timestamp' => $r->created_at->toIso8601String(),
                ])->toArray()
            ]
        ]);
    }
}
