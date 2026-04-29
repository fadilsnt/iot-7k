<?php

use App\Http\Controllers\Api\SensorReadingController;
use App\Models\NotificationLog;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MachineReadingController;
use App\Http\Controllers\Api\RpmReadingController;

Route::post('/machine-readings', [MachineReadingController::class, 'store']);
// Route::post('/machine-readings/bulk', [MachineReadingController::class, 'bulkStore']);

Route::post('/rpm-readings', [RpmReadingController::class, 'store']);

// Arduino API - Simple endpoint without authentication
Route::get('/sensors/{sensor_code}', [SensorReadingController::class, 'storeFromArduino'])
    ->name('api.arduino.reading');

// Get sensor condition by sensor code
Route::get('/sensors/{sensor_code}/condition', function ($sensor_code) {
    $sensor = \App\Models\Sensor::where('sensor_code', $sensor_code)->first();

    if (!$sensor) {
        return response()->json(['error' => 'Sensor not found'], 404);
    }

    return response()->json([
        'condition' => (int)$sensor->condition,
        'value' => (float)$sensor->value
    ]);
})->name('api.sensor.condition');

// Store notification log
Route::post('/notification-logs', function (\Illuminate\Http\Request $request) {
    $validated = $request->validate([
        'sensor_code' => 'required|string',
        'sensor_name' => 'required|string',
        'sensor_type' => 'required|string',
        'condition' => 'required|integer|in:1,2,3',
        'sensor_value' => 'nullable|numeric',
        'message' => 'nullable|string'
    ]);

    // Get sensor_id from sensor_code
    $sensor = \App\Models\Sensor::where('sensor_code', $validated['sensor_code'])->first();

    if (!$sensor) {
        return response()->json(['error' => 'Sensor not found'], 404);
    }

    $validated['sensor_id'] = $sensor->id;

    $log = NotificationLog::create($validated);

    return response()->json([
        'success' => true,
        'log' => $log
    ], 201);
})->name('api.notification-logs.store');

Route::prefix('v1')
    ->group(function (): void {
        Route::get('/sensor-types/{type}/sensors/{sensor}/readings', [SensorReadingController::class, 'index'])
            ->name('api.readings.index');
        Route::post('/readings', [SensorReadingController::class, 'store'])
            ->name('api.readings.store');
    });
