<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MachineReading;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MachineReadingController extends Controller
{
    public function bulkStore(Request $request)
    {
        $data = $request->input('data');
        if (!is_array($data)) {
            return response()->json(['status' => 'error', 'message' => 'Data must be an array'], 422);
        }

        $records = [];
        $now = now();

        foreach ($data as $item) {
            $recordedAt = $now;
            if (isset($item['timestamp'])) {
                try {
                    $recordedAt = $this->parseTimestamp($item['timestamp']);
                } catch (\Throwable $e) {}
            }

            $records[] = [
                'machine_id'  => $item['machine_id'] ?? 'unknown',
                'amp'         => $item['amp'] ?? 0,
                'hm'          => $item['hm'] ?? 0,
                'temp'        => $item['temp'] ?? 0,
                'moist'       => $item['moist'] ?? 0,
                'recorded_at' => $recordedAt,
                'created_at'  => $now,
                'updated_at'  => $now,
            ];

            // Batch insert every 500 records to prevent memory issues
            if (count($records) >= 500) {
                MachineReading::insert($records);
                $records = [];
            }
        }

        if (count($records) > 0) {
            MachineReading::insert($records);
        }

        return response()->json([
            'status'  => 'success',
            'message' => count($data) . ' records processed',
        ], 201);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'machine_id' => 'required|string',
            'amp'        => 'required|numeric',
            'hm'         => 'required|numeric',
            'temp'       => 'required|numeric',
            'moist'      => 'required|numeric',
            'timestamp'  => 'nullable',
        ]);

        $recordedAt = now();

        if ($request->filled('timestamp')) {
            try {
                $recordedAt = $this->parseTimestamp($request->input('timestamp'));
            } catch (\Throwable $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid timestamp format',
                ], 422);
            }
        }

        $reading = MachineReading::create([
            'machine_id'  => $validated['machine_id'],
            'amp'         => $validated['amp'],
            'hm'          => $validated['hm'],
            'temp'        => $validated['temp'],
            'moist'       => $validated['moist'],
            'recorded_at' => $recordedAt,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Data recorded successfully',
            'data'    => $reading
        ], 201);
    }

    private function parseTimestamp(string|int|float $value): Carbon
    {
        if (is_numeric($value)) {
            $numeric = (float) $value;
            if ($numeric >= 1000000000000) {
                $numeric = $numeric / 1000;
            }
            return Carbon::createFromTimestamp($numeric);
        }

        return Carbon::parse((string) $value);
    }
}
