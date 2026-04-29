<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Sensor;
use App\Models\SensorType;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class DeviceController extends Controller
{
    /**
     * Display a listing of devices.
     */
    public function index(): View
    {
        $devices = Device::with(['sensors', 'sensors.sensorType'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('devices.index', compact('devices'));
    }

    /**
     * Show the form for creating a new device.
     */
    public function create(): View
    {
        $sensorTypes = SensorType::all();
        return view('devices.create', compact('sensorTypes'));
    }

    /**
     * Create a new sensor type via AJAX.
     */
    public function createSensorType(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:sensor_types,name'],
            'slug' => ['required', 'string', 'max:100', 'unique:sensor_types,slug'],
            'unit' => ['required', 'string', 'max:20'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $sensorType = SensorType::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Sensor type created successfully',
                'data' => $sensorType,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create sensor type: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created sensor in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sensor_type_id' => ['required', 'exists:sensor_types,id'],
            'sensor_code' => ['required', 'string', 'max:50', 'unique:sensors,sensor_code'],
            'sensor_name' => ['required', 'string', 'max:100'],
            'sensor_description' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $sensorType = SensorType::findOrFail($validated['sensor_type_id']);

            // Create sensor without device association
            $sensor = Sensor::create([
                'sensor_type_id' => $validated['sensor_type_id'],
                'sensor_code' => $validated['sensor_code'],
                'name' => $validated['sensor_name'],
                'unit' => $sensorType->unit,
                'device_id' => null, // Can be assigned to device later
            ]);

            return redirect()->route('devices.index')
                ->with('success', "Sensor '{$sensor->name}' created successfully!");
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to create sensor: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified device.
     */
    public function show(Device $device): View
    {
        $device->load(['sensors.sensorType', 'sensors.readings' => function ($query) {
            $query->latest()->limit(50);
        }]);

        $sensorTypes = SensorType::all();
        return view('devices.show', compact('device', 'sensorTypes'));
    }

    /**
     * Show the form for editing the specified device.
     */
    public function edit(Device $device): View
    {
        $device->load('sensors.sensorType');
        $sensorTypes = SensorType::all();
        return view('devices.edit', compact('device', 'sensorTypes'));
    }

    /**
     * Update the specified device in storage.
     */
    public function update(Request $request, Device $device): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'sensors' => ['required', 'array', 'min:1'],
            'sensors.*.id' => ['nullable', 'exists:sensors,id'],
            'sensors.*.sensor_code' => ['required', 'string', 'max:50'],
            'sensors.*.sensor_type_id' => ['required', 'exists:sensor_types,id'],
            'sensors.*.name' => ['required', 'string', 'max:100'],
        ]);

        try {
            $device->update([
                'name' => $validated['name'],
                'location' => $validated['location'],
                'description' => $validated['description'] ?? null,
            ]);

            // Update or create sensors
            $sensorIds = [];
            foreach ($validated['sensors'] as $sensorData) {
                if (!empty($sensorData['id'])) {
                    $sensor = Sensor::find($sensorData['id']);
                    $sensor->update([
                        'sensor_code' => $sensorData['sensor_code'],
                        'sensor_type_id' => $sensorData['sensor_type_id'],
                        'name' => $sensorData['name'],
                    ]);
                    $sensorIds[] = $sensor->id;
                } else {
                    $sensorType = SensorType::find($sensorData['sensor_type_id']);
                    $sensor = Sensor::create([
                        'device_id' => $device->id,
                        'sensor_type_id' => $sensorData['sensor_type_id'],
                        'sensor_code' => $sensorData['sensor_code'],
                        'name' => $sensorData['name'],
                        'unit' => $sensorType->unit,
                    ]);
                    $sensorIds[] = $sensor->id;
                }
            }

            // Delete removed sensors
            $device->sensors()->whereNotIn('id', $sensorIds)->delete();

            return redirect()->route('devices.show', $device)
                ->with('success', 'Device updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to update device: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified device from storage.
     */
    public function destroy(Device $device): RedirectResponse
    {
        try {
            $deviceName = $device->name;
            $device->delete(); // Cascade delete sensors & readings via foreign keys

            return redirect()->route('devices.index')
                ->with('success', "Device '{$deviceName}' deleted successfully.");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete device: ' . $e->getMessage());
        }
    }

    /**
     * Bulk delete devices.
     */
    public function bulkDelete(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'device_ids' => ['required', 'array', 'min:1'],
            'device_ids.*' => ['exists:devices,id'],
        ]);

        try {
            Device::whereIn('id', $validated['device_ids'])->delete();
            $count = count($validated['device_ids']);

            return redirect()->route('devices.index')
                ->with('success', "{$count} device(s) deleted successfully.");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete devices: ' . $e->getMessage());
        }
    }

    /**
     * Export devices to CSV.
     */
    public function export()
    {
        $devices = Device::with('sensors')->get();

        $csv = "Device ID,Name,Location,Status,Sensors,Last Seen\n";
        foreach ($devices as $device) {
            $sensorCount = $device->sensors->count();
            $status = $device->last_seen_at && $device->last_seen_at->diffInMinutes() < 5 ? 'Online' : 'Offline';
            $lastSeen = $device->last_seen_at?->format('Y-m-d H:i:s') ?? 'Never';

            $csv .= "\"{$device->device_id}\",\"{$device->name}\",\"{$device->location}\",\"{$status}\",{$sensorCount},\"{$lastSeen}\"\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="devices_' . date('Y-m-d_H-i-s') . '.csv"',
        ]);
    }

    /**
     * Show the form for creating a new sensor type.
     */
    public function createSensorTypeForm(): View
    {
        return view('sensor-types.create');
    }

    /**
     * Store a new sensor type.
     */
    public function storeSensorType(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:sensor_types,name'],
            'slug' => ['required', 'string', 'max:100', 'unique:sensor_types,slug'],
            'unit' => ['required', 'string', 'max:20'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        SensorType::create($validated);

        return redirect()->route('devices.index')
            ->with('success', 'Sensor type created successfully!');
    }
}
