# Activity Diagram - iSURF Project

Dokumen ini merinci Activity Diagram untuk masing-masing Use Case dalam sistem **iSURF (Integrated Smart Urban Farming)**. Setiap diagram aktivitas menggambarkan aliran kontrol operasional sistem secara konseptual dan abstrak tanpa detail teknis atau referensi implementasi kode program.

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
Menggambarkan alur aktivitas saat pengguna melakukan masuk log (login) ke platform.

```mermaid
flowchart TD
    Start([Mulai]) --> FillForm[Pengguna memasukkan Username & Password]
    FillForm --> ValidateForm[Sistem memvalidasi format masukan]
    ValidateForm --> CheckFormat{Format masukan benar?}
    
    CheckFormat -- Tidak --> ErrFormat[Tampilkan kesalahan format masukan] --> End([Selesai])
    CheckFormat -- Ya --> QueryUser[Sistem mencari data pengguna]
    
    QueryUser --> CheckUserExists{Pengguna ditemukan?}
    CheckUserExists -- Tidak --> ErrAuth[Tampilkan pesan kredensial salah] --> End
    CheckUserExists -- Ya --> HashCheck[Sistem mencocokkan kata sandi]
    
    HashCheck --> CheckPassword{Kata sandi cocok?}
    CheckPassword -- Tidak --> ErrAuth
    CheckPassword -- Ya --> GenToken[Sistem menerbitkan akses masuk]
    GenToken --> SuccessToken[Arahkan pengguna ke dasbor sesuai peran] --> End
```

**Penjelasan Alur:**
1. Pengguna memasukkan nama pengguna dan kata sandi.
2. Sistem memeriksa format masukan. Jika tidak sesuai ketentuan, proses dibatalkan dengan menampilkan pesan kesalahan format.
3. Sistem memeriksa keberadaan nama pengguna. Jika tidak ada di sistem, akses ditolak dengan menampilkan pesan kesalahan kredensial.
4. Jika nama pengguna terdaftar, sistem mencocokkan kata sandi. Jika tidak cocok, akses ditolak.
5. Jika kata sandi cocok, sistem menerbitkan akses otentikasi dan mengarahkan pengguna ke dasbor utama sesuai perannya.

---

### UC02: Monitoring Real-time Area Data
Menggambarkan alur aktivitas pengguna saat memantau data kondisi lahan pertanian secara real-time pada dasbor.

```mermaid
flowchart TD
    Start([Mulai]) --> SelectArea[Pengguna memilih wilayah pertanian]
    SelectArea --> FetchData[Sistem memuat data telemetri terbaru & grafik riwayat]
    FetchData --> RenderMetrics[Dasbor menampilkan indikator sensor]
    FetchData --> RenderCharts[Dasbor menampilkan grafik tren berkala]
    
    RenderMetrics --> PollLoop[Sistem memperbarui data secara berkala]
    RenderCharts --> PollLoop
    PollLoop --> FetchData
```

**Penjelasan Alur:**
1. Pengguna membuka dasbor pemantauan dan memilih salah satu wilayah pertanian (seperti area Greenhouse atau Nursery).
2. Sistem memuat informasi pembacaan sensor terbaru serta riwayat grafik dari wilayah tersebut.
3. Dasbor menampilkan data terkini dalam bentuk widget indikator (kelembaban, pH, TDS, suhu) dan kurva tren historis.
4. Sistem melakukan pembaruan berkala secara otomatis untuk memastikan data dasbor selalu mutakhir.

---

### UC03: Manage Areas, Sensors & Actuators
Menggambarkan alur pengelolaan data master wilayah (Area), perangkat sensor, dan aktuator kontrol oleh Administrator.

```mermaid
flowchart TD
    Start([Mulai]) --> ChooseAction[Pilih Aksi: Tambah / Ubah / Hapus]
    
    %% CREATE Flow
    ChooseAction -- Tambah --> FormCreate[Submit data baru]
    FormCreate --> CheckDuplicate{Data sudah terdaftar?}
    CheckDuplicate -- Ya --> ErrDuplicate[Tampilkan pesan data sudah ada] --> End([Selesai])
    CheckDuplicate -- Tidak --> InsertMaster[Simpan data baru] --> SuccessCreate[Tampilkan pesan sukses pembuatan] --> End
    
    %% UPDATE Flow
    ChooseAction -- Ubah --> FormUpdate[Kirim data pembaruan]
    FormUpdate --> QueryMaster[Cari data target berdasarkan ID]
    QueryMaster --> CheckExist{Ditemukan?}
    CheckExist -- Tidak --> Err404[Tampilkan pesan data tidak ditemukan] --> End
    CheckExist -- Ya --> SaveUpdate[Simpan perubahan data] --> SuccessUpdate[Tampilkan pesan sukses perubahan] --> End
    
    %% DELETE Flow
    ChooseAction -- Hapus --> ReqDelete[Minta penghapusan data berdasarkan ID]
    ReqDelete --> QueryMasterDelete[Cari data target berdasarkan ID]
    QueryMasterDelete --> CheckExistDelete{Ditemukan?}
    CheckExistDelete -- Tidak --> Err404
    CheckExistDelete -- Ya --> DeleteCascade[Hapus data beserta seluruh log terkait] --> SuccessDelete[Tampilkan notifikasi penghapusan sukses] --> End
```

**Penjelasan Alur:**
1. Administrator memilih menu penambahan, pembaruan, atau penghapusan wilayah, sensor, atau aktuator.
2. **Tambah:** Sistem menguji keunikan data masukan. Jika data sudah ada, sistem menolaknya. Jika baru, data disimpan.
3. **Ubah:** Sistem memverifikasi keberadaan data master sebelum menyimpan perubahan informasi yang diinput administrator.
4. **Hapus:** Sistem menghapus data target beserta seluruh riwayat transaksi log data dan aturan aturan otomatisasi yang melekat pada data tersebut.

---

### UC04: Configure Sensor Thresholds
Menggambarkan pengaturan ambang batas aman peringatan sensor di suatu wilayah.

```mermaid
flowchart TD
    Start([Mulai]) --> InputThreshold[Admin menginput nilai batas bawah & batas atas baru]
    InputThreshold --> QuerySensors[Sistem mencari sensor bertipe sejenis di area target]
    
    QuerySensors --> CheckSensors{Sensor ditemukan?}
    CheckSensors -- Tidak --> ResZero[Tampilkan pesan tidak ada sensor yang disesuaikan] --> End([Selesai])
    CheckSensors -- Ya --> UpdateDB[Perbarui batas ambang aman pada sensor terkait]
    
    UpdateDB --> ResSuccess[Tampilkan notifikasi sukses konfigurasi ambang batas] --> End
```

**Penjelasan Alur:**
1. Administrator memasukkan ambang batas bawah (minimal) dan batas atas (maksimal) untuk tipe pengukuran sensor tertentu pada suatu area.
2. Sistem mencari seluruh sensor sejenis yang terpasang pada area pertanian tersebut.
3. Sistem memperbarui nilai konfigurasi ambang batas pada sensor-sensor tersebut dan menampilkan pemberitahuan berhasil kepada administrator.

---

### UC05: Configure Automation Rules
Menggambarkan pembuatan aturan logika otomasi peralatan kontrol irigasi.

```mermaid
flowchart TD
    Start([Mulai]) --> SelectRuleType[Pilih Tipe Aturan Otomasi]
    
    SelectRuleType -- Kondisi Sensor --> CreateCond[Input: jenis sensor, operator pemicu, nilai batas, aksi alat]
    CreateCond --> InsertCond[Simpan aturan logika kondisi sensor] --> Success([Sukses])
    
    SelectRuleType -- Waktu Terjadwal --> CreateSched[Input: waktu eksekusi, aksi alat]
    CreateSched --> InsertSched[Simpan aturan logika jadwal waktu] --> Success
    
    Success --> WorkerLoop[Sistem otomatis mengevaluasi aturan secara periodik] --> End([Selesai])
```

**Penjelasan Alur:**
1. Administrator menentukan logika kontrol peralatan irigasi otomatis untuk suatu wilayah pertanian.
2. **Aturan Kondisi:** Mengatur agar peralatan aktif berdasarkan pembacaan sensor (contoh: nyalakan pompa jika kelembaban tanah kurang dari 40%).
3. **Aturan Jadwal:** Mengatur agar peralatan aktif pada jam-jam tertentu (contoh: matikan pompa setiap jam 07:00).
4. Aturan disimpan dan dievaluasi terus-menerus oleh sistem pemantau latar belakang berdasarkan data telemetri terkini.

---

### UC06: Manual Trigger Actuator
Menggambarkan alur eksekusi ketika pengguna mengontrol saklar peralatan penyiraman secara manual.

```mermaid
flowchart TD
    Start([Mulai]) --> SelectActuator[Pilih alat kontrol & tentukan instruksi ON/OFF]
    SelectActuator --> QueryActuator[Sistem mencari data peralatan di database]
    
    QueryActuator --> CheckExist{Peralatan ditemukan?}
    CheckExist -- Tidak --> Err404[Tampilkan notifikasi tidak ditemukan] --> End([Selesai])
    CheckExist -- Ya --> CheckCommand{Tipe Instruksi?}
    
    %% ON Command Flow
    CheckCommand -- ON --> CheckWater{Apakah air tangki mencukupi?}
    CheckWater -- Tidak --> ErrFailsafe[Batalkan aksi: Tangki air kritis untuk perlindungan alat] --> End
    CheckWater -- Ya --> UpdateON[Aktifkan peralatan & ubah status menjadi ON] --> End
    
    %% OFF Command Flow
    CheckCommand -- OFF --> CalcWater[Kalkulasi durasi aktif alat & konsumsi air]
    CalcWater --> UpdateRemaining[Perbarui catatan log estimasi sisa air tangki]
    UpdateRemaining --> UpdateOFF[Matikan peralatan & ubah status menjadi OFF] --> End
```

**Penjelasan Alur:**
1. Pengguna menekan tombol saklar untuk menghidupkan atau mematikan peralatan aktuator (seperti pompa air).
2. **Nyalakan (ON):** Sistem menguji cadangan air tangki terlebih dahulu. Jika air berada pada level kritis, perintah ditolak demi melindungi pompa dari kerusakan akibat menyala tanpa air (*failsafe*). Jika aman, pompa dinyalakan.
3. **Matikan (OFF):** Sistem menghitung durasi aktif pompa, menghitung taksiran volume air yang telah disalurkan, memperbarui sisa persediaan tangki air, dan mencatat log penggunaan sebelum mematikan peralatan.

---

### UC07: Ingest Sensor Data
Menggambarkan pemrosesan data pembacaan sensor yang dikirimkan secara otomatis dari lapangan oleh modul IoT.

```mermaid
flowchart TD
    Start([Mulai]) --> IngestAPI[Sistem menerima kiriman paket data sensor dari perangkat IoT]
    IngestAPI --> LoopSensors[Uji setiap data sensor dalam paket]
    
    LoopSensors --> QuerySensor[Cari kesesuaian sensor terdaftar]
    QuerySensor --> CheckExist{Sensor terdaftar?}
    CheckExist -- Tidak --> NextSensor[Abaikan data sensor ini] --> LoopSensors
    
    CheckExist -- Ya --> UpdateHeartbeat[Perbarui status sensor aktif dan waktu kontak terakhir]
    UpdateHeartbeat --> CheckAnomaly{Nilai diluar ambang batas aman?}
    
    CheckAnomaly -- Ya --> LogAnomaly[Simpan log data dengan status Anomali/Kritis]
    LogAnomaly --> CreateAlert[Terbitkan peringatan darurat untuk operator] --> TriggerAuto
    
    CheckAnomaly -- Tidak --> LogNormal[Simpan log data dengan status Normal] --> TriggerAuto
    
    TriggerAuto[Picu evaluasi otomatisasi irigasi] --> TriggerAgg[Picu pembaruan agregasi statistik wilayah]
    TriggerAgg --> NextSensor
    
    NextSensor --> CheckLoopEnd{Semua sensor diproses?}
    CheckLoopEnd -- Tidak --> LoopSensors
    CheckLoopEnd -- Ya --> End([Selesai])
```

**Penjelasan Alur:**
1. Modul mikrokontroler mengirimkan sekumpulan data telemetry hasil sensor lapangan ke sistem.
2. Untuk setiap data sensor yang dikenali, status sensor ditandai aktif (*online*) dan waktu laporan diperbarui.
3. Jika nilai telemetry melanggar ambang batas aman, data dicatat sebagai anomali kritis dan sistem membuat peringatan (*alert*) darurat. Jika aman, data dicatat normal.
4. Sistem memicu evaluasi aturan otomatisasi irigasi wilayah serta perhitungan agregasi statistik wilayah di latar belakang agar respon ke perangkat IoT tetap cepat.

---

### UC08: Update Online Status
Menggambarkan deteksi status keaktifan koneksi sensor online/offline.

```mermaid
flowchart TD
    Start([Mulai]) --> FetchTrigger[Sistem memproses permintaan tampilan sensor]
    FetchTrigger --> LoopSensors[Pemeriksaan berkala aktivitas sensor]
    
    LoopSensors --> CalcDiff[Hitung selang waktu kontak telemetri terakhir]
    CalcDiff --> CheckTimeout{Waktu tunggu terlampaui?}
    
    CheckTimeout -- Ya --> SetOffline[Ubah status koneksi sensor menjadi offline] --> NextSensor
    CheckTimeout -- Tidak --> NextSensor[Pertahankan status koneksi sensor online]
    
    NextSensor --> CheckLoopEnd{Selesai semua sensor?}
    CheckLoopEnd -- Tidak --> LoopSensors
    CheckLoopEnd -- Ya --> ReturnData[Tampilkan status keaktifan sensor terbaru pada dasbor] --> End([Selesai])
```

**Penjelasan Alur:**
1. Sistem memantau keaktifan koneksi sensor saat dasbor diakses.
2. Sistem mengecek selang waktu kontak data sensor terakhir. Jika sensor tidak melaporkan telemetri dalam rentang waktu yang ditentukan (misalnya 5 menit), status konektivitas diubah menjadi tidak aktif (*offline*).
3. Status koneksi terbaru (online/offline) ditampilkan secara visual pada dasbor.

---

### UC09: Request Dataset
Menggambarkan alur pengajuan izin akses unduhan dataset historis oleh pengguna eksternal (peneliti/akademisi).

```mermaid
flowchart TD
    Start([Mulai]) --> FillForm[Pemohon mengisi formulir data & unggah Proposal]
    FillForm --> ValidatePDF{Dokumen berformat PDF?}
    
    ValidatePDF -- Tidak --> ErrPDF[Tampilkan pesan penolakan: Format dokumen wajib PDF] --> End([Selesai])
    ValidatePDF -- Ya --> SavePDF[Simpan proposal pdf di server]
    
    SavePDF --> GenCode[Generate Kode Pelacakan acak]
    GenCode --> InsertRequest[Daftarkan pengajuan sebagai status Tertunda/Pending]
    InsertRequest --> ResSuccess[Tampilkan Kode Pelacakan pengajuan ke pemohon] --> End
```

**Penjelasan Alur:**
1. Pemohon mengisi form identitas, tujuan riset, jangka waktu data, serta mengunggah surat izin/proposal riset.
2. Sistem menguji kesesuaian berkas unggahan (wajib dokumen PDF). Jika tidak sesuai, pengajuan ditolak.
3. Berkas PDF disimpan secara aman di direktori server.
4. Sistem membuat Kode Pelacakan unik (misal: "B7EF32A9") dan menyimpan data pengajuan dengan status tertunda (*pending*) agar pemohon dapat memantau status pengajuannya.

---

### UC10: Review Dataset Request
Menggambarkan proses peninjauan dan ulasan pengajuan data oleh Administrator.

```mermaid
flowchart TD
    Start([Mulai]) --> ViewRequests[Admin meninjau pengajuan dataset tertunda]
    ViewRequests --> ReviewAction[Admin memberikan keputusan: Setujui atau Tolak]
    
    ReviewAction --> CheckStatus{Keputusan Ulasan?}
    
    %% APPROVED FLOW
    CheckStatus -- Disetujui --> GenToken[Terbitkan Token Unduh Dataset yang unik & aman]
    GenToken --> UpdateApprove[Perbarui status disetujui, simpan Token, & catat identitas pengulas] --> Success[Tampilkan ulasan sukses] --> End([Selesai])
    
    %% REJECTED FLOW
    CheckStatus -- Ditolak --> UpdateReject[Perbarui status ditolak & catat ulasan penolakan admin] --> Success
```

**Penjelasan Alur:**
1. Administrator memeriksa kesesuaian metadata form pengajuan serta berkas dokumen proposal yang dilampirkan peneliti.
2. Jika disetujui (**Setujui**), sistem menerbitkan token keamanan unduh dataset yang unik. Token ini berfungsi sebagai kunci pembuka unduhan.
3. Jika ditolak (**Tolak**), status diubah menjadi ditolak tanpa menerbitkan token, disertai penulisan catatan alasan penolakan dari administrator.

---

### UC11: Download Dataset
Menggambarkan pengunduhan berkas dataset historis secara aman.

```mermaid
flowchart TD
    Start([Mulai]) --> RequestDownload[Pemohon memasukkan Token Unduh pada tautan download]
    RequestDownload --> CheckRequest{Token valid & pengajuan disetujui?}
    
    CheckRequest -- Tidak --> ErrAuth[Tampilkan pesan: Akses ditolak/Token kadaluarsa] --> End([Selesai])
    CheckRequest -- Ya --> ExtractParams[Sistem mengambil rentang waktu & daftar sensor terdaftar]
    
    ExtractParams --> QueryLogs[Sistem menarik seluruh log telemetry sensor di database]
    QueryLogs --> CheckLogs{Data telemetry ditemukan?}
    
    CheckLogs -- Tidak --> ErrEmpty[Tampilkan notifikasi: Data kosong untuk periode tersebut] --> End
    CheckLogs -- Ya --> GenCSV[Konversi baris data telemetry menjadi berkas file ekspor CSV]
    
    GenCSV --> StreamResponse[Kirim berkas file unduhan ke pemohon] --> End
```

**Penjelasan Alur:**
1. Pemohon mengakses tautan download dengan menyertakan token unduhan yang diterimias saat disetujui admin.
2. Sistem mengecek status pengajuan terkait token. Jika tidak valid atau pengajuan belum disetujui, sistem menampilkan kesalahan akses ditolak.
3. Jika disetujui, sistem membaca batas tanggal dan sensor yang diperbolehkan, menarik seluruh log telemetry terkait dari database, mengonversi data tersebut menjadi file format tabel (CSV), dan mengirimkannya sebagai file unduhan langsung kepada pemohon.
