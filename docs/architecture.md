# Technical Architecture - iSURF Project

Dokumen ini merinci tumpukan teknologi (*tech stack*), pola arsitektur, dan keputusan teknis yang diambil dalam pengembangan proyek **iSURF**.

## 1. Backend API Layer (`apps/api`)
Backend utama dibangun menggunakan **FastAPI**, framework Python modern yang berfokus pada performa tinggi dan *type-safety*.

### 1.1 Stack Teknologi
- **Framework:** FastAPI
- **Server:** Uvicorn (ASGI)
- **ORM:** SQLAlchemy 2.0 (menggunakan pola *Declarative Mapping*)
- **Database Driver:** PyMySQL
- **Validation:** Pydantic 2.0
- **Security:** JWT (JSON Web Tokens), Passlib (Bcrypt) for password hashing.

### 1.2 Pola Arsitektur
Mengikuti **Layered Architecture** untuk pemisahan tanggung jawab yang jelas:
- **Routers:** Menangani endpoint API, validasi input (Pydantic), dan injeksi dependensi.
- **Services:** Menampung logika bisnis utama dan orkestrasi antar repository.
- **Repositories:** Menangani query database langsung menggunakan SQLAlchemy.
- **Schemas:** Definisi Pydantic untuk request dan response (DTO).
- **Models:** Definisi tabel database SQLAlchemy.

---

## 2. Web Application Layer (`apps/web`)
Aplikasi web berbasis PHP untuk manajemen internal dan monitoring dashboard.

### 2.1 Stack Teknologi
- **Framework:** Yii2 Advanced Project Template
- **Language:** PHP 7.4+ / 8.x
- **Frontend:** Bootstrap 5, jQuery
- **Asset Management:** Composer, AssetPackagist
- **Template Engine:** PHP Native Views / Twig

### 2.2 Struktur Aplikasi
- **Backend Tier:** Untuk fungsionalitas administratif tingkat tinggi.
- **Frontend Tier:** Untuk dashboard user umum dan monitoring.
- **Common Tier:** Berisi model dan konfigurasi yang digunakan bersama oleh frontend dan backend (misal: `User` model, `db` config).

---

## 3. Mobile Application Layer (`apps/mobile`)
Aplikasi mobile lintas platform untuk operasional di lapangan.

### 3.1 Stack Teknologi
- **Framework:** Flutter
- **Language:** Dart
- **State Management:** (TBD - based on project implementation)
- **API Client:** Http / Dio (untuk komunikasi dengan FastAPI)

---

## 4. IoT Layer (`apps/iot` & `hardware`)
Terdiri dari firmware perangkat keras dan API perantara.

### 4.1 Firmware (`hardware/firmware`)
- **Platform:** ESP32 (Arduino Framework)
- **Language:** C++
- **Libraries Utama:** WiFi, HTTPClient, ArduinoJson (untuk payload data sensor).

### 4.2 Local Monitoring API (`apps/iot/esp32-monitoring-api`)
- **Framework:** FastAPI
- **Database:** SQLite (menggunakan `aiosqlite` untuk akses asinkron).
- **Tujuan:** Bertindak sebagai *gateway* lokal atau *buffer* data sebelum dikirim ke server pusat.

---

## 5. Database Schema Strategy
- **MySQL/MariaDB:** Digunakan untuk penyimpanan relasional yang persisten di server pusat.
- **Migrations:**
  - **Yii2:** Menggunakan Yii2 built-in migrations (`php yii migrate`).
  - **FastAPI:** (Opsional) Direkomendasikan menggunakan **Alembic** untuk manajemen skema Python models.

---

## 6. Infrastructure & DevOps
- **Containerization:** Proyek ini siap-Docker dengan `Dockerfile` di masing-masing komponen utama.
- **CI/CD:** Menggunakan GitLab CI untuk build dan test otomatis.
- **Orchestration:** Konfigurasi Helm Chart tersedia untuk deployment ke kluster Kubernetes.
