<?php

namespace App\Http\Controllers;

use App\Models\Sensor;
use App\Models\SensorReading;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardController extends Controller
{
    public function index(): View|RedirectResponse
    {
        // Get TEMP_01 sensor data
        $tempSensor = Sensor::where('sensor_code', 'TEMP_01')->first();
        $tempLatest = null;
        $tempChartData = [];
        
        if ($tempSensor) {
            // Get latest temperature reading
            $tempLatest = SensorReading::where('sensor_id', $tempSensor->id)
                ->latest('recorded_at')
                ->first();
            
            // Get last 24 hours data for chart
            $tempReadings = SensorReading::where('sensor_id', $tempSensor->id)
                ->where('recorded_at', '>=', now()->subHours(24))
                ->orderBy('recorded_at', 'asc')
                ->get();
            
            $tempChartData = [
                'labels' => $tempReadings->map(function($reading) {
                    return $reading->recorded_at->format('H:i');
                })->toArray(),
                'values' => $tempReadings->pluck('value')->toArray(),
            ];
        }
        
        // Get MOIST_01 sensor data
        $moistSensor = Sensor::where('sensor_code', 'MOIST_01')->first();
        $moistLatest = null;
        $moistChartData = [];
        
        if ($moistSensor) {
            // Get latest moisture reading
            $moistLatest = SensorReading::where('sensor_id', $moistSensor->id)
                ->latest('recorded_at')
                ->first();
            
            // Get last 24 hours data for chart
            $moistReadings = SensorReading::where('sensor_id', $moistSensor->id)
                ->where('recorded_at', '>=', now()->subHours(24))
                ->orderBy('recorded_at', 'asc')
                ->get();
            
            $moistChartData = [
                'labels' => $moistReadings->map(function($reading) {
                    return $reading->recorded_at->format('H:i');
                })->toArray(),
                'values' => $moistReadings->pluck('value')->toArray(),
            ];
        }
        
        return view('dashboard', compact(
            'tempLatest',
            'tempChartData',
            'tempSensor',
            'moistLatest',
            'moistChartData',
            'moistSensor'
        ));
    }

    /**
     * API endpoint untuk mendapatkan data dashboard secara realtime
     * OPTIMIZED: Long polling dengan change detection
     */
    public function getData(Request $request): JsonResponse
    {
        // Get last known timestamp from client
        $lastTimestamp = $request->input('last_timestamp');
        $timeout = 10; // Max wait time in seconds (reduced from 30 for faster response)
        $checkInterval = 0.5; // Check every 0.5 seconds (reduced from 1s)
        $startTime = microtime(true);
        
        // Long polling: wait until there's new data or timeout
        $hasChanges = false;
        while ((microtime(true) - $startTime) < $timeout) {
            $hasChanges = $this->checkForDataChanges($lastTimestamp);
            
            if ($hasChanges) {
                // New data available, return immediately
                break;
            }
            
            // No changes, wait before checking again
            usleep($checkInterval * 1000000); // Convert to microseconds
        }
        
        // Log all incoming parameters
        Log::info('getData called', [
            'all_params' => $request->all(),
            'sensor' => $request->input('sensor'),
            'range' => $request->input('range'),
            'custom_start' => $request->input('custom_start'),
            'custom_end' => $request->input('custom_end'),
            'last_timestamp' => $lastTimestamp,
            'has_changes' => $hasChanges,
            'wait_time' => round(microtime(true) - $startTime, 2) . 's'
        ]);
        
        // NEW: Support for single sensor request (custom range from frontend)
        $sensorCode = $request->input('sensor'); // TEMP_01, MOIST_01, etc
        $range = $request->input('range'); // 'custom' or null
        
        // Determine time range based on filter
        $filter = $request->input('filter', '24h'); // default 24 hours
        $startDate = $request->input('start_date') ?: $request->input('custom_start');
        $endDate = $request->input('end_date') ?: $request->input('custom_end');
        
        // Calculate time range
        $isCustomRange = ($startDate && $endDate) || ($range === 'custom');
        
        if ($isCustomRange) {
            // Custom range
            $startTime = \Carbon\Carbon::parse($startDate);
            $endTime = \Carbon\Carbon::parse($endDate);
        } else {
            // Predefined filters
            $endTime = now();
            switch ($filter) {
                case '5m':
                    $startTime = now()->subMinutes(5);
                    break;
                case '15m':
                    $startTime = now()->subMinutes(15);
                    break;
                case '30m':
                    $startTime = now()->subMinutes(30);
                    break;
                case '1h':
                    $startTime = now()->subHour();
                    break;
                case '8h':
                    $startTime = now()->subHours(8);
                    break;
                case '24h':
                default:
                    $startTime = now()->subHours(24);
                    break;
            }
        }
        
        // NEW: If single sensor requested, return only that sensor's data
        if ($sensorCode) {
            return $this->getSingleSensorData($sensorCode, $startTime, $endTime, $isCustomRange);
        }
        
        // Get TEMP_01 sensor data
        $tempSensor = Sensor::where('sensor_code', 'TEMP_01')->first();
        $tempLatest = null;
        $tempChartData = [];
        
        if ($tempSensor) {
            $tempLatest = SensorReading::where('sensor_id', $tempSensor->id)
                ->latest('recorded_at')
                ->first();
            
            $tempReadings = SensorReading::where('sensor_id', $tempSensor->id)
                ->whereBetween('recorded_at', [$startTime, $endTime])
                ->orderBy('recorded_at', 'asc')
                ->get();
            
            // Format label based on mode
            $timeFormat = $this->getTimeFormat($startTime, $endTime, $isCustomRange);
            
            // Sampling untuk semua range (max 500 data points)
            $maxDataPoints = 500;
            if ($tempReadings->count() > $maxDataPoints) {
                $step = ceil($tempReadings->count() / $maxDataPoints);
                $tempReadings = $tempReadings->filter(function($reading, $index) use ($step) {
                    return $index % $step === 0;
                })->values();
            }
            
            $tempChartData = [
                'labels' => $tempReadings->map(function($reading) use ($timeFormat) {
                    return $reading->recorded_at->format($timeFormat);
                })->toArray(),
                'fullDates' => $tempReadings->map(function($reading) {
                    return $reading->recorded_at->format('d/m/Y H:i:s');
                })->toArray(),
                'values' => $tempReadings->pluck('value')->toArray(),
            ];
        }
        
        // Get MOIST_01 sensor data
        $moistSensor = Sensor::where('sensor_code', 'MOIST_01')->first();
        $moistLatest = null;
        $moistChartData = [];
        
        if ($moistSensor) {
            $moistLatest = SensorReading::where('sensor_id', $moistSensor->id)
                ->latest('recorded_at')
                ->first();
            
            $moistReadings = SensorReading::where('sensor_id', $moistSensor->id)
                ->whereBetween('recorded_at', [$startTime, $endTime])
                ->orderBy('recorded_at', 'asc')
                ->get();
            
            // Format label based on mode
            $timeFormat = $this->getTimeFormat($startTime, $endTime, $isCustomRange);
            
            // Sampling untuk semua range (max 500 data points)
            $maxDataPoints = 500;
            if ($moistReadings->count() > $maxDataPoints) {
                $step = ceil($moistReadings->count() / $maxDataPoints);
                $moistReadings = $moistReadings->filter(function($reading, $index) use ($step) {
                    return $index % $step === 0;
                })->values();
            }
            
            $moistChartData = [
                'labels' => $moistReadings->map(function($reading) use ($timeFormat) {
                    return $reading->recorded_at->format($timeFormat);
                })->toArray(),
                'fullDates' => $moistReadings->map(function($reading) {
                    return $reading->recorded_at->format('d/m/Y H:i:s');
                })->toArray(),
                'values' => $moistReadings->pluck('value')->toArray(),
            ];
        }
        
        return response()->json([
            'tempLatest' => $tempLatest,
            'tempChartData' => $tempChartData,
            'tempThreshold' => [
                'plus_1' => $tempSensor->threshold_plus_1,
                'plus_2' => $tempSensor->threshold_plus_2,
                'min_1' => $tempSensor->threshold_min_1,
                'min_2' => $tempSensor->threshold_min_2,
            ],
            'moistLatest' => $moistLatest,
            'moistChartData' => $moistChartData,
            'moistThreshold' => [
                'plus_1' => $moistSensor->threshold_plus_1,
                'plus_2' => $moistSensor->threshold_plus_2,
                'min_1' => $moistSensor->threshold_min_1,
                'min_2' => $moistSensor->threshold_min_2,
            ],
            'isCustomRange' => $isCustomRange,
            'timestamp' => now()->timestamp, // Current server timestamp for next request
            'has_changes' => $hasChanges ?? true, // Indicate if this response contains new data
        ]);
    }
    
    /**
     * Check if there are any new sensor readings since last timestamp
     * Returns true if new data exists, false otherwise
     * OPTIMIZED: Fast query with index usage
     */
    private function checkForDataChanges($lastTimestamp): bool
    {
        if (!$lastTimestamp) {
            return true; // First request, always return data
        }
        
        try {
            $lastDateTime = \Carbon\Carbon::createFromTimestamp($lastTimestamp);
            
            // Get sensors efficiently
            $sensorIds = Sensor::whereIn('sensor_code', ['TEMP_01', 'MOIST_01'])
                ->pluck('id')
                ->toArray();
            
            if (empty($sensorIds)) {
                return false;
            }
            
            // Fast check: use max(id) or max(recorded_at) for quick detection
            $hasNewReadings = SensorReading::whereIn('sensor_id', $sensorIds)
                ->where('recorded_at', '>', $lastDateTime)
                ->exists();
            
            return $hasNewReadings;
            
        } catch (\Exception $e) {
            Log::error('Error checking for data changes', [
                'error' => $e->getMessage(),
                'last_timestamp' => $lastTimestamp
            ]);
            return true; // On error, return data to avoid stuck state
        }
    }
    
    /**
     * Determine time format based on range duration
     */
    private function getTimeFormat($startTime, $endTime, $isCustomRange = false): string
    {
        // Custom range ALWAYS shows date only on X-axis (tooltip shows full datetime)
        if ($isCustomRange) {
            return 'd/m/Y';
        }
        
        // Realtime mode: show time with seconds/minutes based on range
        $diffInHours = $startTime->diffInHours($endTime);
        
        if ($diffInHours <= 1) {
            return 'H:i:s'; // Show seconds for very short ranges (≤ 1 hour)
        } else {
            return 'H:i'; // Show hours:minutes for longer ranges
        }
    }
    
    /**
     * Get single sensor data (for custom range requests)
     * Returns flat structure: labels, values, latest
     */
    private function getSingleSensorData($sensorCode, $startTime, $endTime, $isCustomRange): JsonResponse
    {
        // Log the request for debugging
        Log::info('getSingleSensorData called', [
            'sensor_code' => $sensorCode,
            'start_time' => $startTime->format('Y-m-d H:i:s'),
            'end_time' => $endTime->format('Y-m-d H:i:s'),
            'is_custom_range' => $isCustomRange
        ]);
        
        // Get sensor
        $sensor = Sensor::where('sensor_code', $sensorCode)->first();
        
        if (!$sensor) {
            Log::warning('Sensor not found', ['sensor_code' => $sensorCode]);
            return response()->json([
                'error' => 'Sensor not found',
                'labels' => [],
                'values' => [],
                'latest' => null
            ], 404);
        }
        
        Log::info('Sensor found', ['sensor_id' => $sensor->id, 'sensor_name' => $sensor->name]);
        
        // Get latest reading (untuk gauge)
        $latestReading = SensorReading::where('sensor_id', $sensor->id)
            ->whereBetween('recorded_at', [$startTime, $endTime])
            ->latest('recorded_at')
            ->first();
        
        // Get readings in range
        $readings = SensorReading::where('sensor_id', $sensor->id)
            ->whereBetween('recorded_at', [$startTime, $endTime])
            ->orderBy('recorded_at', 'asc')
            ->get();
        
        Log::info('Readings fetched', [
            'total_readings' => $readings->count(),
            'has_latest' => $latestReading !== null
        ]);
        
        // Format label based on mode
        $timeFormat = $this->getTimeFormat($startTime, $endTime, $isCustomRange);
        
        // Sampling untuk data besar (max 500 data points)
        $maxDataPoints = 500;
        if ($readings->count() > $maxDataPoints) {
            $step = ceil($readings->count() / $maxDataPoints);
            $readings = $readings->filter(function($reading, $index) use ($step) {
                return $index % $step === 0;
            })->values();
        }
        
        // Format latest reading
        $latest = null;
        if ($latestReading) {
            $latest = [
                'value' => $latestReading->value,
                'recorded_at' => $latestReading->recorded_at->format('Y-m-d H:i:s'),
                'timestamp' => $latestReading->recorded_at->format('d/m/Y H:i:s'),
                'condition' => $sensor->condition ?? 1
            ];
        }
        
        // Return flat structure
        return response()->json([
            'labels' => $readings->map(function($reading) use ($timeFormat) {
                return $reading->recorded_at->format($timeFormat);
            })->toArray(),
            'fullDates' => $readings->map(function($reading) {
                return $reading->recorded_at->format('d/m/Y H:i:s');
            })->toArray(),
            'values' => $readings->pluck('value')->toArray(),
            'latest' => $latest,
            'sensor_code' => $sensorCode,
            'total_data' => $readings->count(),
            'date_range' => [
                'start' => $startTime->format('Y-m-d H:i:s'),
                'end' => $endTime->format('Y-m-d H:i:s')
            ]
        ]);
    }
    
    /**
     * API endpoint untuk mendapatkan data table dengan pagination (lazy loading)
     */
    public function getTableData(Request $request): JsonResponse
    {
        $sensorCode = $request->input('sensor_code'); // TEMP_01 or MOIST_01
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 20);
        
        // Validate required parameters
        if (!$sensorCode || !$startDate || !$endDate) {
            return response()->json([
                'status' => 'error',
                'message' => 'Missing required parameters'
            ], 400);
        }
        
        // Parse dates
        $startTime = \Carbon\Carbon::parse($startDate);
        $endTime = \Carbon\Carbon::parse($endDate);
        
        // Get sensor
        $sensor = Sensor::where('sensor_code', $sensorCode)->first();
        
        if (!$sensor) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sensor not found'
            ], 404);
        }
        
        // Get total count
        $totalCount = SensorReading::where('sensor_id', $sensor->id)
            ->whereBetween('recorded_at', [$startTime, $endTime])
            ->count();
        
        // Get paginated data (latest first)
        $readings = SensorReading::where('sensor_id', $sensor->id)
            ->whereBetween('recorded_at', [$startTime, $endTime])
            ->orderBy('recorded_at', 'desc') // Latest first
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();
        
        // Format data
        $data = $readings->map(function($reading) {
            return [
                'label' => $reading->recorded_at->format('d/m/Y H:i:s'),
                'value' => $reading->value
            ];
        });
        
        return response()->json([
            'status' => 'success',
            'data' => $data,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $totalCount,
                'total_pages' => ceil($totalCount / $perPage),
                'has_more' => ($page * $perPage) < $totalCount
            ]
        ]);
    }

    public function download(Request $request): StreamedResponse
    {
        // Log request for debugging
        Log::info('Download request received', [
            'type' => $request->input('type'),
            'start' => $request->input('start'),
            'end' => $request->input('end')
        ]);

        $type = $request->input('type'); // 'temp' or 'moist'
        $start = $request->input('start');
        $end = $request->input('end');
        
        // Validate inputs
        if (!$type || !$start || !$end) {
            Log::error('Download validation failed: Missing parameters');
            abort(400, 'Missing required parameters');
        }
        
        // Determine sensor code based on type
        $sensorCode = ($type === 'temp') ? 'TEMP_01' : 'MOIST_01';
        $sensorName = ($type === 'temp') ? 'Temperature' : 'Moisture';
        $unit = ($type === 'temp') ? '°C' : '%';
        
        // Get sensor
        $sensor = Sensor::where('sensor_code', $sensorCode)->first();
        
        if (!$sensor) {
            Log::error('Sensor not found', ['sensor_code' => $sensorCode]);
            abort(404, 'Sensor not found');
        }
        
        // Get data from database
        $readings = SensorReading::where('sensor_id', $sensor->id)
            ->whereBetween('recorded_at', [$start, $end])
            ->orderBy('recorded_at', 'asc')
            ->get();
        
        Log::info('Download data prepared', [
            'sensor_code' => $sensorCode,
            'total_records' => $readings->count(),
            'date_range' => [$start, $end]
        ]);
        
        // Generate filename
        $filename = $sensorCode . '_' . str_replace([':', ' ', '-'], ['', '_', ''], $start) . '_to_' . str_replace([':', ' ', '-'], ['', '_', ''], $end) . '.xls';
        
        // Create Excel-compatible HTML response
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Content-Transfer-Encoding' => 'binary',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];
        
        $callback = function() use ($readings, $sensorName, $unit) {
            // Excel-compatible HTML with proper encoding
            echo "\xEF\xBB\xBF"; // UTF-8 BOM
            echo '<!DOCTYPE html>';
            echo '<html>';
            echo '<head><meta charset="UTF-8"></head>';
            echo '<body>';
            echo '<table border="1">';
            echo '<thead>';
            echo '<tr>';
            echo '<th style="background-color: #4472C4; color: white; font-weight: bold; padding: 10px;">Timestamp</th>';
            echo '<th style="background-color: #4472C4; color: white; font-weight: bold; padding: 10px;">' . htmlspecialchars($sensorName . ' (' . $unit . ')') . '</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            
            foreach ($readings as $reading) {
                echo '<tr>';
                echo '<td style="padding: 8px;">' . $reading->recorded_at->format('Y-m-d H:i:s') . '</td>';
                echo '<td style="padding: 8px; text-align: right;">' . $reading->value . '</td>';
                echo '</tr>';
            }
            
            echo '</tbody>';
            echo '</table>';
            echo '</body>';
            echo '</html>';
        };
        
        return response()->stream($callback, 200, $headers);
    }
}
