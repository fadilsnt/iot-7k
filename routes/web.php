<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\Api\SensorReadingController;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SensorController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\RpmController;



// Redirect root to dashboard (will redirect to login if not authenticated)
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Simple GET-based ingestion endpoint e.g. /temperature/TEMP_MAIN?value=27.6
Route::get('/ingest/{typeSlug}/{sensorCode}', [SensorReadingController::class, 'ingestViaGet'])
    ->where([
        'typeSlug' => '^(temperature|moisture)$',
        'sensorCode' => '^[A-Za-z0-9_-]+$',
    ])
    ->name('iot.ingest.get');

// Require auth for main pages
Route::middleware('auth')->group(function () {
    // Dashboard routes
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/data', [DashboardController::class, 'getData'])->name('dashboard.data');
    Route::get('/dashboard/table-data', [DashboardController::class, 'getTableData'])->name('dashboard.table-data');
    Route::get('/dashboard/download', [DashboardController::class, 'download'])->name('dashboard.download');

    // Activity Log
    Route::get('/activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index');

    // Machines
    Route::get('/machines', [App\Http\Controllers\MachineController::class, 'index'])->name('machines.index');
    Route::get('/machines/kesimpulan', [App\Http\Controllers\MachineController::class, 'kesimpulan'])->name('machines.kesimpulan');
    Route::get('/machines/data', [App\Http\Controllers\MachineController::class, 'getData'])->name('machines.get-data');
    Route::get('/machines/table-data', [App\Http\Controllers\MachineController::class, 'tableData'])->name('machines.table-data');
    Route::get('/machines/download-report', [App\Http\Controllers\MachineController::class, 'downloadReport'])->name('machines.download-report');

    // Machine History
    Route::get('/machines/{machine_id}/history', [App\Http\Controllers\MachineController::class, 'history'])->name('machines.history');
    Route::get('/machines/{machine_id}/history-data', [App\Http\Controllers\MachineController::class, 'historyData'])->name('machines.history-data');
    Route::get('/machines/{machine_id}/history-export', [App\Http\Controllers\MachineController::class, 'historyExport'])->name('machines.history-export');

    // RPM
    Route::get('/rpm', [RpmController::class, 'index'])->name('rpm.index');
    Route::get('/rpm/kesimpulan', [RpmController::class, 'kesimpulan'])->name('rpm.kesimpulan');
    Route::get('/rpm/data', [RpmController::class, 'getData'])->name('rpm.get-data');
    Route::get('/rpm/table-data', [RpmController::class, 'tableData'])->name('rpm.table-data');
    Route::get('/rpm/download-report', [RpmController::class, 'downloadReport'])->name('rpm.download-report');

    // RPM History
    Route::get('/rpm/{rpm_id}/history', [RpmController::class, 'history'])->name('rpm.history');
    Route::get('/rpm/{rpm_id}/history-data', [RpmController::class, 'historyData'])->name('rpm.history-data');
    Route::get('/rpm/{rpm_id}/history-export', [RpmController::class, 'historyExport'])->name('rpm.history-export');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Sensors Management - Main route
    Route::resource('sensors', SensorController::class);
    Route::post('/sensors/bulk-delete', [SensorController::class, 'bulkDelete'])->name('sensors.bulk-delete');
    Route::get('/sensors/export/csv', [SensorController::class, 'export'])->name('sensors.export');

    // Legacy devices routes alias for backward compatibility (redirect to sensors)
    Route::redirect('/devices', '/sensors');
    Route::get(
        '/devices/create',
        function () {
            return redirect('/sensors/create');
        }
    )->name('devices.create');

    // Sensor Types Management
    Route::get('/sensor-types/create', [SensorController::class, 'createSensorTypeForm'])->name('sensor-types.create');
    Route::post('/sensor-types', [SensorController::class, 'storeSensorType'])->name('sensor-types.store');
});

require __DIR__ . '/auth.php';

