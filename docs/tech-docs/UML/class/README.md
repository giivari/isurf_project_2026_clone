# Class Diagram - iSURF Project

Dokumen ini merinci struktur kelas internal sistem, menampilkan relasi utuh antar entitas di tingkat basis data (SQLAlchemy Models) dan penjelasan untuk masing-masing kelas.

## 1. Domain Model (SQLAlchemy Entities)
Berikut adalah relasi antar model inti dalam sistem iSURF secara komprehensif:

```mermaid
classDiagram
    class User {
        +int id
        +string username
        +string email
        +string password_hash
        +string role
        +int status
        +string full_name
        +string avatar_url
        +datetime last_login_at
        +bool authenticate(string password)
        +void updateProfile(dict data)
        +void reviewRequest(int req_id, string action)
    }

    class Device {
        +int id
        +string device_code
        +string name
        +string type
        +string location
        +string status
        +datetime last_heartbeat
        +string firmware_version
        +void register()
        +void updateHeartbeat()
        +list~Sensor~ getSensors()
        +void triggerManualIrrigation()
    }

    class Sensor {
        +int id
        +int device_id
        +string name
        +string sensor_type
        +string unit
        +float min_threshold
        +float max_threshold
        +boolean is_active
        +void updateThresholds(float min, float max)
        +void recordReading(float val)
        +void checkAlert(float val)
    }

    class SensorReading {
        +bigint id
        +int sensor_id
        +int device_id
        +float value
        +datetime recorded_at
        +void save()
    }

    class Alert {
        +bigint id
        +int device_id
        +int sensor_id
        +string alert_type
        +string message
        +float value
        +float threshold_exceeded
        +boolean is_read
        +datetime resolved_at
        +void markAsRead()
        +void resolve()
    }

    class IrrigationSchedule {
        +int id
        +int device_id
        +string name
        +time start_time
        +int duration_minutes
        +string days_of_week
        +boolean is_active
        +void activate()
        +void deactivate()
        +bool checkIfDue()
    }

    class IrrigationLog {
        +bigint id
        +int schedule_id
        +int device_id
        +string trigger_type
        +datetime started_at
        +datetime ended_at
        +string status
        +float water_volume_liters
        +void start()
        +void complete(float volume)
        +void fail()
    }

    class DataRequest {
        +int id
        +string tracking_code
        +string full_name
        +string email
        +string nim_nip
        +string data_type
        +string status
        +int reviewed_by
        +void submit()
        +void approve(int admin_id)
        +void reject(int admin_id)
        +string generateDownloadToken()
    }

    %% Relationships
    Device "1" -- "*" Sensor : has
    Device "1" -- "*" SensorReading : records
    Sensor "1" -- "*" SensorReading : generates
    
    Device "1" -- "*" Alert : triggers
    Sensor "1" -- "0..1" Alert : associated_with
    
    Device "1" -- "*" IrrigationSchedule : has
    IrrigationSchedule "1" -- "*" IrrigationLog : executes
    Device "1" -- "*" IrrigationLog : logs
    
    User "1" -- "*" DataRequest : reviews
```

---

## 2. Penjelasan Class
Berikut adalah penjelasan fungsionalitas dari setiap kelas utama di atas:

### **`User`**
Merepresentasikan entitas pengguna dalam sistem, mencakup administrator maupun peneliti. Mengelola kredensial otentikasi (username, password hash) dan informasi profil dasar. Aktor dengan *role* admin berwenang meninjau permintaan data.

### **`Device`**
Merepresentasikan perangkat keras IoT (seperti Node ESP32) yang terpasang di lapangan. Kelas ini melacak identitas perangkat (`device_code`), lokasi, status online/offline (`last_heartbeat`), dan versi firmware. Merupakan entitas induk (parent) bagi sensor dan jadwal penyiraman.

### **`Sensor`**
Mendefinisikan modul sensor spesifik (misal: sensor pH, TDS, atau kelembapan tanah) yang terhubung ke sebuah `Device`. Kelas ini menyimpan nilai referensi seperti `min_threshold` dan `max_threshold` yang digunakan untuk memicu peringatan jika nilai pembacaan melebihi batas aman.

### **`SensorReading`**
Merupakan log data time-series (telemetry) hasil pembacaan dari `Sensor` fisik pada waktu tertentu. Sangat penting untuk fitur *monitoring* dan *analytics*. Terhubung langsung dengan `Sensor` dan `Device`.

### **`Alert`**
Entitas yang dibuat secara otomatis (atau manual) ketika anomali terdeteksi, misalnya ketika nilai `SensorReading` keluar dari batas `min_threshold` atau `max_threshold` sensor terkait. Berguna untuk memberikan peringatan dini kepada staf di lapangan.

### **`IrrigationSchedule`**
Mendefinisikan jadwal rutin otomatis untuk proses penyiraman di suatu `Device`. Mengatur jam mulai (`start_time`), durasi (`duration_minutes`), dan hari-hari aktif (`days_of_week`).

### **`IrrigationLog`**
Catatan riwayat penyiraman yang telah terjadi. Melacak apakah penyiraman dipicu secara manual, berdasarkan jadwal (`schedule_id`), atau dipicu otomatis oleh sensor. Mengabadikan waktu mulai, waktu selesai, dan status keberhasilan penyiraman.

### **`DataRequest`**
Menampung formulir permintaan dataset historis yang diajukan oleh pengguna/peneliti. Mengandung informasi pemohon dan status persetujuan yang direview oleh `User` (Admin).

---

## 3. API Data Transfer Objects (Pydantic Schemas)
Selain kelas-kelas basis data di atas, sistem menggunakan pola DTO (Data Transfer Object) via Pydantic untuk memisahkan representasi database dengan payload API. Contoh untuk entitas **Device**:

```mermaid
classDiagram
    class DeviceBase {
        +string device_code
        +string name
        +string type
        +string location
    }

    class DeviceCreate {
        +string device_code
        +string name
    }

    class DeviceResponse {
        +int id
        +string status
        +datetime created_at
    }

    DeviceBase <|-- DeviceCreate
    DeviceBase <|-- DeviceResponse
```

---

## 4. Integrasi Router & Model
Setiap router di `apps/api/app/routers/` berinteraksi dengan **Models** melalui **Schemas** sebagai jembatan:

1.  **Request:** User mengirim JSON → Validasi via `SchemaCreate`.
2.  **Logic:** Data diproses dan disimpan menggunakan SQLAlchemy `Model`.
3.  **Response:** Data dikembalikan ke user dikonversi via `SchemaResponse` (menggunakan `from_attributes=True`).
