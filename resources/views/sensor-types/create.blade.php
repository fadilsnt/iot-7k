<x-app-layout>
    <header class="dashboard-header">
        <span class="text-xs uppercase tracking-wide text-white/80 flex items-center gap-2">
            <i class="fa-solid fa-layer-group"></i>
            Add Sensor Type
        </span>
        <h1>Create New Sensor Type</h1>
    </header>

    <div class="content-container">
        <div class="form-container max-w-2xl">
            <form action="{{ route('sensor-types.store') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Sensor Type Information -->
                <div class="form-section">
                    <h3 class="form-section-title">Sensor Type Details</h3>

                    <div class="form-group">
                        <label for="name" class="form-label">Type Name <span class="text-red-500">*</span></label>
                        <input type="text" id="name" name="name" class="form-input @error('name') error @enderror"
                            placeholder="e.g., Temperature, Humidity, Pressure" value="{{ old('name') }}" required>
                        @error('name')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                        <p class="form-help">Unique name for this sensor type</p>
                    </div>

                    <div class="form-group">
                        <label for="slug" class="form-label">Slug <span class="text-red-500">*</span></label>
                        <input type="text" id="slug" name="slug" class="form-input @error('slug') error @enderror"
                            placeholder="e.g., temperature" value="{{ old('slug') }}" required readonly style="background-color: #f3f4f6;">
                        @error('slug')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                        <p class="form-help">Auto-generated from type name (lowercase, underscores)</p>
                    </div>

                    <div class="form-group">
                        <label for="unit" class="form-label">Unit <span class="text-red-500">*</span></label>
                        <input type="text" id="unit" name="unit" class="form-input @error('unit') error @enderror"
                            placeholder="e.g., °C, %, mmHg, ppm, V, A" value="{{ old('unit') }}" required>
                        @error('unit')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                        <p class="form-help">Measurement unit symbol</p>
                    </div>

                    <div class="form-group">
                        <label for="description" class="form-label">Description (Optional)</label>
                        <textarea id="description" name="description" class="form-textarea"
                            placeholder="Add notes about this sensor type..." rows="3">{{ old('description') }}</textarea>
                        @error('description')
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
                        Create Sensor Type
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const nameInput = document.getElementById('name');
            const slugInput = document.getElementById('slug');

            // Auto-generate slug from name
            nameInput.addEventListener('input', (e) => {
                const slug = e.target.value
                    .toLowerCase()
                    .replace(/[^a-z0-9]+/g, '_')
                    .replace(/^_|_$/g, '');
                slugInput.value = slug;
            });
        });
    </script>
    @endpush

</x-app-layout>
