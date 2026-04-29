<?php

namespace App\Http\Controllers;

use App\Models\NotificationLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {

        
        $query = NotificationLog::query()
            ->orderBy('created_at', 'desc');

        // Filter by condition if provided
        if ($request->filled('condition')) {
            $query->where('condition', $request->condition);
        }

        // Filter by sensor if provided
        if ($request->filled('sensor_code')) {
            $query->where('sensor_code', $request->sensor_code);
        }

        // Filter by date range if provided
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(20)->appends($request->except('page'));

        return view('activity-log', compact('logs'));
    }
}
