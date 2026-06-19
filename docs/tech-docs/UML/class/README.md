# Class Diagram - iSURF Project

Dokumen ini merinci struktur kelas internal sistem, menampilkan relasi utuh antar entitas di tingkat basis data (SQLAlchemy Models) dan penjelasan untuk masing-masing kelas.

## 1. Domain Model (SQLAlchemy Entities)

```mermaid
classDiagram
    class User {
        +int id
        +string username
        +string email
        +string password_hash
        +string auth_key
        +int status
        +string full_name
        +string role
        +string avatar_url
    }

    class Area {
        +int id
        +string name
        +string plant
        +string description
        +datetime created_at
        +datetime updated_at
    }

    class Sensor {
        +string id
        +string name
        +string data_type
        +float min_threshold
        +float max_threshold
        +boolean is_online
        +int area_id
        +datetime created_at
        +datetime updated_at
    }

    class Actuator {
        +string id
        +string name
        +float flow_rate_per_sec
        +string valve_status
        +boolean is_auto_enabled
        +int area_id
        +datetime created_at
        +datetime updated_at
    }

    class SensorLog {
        +bigint id
        +date date
        +time time
        +float reading
        +boolean anomalies
        +string status
        +string sensor_id
    }

    class AreaAggregation {
        +bigint id
        +date date
        +time time
        +string data_type
        +float min_value
        +float max_value
        +float avg_value
        +int area_id
    }

    class Alert {
        +bigint id
        +string sensor_id
        +string alert_type
        +string message
        +float value
        +float threshold_exceeded
        +boolean is_read
        +datetime created_at
        +datetime resolved_at
    }

    class AreaConditionRule {
        +int id
        +string data_type
        +string operator
        +float value
        +string action
        +int area_id
        +datetime created_at
        +datetime updated_at
    }

    class AreaScheduleRule {
        +int id
        +time time
        +string action
        +int area_id
        +datetime created_at
        +datetime updated_at
    }

    class WaterUsageLog {
        +bigint id
        +datetime timestamp
        +float water_discharged
        +float water_remaining
        +string actuator_id
    }

    class DataRequest {
        +int id
        +string tracking_code
        +string full_name
        +string email
        +string nim_nip
        +string reason
        +string document_path
        +string data_type
        +json requested_sensors
        +date date_start
        +date date_end
        +string status
        +string admin_notes
        +string download_token
        +datetime created_at
        +datetime reviewed_at
        +int reviewed_by
    }

    %% Relationships
    Area "1" -- "*" Sensor : groups
    Area "1" -- "*" Actuator : groups
    Area "1" -- "*" AreaAggregation : aggregates
    Area "1" -- "*" AreaConditionRule : schedules
    Area "1" -- "*" AreaScheduleRule : schedules

    Sensor "1" -- "*" SensorLog : generates
    Sensor "1" -- "*" Alert : triggers

    Actuator "1" -- "*" WaterUsageLog : logs

    User "1" -- "*" DataRequest : reviews
```

---

## 2. Penjelasan Class
Berikut adalah penjelasan fungsionalitas dari setiap kelas utama di atas:

### **`User`**
Merepresentasikan entitas pengguna dalam sistem (seperti Administrator, Operator, dan Viewer). Mengelola kredensial otentikasi (username, password hash) dan status akun.

### **`Area`**
Merepresentasikan wilayah atau sektor lahan pertanian urban pintar (misalnya "Greenhouse A", "Nursery B"). Kelas ini melacak jenis komoditas tanaman (`plant`) yang ditanam dan deskripsinya. Menjadi wadah pengelompokan bagi sensor dan aktuator.

### **`Sensor`**
Mendefinisikan modul sensor fisik (seperti sensor kelembaban tanah, suhu udara, pH, TDS) yang terpasang di wilayah (`Area`) tertentu. Kelas ini menyimpan ambang batas nilai batas atas (`max_threshold`) dan batas bawah (`min_threshold`) untuk memantau keselamatan kondisi tanaman.

### **`Actuator`**
Mendefinisikan modul aktuator/alat kontrol keluaran fisik (seperti pompa air elektrik, selenoid valve, kipas) yang terpasang pada suatu wilayah (`Area`). Melacak kapasitas aliran air per detik (`flow_rate_per_sec`) dan status katup (`valve_status`).

### **`SensorLog`**
Catatan riwayat data telemetry mentah yang dikirim oleh perangkat IoT untuk sensor tertentu. Menyimpan tanggal (`date`), waktu (`time`), nilai pembacaan (`reading`), penanda anomali (`anomalies`), dan status keselamatan pembacaan.

### **`AreaAggregation`**
Menyimpan rangkuman agregasi statistik (nilai minimum, maksimum, dan rata-rata) per wilayah (`Area`) berdasarkan tipe data tertentu untuk kebutuhan visualisasi grafik analitik yang cepat.

### **`Alert`**
Mencatat kejadian anomali saat nilai sensor keluar dari batas aman yang telah diatur pada `Sensor`. Memuat tingkat keparahan (*alert type*), pesan peringatan, nilai pemicu, dan status penanganan alert.

### **`AreaConditionRule` & `AreaScheduleRule`**
Merupakan aturan logika otomasi penyiraman dan kontrol aktuator. `AreaConditionRule` memicu aksi aktuator berdasarkan kondisi sensor (misalnya: jika Soil Moisture < 40% maka nyalakan pompa). `AreaScheduleRule` memicu aksi berdasarkan waktu terjadwal (misalnya: nyalakan pompa setiap jam 06:00).

### **`WaterUsageLog`**
Catatan penggunaan air historis dari aktifnya aktuator penyiraman. Melacak jumlah air yang dikeluarkan (`water_discharged`) dan sisa ketersediaan air pada tangki penyiraman (`water_remaining`).

### **`DataRequest`**
Formulir permohonan dataset historis yang diajukan oleh pengguna/peneliti eksternal. Melacak masa waktu data yang diminta, alasan pengajuan, file dokumen pdf bukti akademis, status persetujuan, dan token unduh data yang diulas oleh `User` (Admin).

---

## 3. API Data Transfer Objects (Pydantic Schemas)
Sistem menggunakan pola DTO (Data Transfer Object) via Pydantic untuk validasi data masukan API dan format keluaran respons JSON. Contoh representasi DTO untuk entitas **Sensor**:

```mermaid
classDiagram
    class SensorCreate {
        +string id
        +string name
        +string data_type
        +float min_threshold
        +float max_threshold
        +boolean is_online
        +int area_id
    }

    class SensorResponse {
        +string id
        +string name
        +string data_type
        +float min_threshold
        +float max_threshold
        +boolean is_online
        +int area_id
        +datetime created_at
        +datetime updated_at
    }
```

---

## 4. Integrasi Router & Model
Setiap router di `apps/api/app/routers/` berinteraksi dengan **Models** melalui **Schemas/BaseModels** lokal atau global sebagai jembatan:

1.  **Request:** Payload JSON divalidasi secara otomatis menggunakan subclass Pydantic `BaseModel` (misal: `SensorCreate`).
2.  **Logic:** Data diproses dan disimpan menggunakan SQLAlchemy `Model` (anemic model) melalui session database `db`.
3.  **Response:** Objek data SQLAlchemy dikonversi menjadi skema respons JSON (misal: `SensorResponse`) yang dikembalikan ke pemanggil.
