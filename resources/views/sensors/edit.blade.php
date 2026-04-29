<x-app-layout>
    <header class="dashboard-header">
        <span class="text-xs uppercase tracking-wide text-white/80 flex items-center gap-2">
            <i class="fa-solid fa-pen-to-square"></i>
            Edit Sensor
        </span>
        <h1>{{ $sensor->name }}</h1>
    </header>

    <div class="content-container">
        <div class="form-container max-w-2xl">
            <form action="{{ route('sensors.update', $sensor) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="form-section">
                    <h3 class="form-section-title">Sensor Details</h3>

                    <div class="form-group">
                        <label for="sensor_type_id" class="form-label">Sensor Type <span class="text-red-500">*</span></label>
                        <select id="sensor_type_id" name="sensor_type_id" class="form-select @error('sensor_type_id') error @enderror" required>
                            <option value="">-- Select Sensor Type --</option>
                            @foreach ($sensorTypes as $type)
                                <option value="{{ $type->id }}" {{ old('sensor_type_id', $sensor->sensor_type_id) == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }} ({{ $type->unit }})
                                </option>
                            @endforeach
                        </select>
                        @error('sensor_type_id')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="sensor_code" class="form-label">Sensor Code (Read-only)</label>
                        <input type="text" id="sensor_code" class="form-input bg-gray-100" value="{{ $sensor->sensor_code }}" disabled>
                        <p class="form-help">Sensor code cannot be changed</p>
                    </div>

                    <div class="form-group">
                        <label for="sensor_name" class="form-label">Sensor Name <span class="text-red-500">*</span></label>
                        <input type="text" id="sensor_name" name="sensor_name" class="form-input @error('sensor_name') error @enderror"
                            placeholder="e.g., Main Room Temperature" value="{{ old('sensor_name', $sensor->name) }}" required>
                        @error('sensor_name')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="location" class="form-label">Location <span class="text-red-500">*</span></label>
                        <input type="text" id="location" name="location" class="form-input @error('location') error @enderror"
                            placeholder="e.g., Main Greenhouse" value="{{ old('location', $sensor->location) }}" required>
                        @error('location')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="description" class="form-label">Description (Optional)</label>
                        <textarea id="description" name="description" class="form-textarea"
                            placeholder="Add notes about this sensor..." rows="3">{{ old('description', $sensor->description) }}</textarea>
                        @error('description')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="status" class="form-label">Status <span class="text-red-500">*</span></label>
                        <select id="status" name="status" class="form-select @error('status') error @enderror" required>
                            <option value="">-- Select Status --</option>
                            <option value="active" {{ old('status', $sensor->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $sensor->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="maintenance" {{ old('status', $sensor->status) == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                        </select>
                        @error('status')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Threshold Settings Section -->
                <div class="form-section">
                    <h3 class="form-section-title">
                        <i class="fa-solid fa-gauge-high"></i> Threshold Settings
                    </h3>
                    <p class="text-sm text-gray-600 mb-4">
                        <i class="fa-solid fa-info-circle"></i> Set threshold values to monitor sensor readings. Leave empty to disable threshold monitoring.
                    </p>

                    <!-- Threshold Logic Explanation -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                        <h4 class="font-semibold text-blue-900 mb-2">Threshold Logic:</h4>
                        <ul class="text-sm text-blue-800 space-y-1">
                            <li><span class="font-semibold text-green-600">✓ Normal:</span> threshold_min_1 &lt; value &lt; threshold_plus_1</li>
                            <li><span class="font-semibold text-yellow-600">⚠ Warning:</span> (threshold_plus_1 &lt; value &lt; threshold_plus_2) OR (threshold_min_2 &lt; value &lt; threshold_min_1)</li>
                            <li><span class="font-semibold text-red-600">✗ Danger:</span> value &gt; threshold_plus_2 OR value &lt; threshold_min_2</li>
                        </ul>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Upper Thresholds -->
                        <div class="space-y-4">
                            <h4 class="font-semibold text-gray-700 flex items-center gap-2">
                                <i class="fa-solid fa-arrow-up text-red-500"></i> Upper Thresholds
                            </h4>
                            
                            <div class="form-group">
                                <label for="threshold_plus_1" class="form-label">
                                    Threshold +1 (Normal Upper Limit)
                                </label>
                                <input type="number" step="0.01" id="threshold_plus_1" name="threshold_plus_1" 
                                    class="form-input @error('threshold_plus_1') error @enderror"
                                    placeholder="e.g., 30.00" value="{{ old('threshold_plus_1', $sensor->threshold_plus_1) }}">
                                <p class="form-help">Batas atas kondisi normal</p>
                                @error('threshold_plus_1')
                                    <span class="form-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="threshold_plus_2" class="form-label">
                                    Threshold +2 (Danger Upper Limit)
                                </label>
                                <input type="number" step="0.01" id="threshold_plus_2" name="threshold_plus_2" 
                                    class="form-input @error('threshold_plus_2') error @enderror"
                                    placeholder="e.g., 35.00" value="{{ old('threshold_plus_2', $sensor->threshold_plus_2) }}">
                                <p class="form-help">Batas bahaya atas (di atas ini = bahaya)</p>
                                @error('threshold_plus_2')
                                    <span class="form-error">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Lower Thresholds -->
                        <div class="space-y-4">
                            <h4 class="font-semibold text-gray-700 flex items-center gap-2">
                                <i class="fa-solid fa-arrow-down text-blue-500"></i> Lower Thresholds
                            </h4>
                            
                            <div class="form-group">
                                <label for="threshold_min_1" class="form-label">
                                    Threshold -1 (Normal Lower Limit)
                                </label>
                                <input type="number" step="0.01" id="threshold_min_1" name="threshold_min_1" 
                                    class="form-input @error('threshold_min_1') error @enderror"
                                    placeholder="e.g., 20.00" value="{{ old('threshold_min_1', $sensor->threshold_min_1) }}">
                                <p class="form-help">Batas bawah kondisi normal</p>
                                @error('threshold_min_1')
                                    <span class="form-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="threshold_min_2" class="form-label">
                                    Threshold -2 (Danger Lower Limit)
                                </label>
                                <input type="number" step="0.01" id="threshold_min_2" name="threshold_min_2" 
                                    class="form-input @error('threshold_min_2') error @enderror"
                                    placeholder="e.g., 15.00" value="{{ old('threshold_min_2', $sensor->threshold_min_2) }}">
                                <p class="form-help">Batas bahaya bawah (di bawah ini = bahaya)</p>
                                @error('threshold_min_2')
                                    <span class="form-error">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('sensors.index') }}" class="btn btn-secondary">
                        <i class="fa-solid fa-arrow-left"></i>
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-check"></i>
                        Update Sensor
                    </button>
                </div>
            </form>
        </div>
    </div>

</x-app-layout>
