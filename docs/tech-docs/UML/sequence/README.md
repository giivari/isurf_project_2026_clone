# Sequence Diagram - iSURF Project

Dokumen ini merinci Sequence Diagram untuk masing-masing dari ke-11 Use Case dalam sistem **iSURF (Integrated Smart Urban Farming)**. Setiap diagram menggambarkan interaksi pesan antarkomponen untuk menyelaraskan pemahaman teknis dengan implementasi backend FastAPI dan database.

---

## Daftar Isi
1. [UC01: Login & Authentication](#uc01-login--authentication)
2. [UC02: Monitoring Real-time Area Data](#uc02-monitoring-real-time-area-data)
3. [UC03: Manage Areas, Sensors & Actuators](#uc03-manage-areas-sensors--actuators)
4. [UC04: Configure Sensor Thresholds](#uc04-configure-sensor-thresholds)
5. [UC05: Configure Automation Rules](#uc05-configure-automation-rules)
6. [UC06: Manual Trigger Actuator](#uc06-manual-trigger-actuator)
7. [UC07: Ingest Sensor Data](#uc07-ingest-sensor-data)
8. [UC08: Update Online Status](#uc08-update-online-status)
9. [UC09: Request Dataset](#uc09-request-dataset)
10. [UC10: Review Dataset Request](#uc10-review-dataset-request)
11. [UC11: Download Dataset](#uc11-download-dataset)

---

### UC01: Login & Authentication
Menggambarkan proses pertukaran kredensial login hingga penerbitan token otentikasi.

```mermaid
sequenceDiagram
    autonumber
    actor User as Pengguna
    participant Dash as Dasbor Utama
    participant API as API Otentikasi (/api/auth)
    participant DB as Database MySQL

    User->>Dash: Input Username & Password
    Dash->>API: POST /auth/login (username, password)
    API->>DB: Query user berdasarkan username
    DB-->>API: Return user record (password_hash, role)

    alt Kredensial Tidak Cocok
        API-->>Dash: Return status 400 (Incorrect credentials)
        Dash-->>User: Tampilkan pesan kesalahan login
    else Kredensial Cocok (Bcrypt Match)
        API->>API: Generate JWT Access Token (username, role)
        API-->>Dash: Return status 200 + Access Token & role
        Dash->>Dash: Simpan Token di Local Storage
        Dash-->>User: Arahkan ke Halaman Dasbor Utama
    end
```

**Penjelasan:** Pengguna memasukkan username dan password. API `/auth/login` memvalidasi kredensial pengguna ke database menggunakan pencocokan hash bcrypt. Jika berhasil, sistem menerbitkan JWT token untuk authorize sesi berikutnya.

---

### UC02: Monitoring Real-time Area Data
Menggambarkan kueri telemetri wilayah secara real-time untuk visualisasi dasbor.

```mermaid
sequenceDiagram
    autonumber
    actor User as Pengguna
    participant Dash as Dasbor Utama
    participant API as API Readings (/api/readings)
    participant DB as Database MySQL

    User->>Dash: Memilih Wilayah/Area di Dasbor
    
    par Muat Telemetry Terkini
        Dash->>API: GET /readings/latest
        API->>DB: Query tabel area_aggregations berdasarkan area
        DB-->>API: Return data telemetri terbaru
        API-->>Dash: Return status 200 + data latest readings
    and Muat Grafik Historis
        Dash->>API: GET /readings/history/{area_id}/{data_type}
        API->>DB: Query tabel area_aggregations dalam rentang waktu
        DB-->>API: Return list data historis
        API-->>Dash: Return status 200 + list history readings
    end

    Dash->>Dash: Update widget gauge & render grafik garis
    Dash-->>User: Tampilkan visualisasi data wilayah
```

**Penjelasan:** Saat halaman wilayah dibuka, dasbor memicu dua kueri simultan ke API `/readings` untuk mengambil rangkuman status sensor terkini dan deret riwayat datanya untuk memetakan kurva tren di grafik.

---

### UC03: Manage Areas, Sensors & Actuators
Menggambarkan pendaftaran, perubahan, dan penghapusan data master wilayah, sensor, dan aktuator.

```mermaid
sequenceDiagram
    autonumber
    actor Admin as Administrator
    participant Dash as Dasbor Manajemen
    participant API as API Routers (areas/sensors/actuators)
    participant DB as Database MySQL

    %% Add Master Data
    Admin->>Dash: Mengisi form & klik Simpan Master Baru
    Dash->>API: POST / (Payload data baru)
    API->>DB: Cek duplikasi kunci unik
    DB-->>API: Return hasil cek
    alt Data Sudah Ada (Duplikasi)
        API-->>Dash: Return status 400 (Bad Request)
        Dash-->>Admin: Tampilkan pesan kesalahan duplikasi
    else Data Valid
        API->>DB: Simpan record master baru
        DB-->>API: Sukses menyimpan
        API-->>Dash: Return status 201 + Master Data baru
        Dash-->>Admin: Tampilkan pesan sukses pembuatan
    end

    %% Delete Master Data
    Admin->>Dash: Klik Hapus Master Data (ID target)
    Dash->>API: DELETE /{id}
    API->>DB: Hapus record master berdasarkan ID
    Note over DB: Menghapus log/rules terkait secara CASCADE
    DB-->>API: Sukses menghapus
    API-->>Dash: Return status 200 + pesan sukses
    Dash-->>Admin: Tampilkan pesan sukses penghapusan
```

**Penjelasan:** Manajemen Master Data mencakup penambahan dan penghapusan wilayah, sensor, atau aktuator. Penghapusan data master akan membersihkan secara berantai (*cascade delete*) seluruh log transaksi dan aturan otomasi yang melekat padanya di database.

---

### UC04: Configure Sensor Thresholds
Menggambarkan pembaruan ambang batas sensor wilayah secara kolektif.

```mermaid
sequenceDiagram
    autonumber
    actor Admin as Administrator
    participant Dash as Dasbor Manajemen
    participant API as API Areas (/api/areas)
    participant DB as Database MySQL

    Admin->>Dash: Input batas baru (Min/Max) per tipe data area
    Dash->>API: PUT /areas/{area_id}/sensors/thresholds (Payload min/max)
    API->>DB: Kueri semua sensor di area terpilih dengan data_type yang sama
    DB-->>API: Return list sensor terkait
    
    alt Tidak Ada Sensor Terdaftar
        API-->>Dash: Return pesan sukses dengan hitungan 0 sensor terupdate
        Dash-->>Admin: Tampilkan pemberitahuan "0 sensor disesuaikan"
    else Sensor Ditemukan
        API->>DB: Update kolom min_threshold & max_threshold di database
        DB-->>API: Sukses memperbarui data
        API-->>Dash: Return status 200 + hitungan sensor terupdate
        Dash-->>Admin: Tampilkan notifikasi pembaruan batas aman berhasil
    end
```

**Penjelasan:** Administrator mengubah batas aman parameter di area tertentu. API backend mengidentifikasi semua sensor yang terpengaruh dan memperbarui nilai kolom `min_threshold` dan `max_threshold` mereka secara kolektif di basis data.

---

### UC05: Configure Automation Rules
Menggambarkan penambahan aturan kontrol irigasi/aktuator otomatis.

```mermaid
sequenceDiagram
    autonumber
    actor Admin as Administrator
    participant Dash as Dasbor Manajemen
    participant API as API Areas (/api/areas)
    participant DB as Database MySQL

    Admin->>Dash: Buat Aturan Otomasi baru (Kondisi/Jadwal)
    
    alt Aturan Kondisi Sensor
        Dash->>API: POST /areas/{area_id}/conditions (data_type, operator, value, action)
        API->>DB: Simpan ke tabel area_condition_rules
    else Aturan Waktu Terjadwal
        Dash->>API: POST /areas/{area_id}/schedules (time, action)
        API->>DB: Simpan ke tabel area_schedule_rules
    end
    
    DB-->>API: Sukses menyimpan aturan
    API-->>Dash: Return status 201 + detail aturan baru
    Dash-->>Admin: Tampilkan notifikasi aturan otomasi aktif
```

**Penjelasan:** Aturan otomasi dibuat oleh Administrator untuk mengikat aksi aktuator ke kondisi sensor atau jadwal waktu harian tertentu. Sistem menyimpan aturan ini di tabel basis data terpisah untuk dievaluasi secara berkala.

---

### UC06: Manual Trigger Actuator
Menggambarkan interaksi override paksa status aktuator pompa air irigasi.

```mermaid
sequenceDiagram
    autonumber
    actor Worker as Field Worker / Admin
    participant Dash as Dasbor Utama
    participant API as API Irrigation (/api/irrigation)
    participant DB as Database MySQL
    participant Hardware as Aktuator Relay Pompa

    Worker->>Dash: Klik toggle saklar Aktuator (Kirim aksi ON/OFF)
    Dash->>API: POST /irrigation/override/{actuator_id} (Payload: command)
    API->>DB: Ambil profil aktuator dari DB
    DB-->>API: Return data aktuator (valve_status, flow_rate_per_sec)

    alt Command = ON
        API->>DB: Ambil level air tangki terakhir (WaterUsageLog)
        DB-->>API: Return sisa volume air (%)
        alt Level Air Tangki < 5% (Failsafe)
            API-->>Dash: Return status 400 (Water capacity critical)
            Dash-->>Worker: Tampilkan penolakan aksi pompa kering (dry-run)
        else Level Air Tangki Aman
            API->>DB: Update valve_status = 'ON'
            API->>Hardware: Kirim sinyal kontrol ON ke relay fisik
            API-->>Dash: Return status 200 (Forced ON)
            Dash-->>Worker: Nyalakan ikon aktuator di dasbor
        end
    else Command = OFF
        API->>DB: Ambil level air tangki terakhir
        DB-->>API: Return sisa volume air
        API->>API: Hitung estimasi air keluar (durasi * flow_rate_per_sec)
        API->>DB: Simpan record WaterUsageLog baru (sisa air terupdate)
        API->>DB: Update valve_status = 'OFF'
        API->>Hardware: Kirim sinyal kontrol OFF ke relay fisik
        API-->>Dash: Return status 200 (Forced OFF)
        Dash-->>Worker: Matikan ikon aktuator di dasbor
    end
```

**Penjelasan:** Ketika pompa dipaksa menyala (`ON`), sistem mengaktifkan perlindungan kegagalan (*failsafe*) jika kapasitas tangki air kritis. Ketika dimatikan (`OFF`), sistem mendeteksi selang waktu aktif untuk menghitung air terpakai dan menulis data log air di tabel `water_usage_logs`.

---

### UC07: Ingest Sensor Data
Menggambarkan proses penerimaan paket telemetry telemetry sensor berkala dari ESP32.

```mermaid
sequenceDiagram
    autonumber
    participant Node as ESP32 Sensor Node
    participant Gateway as API Gateway (/api/ingest)
    participant DB as Database MySQL
    participant Worker as Background Tasks

    Node->>Gateway: POST /ingest (Payload: sensors list)
    
    rect rgb(240, 248, 255)
        Note over Gateway, DB: Update Status Aktif Sensor
        Gateway->>DB: Ambil data sensor & konfigurasi threshold
        DB-->>Gateway: Return sensor records
        Gateway->>DB: Set is_online = True & updated_at = NOW
    end

    alt Melanggar Threshold (Anomali)
        Gateway->>DB: Tulis SensorLog (anomalies = True, status = 'Kritis')
        Gateway->>DB: Tulis Alert baru ke tabel alerts
    else Nilai Normal
        Gateway->>DB: Tulis SensorLog (anomalies = False, status = 'Normal')
    end

    par Evaluasi Otomasi (Latar Belakang)
        Gateway->>Worker: Picu task evaluate_conditions
        Worker->>DB: Kueri aturan pemicu dan aktuator aktif
        DB-->>Worker: Return aturan & status aktuator
        Note over Worker: Jika kecocokan terpenuhi, ubah status pompa otomatis
        Worker->>DB: Update valve_status aktuator ke target aksi
    and Agregasi Data (Latar Belakang)
        Gateway->>Worker: Picu task aggregate_sensor_data
        Worker->>DB: Tarik log data sensor 1 jam terakhir
        DB-->>Worker: Return log data
        Worker->>Worker: Kalkulasi statistik min, max, & rata-rata
        Worker->>DB: Tulis rangkuman ke tabel area_aggregations
    end

    Gateway-->>Node: Return status 200 OK (Ingest Success)
```

**Penjelasan:** Gateway menerima telemetri, menandai status sensor online, dan mendeteksi anomali untuk memicu peringatan darurat. Di latar belakang, sistem memicu evaluasi otomatisasi irigasi dan agregasi statistik data wilayah tanpa memblokir koneksi mikrokontroler.

---

### UC08: Update Online Status
Menggambarkan pengecekan heartbeat status konektivitas perangkat IoT secara lazy-evaluation.

```mermaid
sequenceDiagram
    autonumber
    actor User as Pengguna / Dasbor
    participant API as API Sensors (/api/sensors)
    participant DB as Database MySQL

    User->>API: GET /sensors (Meminta daftar sensor)
    API->>DB: Kueri semua data sensor dari database
    DB-->>API: Return list sensor records
    
    loop Periksa Setiap Sensor
        API->>API: Hitung selisih waktu (NOW - updated_at)
        alt Selisih waktu > 300 detik & is_online = True
            API->>DB: Update status sensor is_online = False
            Note over API: Tandai bahwa database telah berubah (dirty = True)
        end
    end

    alt Database Kotor (dirty = True)
        API->>DB: Commit seluruh pembaruan status offline
    end
    
    API-->>User: Return status 200 + list sensor terupdate (online/offline)
```

**Penjelasan:** Status keaktifan perangkat diuji setiap kali dasbor meminta data daftar sensor. Jika sensor tertentu tidak mengirimkan telemetry lebih dari 5 menit (300 detik), status koneksinya secara otomatis diubah menjadi `offline` (`is_online = False`).

---

### UC09: Request Dataset
Menggambarkan pendaftaran formulir permohonan ekspor dataset oleh peneliti.

```mermaid
sequenceDiagram
    autonumber
    actor Researcher as Peneliti / User
    participant Portal as Portal Web/Dasbor
    participant API as API Data Requests (/api/data-requests)
    participant DB as Database MySQL

    Researcher->>Portal: Isi form permohonan & unggah proposal PDF
    Portal->>API: POST / (Form-data: proposal.pdf, requested_sensors, dll)
    
    alt Berkas Bukan PDF
        API-->>Portal: Return status 400 (Only PDF allowed)
        Portal-->>Researcher: Tampilkan pesan kegagalan "Berkas wajib PDF"
    else Berkas Valid PDF
        Note over API: Simpan proposal.pdf di folder unggahan server
        API->>API: Generate 8-char tracking_code unik (misal: "A8DF22B0")
        API->>DB: Simpan permohonan baru (status = 'PENDING', document_path)
        DB-->>API: Sukses menyimpan
        API-->>Portal: Return status 201 + tracking_code
        Portal-->>Researcher: Tampilkan kode pelacakan pengajuan dataset
    end
```

**Penjelasan:** Peneliti mengunggah proposal PDF dan mengajukan form permintaan data. Sistem menyimpan file proposal ke server, menghasilkan kode lacak unik 8 karakter acak, lalu mencatat baris pengajuan baru di tabel `data_requests` dengan status awal "PENDING".

---

### UC10: Review Dataset Request
Menggambarkan persetujuan atau penolakan permohonan dataset oleh administrator.

```mermaid
sequenceDiagram
    autonumber
    actor Admin as Administrator
    participant Portal as Portal Dasbor
    participant API as API Data Requests (/api/data-requests)
    participant DB as Database MySQL

    Admin->>Portal: Buka daftar permohonan & ulas pengajuan riset
    Portal->>API: PUT /{request_id}/review (status, admin_notes)
    
    alt Keputusan = APPROVED
        API->>API: Generate 64-char download_token (SHA-256)
        API->>DB: Update data_requests (status = 'APPROVED', download_token, reviewed_by, reviewed_at)
    else Keputusan = REJECTED
        API->>DB: Update data_requests (status = 'REJECTED', download_token = NULL, reviewed_by, reviewed_at)
    end
    
    DB-->>API: Sukses memperbarui ulasan
    API-->>Portal: Return status 200
    Portal-->>Admin: Tampilkan ulasan sukses tersimpan
```

**Penjelasan:** Administrator memeriksa permohonan pengajuan data. Jika disetujui (**APPROVED**), sistem menghasilkan kunci token unduh aman (`download_token`) menggunakan SHA-256. Jika ditolak (**REJECTED**), status diubah tanpa menghasilkan token unduh.

---

### UC11: Download Dataset
Menggambarkan pengunduhan file ekspor telemetri historis menggunakan token akses disetujui.

```mermaid
sequenceDiagram
    autonumber
    actor Researcher as Peneliti / User
    participant Portal as Portal Web/Browser
    participant API as API Data Requests (/api/data-requests)
    participant DB as Database MySQL

    Researcher->>Portal: Mengakses tautan download berisi download_token
    Portal->>API: GET /download?token={download_token}
    API->>DB: Kueri permohonan berdasarkan token
    DB-->>API: Return data request record (status, requested_sensors, periode)

    alt Status Bukan APPROVED
        API-->>Portal: Return status 403 (Forbidden)
        Portal-->>Researcher: Tampilkan pesan "Akses ditolak / belum disetujui"
    else Status APPROVED
        API->>DB: Kueri telemetry sensor_logs sesuai sensor & rentang tanggal
        DB-->>API: Return kumpulan baris telemetry
        API->>API: Format kumpulan baris data menjadi string CSV tabel
        API-->>Portal: Alirkan data stream CSV file download attachment
        Portal-->>Researcher: Mulai proses unduh otomatis berkas file dataset.csv
    end
```

**Penjelasan:** Peneliti mengunduh data riset menggunakan token keamanan. Sistem memeriksa token di database. Jika pengajuan berstatus disetujui, sistem menarik log telemetri sensor terkait pada rentang tanggal pengajuan, mengonversinya menjadi file CSV, lalu mengirimkannya sebagai file unduhan langsung ke browser peneliti.
