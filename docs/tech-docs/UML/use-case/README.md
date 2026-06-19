# Use Case Diagram - iSURF Project

Dokumen ini mendefinisikan interaksi antara aktor luar dengan fungsionalitas sistem **iSURF (Integrated Smart Urban Farming)**.

## 1. Diagram Use Case Utama
Berikut adalah visualisasi use case sistem menggunakan sintaks Mermaid dengan urutan yang dioptimalkan untuk meminimalkan tumpang tindih garis, diselaraskan dengan pendekatan *Area-Centric*:

```mermaid
%%{init: {'flowchart': {'curve': 'linear'}}}%%
flowchart LR
    
    %% Actors
    Admin((Administrator))
    Worker((Field Worker))
    User((Researcher / User))
    IoT((IoT Device / Sensor Node))

    subgraph Platform [iSURF Platform]
        direction TB
        UC01([UC01: Login & Auth])
        UC02([UC02: Monitoring Real-time Area Data])
        UC03([UC03: Manage Areas, Sensors & Actuators])
        UC04([UC04: Configure Sensor Thresholds])
        UC05([UC05: Configure Automation Rules])
        UC06([UC06: Manual Trigger Actuator])
        UC07([UC07: Ingest Sensor Data])
        UC08([UC08: Update Online Status])
        UC09([UC09: Request Dataset])
        UC10([UC10: Review Dataset Request])
        UC11([UC11: Download Dataset])
    end

    %% Admin Connections (Top & Middle)
    Admin --- UC01
    Admin --- UC02
    Admin --- UC03
    Admin --- UC04
    Admin --- UC05
    Admin --- UC06
    Admin --- UC10

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
| **Administrator** | Memiliki akses penuh untuk mengelola pengguna, area pertanian, sensor, aktuator, serta meninjau permohonan akses data. |
| **Researcher / User** | Pengguna yang fokus pada konsumsi data historis untuk kebutuhan riset, serta pemantauan umum. |
| **Field Worker** | Personel lapangan yang memantau kondisi sensor secara mobile dan dapat melakukan override manual aktuator (pompa). |
| **IoT Device / Sensor Node** | Mikrokontroler lapangan (seperti ESP32) yang mengirimkan data telemetry dan memantau kondisi *online/offline*. |

---

## 3. Daftar Use Case
Dokumentasi detail (Sequence & Activity Diagram) dipisahkan per Use Case:

### Group A: Authentication & User Management
- **UC01: Login & Authentication:** Proses masuk ke sistem menggunakan username dan password untuk mendapat token akses.

### Group B: Area & Monitoring
- **UC02: Monitoring Real-time Area Data:** Memantau telemetry sensor (kelembaban tanah, suhu udara, pH, TDS) berdasarkan area pertanian.
- **UC07: Ingest Sensor Data:** Proses pengiriman otomatis dari IoT Device ke endpoint `/ingest` API backend.
- **UC08: Update Online Status:** Perubahan status aktif sensor (`is_online`) berdasarkan *heartbeat* aktivitas pengiriman data terakhir.

### Group C: Control & Configuration
- **UC03: Manage Areas, Sensors & Actuators:** Penambahan, pembaruan, dan penghapusan wilayah (Area) serta sensor dan aktuator yang terikat padanya.
- **UC04: Configure Sensor Thresholds:** Pengaturan ambang batas atas dan batas bawah pembacaan sensor untuk trigger alert anomali.
- **UC05: Configure Automation Rules:** Pengaturan otomasi pompa/aktuator berdasarkan jadwal harian (`AreaScheduleRule`) atau pembacaan kondisi sensor (`AreaConditionRule`).
- **UC06: Manual Trigger Actuator:** Melakukan override paksa status aktuator (pompa 'ON' atau 'OFF') melalui perintah API/Dashboard.

### Group D: Research Data Access
- **UC09: Request Dataset:** Mengajukan formulir permohonan unduh dataset telemetry historis dengan menyertakan berkas pdf proposal/izin.
- **UC10: Review Dataset Request:** Validasi permohonan data oleh Administrator (Approved/Rejected).
- **UC11: Download Dataset:** Melakukan unduh berkas data ekspor dalam format CSV/JSON setelah mendapat token unduh yang disetujui.
