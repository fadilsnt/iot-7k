<?php

namespace App\Http\Controllers;

use App\Models\Sensor;
use App\Models\SensorType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class SensorController extends Controller
{
    /**
     * Display a listing of sensors.
     */
    public function index(): View|RedirectResponse
    {

        
        $sensors = Sensor::with(['sensorType', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('sensors.index', compact('sensors'));
    }

    /**
     * Show the form for creating a new sensor.
     */
    public function create(): View
    {
        $sensorTypes = SensorType::all();
        return view('sensors.create', compact('sensorTypes'));
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
            'location' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'in:active,inactive,maintenance'],
        ]);

        try {
            $userId = Auth::id();
            
            // Create sensor
            $sensor = Sensor::create([
                'sensor_type_id' => $validated['sensor_type_id'],
                'sensor_code' => $validated['sensor_code'],
                'name' => $validated['sensor_name'],
                'location' => $validated['location'],
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'],
                'user_id' => $userId,
            ]);

            return redirect()->route('sensors.show', $sensor)
                ->with('success', "Sensor '{$sensor->name}' created successfully!");
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to create sensor: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified sensor.
     */
    public function show(Sensor $sensor): View
    {
        $sensor->load(['sensorType', 'user', 'readings' => function ($query) {
            $query->latest()->limit(100);
        }]);

        return view('sensors.show', compact('sensor'));
    }

    /**
     * Show the form for editing the specified sensor.
     */
    public function edit(Sensor $sensor): View
    {
        $sensorTypes = SensorType::all();
        return view('sensors.edit', compact('sensor', 'sensorTypes'));
    }

    /**
     * Update the specified sensor in storage.
     */
    public function update(Request $request, Sensor $sensor): RedirectResponse
    {
        $validated = $request->validate([
            'sensor_type_id' => ['required', 'exists:sensor_types,id'],
            'sensor_name' => ['required', 'string', 'max:100'],
            'location' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'in:active,inactive,maintenance'],
            'threshold_plus_1' => ['nullable', 'numeric', 'min:-999999.99', 'max:999999.99'],
            'threshold_plus_2' => ['nullable', 'numeric', 'min:-999999.99', 'max:999999.99'],
            'threshold_min_1' => ['nullable', 'numeric', 'min:-999999.99', 'max:999999.99'],
            'threshold_min_2' => ['nullable', 'numeric', 'min:-999999.99', 'max:999999.99'],
        ]);

        try {
            $sensor->update([
                'sensor_type_id' => $validated['sensor_type_id'],
                'name' => $validated['sensor_name'],
                'location' => $validated['location'],
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'],
                'threshold_plus_1' => $validated['threshold_plus_1'] ?? null,
                'threshold_plus_2' => $validated['threshold_plus_2'] ?? null,
                'threshold_min_1' => $validated['threshold_min_1'] ?? null,
                'threshold_min_2' => $validated['threshold_min_2'] ?? null,
            ]);

            return redirect()->route('sensors.show', $sensor)
                ->with('success', "Sensor updated successfully!");
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to update sensor: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified sensor from storage.
     */
    public function destroy(Sensor $sensor): RedirectResponse
    {
        $sensorName = $sensor->name;

        try {
            // Sensor readings will be deleted via cascade
            $sensor->delete();

            return redirect()->route('sensors.index')
                ->with('success', "Sensor '{$sensorName}' deleted successfully!");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete sensor: ' . $e->getMessage());
        }
    }

    /**
     * Bulk delete sensors.
     */
    public function bulkDelete(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'exists:sensors,id'],
        ]);

        try {
            $count = Sensor::whereIn('id', $validated['ids'])->delete();

            return redirect()->route('sensors.index')
                ->with('success', "$count sensor(s) deleted successfully!");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete sensors: ' . $e->getMessage());
        }
    }

    /**
     * Export sensors to CSV.
     */
    public function export()
    {
        $sensors = Sensor::with(['sensorType', 'user'])->get();

        $csv = "Sensor Code,Sensor Name,Type,Location,Status,User,Created At\n";

        foreach ($sensors as $sensor) {
            $userName = $sensor->user ? $sensor->user->name : 'N/A';
            $csv .= "\"{$sensor->sensor_code}\",\"{$sensor->name}\",\"{$sensor->sensorType->name}\",";
            $csv .= "\"{$sensor->location}\",\"{$sensor->status}\",\"{$userName}\",";
            $csv .= "\"{$sensor->created_at->format('Y-m-d H:i:s')}\"\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="sensors_' . now()->format('Y-m-d_His') . '.csv"',
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

        try {
            SensorType::create($validated);

            return redirect()->route('sensors.index')
                ->with('success', 'Sensor type created successfully!');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to create sensor type: ' . $e->getMessage());
        }
    }
}
