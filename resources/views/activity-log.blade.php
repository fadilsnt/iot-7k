<x-app-layout>
    @push('head')
    <style>
        /* Page Header Section */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid #f1f5f9;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .header-icon {
            width: 56px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            border-radius: 14px;
            box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
        }

        .header-icon i {
            font-size: 1.75rem;
            color: white;
        }

        .header-text h1 {
            font-size: 1.875rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }

        .header-text p {
            color: #64748b;
            margin: 0.25rem 0 0 0;
            font-size: 0.95rem;
        }

        /* Filters Section */
        .filters-section {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.375rem;
        }

        .filter-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .filter-select {
            padding: 0.625rem 2.5rem 0.625rem 1rem;
            border: 1.5px solid #e2e8f0;
            background: white;
            color: #475569;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            border-radius: 10px;
            transition: all 0.3s ease;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23475569' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            min-width: 180px;
        }

        .filter-select:hover {
            border-color: #8b5cf6;
            box-shadow: 0 2px 6px rgba(139, 92, 246, 0.15);
        }

        .filter-select:focus {
            outline: none;
            border-color: #8b5cf6;
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.08);
        }

        .filter-date {
            padding: 0.625rem 1rem;
            border: 1.5px solid #e2e8f0;
            background: white;
            color: #475569;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            border-radius: 10px;
            transition: all 0.3s ease;
            width: 180px;
        }

        .filter-date:hover {
            border-color: #8b5cf6;
            box-shadow: 0 2px 6px rgba(139, 92, 246, 0.15);
        }

        .filter-date:focus {
            outline: none;
            border-color: #8b5cf6;
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.08);
        }

        .btn-filter-apply {
            padding: 0.625rem 1.5rem;
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 6px rgba(139, 92, 246, 0.3);
            margin-top: auto;
        }

        .btn-filter-apply:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(139, 92, 246, 0.4);
        }

        .btn-reset {
            padding: 0.625rem 1.5rem;
            background: white;
            color: #64748b;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: auto;
        }

        .btn-reset:hover {
            border-color: #cbd5e1;
            background: #f8fafc;
        }

        /* Stats Cards */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            padding: 1.25rem;
            border-radius: 12px;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border: 1.5px solid #e2e8f0;
        }

        .stat-card.danger {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            border-color: #fecaca;
        }

        .stat-card.warning {
            background: linear-gradient(135deg, #fefce8 0%, #fef3c7 100%);
            border-color: #fde68a;
        }

        .stat-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
        }

        .stat-card.danger .stat-value {
            color: #dc2626;
        }

        .stat-card.warning .stat-value {
            color: #d97706;
        }

        /* Log List */
        .log-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .log-item {
            display: flex;
            gap: 1.25rem;
            padding: 1.25rem;
            background: #f8fafc;
            border-radius: 12px;
            border: 1.5px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .log-item:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            transform: translateX(4px);
        }

        .log-icon {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            flex-shrink: 0;
        }

        .log-icon.danger {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            box-shadow: 0 3px 10px rgba(220, 38, 38, 0.3);
        }

        .log-icon.warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            box-shadow: 0 3px 10px rgba(245, 158, 11, 0.3);
        }

        .log-icon i {
            font-size: 1.25rem;
            color: white;
        }

        .log-content {
            flex: 1;
        }

        .log-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 0.5rem;
        }

        .log-sensor {
            font-size: 1rem;
            font-weight: 700;
            color: #1e293b;
        }

        .log-time {
            font-size: 0.875rem;
            color: #64748b;
            white-space: nowrap;
        }

        .log-message {
            font-size: 0.875rem;
            color: #475569;
            margin-bottom: 0.5rem;
        }

        .log-details {
            display: flex;
            gap: 1rem;
            font-size: 0.8125rem;
        }

        .log-detail {
            display: flex;
            align-items: center;
            gap: 0.375rem;
            color: #64748b;
        }

        .log-detail i {
            font-size: 0.75rem;
        }

        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge.danger {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .badge.warning {
            background: #fefce8;
            color: #d97706;
            border: 1px solid #fde68a;
        }

        /* Empty State */
        .empty-state {
            padding: 4rem 2rem;
            text-align: center;
        }

        .empty-state i {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 1.5rem;
        }

        .empty-state h3 {
            font-size: 1.25rem;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            font-size: 0.95rem;
            color: #94a3b8;
        }

        /* Pagination */
        .pagination-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 2px solid #f1f5f9;
        }

        .pagination {
            display: flex;
            gap: 0.5rem;
        }

        .pagination a, .pagination span {
            padding: 0.5rem 0.875rem;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .pagination a {
            background: white;
            color: #64748b;
            border: 1.5px solid #e2e8f0;
            text-decoration: none;
        }

        .pagination a:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
            color: #475569;
        }

        .pagination .active span {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
            border: 1.5px solid #7c3aed;
            box-shadow: 0 2px 6px rgba(139, 92, 246, 0.3);
        }

        .pagination .disabled span {
            background: #f8fafc;
            color: #cbd5e1;
            border: 1.5px solid #f1f5f9;
            cursor: not-allowed;
        }
    </style>
    @endpush

    <header class="dashboard-header" style="display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center;">
        <span class="text-xs uppercase tracking-wide text-white/80 flex items-center gap-2">
            <i class="fa-solid fa-clock-rotate-left"></i>
            Activity Log
        </span>
        <h1 style="font-size: 1.25rem; margin-top: 0.5rem;">Riwayat Notifikasi Sensor IoT</h1>
    </header>

    <div class="content-container">
        <div style="background: white; border-radius: 16px; padding: 2rem; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">

            <!-- Filters -->
            <form method="GET" action="{{ route('activity-log.index') }}">
            <div class="filters-section">
                <div class="filter-group">
                    <label class="filter-label">Kondisi</label>
                    <select name="condition" class="filter-select">
                        <option value="">Semua Kondisi</option>
                        <option value="2" {{ request('condition') == '2' ? 'selected' : '' }}>Waspada</option>
                        <option value="3" {{ request('condition') == '3' ? 'selected' : '' }}>Bahaya</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label class="filter-label">Sensor</label>
                    <select name="sensor_code" class="filter-select">
                        <option value="">Semua Sensor</option>
                        <option value="TEMP_01" {{ request('sensor_code') == 'TEMP_01' ? 'selected' : '' }}>Sensor Suhu 1</option>
                        <option value="MOIST_01" {{ request('sensor_code') == 'MOIST_01' ? 'selected' : '' }}>Sensor Kadar Air 1</option>
                        <option value="TEMP_02" {{ request('sensor_code') == 'TEMP_02' ? 'selected' : '' }}>Sensor Suhu 2</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label class="filter-label">Dari Tanggal</label>
                    <input type="date" name="date_from" class="filter-date" value="{{ request('date_from') }}">
                </div>

                <div class="filter-group">
                    <label class="filter-label">Sampai Tanggal</label>
                    <input type="date" name="date_to" class="filter-date" value="{{ request('date_to') }}">
                </div>

                <button type="submit" class="btn-filter-apply">
                    <i class="fa-solid fa-filter"></i> Terapkan
                </button>

                <a href="{{ route('activity-log.index') }}" class="btn-reset">
                    <i class="fa-solid fa-rotate-left"></i> Reset
                </a>
            </div>
        </form>

        <!-- Stats -->
        @php
            $totalDanger = \App\Models\NotificationLog::where('condition', 3)->count();
            $totalWarning = \App\Models\NotificationLog::where('condition', 2)->count();
        @endphp
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-label">Total Logs</div>
                <div class="stat-value">{{ \App\Models\NotificationLog::count() }}</div>
            </div>
            <div class="stat-card danger">
                <div class="stat-label">Bahaya</div>
                <div class="stat-value">{{ $totalDanger }}</div>
            </div>
            <div class="stat-card warning">
                <div class="stat-label">Waspada</div>
                <div class="stat-value">{{ $totalWarning }}</div>
            </div>
        </div>

        <!-- Log List -->
        @if($logs->count() > 0)
            <!-- DEBUG INFO -->
            <div style="background: #fff3cd; border: 2px solid #ffc107; padding: 1rem; margin-bottom: 1rem; border-radius: 8px; font-family: monospace; font-size: 0.875rem;">
                <strong>DEBUG - First Log Data:</strong><br>
                @php $firstLog = $logs->first(); @endphp
                ID: {{ $firstLog->id }}<br>
                sensor_name: {{ $firstLog->sensor_name }}<br>
                sensor_code: {{ $firstLog->sensor_code }}<br>
                sensor_type: {{ $firstLog->sensor_type }}<br>
                condition: {{ $firstLog->condition }}<br>
                condition_name: {{ $firstLog->condition_name }}<br>
                sensor_value: {{ $firstLog->sensor_value ?? 'NULL' }}<br>
                message: {{ $firstLog->message }}<br>
                created_at: {{ $firstLog->created_at }}<br>
            </div>
            
            <div class="log-list">
                @foreach($logs as $log)
                    <div class="log-item">
                        <div class="log-icon {{ $log->condition == 3 ? 'danger' : 'warning' }}">
                            <i class="fa-solid {{ $log->condition == 3 ? 'fa-triangle-exclamation' : 'fa-exclamation-circle' }}"></i>
                        </div>
                        <div class="log-content">
                            <div class="log-header">
                                <div>
                                    <div class="log-sensor">
                                        {{ $log->sensor_name }}
                                        @if($log->sensor_value)
                                            : <span style="color: {{ $log->condition == 3 ? '#dc2626' : '#d97706' }}; font-weight: 700;">{{ $log->sensor_value }} {{ $log->sensor_type == 'temp' ? '°C' : '%' }}</span>
                                        @endif
                                    </div>
                                    <span class="badge {{ $log->condition == 3 ? 'danger' : 'warning' }}">
                                        @if($log->condition == 3)
                                            Bahaya
                                        @elseif($log->condition == 2)
                                            Waspada
                                        @else
                                            Normal
                                        @endif
                                    </span>
                                </div>
                                <div class="log-time">
                                    <i class="fa-solid fa-clock"></i>
                                    {{ $log->created_at->format('d M Y, H:i') }}
                                </div>
                            </div>
                            <div class="log-message">{{ $log->message }}</div>
                            <div class="log-details">
                                <div class="log-detail">
                                    <i class="fa-solid fa-microchip"></i>
                                    <span>{{ $log->sensor_code }}</span>
                                </div>
                                <div class="log-detail">
                                    <i class="fa-solid fa-tag"></i>
                                    <span>{{ ucfirst($log->sensor_type) }}</span>
                                </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="pagination-container">
            {{ $logs->links() }}
        </div>
    @else
        <div class="empty-state">
            <i class="fa-solid fa-inbox"></i>
            <h3>Belum Ada Log</h3>
            <p>Notifikasi sensor akan muncul di sini ketika kondisi sensor berubah</p>
        </div>
    @endif
        </div>
    </div>

    <footer class="ottoman-footer">
        <p>&copy; <span id="footerYear">{{ date('Y') }}</span> Thermocouple Monitoring. All rights reserved.</p>
    </footer>
</x-app-layout>
