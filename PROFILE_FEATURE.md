# 👤 Fitur Profile Admin - IoT TKHS

## 📋 Overview

Fitur Profile Admin memungkinkan pengguna untuk mengelola informasi akun mereka secara lengkap, termasuk upload foto profile, edit informasi pribadi, ubah password, dan hapus akun.

---

## ✨ Fitur Utama

### 1. **Upload & Edit Foto Profile**
- ✅ Upload foto profile (JPG, PNG, GIF)
- ✅ Maksimal ukuran file: 2MB
- ✅ Preview foto langsung di header profile
- ✅ Hover effect untuk ganti foto
- ✅ Foto tersimpan di `storage/app/public/profile_photos`
- ✅ Foto lama otomatis dihapus saat upload foto baru
- ✅ Foto muncul di navigation bar (desktop & mobile)

### 2. **Edit Informasi Profile**
- ✅ **Nama Lengkap** (Required)
- ✅ **Email** (Required, Unique)
- ✅ **Posisi/Jabatan** (Optional)
- ✅ **No. Telepon** (Optional)
- ✅ **Alamat** (Optional, Textarea)
- ✅ **Bio/Deskripsi** (Optional, Max 500 karakter)

### 3. **Change Password**
- ✅ Validasi password saat ini
- ✅ Password baru minimal 8 karakter
- ✅ Konfirmasi password baru
- ✅ Password ter-hash dengan bcrypt

### 4. **Delete Account (Danger Zone)**
- ✅ Modal konfirmasi sebelum hapus
- ✅ Validasi password untuk keamanan
- ✅ Hapus foto profile otomatis
- ✅ Logout otomatis setelah hapus akun
- ✅ Session invalidate untuk keamanan

---

## 🗄️ Database Schema

### Tabel: `users`

```sql
- id (Primary Key)
- name (String, Required)
- email (String, Required, Unique)
- email_verified_at (Timestamp, Nullable)
- password (String, Hashed)
- profile_photo (String, Nullable) -- Path ke foto
- position (String, Nullable) -- Jabatan
- phone (String, Nullable) -- No. Telepon
- address (Text, Nullable) -- Alamat
- bio (Text, Nullable) -- Bio/Deskripsi (Max 500)
- remember_token
- created_at (Timestamp)
- updated_at (Timestamp)
```

### Migration File
`database/migrations/2025_11_13_224603_add_profile_fields_to_users_table.php`

---

## 📁 File Structure

```
app/
├── Http/
│   └── Controllers/
│       └── ProfileController.php          # Controller untuk profile
├── Models/
│   └── User.php                           # Model User dengan fillable fields
database/
├── migrations/
│   └── 2025_11_13_224603_add_profile_fields_to_users_table.php
resources/
├── views/
│   ├── profile/
│   │   └── edit.blade.php                 # Halaman profile lengkap
│   └── layouts/
│       └── navigation.blade.php           # Navigation dengan foto profile
routes/
└── web.php                                 # Routes untuk profile
storage/
└── app/
    └── public/
        └── profile_photos/                 # Folder untuk foto profile
public/
└── storage -> ../storage/app/public       # Symbolic link
```

---

## 🛣️ Routes

```php
Route::middleware('auth')->group(function () {
    // Profile Pages
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');
    
    // Update Profile Information
    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');
    
    // Update Password
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])
        ->name('profile.password.update');
    
    // Delete Account
    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});
```

---

## 🎨 UI/UX Features

### Design Elements
- **Gradient Header**: Purple gradient (`#667eea` → `#764ba2`)
- **Circular Profile Photo**: 150px diameter dengan border putih
- **Hover Effect**: Overlay kamera saat hover foto
- **Form Grid**: 2 kolom di desktop, 1 kolom di mobile
- **Success Alerts**: Auto-hide setelah 5 detik
- **Modal Confirmation**: Untuk delete account
- **Responsive Design**: Mobile-friendly dengan breakpoints

### Colors
- **Primary**: Purple gradient (#667eea → #764ba2)
- **Success**: Green (#10b981)
- **Danger**: Red (#ef4444)
- **Text**: Gray scale (#1e293b, #475569, #64748b)

### Icons (Font Awesome)
- `fa-user-circle` - Profile section
- `fa-camera` - Photo upload
- `fa-lock` - Password section
- `fa-exclamation-triangle` - Danger zone
- `fa-check-circle` - Success messages

---

## 📝 Validation Rules

### Update Profile
```php
'name' => ['required', 'string', 'max:255']
'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,{user_id}']
'position' => ['nullable', 'string', 'max:255']
'phone' => ['nullable', 'string', 'max:20']
'address' => ['nullable', 'string']
'bio' => ['nullable', 'string', 'max:500']
'profile_photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048']
```

### Change Password
```php
'current_password' => ['required', 'current_password']
'password' => ['required', 'confirmed', 'min:8']
```

### Delete Account
```php
'password' => ['required', 'current_password']
```

---

## 🔐 Security Features

1. **Password Hashing**: Menggunakan `Hash::make()` dengan bcrypt
2. **CSRF Protection**: Semua form dilindungi `@csrf`
3. **Authentication Required**: Semua routes menggunakan `auth` middleware
4. **Password Confirmation**: Double validation untuk delete account
5. **Session Invalidate**: Session dihapus setelah delete account
6. **File Validation**: Upload foto divalidasi type dan size

---

## 🚀 Usage

### Akses Halaman Profile
1. Login ke aplikasi
2. Klik nama user di navigation bar (top right)
3. Pilih **"Profile"** dari dropdown

### Upload Foto Profile
1. Klik icon kamera pada foto profile (atau hover)
2. Pilih foto dari komputer (Max 2MB)
3. Foto akan preview dengan nama file
4. Klik **"Simpan Perubahan"**

### Edit Informasi
1. Isi form dengan informasi yang diinginkan
2. Klik **"Simpan Perubahan"**
3. Success message muncul jika berhasil

### Ubah Password
1. Masukkan password saat ini
2. Masukkan password baru (min 8 karakter)
3. Konfirmasi password baru
4. Klik **"Update Password"**

### Hapus Akun
1. Scroll ke **"Danger Zone"**
2. Klik **"Hapus Akun"**
3. Masukkan password untuk konfirmasi
4. Klik **"Hapus Akun Saya"**
5. Akun dan semua data akan dihapus permanen

---

## 📱 Responsive Behavior

### Desktop (> 768px)
- Form grid 2 kolom
- Full width cards
- Sidebar navigation dengan foto

### Mobile (≤ 768px)
- Form grid 1 kolom
- Stack layout
- Hamburger menu dengan foto profile

---

## 🔄 Auto Features

1. **Auto-delete Old Photo**: Saat upload foto baru, foto lama dihapus otomatis
2. **Auto-hide Success**: Success alerts hilang otomatis setelah 5 detik
3. **Auto Logout**: Logout otomatis setelah delete account
4. **Email Verification Reset**: Email verification di-reset jika email diubah

---

## 🐛 Error Handling

- ✅ Validation errors ditampilkan per field
- ✅ File upload errors (size, type)
- ✅ Password mismatch
- ✅ Current password incorrect
- ✅ Email already exists

---

## 📦 Dependencies

- Laravel 11.x
- Font Awesome 6.x (Icons)
- Vite (Asset bundling)
- Storage (File management)

---

## 🎯 Future Enhancements

- [ ] Crop foto sebelum upload
- [ ] Multiple foto (gallery)
- [ ] Social media links
- [ ] Two-factor authentication
- [ ] Activity log per user
- [ ] Export profile data
- [ ] Dark mode preference

---

## 📞 Support

Untuk pertanyaan atau masalah terkait fitur profile, hubungi:
- **Developer**: [Your Name]
- **Email**: [your-email@example.com]

---

## 📄 License

This project is part of IoT TKHS monitoring system.

---

**Last Updated**: November 13, 2025
**Version**: 1.0.0
