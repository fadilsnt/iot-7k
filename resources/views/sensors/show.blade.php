<x-app-layout>
    <header class="dashboard-header">
        <span class="text-xs uppercase tracking-wide text-white/80 flex items-center gap-2">
            <i class="fa-solid fa-microchip"></i>
            Sensor Details
        </span>
        <h1>{{ $sensor->name }}</h1>
    </header>

    <div class="content-container">
        @if (session('success'))
            <div class="alert alert-success mb-4">
                <i class="fa-solid fa-check-circle"></i>
                {{ session('success') }}
            </div>
        @endif

        <!-- Sensor Overview -->
        <div class="grid grid-cols-4 gap-4 mb-6">
            <div class="info-card">
                <div class="info-label">Sensor Code</div>
                <div class="info-value font-mono">{{ $sensor->sensor_code }}</div>
            </div>
            <div class="info-card">
                <div class="info-label">Type</div>
                <div class="info-value">{{ $sensor->sensorType->name }} ({{ $sensor->sensorType->unit }})</div>
            </div>
            <div class="info-card">
                <div class="info-label">Location</div>
                <div class="info-value text-sm">{{ $sensor->location }}</div>
            </div>
            <div class="info-card">
                <div class="info-label">Status</div>
                <div class="info-value">
                    <span class="status-indicator {{ $sensor->status === 'active' ? 'online' : ($sensor->status === 'inactive' ? 'offline' : 'maintenance') }}">
                        <span class="pulse"></span>
                        {{ ucfirst($sensor->status) }}
                    </span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-3 gap-4 mb-6">
            <!-- Sensor Information Panel -->
            <div class="col-span-2">
                <div class="card">
                    <div class="card-header flex items-center justify-between">
                        <h3>Sensor Information</h3>
                        <a href="{{ route('sensors.edit', $sensor) }}" class="btn btn-secondary btn-sm">
                            <i class="fa-solid fa-pen-to-square"></i>
                            Edit
                        </a>
                    </div>
                    <div class="card-body space-y-4">
                        <div class="info-row">
                            <span class="info-label">Sensor Type</span>
                            <span class="info-value">
                                {{ $sensor->sensorType->name }} ({{ $sensor->sensorType->unit }})
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Location</span>
                            <span class="info-value">
                                <i class="fa-solid fa-map-pin mr-2"></i>
                                {{ $sensor->location }}
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Description</span>
                            <span class="info-value">{{ $sensor->description ?? 'No description' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Created</span>
                            <span class="info-value">{{ $sensor->created_at->format('M d, Y H:i') }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Last Updated</span>
                            <span class="info-value">{{ $sensor->updated_at->format('M d, Y H:i') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="card">
                <div class="card-header">
                    <h3>Statistics</h3>
                </div>
                <div class="card-body space-y-4">
                    <div class="stat-item">
                        <div class="stat-number">{{ $sensor->readings->count() }}</div>
                        <div class="stat-label">Total Readings</div>
                    </div>
                    @if ($sensor->readings->isNotEmpty())
                        <div class="stat-item">
                            <div class="stat-number">{{ $sensor->readings->max('value') }}</div>
                            <div class="stat-label">Max Value</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">{{ number_format($sensor->readings->avg('value'), 2) }}</div>
                            <div class="stat-label">Average Value</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sensor Readings -->
        <div class="card">
            <div class="card-header">
                <h3>
                    <i class="fa-solid fa-chart-line mr-2"></i>
                    Recent Readings
                </h3>
            </div>
            @if ($sensor->readings->isNotEmpty())
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Value</th>
                                <th>Unit</th>
                                <th>Timestamp</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sensor->readings->take(20) as $reading)
                                <tr>
                                    <td class="text-blue-600 font-semibold">{{ number_format($reading->value, 2) }}</td>
                                    <td>{{ $sensor->sensorType->unit }}</td>
                                    <td class="text-sm text-muted">
                                        {{ $reading->recorded_at->format('M d, H:i:s') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state py-8">
                    <i class="fa-solid fa-chart-line opacity-30"></i>
                    <p>No readings yet</p>
                </div>
            @endif
        </div>

        <!-- Form Actions -->
        <div class="form-actions mt-6">
            <a href="{{ route('sensors.index') }}" class="btn btn-secondary">
                <i class="fa-solid fa-arrow-left"></i>
                Back to Sensors
            </a>
            <form action="{{ route('sensors.destroy', $sensor) }}" method="POST" class="inline"
                onsubmit="return confirm('Delete this sensor and all its readings? This action cannot be undone.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i class="fa-solid fa-trash"></i>
                    Delete Sensor
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
