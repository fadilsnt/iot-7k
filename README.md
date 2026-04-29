# IoT TKHS - Temperature & Moisture Monitoring System

![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat&logo=php)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=flat&logo=mysql)
![License](https://img.shields.io/badge/License-MIT-green.svg)

Sistem monitoring IoT untuk PT. Tuju Kuda Hitam Sakti yang memantau suhu dan kelembaban secara real-time dengan notifikasi otomatis untuk kondisi warning dan danger.

## 📋 Daftar Isi

- [Fitur Utama](#-fitur-utama)
- [Teknologi](#-teknologi)
- [Persyaratan Sistem](#-persyaratan-sistem)
- [Instalasi](#-instalasi)
- [Konfigurasi](#-konfigurasi)
- [Penggunaan](#-penggunaan)
- [Struktur Database](#-struktur-database)
- [API Endpoints](#-api-endpoints)
- [Troubleshooting](#-troubleshooting)
- [Kontribusi](#-kontribusi)
- [Lisensi](#-lisensi)

## ✨ Fitur Utama

### 📊 Dashboard Real-time
- **Monitoring Langsung**: Tampilan gauge untuk suhu dan kelembaban dengan status real-time
- **Grafik Interaktif**: Chart.js untuk visualisasi trend data dengan multiple sensor
- **Filter Dinamis**: Filter berdasarkan periode waktu (5m, 15m, 30m, 1h, 8h, 24h, custom range)
- **Multiple Sensors**: Support untuk Sensor Suhu 1, Sensor Suhu 2, dan Sensor Kadar Air
- **Auto-refresh**: Data diperbarui otomatis menggunakan AJAX

### 📈 Data Logs
- **Tabel Data**: Tampilan data sensor dalam bentuk tabel dengan pagination
- **Lazy Loading**: Load data secara bertahap untuk performa optimal
- **Export Excel**: Download data dalam format XLSX dengan filtering
- **Filter Lanjutan**: Filter berdasarkan sensor, tanggal, dan rentang waktu

### 🔔 Sistem Notifikasi
- **Real-time Alerts**: Notifikasi bell icon dengan badge counter
- **Kondisi Sensor**: Auto-detect kondisi Normal, Waspada, dan Bahaya
- **Threshold Settings**: Pengaturan batas nilai untuk warning dan danger
- **Panel Notifikasi**: Dropdown panel dengan list notifikasi yang cantik
- **Mark as Read**: Tandai notifikasi sudah dibaca dengan satu klik
- **Check Interval**: Update setiap 5 detik dengan parallel API calls

### 📋 Activity Log
- **Permanent Record**: Semua notifikasi tersimpan permanen di database
- **Filter Lengkap**: Filter berdasarkan kondisi, sensor, dan tanggal
- **Pagination**: Navigasi halaman dengan query parameter preserved
- **Statistics**: Card stats untuk total logs, bahaya, dan waspada
- **Timeline View**: Tampilan log dengan icon dan badge warna

### ⚙️ Sensors Management
- **CRUD Operations**: Tambah, edit, hapus sensor
- **Bulk Actions**: Delete multiple sensors sekaligus
- **Sensor Types**: Support temperature dan moisture sensors
- **Threshold Config**: Set nilai min/max untuk warning dan danger per sensor
- **Validation**: Form validation untuk data integrity

### 👤 Profile Management
- **Upload Foto Profile**: Upload dan ganti foto profile (Max 2MB)
- **Edit Informasi**: Nama, email, posisi/jabatan, telepon, alamat, bio
- **Change Password**: Ubah password dengan validasi current password
- **Delete Account**: Hapus akun dengan konfirmasi password (Danger Zone)
- **Profile Display**: Foto profile muncul di navigation bar (desktop & mobile)
- **Auto-features**: Auto-delete old photo, auto-hide success messages
- **Responsive Design**: Mobile-friendly dengan breakpoints

## 🛠 Teknologi

### Backend
- **Laravel 12.x** - PHP Framework
- **PHP 8.2+** - Programming Language
- **MySQL 8.0+** - Database

### Frontend
- **Blade Templates** - Laravel Templating Engine
- **Tailwind CSS** - Utility-first CSS Framework
- **Chart.js 4.4.0** - JavaScript Charting Library
- **jQuery 3.7.1** - JavaScript Library
- **Font Awesome 6.5.2** - Icon Library
- **Flatpickr** - Date/Time Picker

### Tools
- **Vite** - Frontend Build Tool
- **Composer** - PHP Dependency Manager
- **npm** - JavaScript Package Manager

## 💻 Persyaratan Sistem

- PHP >= 8.2
- Composer
- Node.js & npm
- MySQL 8.0+
- Apache/Nginx dengan mod_rewrite
- Extension PHP:
  - OpenSSL
  - PDO
  - Mbstring
  - Tokenizer
  - XML
  - Ctype
  - JSON
  - BCMath

## 📥 Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/your-repo/iot-tkhs.git
cd iot-tkhs
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install
```

### 3. Environment Setup

```bash
# Copy environment file
copy .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Database Configuration

Edit file `.env` dan sesuaikan konfigurasi database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=iot_tkhs
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Database Migration

```bash
# Buat database
mysql -u root -e "CREATE DATABASE iot_tkhs"

# Run migrations
php artisan migrate

# (Optional) Seed data
php artisan db:seed
```

### 6. Build Assets

```bash
# Development build
npm run dev

# Production build
npm run build
```

### 7. Storage Link

```bash
php artisan storage:link
```

### 8. File Permissions

```bash
# Windows (PowerShell)
icacls storage /grant "IIS_IUSRS:(OI)(CI)F" /T
icacls bootstrap\cache /grant "IIS_IUSRS:(OI)(CI)F" /T

# Linux/Mac
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### 9. Start Server

```bash
# Development server
php artisan serve

# Atau akses via XAMPP
# http://localhost/iot-tkhs/public
```

## ⚙️ Konfigurasi

### Sensor Setup

1. Login ke aplikasi
2. Buka menu **Sensors**
3. Klik **Add New Sensor**
4. Isi form:
   - Sensor Code: `TEMP_01`, `MOIST_01`, dll
   - Sensor Name: Nama sensor
   - Sensor Type: Temperature/Moisture
   - Status: Active/Inactive
5. Klik **Save**

### Threshold Settings

1. Buka menu **Sensors**
2. Klik **Edit** pada sensor
3. Set nilai threshold:
   - **Warning Min/Max**: Batas nilai untuk kondisi waspada
   - **Danger Min/Max**: Batas nilai untuk kondisi bahaya
4. Save changes

### Notification Configuration

Notifikasi otomatis aktif untuk sensor dengan kondisi:
- **Waspada (Warning)**: Nilai sensor di luar batas warning
- **Bahaya (Danger)**: Nilai sensor di luar batas danger

## 📖 Penggunaan

### Dashboard

1. **View Real-time Data**
   - Gauge menampilkan nilai terkini
   - Status: Normal (hijau), Waspada (kuning), Bahaya (merah)

2. **Filter Data**
   - Pilih periode waktu dari dropdown
   - Klik tombol periode untuk quick filter
   - Gunakan custom range untuk rentang spesifik

3. **View Notifications**
   - Klik bell icon di header
   - Lihat list notifikasi aktif
   - Klik "Tandai Semua Sudah Dibaca" untuk clear

### Data Logs

1. **View Table Data**
   - Scroll tabel untuk lazy load data
   - Klik header untuk sort
   - Pilih rows per page

2. **Filter Data**
   - Pilih sensor dari dropdown
   - Set rentang tanggal
   - Klik "Terapkan"

3. **Export Data**
   - Klik tombol "Download"
   - Data terfilter akan di-export ke Excel

### Activity Log

1. **View History**
   - Semua notifikasi tersimpan permanen
   - Timeline view dengan icon kondisi

2. **Filter Logs**
   - Filter berdasarkan kondisi (Waspada/Bahaya)
   - Pilih sensor spesifik
   - Set rentang tanggal

3. **Pagination**
   - 20 logs per halaman
   - Filter tetap aktif saat navigasi

### Sensors Management

1. **Add Sensor**
   - Klik "Add New Sensor"
   - Isi form lengkap
   - Set threshold values

2. **Edit Sensor**
   - Klik icon edit
   - Update data
   - Save changes

3. **Delete Sensor**
   - Pilih sensor dengan checkbox
   - Klik "Delete Selected"
   - Confirm deletion

## 🗄️ Struktur Database

### Tabel: `users`
User authentication dan authorization

### Tabel: `devices`
Informasi perangkat IoT
- `id`, `device_code`, `device_name`, `location`, `status`, `last_seen`

### Tabel: `sensor_types`
Jenis sensor (temperature, moisture)
- `id`, `name`, `slug`, `unit`, `icon`, `color`

### Tabel: `sensors`
Konfigurasi sensor
- `id`, `device_id`, `sensor_type_id`, `sensor_code`, `sensor_name`
- `min_value`, `max_value`, `status`, `condition`
- `created_at`, `updated_at`

### Tabel: `sensor_readings`
Data pembacaan sensor
- `id`, `sensor_id`, `value`, `timestamp`, `created_at`

### Tabel: `threshold_settings`
Pengaturan threshold warning/danger
- `id`, `sensor_id`, `warning_min`, `warning_max`
- `danger_min`, `danger_max`, `created_at`, `updated_at`

### Tabel: `notification_logs`
Log notifikasi permanen
- `id`, `sensor_id`, `sensor_code`, `sensor_name`, `sensor_type`
- `condition`, `sensor_value`, `message`, `created_at`

## 🔌 API Endpoints

### Arduino API (No Auth)

#### Store Reading
```http
GET /iot-tkhs/api/sensors/{sensor_code}?value={value}
```
**Contoh:**
```
GET /iot-tkhs/api/sensors/TEMP_01?value=28.5
```

**Response:**
```json
{
  "success": true,
  "message": "Reading stored successfully",
  "data": {
    "sensor_code": "TEMP_01",
    "value": 28.5,
    "timestamp": "2025-11-13 20:30:00"
  }
}
```

### Web API (Authenticated)

#### Get Sensor Condition
```http
GET /iot-tkhs/api/sensors/{sensor_code}/condition
```

**Response:**
```json
2  // 1=Normal, 2=Warning, 3=Danger
```

#### Store Notification Log
```http
POST /iot-tkhs/api/notification-logs
Content-Type: application/json

{
  "sensor_code": "TEMP_01",
  "sensor_name": "Sensor Suhu 1",
  "sensor_type": "temp",
  "condition": 3,
  "sensor_value": 35.2,
  "message": "Sensor dalam kondisi Bahaya"
}
```

**Response:**
```json
{
  "success": true,
  "log": {
    "id": 1,
    "sensor_code": "TEMP_01",
    "condition": 3,
    "created_at": "2025-11-13T20:30:00.000000Z"
  }
}
```

#### Get Dashboard Data
```http
GET /iot-tkhs/dashboard/data?sensor={code}&range={period}
```

**Parameters:**
- `sensor`: TEMP_01, TEMP_02, MOIST_01
- `range`: 5m, 15m, 30m, 1h, 8h, 24h

**Response:**
```json
{
  "labels": ["20:25", "20:26", "20:27"],
  "values": [27.5, 27.8, 28.1],
  "latest": {
    "value": 28.1,
    "timestamp": "2025-11-13 20:27:00",
    "condition": 1
  }
}
```

## 🐛 Troubleshooting

### Error: "Base table or view not found"
**Solusi:**
```bash
php artisan migrate:fresh
```

### Error: "Class 'App\Models\...' not found"
**Solusi:**
```bash
composer dump-autoload
php artisan config:clear
php artisan cache:clear
```

### Assets tidak load setelah build
**Solusi:**
```bash
npm run build
php artisan view:clear
```

### Notifikasi tidak muncul
**Solusi:**
1. Cek threshold settings di sensor
2. Pastikan sensor status = Active
3. Cek browser console untuk error
4. Test API endpoint `/api/sensors/{code}/condition`

### Data tidak update real-time
**Solusi:**
1. Cek interval JavaScript (dashboard.blade.php)
2. Verify AJAX requests di Network tab
3. Pastikan server running
4. Clear browser cache

## 🤝 Kontribusi

Kontribusi sangat diterima! Silakan:

1. Fork repository
2. Buat branch baru (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buat Pull Request

## 📝 Changelog

### Version 1.0.0 (November 2025)
- ✅ Initial release
- ✅ Dashboard dengan real-time monitoring
- ✅ Multiple sensor support
- ✅ Notification system
- ✅ Activity log permanent storage
- ✅ Excel export functionality
- ✅ Threshold configuration
- ✅ Sensors CRUD management

## 📄 Lisensi

Project ini dilisensikan di bawah [MIT License](LICENSE).

## 👥 Tim Pengembang

**PT. Tuju Kuda Hitam Sakti**
- Email: info@tkhs.co.id
- Website: https://tkhs.co.id

## 🙏 Acknowledgments

- Laravel Framework
- Chart.js
- Tailwind CSS
- Font Awesome
- jQuery Team

---

**© 2025 PT. Tuju Kuda Hitam Sakti. All rights reserved.**
