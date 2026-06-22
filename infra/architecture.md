# Arsitektur Infrastruktur iSURF

Dokumen ini menjelaskan rancangan infrastruktur deployment untuk proyek iSurf Lab menggunakan pendekatan kontainerisasi (Docker Compose).

## Struktur Kontainer

Infrastruktur iSURF dirancang untuk berjalan di atas 3 *container* utama yang diatur melalui berkas `docker-compose.yml`:

1. **`backend` Container (FastAPI Python)**
   - Berisi interpretator Python 3.10+.
   - Menjalankan Uvicorn server yang mengekspos REST API.
   - Bertanggung jawab menjalankan skrip `automation_worker.py` di background (atau sebagai container terpisah tergantung beban).

2. **`frontend` Container (PHP Yii2)**
   - Menggunakan image web server (Apache/Nginx) lengkap dengan PHP 7.4/8.x.
   - Memproksikan web port ke `20080` (host).
   - Menghidangkan file statis (CSS/JS) dan HTML.

3. **`mysql` Container (Database)**
   - Menggunakan image resmi `mysql:5.7`.
   - Mengamankan data dalam volume terpisah agar tidak hilang saat container direstart.
   - Diakses oleh kedua container (backend & frontend) di atas melalui internal Docker network.

## Lingkungan VPS (Virtual Private Server)

Bila di-*deploy* ke lingkungan produksi:
- Seluruh container dijalankan di satu mesin *node* Linux (misal: Ubuntu 22.04) berkinerja tinggi.
- Sebuah Nginx di level host (*Reverse Proxy*) menangani sertifikat SSL/TLS (HTTPS) dan merutekan:
  - `api.isurf-lab.ipb.ac.id` ke container Backend.
  - `isurf-lab.ipb.ac.id` ke container Frontend.

Hal ini mengisolasi setiap komponen, memudahkan perbaikan bila salah satu *environment* memiliki isu *library* dependensi tanpa merusak komponen lainnya.
