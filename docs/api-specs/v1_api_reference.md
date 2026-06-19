# API Reference - iSURF Project

Dokumen ini merinci endpoint API yang tersedia pada **Main REST API** (FastAPI). Base URL untuk lingkungan lokal adalah `http://localhost:8000/api`.

## 1. Authentication
Endpoint untuk menangani sesi pengguna dan otentikasi.

### 1.1 Login
- **URL:** `/auth/login`
- **Method:** `POST`
- **Content-Type:** `application/x-www-form-urlencoded`
- **Request Body:**
    - `username` (string)
    - `password` (string)
- **Response (200 OK):**
    ```json
    {
      "access_token": "jwt_token_here",
      "token_type": "bearer",
      "role": "admin"
    }
    ```

### 1.2 Get My Profile
- **URL:** `/auth/me`
- **Method:** `GET`
- **Auth Required:** `Bearer Token`
- **Response (200 OK):**
    ```json
    {
      "username": "johndoe",
      "role": "admin"
    }
    ```

---

## 2. Devices & Sensors
Manajemen perangkat IoT dan konfigurasi sensor.

### 2.1 List Devices
- **URL:** `/devices/`
- **Method:** `GET`
- **Query Params:** `skip` (default 0), `limit` (default 100)
- **Response:** Array of `Device` objects.

### 2.2 Create Device
- **URL:** `/devices/`
- **Method:** `POST`
- **Request Body:**
    ```json
    {
      "device_code": "NODE-001",
      "name": "Greenhouse A",
      "type": "hydroponic",
      "location": "Jakarta"
    }
    ```

### 2.3 Update Sensor Thresholds
- **URL:** `/devices/{device_id}/sensors/{sensor_id}/thresholds`
- **Method:** `PUT`
- **Request Body:**
    ```json
    {
      "min_threshold": 5.5,
      "max_threshold": 8.0
    }
    ```

---

## 3. IoT Gateway (Direct Ingestion)
Endpoint khusus yang digunakan oleh perangkat ESP32 untuk mengirimkan data sensor.

### 3.1 Data Ingestion
- **URL:** `/iot/ingest`
- **Method:** `POST`
- **Request Body:**
    ```json
    {
      "api_key": "supersecure",
      "device_code": "NODE-001",
      "sensor_name": "pH",
      "value": 6.8
    }
    ```

### 3.2 Heartbeat
- **URL:** `/iot/heartbeat`
- **Method:** `POST`
- **Request Body:**
    ```json
    {
      "api_key": "supersecure",
      "device_code": "NODE-001",
      "firmware_version": "1.0.2"
    }
    ```

### 3.3 Get Device Config
- **URL:** `/iot/config`
- **Method:** `GET`
- **Query Params:** `device_code`, `api_key`

---

## 4. Error Handling
API menggunakan kode status HTTP standar:
- `200 OK`: Request berhasil.
- `400 Bad Request`: Parameter input tidak valid.
- `401 Unauthorized`: Token hilang atau tidak valid.
- `403 Forbidden`: API Key salah atau role tidak diizinkan.
- `404 Not Found`: Resource tidak ditemukan.
- `500 Internal Server Error`: Kesalahan pada server.
