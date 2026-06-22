# iSURF — IoT for Smart Urban Farming Laboratory

Proyek ini adalah implementasi dari sistem **iSurf Lab (IoT for Smart Urban Farming Laboratory)** untuk Departemen Ilmu Komputer FMIPA IPB. 

Sistem ini memfasilitasi pemantauan lingkungan *Greenhouse* (suhu, kelembaban, level air, pH, TDS) secara *real-time* dan melakukan kontrol irigasi/nutrisi baik secara otomatis maupun manual melalui sebuah antarmuka web terpusat.

## Arsitektur Sistem (3-Tier)

1. **IoT Edge Devices**: Menggunakan ESP32 / Arduino untuk mengakuisisi data sensor dan mengendalikan aktuator.
2. **Backend API**: Menggunakan **FastAPI** (Python) untuk *high-concurrency data ingestion* dan *background automation*.
3. **Frontend Dashboard**: Menggunakan **Yii2 Advanced Template** (PHP) dan *Chart.js* untuk visualisasi analitik web.

## Struktur Direktori

```text
ilkom-isurf-project/
├── apps/
│   ├── api/          # Backend FastAPI Python
│   ├── iot/          # Kode mikrokontroler (Arduino/ESP32)
│   ├── mobile/       # Aplikasi mobile pendukung (Flutter)
│   ├── rbac/         # Module SSO / RBAC
│   └── web/          # Frontend Web Dashboard (Yii2 PHP)
├── assets/           # Kumpulan file aset dokumentasi
├── capstone/         # Laporan akademik dan diagram arsitektur
├── docs/             # Dokumentasi pengembangan sistem
├── history/          # Riwayat pengembangan proyek sebelumnya
├── infra/            # Skrip konfigurasi infrastruktur (Docker)
└── refs/             # Referensi akademik dan jurnal
```

## Persyaratan (Prerequisites)

- Python 3.10+
- PHP 7.4+ atau 8.x
- Composer
- MySQL 5.7+ atau MariaDB
- Node.js (untuk keperluan asset management, opsional)

## Cara Menjalankan di Lokal (Development)

### 1. Backend (FastAPI)
```bash
cd apps/api
# Salin konfigurasi environment
cp .env.example .env
# Sesuaikan DATABASE_URL di dalam .env dengan koneksi MySQL Anda

# Buat virtual environment dan install dependencies
python -m venv venv
venv\Scripts\activate  # Untuk Windows
pip install -r requirements.txt

# Jalankan server
uvicorn app.main:app --reload
```
Server backend akan berjalan di `http://127.0.0.1:8000`.

*(Opsional) Masukkan data dummy:*
```bash
python seed_dummy.py
```

### 2. Frontend (Yii2)
```bash
cd apps/web
composer install

# Inisialisasi environment Yii2
php init --env=Development --overwrite=y

# Jalankan server
php yii serve --docroot=frontend/web
```
Dashboard akan dapat diakses melalui browser di alamat `http://localhost:8080`.

## Branching Strategy

Pola pengembangan menggunakan metode Git Flow sederhana:
* `master` : kode produksi yang stabil
* `development` : kode pengembangan sebelum masuk master
* `feature/nama-fitur` : untuk pengerjaan fitur baru

## Kredit

Dikembangkan untuk revitalisasi fasilitas laboratorium iSurf di lingkungan Institut Pertanian Bogor.
