<x-app-layout>
    @push('head')
        <style>
            /* Responsive table -> cards fallback */
            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .data-table {
                width: 100%;
                border-collapse: collapse;
            }

            .data-table th,
            .data-table td {
                padding: 0.65rem;
                text-align: left;
                border-bottom: 1px solid rgba(15, 23, 42, 0.04);
            }

            .data-table th {
                font-weight: 600;
                font-size: 0.875rem;
                color: rgba(2, 6, 23, 0.7);
            }

            /* mobile: hide table, show cards */
            .sensor-cards {
                display: none;
            }

            @media (max-width: 768px) {
                .table-responsive {
                    display: none;
                }

                .sensor-cards {
                    display: block;
                }

                .sensor-cards .card {
                    border: 1px solid rgba(15, 23, 42, 0.04);
                    padding: 0.75rem;
                    border-radius: 10px;
                    margin-bottom: 0.75rem;
                    background: var(--card, #fff);
                }

                .sensors-header {
                    flex-direction: column;
                    align-items: stretch;
                    gap: 0.5rem;
                }

                .sensors-header .actions {
                    display: flex;
                    gap: 0.5rem;
                    flex-wrap: wrap;
                    width: 100%;
                }

                .sensor-meta {
                    color: rgba(71, 85, 105, 0.9);
                    font-size: 0.9rem;
                    margin-top: 0.35rem;
                }

                .card-actions {
                    display: flex;
                    gap: 0.5rem;
                    justify-content: flex-end;
                    margin-top: 0.5rem;
                }
            }

            /* desktop: keep cards hidden */
            @media (min-width: 769px) {
                .sensor-cards {
                    display: none;
                }
            }

            .status-indicator {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.25rem 0.55rem;
                border-radius: 999px;
                font-weight: 600;
                font-size: 0.85rem;
            }

            .status-indicator .pulse {
                width: 8px;
                height: 8px;
                border-radius: 999px;
                display: inline-block;
                background: #94a3b8;
            }

            .status-indicator.active .pulse {
                background: #10b981;
            }

            .status-indicator.offline .pulse {
                background: #f97316;
            }

            .status-indicator.maintenance .pulse {
                background: #f59e0b;
            }
        </style>
    @endpush

    <header class="dashboard-header"
        style="display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center;">
        <span class="text-xs uppercase tracking-wide text-white/80 flex items-center gap-2">
            <i class="fa-solid fa-microchip"></i>
            Sensors Management
        </span>
        <h1 style="font-size: 1.25rem; margin-top: 0.5rem;">Manage Your Sensors</h1>
    </header>

    <div class="content-container">
        <!-- Flash Messages -->
        @if ($errors->any())
            <div class="alert alert-danger mb-4">
                <div class="flex items-start gap-3">
                    <i class="fa-solid fa-circle-exclamation text-lg"></i>
                    <div>
                        <strong>Validation Errors</strong>
                        <ul class="mt-2 ml-4 list-disc">
                            @foreach ($errors->all() as $error)
                                <li class="text-sm">{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success mb-4">
                <i class="fa-solid fa-check-circle"></i>
                {{ session('success') }}
            </div>
        @endif

        <!-- Header Actions -->
        <div class="sensors-header mb-6 flex items-center justify-between">
            <div class="actions flex gap-3 flex-wrap">
                <a href="{{ route('sensors.create') }}" class="btn btn-primary">
                    <i class="fa-solid fa-plus"></i>
                    Create Sensor
                </a>
                <a href="{{ route('sensor-types.create') }}" class="btn btn-secondary">
                    <i class="fa-solid fa-layer-group"></i>
                    Create Sensor Type
                </a>
                <a href="{{ route('sensors.export') }}" class="btn btn-secondary">
                    <i class="fa-solid fa-download"></i>
                    Export CSV
                </a>
            </div>
        </div>

        <!-- Sensors Table (desktop) + Cards (mobile) -->
        <div class="table-card">
            @if ($sensors->count() > 0)
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th width="5%"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                                <th>Sensor Code</th>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th width="10%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sensors as $sensor)
                                @php
                                    $statusClass =
                                        $sensor->status === 'active'
                                            ? 'active'
                                            : ($sensor->status === 'inactive'
                                                ? 'offline'
                                                : 'maintenance');
                                @endphp
                                <tr class="hover:bg-blue-50 transition-colors">
                                    <td><input type="checkbox" class="form-check-input sensor-checkbox"
                                            value="{{ $sensor->id }}"></td>
                                    <td class="font-mono text-sm font-semibold">{{ $sensor->sensor_code }}</td>
                                    <td class="font-semibold">{{ $sensor->name }}</td>
                                    <td class="text-muted"><span
                                            class="badge badge-info">{{ $sensor->sensorType->name }}
                                            ({{ $sensor->sensorType->unit }})
                                        </span></td>
                                    <td class="text-muted"><i class="fa-solid fa-map-pin text-xs mr-1"></i>
                                        {{ $sensor->location }}</td>
                                    <td><span class="status-indicator {{ $statusClass }}"><span
                                                class="pulse"></span>{{ ucfirst($sensor->status) }}</span></td>
                                    <td class="text-sm text-muted">{{ $sensor->created_at?->format('M d, Y') ?? '-' }}
                                    </td>
                                    <td>
                                        <div class="flex gap-2 justify-end">
                                            <a href="{{ route('sensors.show', $sensor) }}" class="btn-icon-sm"
                                                title="View"><i class="fa-solid fa-eye"></i></a>
                                            <a href="{{ route('sensors.edit', $sensor) }}" class="btn-icon-sm"
                                                title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
                                            <form action="{{ route('sensors.destroy', $sensor) }}" method="POST"
                                                class="inline" onsubmit="return confirm('Are you sure?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="btn-icon-sm text-red-600 hover:text-red-700"
                                                    title="Delete"><i class="fa-solid fa-trash"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Mobile: stacked cards -->
                <div class="sensor-cards" aria-hidden="true">
                    @foreach ($sensors as $sensor)
                        @php
                            $statusClass =
                                $sensor->status === 'active'
                                    ? 'active'
                                    : ($sensor->status === 'inactive'
                                        ? 'offline'
                                        : 'maintenance');
                        @endphp
                        <div class="card">
                            <div
                                style="display:flex; justify-content:space-between; gap:0.75rem; align-items:flex-start;">
                                <div style="min-width:0;">
                                    <div class="font-mono text-sm font-semibold">{{ $sensor->sensor_code }}</div>
                                    <div class="font-semibold">{{ $sensor->name }}</div>
                                    <div class="sensor-meta"><span
                                            class="badge badge-info">{{ $sensor->sensorType->name }}
                                            ({{ $sensor->sensorType->unit }})
                                        </span></div>
                                    <div class="sensor-meta"><i
                                            class="fa-solid fa-map-pin text-xs mr-1"></i>{{ $sensor->location }}</div>
                                </div>
                                <div style="text-align:right; min-width:0;">
                                    <div><span class="status-indicator {{ $statusClass }}"><span
                                                class="pulse"></span> {{ ucfirst($sensor->status) }}</span></div>
                                    <div class="text-sm text-muted" style="margin-top:0.35rem;">
                                        {{ $sensor->created_at?->format('M d, Y') ?? '-' }}</div>
                                    <div class="card-actions">
                                        <a href="{{ route('sensors.show', $sensor) }}" class="btn-icon-sm"
                                            title="View"><i class="fa-solid fa-eye"></i></a>
                                        <a href="{{ route('sensors.edit', $sensor) }}" class="btn-icon-sm"
                                            title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
                                        <form action="{{ route('sensors.destroy', $sensor) }}" method="POST"
                                            class="inline" onsubmit="return confirm('Are you sure?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-icon-sm text-red-600 hover:text-red-700"
                                                title="Delete"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <i class="fa-solid fa-microchip text-4xl opacity-30"></i>
                    <h3>No Sensors Yet</h3>
                    <p>Start by creating your first sensor.</p>
                    <a href="{{ route('sensors.create') }}" class="btn btn-primary mt-4">
                        <i class="fa-solid fa-plus"></i>
                        Create Sensor
                    </a>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const selectAllCheckbox = document.getElementById('selectAll');
                const sensorCheckboxes = document.querySelectorAll('.sensor-checkbox');
                const bulkActions = document.getElementById('bulkActions');
                const selectedIds = document.getElementById('selectedIds');

                // Guard clause
                if (!selectAllCheckbox || sensorCheckboxes.length === 0) {
                    return;
                }

                function updateBulkActions() {
                    const checked = Array.from(sensorCheckboxes).filter(cb => cb.checked);
                    if (checked.length > 0) {
                        bulkActions.classList.remove('hidden');
                        selectedIds.innerHTML = checked.map(cb =>
                            `<input type="hidden" name="ids[]" value="${cb.value}">`
                        ).join('');
                    } else {
                        bulkActions.classList.add('hidden');
                    }
                }

                if (selectAllCheckbox) {
                    selectAllCheckbox.addEventListener('change', () => {
                        sensorCheckboxes.forEach(cb => cb.checked = selectAllCheckbox.checked);
                        updateBulkActions();
                    });
                }

                sensorCheckboxes.forEach(cb => {
                    cb.addEventListener('change', () => {
                        if (selectAllCheckbox) {
                            selectAllCheckbox.checked = Array.from(sensorCheckboxes).every(c => c
                                .checked);
                        }
                        updateBulkActions();
                    });
                });
            });
        </script>
    @endpush

    <footer class="ottoman-footer">
        <p>&copy; <span id="footerYear">{{ date('Y') }}</span> Thermocouple Monitoring. All rights reserved.</p>
    </footer>
</x-app-layout>
