# Arsitektur Sistem iSURF

Sistem pemantauan dan kontrol cerdas iSURF menggunakan pendekatan arsitektur **3-Tier** yang memisahkan antara layer perangkat keras (IoT), layer logika bisnis (Backend), dan layer presentasi (Frontend).

## 1. IoT Hardware Layer (Edge)

Komponen fisik yang berada di lahan *Greenhouse*. Bertugas untuk membaca kondisi lingkungan secara fisik dan mengeksekusi perintah aktuator.

- **Mikrokontroler Utama**: ESP32 / NodeMCU / Arduino Mega dengan modul WiFi.
- **Sensor Node**: Perangkat yang membaca metrik suhu, kelembaban udara, kelembaban tanah, pH, TDS, dan ultrasonik (level air).
- **Actuator Node**: Relai yang mengontrol pompa sirkulasi air (NFT), pompa irigasi tetes, exhaust fan, dan katup solenoid.

> Komunikasi ke server dilakukan menggunakan modul WiFi built-in melalui protokol HTTP REST (menggunakan endpoint `/iot/ingest`).

## 2. Backend API Layer (Logika Inti)

Dibangun menggunakan **Python FastAPI**, layer ini sangat krusial karena melayani permintaan asinkron tinggi dari ratusan sensor secara real-time.

- **API Endpoints (`app/routers/`)**: Menangani penerimaan data IoT dan request dari Dashboard web.
- **Database (MySQL)**: Diakses menggunakan ORM SQLAlchemy. Tabel diatur secara *Area-centric* di mana sensor dan aktuator dikelompokkan ke dalam sebuah entitas `Area`.
- **Background Workers (`app/utils/automation.py`)**: Skrip terpisah yang secara terus-menerus membandingkan bacaan data dengan ambang batas (threshold), memicu alarm (*alerts*), dan mengubah status aktuator jika mode "Auto" aktif.
- **RBAC & Auth**: Menggunakan token JWT berbatas waktu untuk melindungi endpoint pengiriman perintah kontrol aktuator.

## 3. Frontend Web Layer (Presentasi)

Dibangun menggunakan **PHP Yii2 Advanced Template**, ini adalah antarmuka web yang diakses oleh para petani kota, asisten lab, dosen, dan mahasiswa.

- **Dashboard UI**: Menyajikan grafik fluktuasi cuaca harian dan level air menggunakan *Chart.js*.
- **Real-time AJAX Polling**: Komponen *JavaScript* memanggil API FastAPI secara berkala agar tampilan Dasbor dapat berubah seketika tanpa me-refresh halaman (menciptakan ilusi *Single Page Application*).
- **Data Request Portal**: Form untuk peneliti eksternal untuk mengunduh arsip jutaan data sensor setelah di-approve oleh admin.

## Keunggulan Arsitektur Saat Ini
Berbeda dengan desain monolitik pada iterasi tahun 2019, arsitektur *microservice-like* ini mengizinkan Backend Python memfokuskan CPU penuh untuk algoritma pemrosesan IoT, sementara web server PHP fokus memberikan HTML/CSS ke pengunjung biasa. Jika Dasbor sedang diakses oleh 1000 orang secara simultan, operasi otomasi pompa di Greenhouse tidak akan pernah terganggu oleh beban *traffic* website.
