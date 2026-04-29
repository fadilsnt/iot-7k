<x-app-layout>
    <header class="dashboard-header">
        <span class="text-xs uppercase tracking-wide text-white/80 flex items-center gap-2">
            <i class="fa-solid fa-plus"></i>
            Add Sensor
        </span>
        <h1>Create New Sensor</h1>
    </header>

    <div class="content-container">
        <div class="form-container max-w-2xl">
            <form action="{{ route('sensors.store') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Sensor Information -->
                <div class="form-section">
                    <h3 class="form-section-title">Sensor Details</h3>

                    <div class="form-group">
                        <label for="sensor_type_id" class="form-label">Sensor Type <span class="text-red-500">*</span></label>
                        <div class="flex gap-2">
                            <select id="sensor_type_id" name="sensor_type_id" class="form-select flex-1 @error('sensor_type_id') error @enderror" required>
                                <option value="">-- Select Sensor Type --</option>
                                @foreach ($sensorTypes as $type)
                                    <option value="{{ $type->id }}" {{ old('sensor_type_id') == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }} ({{ $type->unit }})
                                    </option>
                                @endforeach
                            </select>
                            <a href="{{ route('sensor-types.create') }}" class="btn btn-secondary btn-sm whitespace-nowrap" title="Create new sensor type">
                                <i class="fa-solid fa-plus"></i>
                                New Type
                            </a>
                        </div>
                        @error('sensor_type_id')
                            <span class="form-error block mt-2">{{ $message }}</span>
                        @enderror
                        <p class="form-help mt-2">Select existing type or create new</p>
                    </div>

                    <div class="form-group">
                        <label for="sensor_code" class="form-label">Sensor Code <span class="text-red-500">*</span></label>
                        <input type="text" id="sensor_code" name="sensor_code" class="form-input @error('sensor_code') error @enderror"
                            placeholder="e.g., TEMP_ROOM_01, HUM_SHED_02" value="{{ old('sensor_code') }}" required>
                        @error('sensor_code')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                        <p class="form-help">Unique identifier for this sensor</p>
                    </div>

                    <div class="form-group">
                        <label for="sensor_name" class="form-label">Sensor Name <span class="text-red-500">*</span></label>
                        <input type="text" id="sensor_name" name="sensor_name" class="form-input @error('sensor_name') error @enderror"
                            placeholder="e.g., Main Room Temperature, Outside Humidity" value="{{ old('sensor_name') }}" required>
                        @error('sensor_name')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                        <p class="form-help">Descriptive name for this sensor</p>
                    </div>

                    <div class="form-group">
                        <label for="location" class="form-label">Location <span class="text-red-500">*</span></label>
                        <input type="text" id="location" name="location" class="form-input @error('location') error @enderror"
                            placeholder="e.g., Main Greenhouse, Storage Room A" value="{{ old('location') }}" required>
                        @error('location')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                        <p class="form-help">Physical location of the sensor</p>
                    </div>

                    <div class="form-group">
                        <label for="description" class="form-label">Description (Optional)</label>
                        <textarea id="description" name="description" class="form-textarea"
                            placeholder="Add notes about this sensor (purpose, specifications, etc.)..." rows="3">{{ old('description') }}</textarea>
                        @error('description')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="status" class="form-label">Status <span class="text-red-500">*</span></label>
                        <select id="status" name="status" class="form-select @error('status') error @enderror" required>
                            <option value="">-- Select Status --</option>
                            <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="maintenance" {{ old('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                        </select>
                        @error('status')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <a href="{{ route('sensors.index') }}" class="btn btn-secondary">
                        <i class="fa-solid fa-arrow-left"></i>
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-check"></i>
                        Create Sensor
                    </button>
                </div>
            </form>
        </div>
    </div>

</x-app-layout>
