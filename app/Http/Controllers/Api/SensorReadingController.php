<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\Sensor;
use App\Models\SensorReading;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SensorReadingController extends Controller
{
    protected function resolveSensor(string $typeSlug, string $sensorCode): Sensor
    {
        return Sensor::query()
            ->where('sensor_code', $sensorCode)
            ->whereHas('sensorType', fn ($q) => $q->where('slug', $typeSlug))
            ->with('device')
            ->firstOrFail();
    }

    protected function persistReading(Sensor $sensor, float $value, ?string $recordedAt = null, ?array $metadata = null): SensorReading
    {
        $device = $sensor->device;

        if ($device instanceof Device) {
            $device->forceFill(['last_seen_at' => now()])->save();
        }

        $reading = SensorReading::create([
            'sensor_id' => $sensor->id,
            'value' => $value,
            'recorded_at' => $recordedAt ? Carbon::parse($recordedAt) : now(),
            'metadata' => $metadata,
        ]);

        // Auto-update sensor condition based on new reading
        $sensor->fresh()->updateCondition();

        return $reading;
    }

    /**
     * Store one or many sensor readings pushed from IoT devices.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_id' => ['required', 'string', 'exists:devices,device_id'],
            'readings' => ['required', 'array', 'min:1'],
            'readings.*.sensor_code' => ['required', 'string'],
            'readings.*.value' => ['required', 'numeric'],
            'readings.*.recorded_at' => ['nullable', 'date'],
            'readings.*.metadata' => ['nullable', 'array'],
        ]);

        /** @var \App\Models\Device $device */
        $device = Device::where('device_id', $validated['device_id'])->firstOrFail();

        $storedReadings = [];

        DB::transaction(function () use (&$storedReadings, $device, $validated): void {
            $device->forceFill(['last_seen_at' => now()])->save();

            foreach ($validated['readings'] as $index => $reading) {
                /** @var Sensor|null $sensor */
                $sensor = Sensor::where('device_id', $device->id)
                    ->where('sensor_code', $reading['sensor_code'])
                    ->first();

                if (!$sensor) {
                    throw ValidationException::withMessages([
                        "readings.$index.sensor_code" => "Sensor code {$reading['sensor_code']} is not registered for device {$device->device_id}.",
                    ]);
                }

                $storedReadings[] = $this->persistReading(
                    $sensor,
                    (float) $reading['value'],
                    $reading['recorded_at'] ?? null,
                    $reading['metadata'] ?? null
                );
                
                // Update sensor condition
                $sensor->fresh()->updateCondition();
            }
        });

        return response()->json([
            'status' => 'success',
            'device' => [
                'id' => $device->id,
                'device_id' => $device->device_id,
                'name' => $device->name,
            ],
            'readings_received' => count($validated['readings']),
            'readings_stored' => count($storedReadings),
            'reading_ids' => collect($storedReadings)->pluck('id'),
        ], JsonResponse::HTTP_CREATED);
    }

    /**
     * Fetch sensor readings filtered by sensor type + sensor code.
     */
    public function index(Request $request, string $typeSlug, string $sensorCode): JsonResponse
    {
        $sensor = $this->resolveSensor($typeSlug, $sensorCode)
            ->loadMissing('sensorType:id,slug,name,unit', 'device:id,device_id,name');

        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'order' => ['nullable', 'in:asc,desc'],
        ]);

        $limit = $validated['limit'] ?? 200;
        $order = $validated['order'] ?? 'desc';

        $query = $sensor->readings()->orderBy('recorded_at', $order);

        if (!empty($validated['from'])) {
            $query->where('recorded_at', '>=', Carbon::parse($validated['from']));
        }

        if (!empty($validated['to'])) {
            $query->where('recorded_at', '<=', Carbon::parse($validated['to']));
        }

        $readings = $query->limit($limit)->get()->map(function (SensorReading $reading) {
            return [
                'id' => $reading->id,
                'value' => (float) $reading->value,
                'recorded_at' => $reading->recorded_at?->toIso8601String(),
                'metadata' => $reading->metadata,
            ];
        });

        return response()->json([
            'sensor' => [
                'id' => $sensor->id,
                'code' => $sensor->sensor_code,
                'name' => $sensor->name,
                'type' => $sensor->sensorType?->only(['id', 'slug', 'name', 'unit']),
                'device' => $sensor->device?->only(['id', 'device_id', 'name']),
            ],
            'filters' => [
                'from' => $validated['from'] ?? null,
                'to' => $validated['to'] ?? null,
                'limit' => $limit,
                'order' => $order,
            ],
            'count' => $readings->count(),
            'data' => $readings,
        ]);
    }

    /**
     * Lightweight GET ingestion endpoint: /temperature/{sensor}?value=27.6
     */
    public function ingestViaGet(Request $request, string $typeSlug, string $sensorCode): JsonResponse
    {
        $validated = $request->validate([
            'value' => ['required', 'numeric'],
            'recorded_at' => ['nullable', 'date'],
        ]);

        $sensor = $this->resolveSensor($typeSlug, $sensorCode);

        $reading = $this->persistReading(
            $sensor,
            (float) $validated['value'],
            $validated['recorded_at'] ?? null
        );

        return response()->json([
            'status' => 'success',
            'sensor' => [
                'code' => $sensor->sensor_code,
                'type' => $sensor->sensorType?->slug,
            ],
            'value' => (float) $reading->value,
            'recorded_at' => $reading->recorded_at?->toIso8601String(),
        ]);
    }

    /**
     * Simple Arduino endpoint: GET /api/sensors/{sensor_code}?value=25
     * Format: http://localhost/iot-tkhs/api/sensors/TEMP_01?value=25
     */
    public function storeFromArduino(Request $request, string $sensor_code): JsonResponse
    {
        try {
            // Validate input
            $validated = $request->validate([
                'value' => ['required', 'numeric'],
            ]);

            // Find sensor by code
            $sensor = Sensor::where('sensor_code', $sensor_code)->first();

            if (!$sensor) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Sensor tidak ditemukan',
                    'sensor_code' => $sensor_code,
                ], 404);
            }

            // Store reading
            $reading = SensorReading::create([
                'sensor_id' => $sensor->id,
                'value' => (float) $validated['value'],
                'recorded_at' => now(),
            ]);

            // Auto-update sensor condition
            $sensor->fresh()->updateCondition();

            return response()->json([
                'status' => 'success',
                'message' => 'Data terkirim',
                'data' => [
                    'sensor_code' => $sensor->sensor_code,
                    'sensor_name' => $sensor->name,
                    'value' => (float) $reading->value,
                    'recorded_at' => $reading->recorded_at->format('Y-m-d H:i:s'),
                ],
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menyimpan data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
