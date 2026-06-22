# Ikhtisar Arsitektur iSURF

iSURF adalah sebuah platform pemantauan pertanian pintar yang dibangun dengan pendekatan modul terpisah.

**Alur Data:**
`Lingkungan` → `Sensor Fisik` → `ESP32` → `FastAPI Backend` → `Database MySQL` → `Yii2 Frontend` → `Browser Pengguna`.

1. **FastAPI (Port 8000)**: Otak utama dari sistem. Menerima *payload* JSON dari sensor dan mengevaluasi status otomatis pompa. Menyediakan REST API.
2. **Yii2 (Port 80/20080)**: Antarmuka (*User Interface*) yang menampilkan grafis dasbor dan tombol kendali manual.
3. **MySQL**: *Single source of truth*. Pusat seluruh rekam data.
4. **ESP32 Firmware**: Skrip `C++` yang menembakkan request POST berisi metrik dari lahan.

Keseluruhan sistem ini dapat dijalankan dalam lingkungan jaringan lokal (LAN) maupun di *deploy* ke Cloud VPS Publik untuk aksesibilitas global.
