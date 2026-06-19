# Data Dictionary - iSURF Project

Dokumen ini merinci struktur basis data MySQL/MariaDB yang digunakan oleh sistem **iSURF (Integrated Smart Urban Farming)**, sesuai dengan model SQLAlchemy nyata di backend FastAPI.

## 1. Entity Relationship Diagram (ERD) Overview
Relasi antar tabel utama diorganisasikan dengan pendekatan **Area-Centric**:
*   `users` mengelola otentikasi akun pengguna.
*   `areas` mendefinisikan wilayah pertanian urban pintar (Greenhouse, Nursery, dll.).
*   `sensors` & `actuators` terikat langsung pada suatu `area` wilayah.
*   `sensor_logs` mencatat riwayat telemetry mentah per sensor.
*   `area_aggregations` rangkuman statistik data berkala per wilayah untuk optimasi kueri grafik.
*   `alerts` mencatat anomali telemetry yang tidak sesuai ambang batas sensor.
*   `area_condition_rules` & `area_schedule_rules` mengelola otomasi kontrol pompa dan peralatan.
*   `water_usage_logs` memantau debit penggunaan dan sisa air tangki.
*   `data_requests` menyimpan pengajuan izin download dataset oleh peneliti/akademisi.

---

## 2. Tabel: `users`
Menyimpan kredensial otentikasi serta profil pengguna sistem.

| Kolom | Tipe Data | Nullable | Keterangan |
| :--- | :--- | :--- | :--- |
| `id` | INT (PK) | No | Auto increment. Identifier unik pengguna. |
| `username` | VARCHAR(255) | No | Username login unik. |
| `email` | VARCHAR(255) | No | Alamat email unik pengguna. |
| `password_hash` | VARCHAR(255) | No | Hash password login. |
| `auth_key` | VARCHAR(32) | No | Kunci otentikasi (untuk session dan Yii2 'remember me'). |
| `status` | SMALLINT | No | Status keaktifan akun (default: 10 = Active). |
| `full_name` | VARCHAR(255) | Yes | Nama lengkap pengguna. |
| `role` | VARCHAR(50) | Yes | Peran akses pengguna (default: 'admin'). |
| `avatar_url` | VARCHAR(255) | Yes | Path/URL foto profil pengguna. |

---

## 3. Tabel: `areas`
Mendefinisikan wilayah lahan pertanian urban pintar.

| Kolom | Tipe Data | Nullable | Keterangan |
| :--- | :--- | :--- | :--- |
| `id` | INT (PK) | No | Auto increment. Identifier unik wilayah. |
| `name` | VARCHAR(100) | No | Nama unik wilayah (misal: "Greenhouse Alpha"). |
| `plant` | VARCHAR(100) | Yes | Nama komoditas tanaman yang ditanam (misal: "Tomat"). |
| `description` | VARCHAR(500) | Yes | Deskripsi atau catatan singkat wilayah tersebut. |
| `created_at` | DATETIME | No | Waktu wilayah ditambahkan. |
| `updated_at` | DATETIME | No | Waktu pembaruan informasi wilayah. |

---

## 4. Tabel: `sensors`
Menyimpan konfigurasi modul sensor yang terpasang di wilayah pertanian.

| Kolom | Tipe Data | Nullable | Keterangan |
| :--- | :--- | :--- | :--- |
| `id` | VARCHAR(50) (PK) | No | Kode identitas unik sensor fisik (misal: "DHT22_ALPHA"). |
| `name` | VARCHAR(100) | No | Nama sensor deskriptif (misal: "Sensor Kelembaban Tanah"). |
| `data_type` | VARCHAR(100) | No | Jenis pembacaan sensor (misal: "Suhu Udara", "pH"). |
| `min_threshold` | FLOAT | Yes | Batas minimal nilai normal sebelum alert dipicu. |
| `max_threshold` | FLOAT | Yes | Batas maksimal nilai normal sebelum alert dipicu. |
| `is_online` | BOOLEAN | No | Status konektivitas sensor online/offline. |
| `area_id` | INT (FK) | Yes | Menghubungkan sensor ke tabel `areas` (ON DELETE CASCADE). |
| `created_at` | DATETIME | No | Waktu pendaftaran sensor. |
| `updated_at` | DATETIME | No | Waktu terakhir perubahan konfigurasi sensor. |

---

## 5. Tabel: `actuators`
Menyimpan aktuator (katup/pompa kontrol) yang terpasang di wilayah pertanian.

| Kolom | Tipe Data | Nullable | Keterangan |
| :--- | :--- | :--- | :--- |
| `id` | VARCHAR(50) (PK) | No | Kode identitas unik aktuator fisik (misal: "VALVE_01"). |
| `name` | VARCHAR(100) | No | Nama deskriptif aktuator (misal: "Pompa Air Utama"). |
| `flow_rate_per_sec`| FLOAT | No | Debit air keluaran per detik (liter/detik) untuk kalkulasi penggunaan air. |
| `valve_status` | VARCHAR(20) | No | Status aktif alat ('ON' atau 'OFF'). |
| `is_auto_enabled` | BOOLEAN | No | Status kontrol otomatisasi aktif/tidak aktif. |
| `area_id` | INT (FK) | Yes | Menghubungkan aktuator ke tabel `areas` (ON DELETE CASCADE). |
| `created_at` | DATETIME | No | Waktu pendaftaran aktuator. |
| `updated_at` | DATETIME | No | Waktu pembaruan status aktuator. |

---

## 6. Tabel: `sensor_logs`
Menyimpan riwayat (logs) telemetry mentah time-series dari sensor.

| Kolom | Tipe Data | Nullable | Keterangan |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT (PK) | No | Auto increment. Identifier unik log pembacaan. |
| `date` | DATE | No | Tanggal pencatatan data (INDEX). |
| `time` | TIME | No | Waktu pencatatan data. |
| `reading` | FLOAT | No | Nilai numerik hasil pembacaan sensor. |
| `anomalies` | BOOLEAN | No | Status apakah nilai ini melanggar threshold sensor (anomali). |
| `status` | VARCHAR(50) | Yes | Keterangan status pembacaan (misal: "Normal", "Kritis"). |
| `sensor_id` | VARCHAR(50) (FK)| No | Menghubungkan ke tabel `sensors` (ON DELETE CASCADE). |

---

## 7. Tabel: `area_aggregations`
Rangkuman data rata-rata, minimal, dan maksimal per jenis sensor per wilayah untuk kebutuhan efisiensi kueri analitik grafik.

| Kolom | Tipe Data | Nullable | Keterangan |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT (PK) | No | Auto increment. Identifier unik agregasi. |
| `date` | DATE | No | Tanggal masa agregasi (INDEX). |
| `time` | TIME | No | Waktu/jam masa agregasi. |
| `data_type` | VARCHAR(100) | No | Jenis tipe data sensor yang diagregasikan. |
| `min_value` | FLOAT | Yes | Nilai terkecil dalam rentang agregasi. |
| `max_value` | FLOAT | Yes | Nilai terbesar dalam rentang agregasi. |
| `avg_value` | FLOAT | Yes | Nilai rata-rata dalam rentang agregasi. |
| `area_id` | INT (FK) | No | Menghubungkan ke tabel `areas` (ON DELETE CASCADE). |

---

## 8. Tabel: `alerts`
Mencatat peringatan keselamatan tanaman jika pembacaan sensor keluar batas aman.

| Kolom | Tipe Data | Nullable | Keterangan |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT (PK) | No | Auto increment. Identifier unik alert. |
| `sensor_id` | VARCHAR(50) (FK)| No | Sensor terkait pemicu alert (ON DELETE CASCADE). |
| `alert_type` | VARCHAR(50) | No | Jenis alert (misal: "Threshold Violation"). |
| `message` | TEXT | No | Deskripsi detail alert (misal: "pH melebihi batas wajar"). |
| `value` | FLOAT | Yes | Nilai sensor saat alert terjadi. |
| `threshold_exceeded`| FLOAT | Yes | Nilai ambang batas sensor yang terlanggar. |
| `is_read` | BOOLEAN | No | Status pembacaan notifikasi oleh admin (default: FALSE, INDEX). |
| `created_at` | DATETIME | No | Waktu alert tercatat (INDEX). |
| `resolved_at` | DATETIME | Yes | Waktu alert selesai ditangani. |

---

## 9. Tabel: `area_condition_rules`
Menyimpan aturan logika otomatis berdasarkan pembacaan kondisi sensor wilayah.

| Kolom | Tipe Data | Nullable | Keterangan |
| :--- | :--- | :--- | :--- |
| `id` | INT (PK) | No | Auto increment. Identifier unik aturan kondisi. |
| `data_type` | VARCHAR(100) | No | Tipe data sensor referensi (misal: "Kelembaban Tanah"). |
| `operator` | VARCHAR(10) | No | Operator pembanding (contoh: `<`, `>`, `==`). |
| `value` | FLOAT | No | Nilai acuan perbandingan (contoh: `40.0`). |
| `action` | VARCHAR(20) | No | Perintah aksi bagi aktuator wilayah ('ON' atau 'OFF'). |
| `area_id` | INT (FK) | No | Menghubungkan aturan ke tabel `areas` (ON DELETE CASCADE). |
| `created_at` | DATETIME | No | Waktu pembuatan aturan. |
| `updated_at` | DATETIME | No | Waktu terakhir pembaruan aturan. |

---

## 10. Tabel: `area_schedule_rules`
Menyimpan aturan jadwal harian waktu aktif penyiraman otomatis wilayah.

| Kolom | Tipe Data | Nullable | Keterangan |
| :--- | :--- | :--- | :--- |
| `id` | INT (PK) | No | Auto increment. Identifier unik aturan jadwal. |
| `time` | TIME | No | Waktu target pengeksekusian perintah aksi (format: HH:MM:SS). |
| `action` | VARCHAR(20) | No | Perintah aksi bagi aktuator wilayah ('ON' atau 'OFF'). |
| `area_id` | INT (FK) | No | Menghubungkan aturan ke tabel `areas` (ON DELETE CASCADE). |
| `created_at` | DATETIME | No | Waktu pembuatan aturan jadwal. |
| `updated_at` | DATETIME | No | Waktu terakhir pembaruan jadwal. |

---

## 11. Tabel: `water_usage_logs`
Mencatat akumulasi debit air yang dikeluarkan aktuator penyiraman dan sisa air tangki.

| Kolom | Tipe Data | Nullable | Keterangan |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT (PK) | No | Auto increment. Identifier unik log penggunaan air. |
| `timestamp` | DATETIME | No | Waktu perekaman log air (INDEX). |
| `water_discharged` | FLOAT | No | Akumulasi air yang dikeluarkan dalam liter (default: 0.0). |
| `water_remaining` | FLOAT | No | Perkiraan sisa air dalam tangki dalam liter (default: 0.0). |
| `actuator_id` | VARCHAR(50) (FK)| No | Aktuator katup air terkait (ON DELETE CASCADE). |

---

## 12. Tabel: `data_requests`
Formulir persetujuan peneliti/umum untuk mengakses dan mengunduh berkas dataset telemetri pertanian.

| Kolom | Tipe Data | Nullable | Keterangan |
| :--- | :--- | :--- | :--- |
| `id` | INT (PK) | No | Auto increment. Identifier unik pengajuan. |
| `tracking_code` | VARCHAR(50) | No | Kode pelacakan acak unik 8 karakter (misal: "B7EF32A9", INDEX). |
| `full_name` | VARCHAR(255) | No | Nama lengkap pemohon data. |
| `email` | VARCHAR(255) | No | Alamat email pemohon. |
| `nim_nip` | VARCHAR(50) | No | Identitas civitas/NIP/NIM pemohon. |
| `reason` | TEXT | No | Deskripsi tujuan penggunaan dataset. |
| `document_path` | VARCHAR(255) | No | Lokasi file berkas proposal pdf pengajuan yang diunggah. |
| `data_type` | VARCHAR(20) | No | Rentang tipe data pengajuan ('monitoring' atau 'analytics'). |
| `requested_sensors`| JSON | Yes | Payload JSON daftar sensor ID yang diminta aksesnya. |
| `date_start` | DATE | Yes | Batas tanggal awal sensor log yang diajukan. |
| `date_end` | DATE | Yes | Batas tanggal akhir sensor log yang diajukan. |
| `status` | VARCHAR(20) | No | Status permohonan (misal: "PENDING", "APPROVED", "REJECTED"). |
| `admin_notes` | TEXT | Yes | Catatan administrator ulasan persetujuan/penolakan data. |
| `download_token` | VARCHAR(64) | Yes | Kunci akses token temporer untuk melakukan unduhan file. |
| `created_at` | DATETIME | No | Waktu pengajuan formulir terkirim. |
| `reviewed_at` | DATETIME | Yes | Waktu ulasan keputusan admin diberikan. |
| `reviewed_by` | INT (FK) | Yes | Menghubungkan admin pengulas ke tabel `users` (ON DELETE SET NULL). |
