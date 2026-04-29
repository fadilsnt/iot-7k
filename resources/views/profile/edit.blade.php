<x-app-layout>
    @push('head')
        @vite(['resources/css/dashboard.css'])
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <style>
            .profile-container {
                max-width: 1200px;
                margin: 0 auto;
                padding: 2rem 1rem;
            }

            .profile-card {
                background: white;
                border-radius: 16px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
                overflow: hidden;
                margin-bottom: 2rem;
            }

            .profile-header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                padding: 2rem;
                color: white;
                text-align: center;
                position: relative;
            }

            .profile-photo-container {
                position: relative;
                width: 150px;
                height: 150px;
                margin: 0 auto 1rem;
            }

            .profile-photo {
                width: 150px;
                height: 150px;
                border-radius: 50%;
                object-fit: cover;
                border: 5px solid white;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
                background: white;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 4rem;
                color: #667eea;
            }

            .profile-photo-overlay {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.6);
                border-radius: 50%;
                display: none;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                color: white;
                font-size: 1.5rem;
            }

            .profile-photo-container:hover .profile-photo-overlay {
                display: flex;
            }

            .profile-name {
                font-size: 1.75rem;
                font-weight: 700;
                margin-bottom: 0.5rem;
            }

            .profile-position {
                font-size: 1.125rem;
                opacity: 0.95;
                font-weight: 400;
            }

            .profile-body {
                padding: 2rem;
            }

            .section-title {
                font-size: 1.25rem;
                font-weight: 600;
                color: #1e293b;
                margin-bottom: 1.5rem;
                display: flex;
                align-items: center;
                gap: 0.5rem;
                border-bottom: 2px solid #e2e8f0;
                padding-bottom: 0.75rem;
            }

            .section-title i {
                color: #667eea;
            }

            .form-group {
                margin-bottom: 1.5rem;
            }

            .form-label {
                display: block;
                font-size: 0.875rem;
                font-weight: 600;
                color: #475569;
                margin-bottom: 0.5rem;
            }

            .form-input {
                width: 100%;
                padding: 0.75rem 1rem;
                border: 2px solid #e2e8f0;
                border-radius: 8px;
                font-size: 0.875rem;
                transition: all 0.2s ease;
                font-family: 'Poppins', sans-serif;
            }

            .form-input:focus {
                outline: none;
                border-color: #667eea;
                box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            }

            .form-textarea {
                min-height: 100px;
                resize: vertical;
            }

            .form-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 1.5rem;
            }

            .btn-primary {
                padding: 0.75rem 1.5rem;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border: none;
                border-radius: 8px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s ease;
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
            }

            .btn-primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
            }

            .btn-danger {
                padding: 0.75rem 1.5rem;
                background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
                color: white;
                border: none;
                border-radius: 8px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s ease;
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
            }

            .btn-danger:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 16px rgba(239, 68, 68, 0.4);
            }

            .alert {
                padding: 1rem 1.5rem;
                border-radius: 8px;
                margin-bottom: 1.5rem;
                display: flex;
                align-items: center;
                gap: 0.75rem;
            }

            .alert-success {
                background: #d1fae5;
                color: #065f46;
                border: 1px solid #10b981;
            }

            .alert-error {
                background: #fee2e2;
                color: #991b1b;
                border: 1px solid #ef4444;
            }

            .file-input-wrapper {
                position: relative;
                overflow: hidden;
                display: inline-block;
            }

            .file-input-wrapper input[type=file] {
                position: absolute;
                left: -9999px;
            }

            .file-input-label {
                padding: 0.75rem 1.5rem;
                background: #f1f5f9;
                color: #475569;
                border: 2px dashed #cbd5e1;
                border-radius: 8px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s ease;
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
            }

            .file-input-label:hover {
                background: #e2e8f0;
                border-color: #667eea;
                color: #667eea;
            }

            .file-name {
                margin-top: 0.5rem;
                font-size: 0.875rem;
                color: #64748b;
            }

            @media (max-width: 768px) {
                .form-grid {
                    grid-template-columns: 1fr;
                }

                .profile-container {
                    padding: 1rem 0.5rem;
                }
            }
        </style>
    @endpush

    <div class="profile-container">
        <!-- Success Messages -->
        @if (session('status') === 'profile-updated')
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span>Profile berhasil diupdate!</span>
            </div>
        @endif

        <!-- Profile Card -->
        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-photo-container">
                    @if($user->profile_photo)
                        <img src="{{ asset('storage/' . $user->profile_photo) }}" alt="Profile Photo" class="profile-photo">
                    @else
                        <div class="profile-photo">
                            <i class="fas fa-user"></i>
                        </div>
                    @endif
                    <div class="profile-photo-overlay" onclick="document.getElementById('profile_photo').click()">
                        <i class="fas fa-camera"></i>
                    </div>
                </div>
                <div class="profile-name">{{ $user->name }}</div>
                <div class="profile-position">{{ $user->position ?? 'Administrator' }}</div>
            </div>

            <div class="profile-body">
                <!-- Update Profile Information -->
                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')

                    <div class="section-title">
                        <i class="fas fa-user-circle"></i>
                        <span>Informasi Profile</span>
                    </div>

                    <!-- Photo Upload (Hidden) -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-camera"></i> Foto Profile
                        </label>
                        <div class="file-input-wrapper">
                            <input type="file" id="profile_photo" name="profile_photo" accept="image/*" onchange="updateFileName(this)">
                            <label for="profile_photo" class="file-input-label">
                                <i class="fas fa-upload"></i>
                                Pilih Foto (Max 2MB)
                            </label>
                        </div>
                        <div class="file-name" id="file-name"></div>
                        @error('profile_photo')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="name" class="form-label">
                                <i class="fas fa-user"></i> Nama Lengkap
                            </label>
                            <input type="text" id="name" name="name" class="form-input" value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope"></i> Email
                            </label>
                            <input type="email" id="email" name="email" class="form-input" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="position" class="form-label">
                                <i class="fas fa-briefcase"></i> Posisi / Jabatan
                            </label>
                            <input type="text" id="position" name="position" class="form-input" value="{{ old('position', $user->position) }}" placeholder="e.g. System Administrator">
                        </div>

                        <div class="form-group">
                            <label for="phone" class="form-label">
                                <i class="fas fa-phone"></i> No. Telepon
                            </label>
                            <input type="text" id="phone" name="phone" class="form-input" value="{{ old('phone', $user->phone) }}" placeholder="e.g. 08123456789">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="address" class="form-label">
                            <i class="fas fa-map-marker-alt"></i> Alamat
                        </label>
                        <textarea id="address" name="address" class="form-input form-textarea" placeholder="Masukkan alamat lengkap">{{ old('address', $user->address) }}</textarea>
                    </div>

                    <div class="form-group">
                        <label for="bio" class="form-label">
                            <i class="fas fa-pen"></i> Bio / Deskripsi
                        </label>
                        <textarea id="bio" name="bio" class="form-input form-textarea" placeholder="Ceritakan sedikit tentang diri Anda (max 500 karakter)">{{ old('bio', $user->bio) }}</textarea>
                    </div>

                    <div style="text-align: right;">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-save"></i>
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Change Password Card -->
        <div class="profile-card">
            <div class="profile-body">
                <form method="POST" action="{{ route('profile.password.update') }}">
                    @csrf
                    @method('PATCH')

                    <div class="section-title">
                        <i class="fas fa-lock"></i>
                        <span>Ubah Password</span>
                    </div>

                    <div class="form-group">
                        <label for="current_password" class="form-label">
                            <i class="fas fa-key"></i> Password Saat Ini
                        </label>
                        <input type="password" id="current_password" name="current_password" class="form-input" required>
                        @error('current_password')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock"></i> Password Baru
                            </label>
                            <input type="password" id="password" name="password" class="form-input" required>
                            @error('password')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation" class="form-label">
                                <i class="fas fa-lock"></i> Konfirmasi Password Baru
                            </label>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-input" required>
                        </div>
                    </div>

                    <div style="text-align: right;">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-shield-alt"></i>
                            Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Account Card -->
        <div class="profile-card">
            <div class="profile-body">
                <div class="section-title">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Danger Zone</span>
                </div>

                <p style="color: #64748b; margin-bottom: 1rem;">
                    Setelah akun Anda dihapus, semua data akan dihapus secara permanen. Sebelum menghapus akun, pastikan untuk mengunduh data yang ingin Anda simpan.
                </p>

                <button type="button" class="btn-danger" onclick="confirmDelete()">
                    <i class="fas fa-trash-alt"></i>
                    Hapus Akun
                </button>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal-overlay" id="deleteModal">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h3>
                    <i class="fas fa-exclamation-triangle" style="color: #ef4444;"></i>
                    Konfirmasi Hapus Akun
                </h3>
                <button type="button" class="modal-close" onclick="closeDeleteModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <p style="margin-bottom: 1rem; color: #475569;">
                    Apakah Anda yakin ingin menghapus akun? Semua data akan dihapus secara permanen dan tidak dapat dikembalikan.
                </p>
                <form method="POST" action="{{ route('profile.destroy') }}" id="deleteForm">
                    @csrf
                    @method('DELETE')

                    <div class="form-group">
                        <label for="delete_password" class="form-label">
                            Password untuk Konfirmasi
                        </label>
                        <input type="password" id="delete_password" name="password" class="form-input" required>
                        @error('password', 'userDeletion')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn-cancel" onclick="closeDeleteModal()">
                            <i class="fas fa-times"></i>
                            Batal
                        </button>
                        <button type="submit" class="btn-danger">
                            <i class="fas fa-trash-alt"></i>
                            Hapus Akun Saya
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function updateFileName(input) {
                const fileNameDisplay = document.getElementById('file-name');
                if (input.files && input.files[0]) {
                    fileNameDisplay.textContent = '📷 ' + input.files[0].name;
                } else {
                    fileNameDisplay.textContent = '';
                }
            }

            function confirmDelete() {
                document.getElementById('deleteModal').classList.add('active');
            }

            function closeDeleteModal() {
                document.getElementById('deleteModal').classList.remove('active');
            }

            // Close modal when clicking outside
            document.getElementById('deleteModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeDeleteModal();
                }
            });

            // Auto-hide success messages after 5 seconds
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert-success');
                alerts.forEach(function(alert) {
                    alert.style.transition = 'opacity 0.5s ease';
                    alert.style.opacity = '0';
                    setTimeout(function() {
                        alert.remove();
                    }, 500);
                });
            }, 5000);

            // SweetAlert for password updated
            @if (session('status') === 'password-updated')
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Password berhasil diubah!',
                    confirmButtonColor: '#667eea',
                    confirmButtonText: 'OK'
                });
            @endif

            // SweetAlert for validation errors on password
            @if ($errors->has('current_password') || $errors->has('password'))
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: '{{ $errors->first('current_password') ?: $errors->first('password') }}',
                    confirmButtonColor: '#ef4444',
                    confirmButtonText: 'OK'
                });
            @endif
        </script>
    @endpush
</x-app-layout>
