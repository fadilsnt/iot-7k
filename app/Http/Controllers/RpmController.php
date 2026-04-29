<?php

namespace App\Http\Controllers;

use App\Models\RpmReadings;
use App\Exports\MachineHistoryExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RpmController extends Controller
{
    public function index(): View
    {
        // For Dashboard/Grid: HM is calculated from the start of the current shift
        $range = $this->resolveShiftRange('auto', now());
        $startDate = $range[0];
        $endDate = $range[1];

        // 1. Get all unique machine names
        $allMachineNames = RpmReadings::query()
            ->select('rpm_id')
            ->distinct()
            ->pluck('rpm_id');

        // 2. Get calculated HM for the selected range
        $calculatedHM = $this->getCalculatedHM($startDate, $endDate);

        // 3. Get the absolute latest reading for each machine, regardless of time range
        $latestReadings = RpmReadings::query()
            ->select('id', 'rpm_id', 'rpm', 'temp', 'humi', 'recorded_at')
            ->whereIn('id', function ($query) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('rpm_readings')
                    ->groupBy('rpm_id');
            })
            ->get()
            ->keyBy('rpm_id');

        // 4. Combine all data
        $machines = $allMachineNames->map(function ($machineName) use ($latestReadings, $calculatedHM) {
            $latest = $latestReadings->get($machineName);

            return [
                'id' => $latest->id ?? null,
                'name' => $machineName,
                'rpm' => $latest->rpm ?? 0,
                'hm' => round($calculatedHM[$machineName] ?? 0, 2),
                'temp' => $latest->temp ?? 0,
                'humi' => $latest->humi ?? 0,
                'last_updated' => $latest && $latest->recorded_at
                    ? Carbon::parse($latest->recorded_at)->format('Y-m-d H:i:s')
                    : '-',
                'status' => 'active', // You might want to determine status based on last_updated
            ];
        });

        $machines = $machines
            ->sortBy('name', SORT_NATURAL)
            ->values();

        return view('rpm.index', compact('machines'));
    }

    public function kesimpulan(): View
    {
        // Same dataset as index(), different view.
        // Duplicated intentionally to avoid touching existing logic flow.
        $range = $this->resolveShiftRange('auto', now());
        $startDate = $range[0];
        $endDate = $range[1];

        $allMachineNames = RpmReadings::query()
            ->select('rpm_id')
            ->distinct()
            ->pluck('rpm_id');

        $calculatedHM = $this->getCalculatedHM($startDate, $endDate);

        $latestReadings = RpmReadings::query()
            ->select('id', 'rpm_id', 'rpm', 'temp', 'humi', 'recorded_at')
            ->whereIn('id', function ($query) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('rpm_readings')
                    ->groupBy('rpm_id');
            })
            ->get()
            ->keyBy('rpm_id');

        $machines = $allMachineNames->map(function ($machineName) use ($latestReadings, $calculatedHM) {
            $latest = $latestReadings->get($machineName);

            return [
                'id' => $latest->id ?? null,
                'name' => $machineName,
                'rpm' => $latest->rpm ?? 0,
                'hm' => round($calculatedHM[$machineName] ?? 0, 2),
                'temp' => $latest->temp ?? 0,
                'humi' => $latest->humi ?? 0,
                'last_updated' => $latest && $latest->recorded_at
                    ? Carbon::parse($latest->recorded_at)->format('Y-m-d H:i:s')
                    : '-',
                'status' => 'active',
            ];
        });

        $machines = $machines
            ->sortBy('name', SORT_NATURAL)
            ->values();

        return view('rpm.kesimpulan', compact('machines'));
    }

    /**
     * Get machine data for AJAX updates with filtering
     */
    public function getData(Request $request)
    {
        // 1. Resolve date range for HM calculation
        $hasCustomRange = $request->filled('start') && $request->filled('end');
        if ($hasCustomRange) {
            [$startDate, $endDate, $error] = $this->resolveRange(
                $request,
                ['1h' => 1, '8h' => 8, '24h' => 24],
                24
            );
        } else {
            $range = $this->resolveShiftRange('auto', now());
            $startDate = $range[0];
            $endDate = $range[1];
            $error = null;
        }

        if ($error) {
            return response()->json(['error' => 'Invalid date range', 'message' => $error], 422);
        }

        // 2. Get all unique machine names
        $allMachineNames = RpmReadings::query()
            ->select('rpm_id')
            ->distinct()
            ->pluck('rpm_id');

        // 3. Get calculated HM for the selected range
        $calculatedHM = $this->getCalculatedHM($startDate, $endDate);

        // 4. Get the absolute latest reading for each machine
        $latestReadings = RpmReadings::query()
            ->select('id', 'rpm_id', 'rpm', 'temp', 'humi', 'recorded_at')
            ->whereIn('id', function ($query) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('rpm_readings')
                    ->groupBy('rpm_id');
            })
            ->get()
            ->keyBy('rpm_id');

        // 5. Combine all data
        $machines = $allMachineNames->map(function ($machineName) use ($latestReadings, $calculatedHM) {
            $latest = $latestReadings->get($machineName);
            return [
                'id' => $latest->id ?? null,
                'name' => $machineName,
                'rpm' => $latest->rpm ?? 0,
                'hm' => round($calculatedHM[$machineName] ?? 0, 2),
                'temp' => $latest->temp ?? 0,
                'humi' => $latest->humi ?? 0,
                'last_updated' => $latest && $latest->recorded_at ? Carbon::parse($latest->recorded_at)->format('Y-m-d H:i:s') : '-',
                'status' => 'active',
            ];
        });

        // Sort naturally
        $machines = $machines->sortBy('name', SORT_NATURAL)->values();

        return response()->json([
            'data' => $machines,
            'range' => [
                'start' => $startDate ? $startDate->format('Y-m-d H:i:s') : null,
                'end' => $endDate ? $endDate->format('Y-m-d H:i:s') : null
            ]
        ]);
    }
    public function history(string $rpm_id): View
    {
        return view('rpm.history', compact('rpm_id'));
    }

    public function historyData(Request $req, string $rpm_id)
    {
        try {
            Log::info("HistoryData start for $rpm_id", $req->all());

            [$startDate, $endDate, $error] = $this->resolveRange(
                $req,
                ['1h' => 1, '2h' => 2, '4h' => 4, '8h' => 8, '16h' => 16, '24h' => 24],
                1
            );

            if ($error) {
                Log::error("HistoryData range error for $rpm_id: $error");
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid date range',
                    'message' => $error
                ], 422);
            }

            Log::info("HistoryData range resolved: {$startDate->toDateTimeString()} to {$endDate->toDateTimeString()}");

            $accumulatedHMByReadingId = $this->getAccumulatedHMByReadingId($startDate, $endDate, $rpm_id);
            $accumulatedHM = !empty($accumulatedHMByReadingId)
                ? round((float) end($accumulatedHMByReadingId), 2)
                : 0.0;

            $allowedPerPage = [50, 100, 200, 300, 400, 500];
            $perPageRaw = strtolower((string) $req->query('per_page', '50'));
            $page = max((int) $req->query('page', 1), 1);

            $baseQuery = RpmReadings::query()
                ->where('rpm_id', $rpm_id)
                ->whereBetween('recorded_at', [$startDate, $endDate])
                ->orderBy('recorded_at', 'desc');

            if ($perPageRaw === 'all') {
                $rows = $baseQuery
                    ->get()
                    ->map(function ($r) use ($rpm_id, $accumulatedHMByReadingId) {
                        return [
                            'name' => $rpm_id,
                            'last_updated' => $r->recorded_at
                                ? Carbon::parse($r->recorded_at)->format('Y-m-d H:i:s')
                                : '-',
                            'rpm' => round((float) ($r->rpm ?? 0), 2),
                            'hm' => round((float) ($accumulatedHMByReadingId[$r->id] ?? 0), 2),
                            'temp' => round((float) ($r->temp ?? 0), 1),
                            'humi' => round((float) ($r->humi ?? 0), 1),
                        ];
                    })
                    ->values();

                $total = $rows->count();
                $pagination = [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => 'all',
                    'total' => $total,
                    'from' => $total > 0 ? 1 : 0,
                    'to' => $total,
                ];
            } else {
                $perPage = (int) $perPageRaw;
                if (!in_array($perPage, $allowedPerPage, true)) {
                    $perPage = 50;
                }

                $paginator = $baseQuery->paginate($perPage, ['*'], 'page', $page);

                $rows = collect($paginator->items())
                    ->map(function ($r) use ($rpm_id, $accumulatedHMByReadingId) {
                        return [
                            'name' => $rpm_id,
                            'last_updated' => $r->recorded_at
                                ? Carbon::parse($r->recorded_at)->format('Y-m-d H:i:s')
                                : '-',
                            'rpm' => round((float) ($r->rpm ?? 0), 2),
                            'hm' => round((float) ($accumulatedHMByReadingId[$r->id] ?? 0), 2),
                            'temp' => round((float) ($r->temp ?? 0), 1),
                            'humi' => round((float) ($r->humi ?? 0), 1),
                        ];
                    })
                    ->values();

                $pagination = [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'from' => $paginator->firstItem() ?? 0,
                    'to' => $paginator->lastItem() ?? 0,
                ];
            }

            Log::info("HistoryData rows fetched", [
                'count' => $rows->count(),
                'pagination' => $pagination,
            ]);

            return response()->json([
                'success' => true,
                'data' => $rows,
                'pagination' => $pagination,
                'summary_info' => [
                    'start' => $startDate->format('Y-m-d H:i:s'),
                    'end' => $endDate->format('Y-m-d H:i:s'),
                    'accumulated_hm' => $accumulatedHM,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error("History Data Error for $rpm_id: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Server Error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function getAccumulatedHMByReadingId(Carbon $startDate, Carbon $endDate, string $rpm_id): array
    {
        $readings = RpmReadings::query()
            ->select('id', 'rpm', 'recorded_at')
            ->where('rpm_id', $rpm_id)
            ->whereBetween('recorded_at', [$startDate, $endDate])
            ->orderBy('recorded_at', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $accumulatedSeconds = 0;
        $previousRecordedAt = null;
        $previousAmp = null;
        $hmByReadingId = [];

        foreach ($readings as $reading) {
            if ($previousRecordedAt !== null && $previousAmp !== null && (float) $previousAmp > 0) {
                $diffSeconds = Carbon::parse($previousRecordedAt)->diffInSeconds(Carbon::parse($reading->recorded_at));
                $accumulatedSeconds += min($diffSeconds, 30);
            }

            $hmByReadingId[$reading->id] = $accumulatedSeconds / 3600;

            $previousRecordedAt = $reading->recorded_at;
            $previousAmp = $reading->rpm;
        }

        return $hmByReadingId;
    }

    public function historyExport(Request $req, string $rpm_id)
    {
        [$startDate, $endDate, $error] = $this->resolveRange(
            $req,
            ['1h' => 1, '2h' => 2, '4h' => 4, '8h' => 8, '16h' => 16, '24h' => 24],
            1
        );

        if ($error) {
            abort(422, $error);
        }

        $accumulatedHMByReadingId = $this->getAccumulatedHMByReadingId($startDate, $endDate, $rpm_id);

        $rows = RpmReadings::query()
            ->select('id', 'rpm', 'temp', 'humi', 'recorded_at')
            ->where('rpm_id', $rpm_id)
            ->whereBetween('recorded_at', [$startDate, $endDate])
            ->orderBy('recorded_at', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $data = $rows->map(function ($row) use ($rpm_id, $accumulatedHMByReadingId) {
            return [
                'No. RPM' => $rpm_id,
                'Timestamp' => $row->recorded_at ? Carbon::parse($row->recorded_at)->format('Y-m-d H:i:s') : '-',
                'RPM' => round((float) ($row->rpm ?? 0), 2),
                'HM' => round((float) ($accumulatedHMByReadingId[$row->id] ?? 0), 2),
                'TEMP' => round((float) ($row->temp ?? 0), 1),
                'HUMI' => round((float) ($row->humi ?? 0), 1),
            ];
        })->values()->all();

        $filename = "History_{$rpm_id}_" . $startDate->format('Ymd') . "_" . $endDate->format('Ymd') . ".xlsx";

        return Excel::download(
            new MachineHistoryExport($data, $rpm_id, [
                'start' => $startDate->format('d-m-Y H:i'),
                'end' => $endDate->format('d-m-Y H:i')
            ]),
            $filename
        );
    }
    public function tableData(Request $req)
    {
        [$startDate, $endDate, $error] = $this->resolveRange(
            $req,
            ['1h' => 1, '2h' => 2, '4h' => 4, '8h' => 8, '16h' => 16, '24h' => 24],
            1
        );

        if ($error) {
            return response()->json([
                'error' => 'Invalid date range',
                'message' => $error,
            ], 422);
        }

        $calculatedHM = $this->getCalculatedHM($startDate, $endDate);

        $rows = RpmReadings::query()
            ->selectRaw('
                rpm_id, 
                AVG(rpm) as rpm, 
                AVG(temp) as temp, 
                AVG(humi) as humi, 
                MAX(recorded_at) as last_updated
            ')
            ->whereBetween('recorded_at', [$startDate, $endDate])
            ->groupBy('rpm_id')
            ->get()
            ->map(function ($r) use ($calculatedHM) {
                return [
                    'name' => $r->rpm_id,
                    'last_updated' => $r->last_updated ? Carbon::parse($r->last_updated)->format('Y-m-d H:i:s') : '-',
                    'rpm' => round((float)$r->rpm, 2),
                    'hm' => round($calculatedHM[$r->rpm_id] ?? 0, 2),
                    'temp' => round((float)$r->temp, 1),
                    'humi' => round((float)$r->humi, 1),
                    'status' => 'active'
                ];
            });

        // 3. Custom Sorting Logic
        $sortBy = $req->query('sort_by', 'auto');
        if ($sortBy === 'auto') {
            $rows = $rows->sort(function ($a, $b) {
                // Priority 1: HM ASC
                if ($a['hm'] != $b['hm']) {
                    return $a['hm'] <=> $b['hm'];
                }

                // Priority 2: Ampere < 14 first, then value ASC
                $aUnder = $a['rpm'] < 14;
                $bUnder = $b['rpm'] < 14;
                if ($aUnder && !$bUnder) return -1;
                if (!$aUnder && $bUnder) return 1;
                if ($a['rpm'] != $b['rpm']) {
                    return $a['rpm'] <=> $b['rpm'];
                }

                // Priority 3: Temp deviation from 60 DESC, value ASC
                $aTempDev = abs($a['temp'] - 60);
                $bTempDev = abs($b['temp'] - 60);
                if (round($aTempDev, 1) != round($bTempDev, 1)) {
                    return $bTempDev <=> $aTempDev;
                }
                if ($a['temp'] != $b['temp']) {
                    return $a['temp'] <=> $b['temp'];
                }

                // Priority 4: Moist deviation from 20 DESC, value ASC
                $aMoistDev = abs($a['humi'] - 20);
                $bMoistDev = abs($b['humi'] - 20);
                if (round($aMoistDev, 1) != round($bMoistDev, 1)) {
                    return $bMoistDev <=> $aMoistDev;
                }
                if ($a['humi'] != $b['humi']) {
                    return $a['humi'] <=> $b['humi'];
                }

                return strnatcmp($a['name'], $b['name']);
            });
        } elseif ($sortBy === 'rpm') {
            $rows = $rows->sort(function ($a, $b) {
                $aUnder = $a['rpm'] < 14;
                $bUnder = $b['rpm'] < 14;
                if ($aUnder && !$bUnder) return -1;
                if (!$aUnder && $bUnder) return 1;
                if ($a['rpm'] != $b['rpm']) {
                    return $a['rpm'] <=> $b['rpm'];
                }
                return strnatcmp($a['name'], $b['name']);
            });
        } elseif ($sortBy === 'hm') {
            $rows = $rows->sort(function ($a, $b) {
                if ($a['hm'] != $b['hm']) {
                    return $a['hm'] <=> $b['hm'];
                }
                return strnatcmp($a['name'], $b['name']);
            });
        } elseif ($sortBy === 'temp') {
            $rows = $rows->sort(function ($a, $b) {
                $distA = abs($a['temp'] - 60);
                $distB = abs($b['temp'] - 60);
                if (round($distA, 1) != round($distB, 1)) return $distB <=> $distA;
                if ($a['temp'] != $b['temp']) return $a['temp'] <=> $b['temp'];
                return strnatcmp($a['name'], $b['name']);
            });
        } elseif ($sortBy === 'humi') {
            $rows = $rows->sort(function ($a, $b) {
                $distA = abs($a['humi'] - 20);
                $distB = abs($b['humi'] - 20);
                if (round($distA, 1) != round($distB, 1)) return $distB <=> $distA;
                if ($a['humi'] != $b['humi']) return $a['humi'] <=> $b['humi'];
                return strnatcmp($a['name'], $b['name']);
            });
        } else {
            $rows = $rows->sortBy('name', SORT_NATURAL);
        }

        return response()->json([
            'data' => $rows->values(),
            'summary_info' => [
                'start' => $startDate->format('Y-m-d H:i:s'),
                'end' => $endDate->format('Y-m-d H:i:s'),
                'is_summary' => true,
                'current_sort' => $sortBy
            ]
        ]);
    }



    private function resolveRange(Request $request, array $filterHours, int $defaultHours): array
    {
        $start = $request->input('start');
        $end = $request->input('end');

        if (($start && !$end) || (!$start && $end)) {
            return [null, null, 'Start and end must be provided together.'];
        }

        if ($start && $end) {
            try {
                $startDate = $this->parseDateTime($start);
                $endDate = $this->parseDateTime($end);
            } catch (\Throwable $e) {
                return [null, null, 'Start/end format is invalid.'];
            }

            if ($startDate->greaterThanOrEqualTo($endDate)) {
                return [null, null, 'Start must be before end.'];
            }

            $now = now();
            if ($startDate->greaterThan($now)) {
                return [null, null, 'Start must not be in the future.'];
            }
            if ($endDate->greaterThan($now)) {
                return [null, null, 'End must not be in the future.'];
            }

            return [$startDate, $endDate, null];
        }

        $filter = $request->input('filter');
        if ($filter) {
            $shiftRange = $this->resolveShiftRange($filter, now());
            if ($shiftRange) {
                return [$shiftRange[0], $shiftRange[1], null];
            }
        }
        $hours = $filterHours[$filter] ?? $defaultHours;
        $endDate = now();
        $startDate = now()->subHours($hours);

        return [$startDate, $endDate, null];
    }

    private function resolveShiftRange(string $filter, Carbon $now): ?array
    {
        $shiftStartHours = [
            'shift1' => 7,
            'shift2' => 15,
            'shift3' => 23,
        ];

        if ($filter === 'auto' || $filter === 'current') {
            $hour = $now->hour;
            if ($hour >= 7 && $hour < 15) $filter = 'shift1';
            elseif ($hour >= 15 && $hour < 23) $filter = 'shift2';
            else $filter = 'shift3';
        }

        if (!array_key_exists($filter, $shiftStartHours)) {
            return null;
        }

        $start = $now->copy()->setTime($shiftStartHours[$filter], 0, 0);

        // If it's shift 3 and current time is between 00:00 and 07:00, 
        // the start was yesterday at 23:00.
        // Otherwise, if now < start, it means we are in the morning but looking for previous day's shift.
        if ($now->lt($start)) {
            $start->subDay();
        }

        $end = $start->copy()->addHours(8)->subSecond();

        // Current shift limit is always "now" so it doesn't show future data
        $realEnd = $end->copy();
        if ($realEnd->greaterThan($now)) {
            $realEnd = $now->copy();
        }

        return [$start, $realEnd];
    }

    /**
     * Calculate HM based on time duration between readings where rpm > 0.
     * Uses MySQL LAG() window function for efficiency.
     */
    private function getCalculatedHM(Carbon $startDate, Carbon $endDate, ?string $rpm_id = null)
    {
        $whereClause = $rpm_id ? "WHERE rpm_id = ? AND recorded_at BETWEEN ? AND ?" : "WHERE recorded_at BETWEEN ? AND ?";
        $params = $rpm_id ? [$rpm_id, $startDate->format('Y-m-d H:i:s'), $endDate->format('Y-m-d H:i:s')] : [$startDate->format('Y-m-d H:i:s'), $endDate->format('Y-m-d H:i:s')];
        $sql = "
            SELECT 
                rpm_id,
                SUM(CASE WHEN prev_rpm > 0 THEN diff_seconds ELSE 0 END) / 3600 as total_hm
            FROM (
                SELECT 
                    rpm_id,
                    rpm,
                    recorded_at,
                    LAG(rpm) OVER (PARTITION BY rpm_id ORDER BY recorded_at) as prev_rpm,
                    LEAST(
                        TIMESTAMPDIFF(SECOND, LAG(recorded_at) OVER (PARTITION BY rpm_id ORDER BY recorded_at), recorded_at),
                        30
                    ) as diff_seconds
                FROM rpm_readings
                $whereClause
            ) t
            GROUP BY rpm_id
        ";

        $results = DB::select($sql, $params);

        return collect($results)->pluck('total_hm', 'rpm_id')->toArray();
    }

    public function downloadReport(Request $request)
    {
        // Increase limits for report generation
        set_time_limit(300);
        ini_set('memory_limit', '512M');

        // Validate input
        $request->validate([
            'start' => 'required|string',
            'end' => 'required|string',
            'line' => 'required|string',
            'hari_kerja' => 'required|numeric|min:1',
            'jam_kerja' => 'required|numeric|min:1',
            'total_briket' => 'required|numeric|min:0',
        ]);

        // Parse datetime
        try {
            $startDate = $this->parseDateTime($request->input('start'));
            $endDate = $this->parseDateTime($request->input('end'));
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => 'Invalid date format']);
        }

        // Validate date range
        if ($startDate->greaterThanOrEqualTo($endDate)) {
            return back()->withErrors(['error' => 'Start date must be before end date']);
        }

        $now = now();
        if ($startDate->greaterThan($now) || $endDate->greaterThan($now)) {
            return back()->withErrors(['error' => 'Dates must not be in the future']);
        }

        // Get calculated HM for the range
        $calculatedHM = $this->getCalculatedHM($startDate, $endDate);

        // Get first and last HM reading for each machine
        $machines = RpmReadings::query()
            ->whereBetween('recorded_at', [$startDate, $endDate])
            ->select('rpm_id')
            ->distinct()
            ->get()
            ->map(function ($machine) use ($startDate, $endDate, $calculatedHM) {
                $machineName = $machine->rpm_id;

                // Get first reading (HM Awal)
                $firstReading = RpmReadings::where('rpm_id', $machineName)
                    ->where('recorded_at', '>=', $startDate)
                    ->orderBy('recorded_at', 'asc')
                    ->first();

                // Get last reading (HM Akhir)
                $lastReading = RpmReadings::where('rpm_id', $machineName)
                    ->where('recorded_at', '<=', $endDate)
                    ->orderBy('recorded_at', 'desc')
                    ->first();

                return [
                    'name' => $machineName,
                    'hm_awal' => $firstReading ? round((float)$firstReading->hm, 2) : 0,
                    'hm_akhir' => $lastReading ? round((float)$lastReading->hm, 2) : 0,
                    'hm' => round($calculatedHM[$machineName] ?? 0, 2),
                ];
            })
            ->sortBy('name', SORT_NATURAL)
            ->values();

        // Prepare export parameters
        $params = [
            'start' => $startDate->format('d-m-Y H:i'),
            'end' => $endDate->format('d-m-Y H:i'),
            'line' => $request->input('line'),
            'hari_kerja' => (int)$request->input('hari_kerja'),
            'jam_kerja' => (int)$request->input('jam_kerja'),
            'total_briket' => (int)$request->input('total_briket'),
        ];

        // Generate filename
        $filename = 'Rekap_HM_Mesin_Press_' . $startDate->format('Ymd') . '_' . $endDate->format('Ymd') . '.xlsx';

        // Export to Excel
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\MachineReportExport($machines, $params),
            $filename
        );
    }

    private function parseDateTime(string $value): Carbon

    {
        try {
            $parsed = Carbon::createFromFormat('Y-m-d H:i', $value);
            if ($parsed !== false) {
                return $parsed;
            }
        } catch (\Throwable $e) {
            return Carbon::parse($value);
        }

        return Carbon::parse($value);
    }
}


