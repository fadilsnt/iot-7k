# Dokumentasi API - IoT TKHS

Dokumentasi lengkap API endpoints untuk sistem monitoring IoT TKHS.

## 📋 Base URL

```
Development: http://localhost/iot-tkhs
Production: https://yourdomain.com
```

## 🔐 Authentication

Admin / User Account
email : admin@tujukuda.com
password : Zxcvbn78

API web menggunakan Laravel session authentication. User harus login melalui web interface.

API Arduino **tidak memerlukan authentication** untuk kemudahan integrasi dengan perangkat IoT.

## 📡 Arduino API Endpoints

### 1. Store Sensor Reading

Endpoint untuk Arduino/ESP32 mengirim data sensor.

**Endpoint:**
```http
GET /api/sensors/{sensor_code}?value={value}
```

**Method:** `GET`

**Parameters:**
- `sensor_code` (path): Kode sensor (TEMP_01, MOIST_01, TEMP_02)
- `value` (query): Nilai sensor (float/integer)

**Example Request:**
```bash
# Temperature sensor
curl "http://localhost/iot-tkhs/api/sensors/TEMP_01?value=28.5"

# Moisture sensor
curl "http://localhost/iot-tkhs/api/sensors/MOIST_01?value=65.3"
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Reading stored successfully",
  "data": {
    "sensor_code": "TEMP_01",
    "value": 28.5,
    "condition": 1,
    "timestamp": "2025-11-13 20:30:45"
  }
}
```

**Error Response (404 Not Found):**
```json
{
  "success": false,
  "message": "Sensor not found: TEMP_01"
}
```

**Error Response (422 Unprocessable Entity):**
```json
{
  "success": false,
  "message": "Value is required"
}
```


## 🌐 Web API Endpoints

### 2. Get Sensor Condition

Get kondisi terkini dari sensor (Normal/Warning/Danger).

**Endpoint:**
```http
GET /api/sensors/{sensor_code}/condition
```

**Method:** `GET`

**Authentication:** Required (Session)

**Parameters:**
- `sensor_code` (path): Kode sensor

**Example Request:**
```bash
curl -X GET "http://localhost/iot-tkhs/api/sensors/TEMP_01/condition" \
  -H "Cookie: laravel_session=YOUR_SESSION"
```

**Success Response (200 OK):**
```json
2
```

**Response Values:**
- `1`: Normal
- `2`: Warning (Waspada)
- `3`: Danger (Bahaya)

**Error Response (404 Not Found):**
```json
{
  "error": "Sensor not found"
}
```

---

### 3. Store Notification Log

Save notifikasi ke database untuk activity log.

**Endpoint:**
```http
POST /api/notification-logs
```

**Method:** `POST`

**Authentication:** Not Required

**Request Body (JSON):**
```json
{
  "sensor_code": "TEMP_01",
  "sensor_name": "Sensor Suhu 1",
  "sensor_type": "temp",
  "condition": 3,
  "sensor_value": 35.2,
  "message": "Sensor Sensor Suhu 1 dalam kondisi Bahaya"
}
```

**Fields:**
- `sensor_code` (required, string): Kode sensor
- `sensor_name` (required, string): Nama sensor
- `sensor_type` (required, string): Tipe sensor (temp/moist)
- `condition` (required, integer): Kondisi (1=Normal, 2=Warning, 3=Danger)
- `sensor_value` (optional, numeric): Nilai sensor
- `message` (optional, string): Pesan notifikasi

**Example Request:**
```bash
curl -X POST "http://localhost/iot-tkhs/api/notification-logs" \
  -H "Content-Type: application/json" \
  -d '{
    "sensor_code": "TEMP_01",
    "sensor_name": "Sensor Suhu 1",
    "sensor_type": "temp",
    "condition": 3,
    "sensor_value": 35.2,
    "message": "Sensor dalam kondisi Bahaya"
  }'
```

**Success Response (201 Created):**
```json
{
  "success": true,
  "log": {
    "id": 123,
    "sensor_id": 1,
    "sensor_code": "TEMP_01",
    "sensor_name": "Sensor Suhu 1",
    "sensor_type": "temp",
    "condition": 3,
    "sensor_value": 35.2,
    "message": "Sensor dalam kondisi Bahaya",
    "created_at": "2025-11-13T20:30:45.000000Z",
    "updated_at": "2025-11-13T20:30:45.000000Z"
  }
}
```

**Error Response (404 Not Found):**
```json
{
  "error": "Sensor not found"
}
```

**Validation Error (422 Unprocessable Entity):**
```json
{
  "message": "The sensor code field is required.",
  "errors": {
    "sensor_code": ["The sensor code field is required."],
    "condition": ["The condition field must be between 1 and 3."]
  }
}
```

---

### 4. Get Dashboard Data

Ambil data sensor untuk grafik dashboard dengan filtering.

**Endpoint:**
```http
GET /dashboard/data
```

**Method:** `GET`

**Authentication:** Required (Session)

**Query Parameters:**
- `sensor` (required, string): Kode sensor (TEMP_01, TEMP_02, MOIST_01)
- `range` (required, string): Periode waktu (5m, 15m, 30m, 1h, 8h, 24h)
- `custom_start` (optional, datetime): Start date untuk custom range
- `custom_end` (optional, datetime): End date untuk custom range

**Example Request:**
```bash
# Quick filter
curl -X GET "http://localhost/iot-tkhs/dashboard/data?sensor=TEMP_01&range=1h" \
  -H "Cookie: laravel_session=YOUR_SESSION"

# Custom range
curl -X GET "http://localhost/iot-tkhs/dashboard/data?sensor=TEMP_01&range=custom&custom_start=2025-11-13%2010:00:00&custom_end=2025-11-13%2020:00:00" \
  -H "Cookie: laravel_session=YOUR_SESSION"
```

**Success Response (200 OK):**
```json
{
  "labels": [
    "19:25",
    "19:26",
    "19:27",
    "19:28",
    "19:29",
    "19:30"
  ],
  "values": [
    27.5,
    27.8,
    28.1,
    28.0,
    27.9,
    28.2
  ],
  "latest": {
    "value": 28.2,
    "timestamp": "2025-11-13 19:30:45",
    "condition": 1,
    "condition_name": "Normal"
  },
  "stats": {
    "min": 27.5,
    "max": 28.2,
    "avg": 27.95,
    "count": 6
  }
}
```

**Error Response (400 Bad Request):**
```json
{
  "error": "Sensor parameter is required"
}
```

---

### 5. Get Table Data

Ambil data sensor dalam format tabel dengan lazy loading dan filtering.

**Endpoint:**
```http
GET /dashboard/table-data
```

**Method:** `GET`

**Authentication:** Required (Session)

**Query Parameters:**
- `sensor` (required, string): Kode sensor
- `date_from` (optional, date): Filter dari tanggal (Y-m-d)
- `date_to` (optional, date): Filter sampai tanggal (Y-m-d)
- `offset` (optional, integer): Offset untuk lazy loading (default: 0)
- `limit` (optional, integer): Jumlah data per request (default: 50)

**Example Request:**
```bash
curl -X GET "http://localhost/iot-tkhs/dashboard/table-data?sensor=TEMP_01&offset=0&limit=50" \
  -H "Cookie: laravel_session=YOUR_SESSION"
```

**Success Response (200 OK):**
```json
{
  "data": [
    {
      "id": 150,
      "value": 28.2,
      "timestamp": "2025-11-13 19:30:45",
      "formatted_time": "13 Nov 2025, 19:30",
      "condition": 1,
      "condition_name": "Normal"
    },
    {
      "id": 149,
      "value": 27.9,
      "timestamp": "2025-11-13 19:29:45",
      "formatted_time": "13 Nov 2025, 19:29",
      "condition": 1,
      "condition_name": "Normal"
    }
  ],
  "total": 1500,
  "has_more": true,
  "next_offset": 50
}
```

---

### 6. Download Excel

Download data sensor dalam format Excel dengan filtering.

**Endpoint:**
```http
GET /dashboard/download
```

**Method:** `GET`

**Authentication:** Required (Session)

**Query Parameters:**
- `sensor` (required, string): Kode sensor
- `date_from` (optional, date): Filter dari tanggal
- `date_to` (optional, date): Filter sampai tanggal

**Example Request:**
```bash
curl -X GET "http://localhost/iot-tkhs/dashboard/download?sensor=TEMP_01&date_from=2025-11-01&date_to=2025-11-13" \
  -H "Cookie: laravel_session=YOUR_SESSION" \
  --output sensor_data.xlsx
```

**Response:**
- File Excel (XLSX) dengan nama: `sensor_TEMP_01_20251113_193045.xlsx`

**Excel Content:**
| ID | Sensor Code | Value | Timestamp | Condition |
|----|-------------|-------|-----------|-----------|
| 150 | TEMP_01 | 28.2 | 2025-11-13 19:30:45 | Normal |
| 149 | TEMP_01 | 27.9 | 2025-11-13 19:29:45 | Normal |

---

## 📊 Response Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request - Invalid parameters |
| 401 | Unauthorized - Not authenticated |
| 404 | Not Found - Resource not found |
| 422 | Unprocessable Entity - Validation error |
| 500 | Internal Server Error |

---

## 🔧 Rate Limiting

API Arduino tidak memiliki rate limiting untuk memastikan data sensor dapat dikirim secara continuous.

Web API menggunakan rate limiting default Laravel:
- 60 requests per minute untuk authenticated users
- 10 requests per minute untuk guest users

---

## 📝 Best Practices

### Arduino/IoT Device

1. **Error Handling**: Selalu handle error response
2. **Retry Logic**: Implement retry dengan exponential backoff
3. **Timeout**: Set HTTP timeout (5-10 detik)
4. **Batch Sending**: Jangan kirim terlalu sering (minimal 5 detik interval)
5. **Buffer Data**: Simpan data offline jika koneksi gagal

### Web Application

1. **Authentication**: Always include session cookie
2. **Caching**: Cache data yang sering diakses
3. **Pagination**: Gunakan offset/limit untuk data besar
4. **Error Handling**: Handle semua HTTP error codes
5. **Timeout**: Set request timeout yang appropriate

---

## 🐛 Common Errors

### Arduino API

**Error: "Sensor not found"**
- Pastikan sensor sudah dibuat di Sensors Management
- Cek spelling kode sensor (case sensitive)
- Verifikasi sensor status = Active

**Error: "Value is required"**
- Tambahkan parameter `?value=XX` di URL
- Pastikan value adalah number (float/integer)

### Web API

**Error: "Unauthenticated"**
- Login dulu melalui web interface
- Include session cookie dalam request
- Cek session expiration

**Error: "Too Many Requests"**
- Reduce request frequency
- Implement caching
- Contact admin untuk increase limit

---

## 📞 Support

Jika ada pertanyaan atau issue:
- Email: info@tkhs.co.id
- GitHub Issues: https://github.com/your-repo/iot-tkhs/issues

---

**© 2025 PT. Tuju Kuda Hitam Sakti**
