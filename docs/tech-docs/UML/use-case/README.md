# Use Case Diagram - iSURF Project

Dokumen ini mendefinisikan interaksi antara aktor luar dengan fungsionalitas sistem **iSURF (Integrated Smart Urban Farming)**.

## 1. Diagram Use Case Utama
Berikut adalah visualisasi use case sistem menggunakan sintaks Mermaid dengan urutan yang dioptimalkan untuk meminimalkan tumpang tindih garis:

```mermaid
%%{init: {'flowchart': {'curve': 'linear'}}}%%
flowchart LR
    
    %% Actors
    Admin((Administrator))
    Worker((Field Worker))
    User((Researcher / User))
    IoT((IoT Device ESP32))

    subgraph Platform [iSURF Platform]
        direction TB
        UC01([UC01: Login & Auth])
        UC02([UC02: Monitoring Real-time Data])
        UC06([UC06: Manual Trigger Irrigation])
        UC03([UC03: Register & Manage Device])
        UC05([UC05: Manage Irrigation Schedule])
        UC10([UC10: Review Dataset Request])
        UC04([UC04: Configure Sensor Thresholds])
        UC09([UC09: Request Dataset])
        UC11([UC11: Download Dataset])
        UC07([UC07: Ingest Sensor Data])
        UC08([UC08: Send Heartbeat])
    end

    %% Admin Connections (Top & Middle)
    Admin --- UC01
    Admin --- UC02
    Admin --- UC06
    Admin --- UC03
    Admin --- UC05
    Admin --- UC10
    Admin --- UC04

    %% Field Worker Connections (Top)
    UC01 --- Worker
    UC02 --- Worker
    UC06 --- Worker

    %% Researcher Connections (Middle & Bottom)
    User --- UC01
    User --- UC02
    User --- UC09
    User --- UC11

    %% IoT Device Connections (Bottom)
    UC04 --- IoT
    UC07 --- IoT
    UC08 --- IoT
```

---

## 2. Definisi Aktor
| Aktor | Deskripsi |
| :--- | :--- |
| **Administrator** | Memiliki akses penuh untuk mengelola pengguna, perangkat IoT, dan melakukan audit terhadap permintaan data. |
| **Researcher / User** | Pengguna yang fokus pada konsumsi data untuk penelitian atau pemantauan umum. |
| **Field Worker** | Personel di lapangan yang memantau sistem melalui aplikasi mobile dan melakukan tindakan manual jika diperlukan. |
| **IoT Device** | Perangkat keras (ESP32) yang berinteraksi dengan API for mengirim data telemetri. |

---

## 3. Daftar Use Case
Dokumentasi detail (Sequence & Activity Diagram) akan dipisahkan per Use Case:

### Group A: Authentication & User Management
- **UC01: Login & Authentication:** Proses masuk ke sistem menggunakan username dan password untuk mendapatkan akses via Web/Mobile.

### Group B: Device & Monitoring
- **UC02: Monitoring Real-time Data:** Melihat data sensor (pH, TDS, Temp, dll) secara langsung melalui dashboard.
- **UC07: Ingest Sensor Data:** Proses otomatis perangkat IoT mengirimkan pembacaan sensor ke server.
- **UC08: Send Heartbeat:** Perangkat IoT melaporkan status aktif secara periodik.

### Group C: Control & Configuration
- **UC03: Register & Manage Device:** Menambahkan perangkat baru ke sistem (oleh Admin).
- **UC04: Configure Sensor Thresholds:** Mengatur batas aman nilai sensor untuk memicu peringatan otomatis.
- **UC05: Manage Irrigation Schedule:** Penjadwalan penyiraman otomatis berdasarkan waktu.
- **UC06: Manual Trigger Irrigation:** Menyalakan pompa/actuator secara langsung dari aplikasi.

### Group D: Research Data Access
- **UC09: Request Dataset:** Mengajukan permohonan data historis dalam rentang waktu tertentu.
- **UC10: Review Dataset Request:** Validasi permohonan data oleh Administrator.
- **UC11: Download Dataset:** Mengunduh file data yang telah disetujui (CSV/JSON).
