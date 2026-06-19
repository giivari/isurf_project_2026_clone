# Data Dictionary - iSURF Project

Dokumen ini merinci struktur basis data MySQL yang digunakan oleh sistem iSURF. Basis data ini mendukung fungsionalitas Backend API (FastAPI) dan Web ERP (Yii2).

## 1. Entity Relationship Diagram (ERD) Overview
Secara garis besar, relasi antar tabel utama adalah sebagai berikut:
- **users** mengelola otentikasi dan identitas pengguna.
- **devices** adalah pusat data perangkat IoT.
- **sensors** terikat pada satu *device*.
- **sensor_readings** mencatat histori data telemetry dari sensor.
- **alerts** mencatat anomali data sensor dan peringatan sistem.
- **irrigation_schedules** & **irrigation_logs** mengelola otomasi dan riwayat penyiraman.
- **data_requests** menyimpan permohonan data oleh pengguna eksternal (peneliti/umum).

---

## 2. Tabel: `users`
Mengelola data pengguna sistem. Struktur ini memperluas struktur bawaan `yii2-app-advanced` untuk kebutuhan iSURF.

| Kolom | Tipe Data | Nullable | Keterangan |
| :--- | :--- | :--- | :--- |
| `id` | INT (PK) | No | Auto increment. Identifier unik pengguna. |
| `username` | VARCHAR(255) | No | Username login unik. |
| `email` | VARCHAR(255) | No | Alamat email unik pengguna. |
| `password_hash` | VARCHAR(255) | No | Hash password menggunakan bcrypt. |
| `auth_key` | VARCHAR(32) | No | Kunci otentikasi (untuk fungsionalitas "remember me" di Yii2). |
| `password_reset_token` | VARCHAR(255) | Yes | Token unik untuk reset password. |
| `status` | SMALLINT | No | Status akun (default: 10 = Active). |
| `created_at` | INT | No | Timestamp UNIX waktu pembuatan akun. |
| `updated_at` | INT | No | Timestamp UNIX waktu pembaruan akun terakhir. |
| `full_name` | VARCHAR(255) | Yes | Nama lengkap pengguna. |
| `role` | ENUM('admin') | Yes | Hak akses/peran pengguna (default: 'admin'). |
| `avatar_url` | VARCHAR(255) | Yes | URL atau path file foto profil pengguna. |
| `last_login_at` | INT | Yes | Timestamp UNIX waktu login terakhir. |

---

## 3. Tabel: `devices`
Menyimpan informasi perangkat keras IoT yang terdaftar di sistem.

| Kolom | Tipe Data | Nullable | Keterangan |
| :--- | :--- | :--- | :--- |
| `id` | INT (PK) | No | Auto increment. Identifier unik perangkat. |
| `device_code` | VARCHAR(100) | No | Kode unik identitas perangkat (misal: NODE-001). |
| `name` | VARCHAR(255) | No | Nama deskriptif perangkat. |
| `type` | VARCHAR(100) | Yes | Jenis perangkat (misal: Hydroponic, Soil). |
| `location` | VARCHAR(255) | Yes | Lokasi fisik perangkat terpasang. |
| `status` | ENUM('online', 'offline', 'maintenance') | No | Status operasional perangkat (default: 'offline'). |
| `last_heartbeat`| DATETIME | Yes | Waktu terakhir perangkat mengirim sinyal aktif. |
| `firmware_version`| VARCHAR(50) | Yes | Versi firmware yang berjalan di perangkat. |
| `created_at` | DATETIME | No | Waktu pembuatan baris data (default: CURRENT_TIMESTAMP). |
| `updated_at` | DATETIME | No | Waktu pembaruan data terakhir (default: CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP). |

---

## 4. Tabel: `sensors`
Menyimpan konfigurasi sensor yang terpasang pada sebuah perangkat.

| Kolom | Tipe Data | Nullable | Keterangan |
| :--- | :--- | :--- | :--- |
| `id` | INT (PK) | No | Auto increment. Identifier unik sensor. |
| `device_id` | INT (FK) | No | Relasi ke tabel `devices`. Menghubungkan sensor dengan perangkatnya. |
| `name` | VARCHAR(255) | No | Nama deskriptif sensor (misal: pH Sensor A). |
| `sensor_type` | ENUM('tds', 'ph', 'moisture', 'ultrasonic', 'temperature') | No | Jenis sensor yang terpasang. |
| `unit` | VARCHAR(20) | No | Satuan ukuran data (misal: ppm, pH, %, cm, °C). |
| `min_threshold` | FLOAT | Yes | Batas bawah nilai sensor untuk memicu peringatan (alert). |
| `max_threshold` | FLOAT | Yes | Batas atas nilai sensor untuk memicu peringatan (alert). |
| `is_active` | BOOLEAN | No | Status aktif/nonaktif sensor (default: TRUE). |
| `created_at` | DATETIME | No | Waktu pembuatan baris data (default: CURRENT_TIMESTAMP). |

---

## 5. Tabel: `sensor_readings`
Log histori data sensor (Telemetry). Tabel ini memiliki volume data terbesar dan dioptimalkan dengan indeks pada `recorded_at` serta kombinasi `(device_id, sensor_id)`.

| Kolom | Tipe Data | Nullable | Keterangan |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT (PK) | No | Auto increment. Identifier unik pembacaan data. |
| `sensor_id` | INT (FK) | No | Relasi ke tabel `sensors` (CASCADE). |
| `device_id` | INT (FK) | No | Relasi ke tabel `devices` (CASCADE). |
| `value` | FLOAT | No | Nilai numerik hasil pembacaan sensor. |
| `recorded_at` | DATETIME | No | Waktu data dicatat di sistem (default: CURRENT_TIMESTAMP). |

---

## 6. Tabel: `alerts`
Mencatat histori peringatan saat pembacaan data sensor melebihi ambang batas atau ketika terjadi anomali pada sistem.

| Kolom | Tipe Data | Nullable | Keterangan |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT (PK) | No | Auto increment. Identifier unik alert. |
| `device_id` | INT (FK) | No | Relasi ke tabel `devices` (CASCADE). Perangkat yang memicu alert. |
| `sensor_id` | INT (FK) | Yes | Relasi ke tabel `sensors` (SET NULL). Sensor terkait yang memicu alert. |
| `alert_type` | ENUM('info', 'warning', 'critical') | No | Tingkat keparahan peringatan. |
| `message` | TEXT | No | Detail pesan/deskripsi peringatan. |
| `value` | FLOAT | Yes | Nilai sensor saat alert dipicu. |
| `threshold_exceeded` | FLOAT | Yes | Nilai ambang batas yang dilalui/dilewati. |
| `is_read` | BOOLEAN | No | Status apakah alert sudah dibaca oleh admin (default: FALSE). |
| `created_at` | DATETIME | No | Waktu peringatan terjadi (default: CURRENT_TIMESTAMP). |
| `resolved_at` | DATETIME | Yes | Waktu peringatan berhasil ditangani/selesai. |

---

## 7. Tabel: `irrigation_schedules`
Pengaturan jadwal penyiraman otomatis (waktu, durasi, dan hari dalam seminggu).

| Kolom | Tipe Data | Nullable | Keterangan |
| :--- | :--- | :--- | :--- |
| `id` | INT (PK) | No | Auto increment. Identifier unik jadwal. |
| `device_id` | INT (FK) | No | Relasi ke tabel `devices` (CASCADE). Perangkat penyiraman terkait. |
| `name` | VARCHAR(255) | No | Nama atau deskripsi jadwal (misal: "Penyiraman Pagi"). |
| `start_time` | TIME | No | Waktu mulai penyiraman (format: HH:MM:SS). |
| `duration_minutes` | INT | No | Durasi penyiraman dalam menit. |
| `days_of_week` | VARCHAR(50) | No | Hari pelaksanaan penyiraman (format teks, contoh: `1,3,5` untuk Senin, Rabu, Jumat). |
| `is_active` | BOOLEAN | No | Status keaktifan jadwal (default: TRUE). |
| `created_at` | DATETIME | No | Waktu pembuatan baris data (default: CURRENT_TIMESTAMP). |
| `updated_at` | DATETIME | No | Waktu pembaruan data terakhir (default: CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP). |

---

## 8. Tabel: `irrigation_logs`
Mencatat riwayat aktivitas penyiraman yang dijalankan oleh sistem, baik terjadwal, manual, maupun berbasis sensor otomatis.

| Kolom | Tipe Data | Nullable | Keterangan |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT (PK) | No | Auto increment. Identifier unik log penyiraman. |
| `schedule_id` | INT (FK) | Yes | Relasi ke tabel `irrigation_schedules` (SET NULL). Jadwal asal jika dipicu secara terjadwal. |
| `device_id` | INT (FK) | No | Relasi ke tabel `devices` (CASCADE). Perangkat penyiraman yang melakukan aksi. |
| `trigger_type` | ENUM('manual', 'scheduled', 'auto_sensor') | No | Jenis pemicu penyiraman. |
| `started_at` | DATETIME | No | Waktu penyiraman dimulai. |
| `ended_at` | DATETIME | Yes | Waktu penyiraman berakhir. |
| `status` | ENUM('running', 'completed', 'failed') | No | Status eksekusi penyiraman (default: 'running'). |
| `water_volume_liters` | FLOAT | Yes | Volume air yang terpakai selama proses penyiraman (liter). |

---

## 9. Tabel: `data_requests`
Mengelola formulir pengajuan permohonan dataset/telemetri oleh peneliti atau masyarakat umum untuk keperluan analisis.

| Kolom | Tipe Data | Nullable | Keterangan |
| :--- | :--- | :--- | :--- |
| `id` | INT (PK) | No | Auto increment. Identifier unik permohonan. |
| `tracking_code` | VARCHAR(50) | No | Kode unik untuk melacak status pengajuan permohonan (UNIQUE). |
| `full_name` | VARCHAR(255) | No | Nama lengkap pemohon. |
| `email` | VARCHAR(255) | No | Alamat email aktif pemohon. |
| `nim_nip` | VARCHAR(50) | No | NIM (Mahasiswa) atau NIP (Pegawai/Peneliti) dari pemohon. |
| `reason` | TEXT | No | Alasan/tujuan pengajuan dataset. |
| `document_path` | VARCHAR(255) | No | Path lokasi dokumen proposal/bukti fisik pengajuan di server. |
| `data_type` | ENUM('monitoring', 'analytics') | No | Jenis data yang diminta. |
| `requested_sensors` | JSON | Yes | Data array sensor spesifik yang diminta (format JSON). |
| `date_start` | DATE | Yes | Batas tanggal awal dataset yang diminta. |
| `date_end` | DATE | Yes | Batas tanggal akhir dataset yang diminta. |
| `status` | ENUM('pending', 'approved', 'rejected') | No | Status persetujuan permohonan (default: 'pending'). |
| `admin_notes` | TEXT | Yes | Catatan atau alasan dari administrator yang meninjau permohonan. |
| `download_token` | VARCHAR(64) | Yes | Token keamanan untuk mengunduh berkas dataset jika disetujui. |
| `created_at` | DATETIME | No | Waktu permohonan diajukan (default: CURRENT_TIMESTAMP). |
| `reviewed_at` | DATETIME | Yes | Waktu peninjauan permohonan oleh administrator. |
| `reviewed_by` | INT (FK) | Yes | Relasi ke tabel `users` (SET NULL). Administrator yang memproses/meninjau permohonan. |
