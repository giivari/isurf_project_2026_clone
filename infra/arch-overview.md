# Ikhtisar Infrastruktur iSURF

Infrastruktur iSURF dideploy secara terisolasi menggunakan **Docker Compose** yang merangkum:

- `frontend`: Web server PHP untuk Yii2 Dashboard (Port host: 20080).
- `backend`: Server Uvicorn untuk FastAPI (Port host: 21080 / 8000).
- `mysql`: Database server (MySQL 5.7).

Pendekatan dockerisasi ini memungkinkan instalasi instan di laptop *developer* mana pun maupun kemudahan relokasi peladen (VPS *migration*) tanpa konfigurasi ulang sistem operasi.
