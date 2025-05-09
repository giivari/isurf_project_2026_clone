# PEMBANGUNAN APLIKASI ERP PETROLAB BERBASISKAN RBAC DAN SSO

> 
> Dibuat oleh:  
> * DrEng. Heru Sukoco, S.Si., M.T.  
> * Deputi Manajer  
> * Divisi Teknologi Informasi (TI)  
> * PT. Petrolab Services  
> 
> Tanggal dibuat: Selasa, 04 Februari 2025  
> Tanggal diperbarui: Senin, 05 Mei 2025  
> Versi: 1.0 - Revisi: 0
> 

## 📚 PANDUAN KOMPREHENSIF UNTUK ERP PETROLAB

### Section 1: Types of ERP Petrolab

- [ ] ...
- [ ] ...
- [ ] ...


### Section 2: Benefits of ERP Petrolab

- [ ] ...
- [ ] ...
- [ ] ...

### Section 3: ERP Management System

- [ ] ...
- [ ] ...
- [ ] ...

### Section 4: Market Trends in ERP Petrolab

- [ ] ...
- [ ] ...
- [ ] ...


## ✅ TUJUAN

Proyek ini bertujuan untuk membangun aplikasi **United Tractor (Digitalisasi UT)** berbasis Yii2 (frontend, backend, MVC, API) dan Flutter (mobile frontend) yang dikelola dengan GitLab, metode percabangan (branching) dan bukan pembagian folder. Untuk itu, pembangunan sistem **United Tractor (Digitalisasi UT)** dengan sistem GitLab Workflow.

## 📚 KONSEP UTAMA

Dalam pembangunan aplikasi UT, repository yang digunakan untuk aplikasi bisnis UT menggunakan sistem repository tunggal dengan pembagian sebagai berikut:  
•	Master branch **main** adalah untuk versi stabil dan siap di*deploy*.  
•	Setiap **fitur baru**, **perbaikan bug**, atau **pengembangan module** dibuat di **branch baru**  
•	Pakai GitLab **Merge Request (MR)** untuk penggabungan  
•	Pakai GitLab **CI/CD** untuk otomatisasi (sifatnya opsional)  

## 🛠️ LINGKUNGAN PENGEMBANGAN 

Aplikasi UT dikembangkan menggunakan template **BASIC dari Yii2** untuk aplikasi berbasiskan **web**, **Flutter dan DART** untuk aplikasi berbasiskan **mobile**, dan **MySQL/MariaDB** untuk sistem manajemen basis datanya.

Templat proyek BASIC dari Yii 2 merupakan sebuah kerangka (*skeleton*) aplikasi yang terbaik untuk membuat proyek kecil secara cepat [Yii 2](http://www.yiiframework.com/).

Template BASIC terdiri dari fitur-fitur dasar termasuk  **user login/logout** dan **contact page**. Template tersebut menyertakan semua hal-hal umum yang digunakan konfigurasi yang akan mengizinkan pengembang fokus pada penambahan fitur-fitur baru di aplikasi yang dikembangkan.

[![Latest Stable Version](https://poser.pugx.org/yiisoft/yii2-app-basic/v/stable.png)](https://packagist.org/packages/yiisoft/yii2-app-basic)
[![Total Downloads](https://poser.pugx.org/yiisoft/yii2-app-basic/downloads.png)](https://packagist.org/packages/yiisoft/yii2-app-basic)
[![Build Status](https://travis-ci.org/yiisoft/yii2-app-basic.svg?branch=master)](https://travis-ci.org/yiisoft/yii2-app-basic)

## 📁 STRUKTUR DIREKTORI


```yii2

Yii 2 Basic Tamplate  

ut-apps/   
├── api/           # REST API jika diperlukan. Aplikasi API untuk koneksi ke berbagai aplikasi eksternal  
├── assets/        # contains assets definition    
├── commands/      # contains console commands  (controllers)  
├── config/        # contains application  configurations  
├── controllers/   # contains Web controller classes  
├── mail/          # contains view files for e-mails  
├── models/        # contains model classes  
├── runtime/       # contains files generated during runtime  
├── tests/         # contains various tests for the basic application  
├── vendor/        # contains dependent 3rd-party packages  
├── views/         # contains view files for the Web application  
├── web/           # contains the entry script and Web resources    
├── mobile             # Aplikasi mobile Flutter  
│  
├── docs/              # Dokumentasi proyek  
│   ├── user-manual/   # Panduan pengguna (DOCX, PDF)  
│   ├── tech-docs/     # Dokumentasi teknis (API, sistem)  
│   ├── api-specs/     # OpenAPI/Swagger, Postman, ERD  
│   └── releases/      # Release notes, changelog, etc.  
│   └── architecture.md  
│  
├── infra/  
│   ├── docker/        # Dockerfiles, docker-compose  
│   ├── helm-chart/    # Deployment Helm Charts (K8s)  
│   ├── terraform/     # Jika menggunakan IaaC  
│   └── firebase/      # firebase.json, hosting config  
│  
├── tests/  
│   ├── api/           # REST API tests  
│   ├── backend/       # PHPUnit for backend tests
│   ├── frontend/      # PHPUnit for frontend tests
│   └── mobile/        # Flutter unit/widget tests 
```

```yii2

Yii Advanced Template

rbac-sso/  
├── apps/                 # Aplikasi DIGDAYA::MATASAPI berbasiskan Yii2 Advanced, Flutter, IoT  
|   ├── api/              # REST API jika diperlukan. Aplikasi API untuk koneksi ke berbagai aplikasi eksternal  
│       ├── modules/  
│       │   ├── pap/       # Registrasi Ternak, identifikasi induk  
│       │   │   ├── controllers/  
│       │   │   ├── models/  
│       │   │   ├── views/  
│       │   │   └── Module.php  
│       │   ├── vhms/       # Monitoring aktivitas harian, status ternak  
│       │   ├── pstr/       # Pencatatan konsumsi pakan  


│   ├── backend/          # Aplikasi web untuk admin atau internal  
│   │   ├── assets/       # Asset bundle (JS/CSS)  
│   │   ├── config/       # Konfigurasi backend (routing, db, params, modules)  
│   │   ├── controllers/  # Controller untuk modul backend  
│   │   ├── models/       # Model khusus backend (bisa override common/models)  
│   │   ├── modules/  
│   │   │   ├── pap/       # Registrasi Ternak, identifikasi induk  
│   │   │   │   ├── controllers/  
│   │   │   │   ├── models/  
│   │   │   │   ├── views/  
│   │   │   │   └── Module.php  
│   │   │   ├── vhms/       # Monitoring aktivitas harian, status ternak  
│   │   │   ├── pstr/            # Pencatatan konsumsi pakan  
│   │   │   ├── kesehatan/        # Riwayat pemeriksaan, vaksinasi  
│   │   │   ├── sertifikasi/      # Data pelatihan & sertifikat pengurus SPR
│   │   │   ├── silsilah/         # Pohon keluarga ternak
│   │   │   ├── ekonomi/          # Manajemen penjualan, pembelian, laba rugi
│   │   │   ├── harga/            # Prediksi harga ternak, estimasi pasar
│   │   │   └── notifikasi/       # Kirim notifikasi via email/whatsapp/telegram
│   │   ├── views/        # Tampilan (HTML/PHP) untuk backend  
│   │   └── web/          # Root web server untuk backend (index.php, assets)  
│   │  
│   ├── common/           # File bersama antara backend & frontend  
│   │   ├── config/       # Konfigurasi umum (database, params global)  
│   │   ├── mail/         # Template email (HTML/TXT)  
│   │   ├── models/       # Model umum (User, LoginForm, dsb)  
│   │   └── tests/        # Unit test untuk kode umum  
|   │  
|   ├── console/          # Aplikasi berbasis command line  
|   │   ├── config/       # Konfigurasi CLI  
|   │   ├── controllers/  # Console command (misal `yii migrate`)  
|   │   ├── migrations/   # File migrasi database (php class)  
|   │   └── models/       # Model khusus CLI jika dibutuhkan  
|   │  
|   ├── environments/     # Template environment (`dev`, `prod`) config  
|   │   ├── dev/          # Konfigurasi untuk pengembangan (debug, Gii)  
|   │   └── prod/         # Konfigurasi produksi (caching, no-debug)  
|   │  
|   ├── frontend/         # Aplikasi publik (web user biasa)  
|   │   ├── assets/       # Asset bundle frontend  
|   │   ├── config/       # Konfigurasi frontend  
|   │   ├── controllers/  # Controller pengguna  
|   │   ├── models/       # Model frontend (override common/models jika perlu)  
|   │   ├── views/        # Template tampilan (HTML)  
|   │   └── web/          # Web root untuk frontend  
|   ├── migration/        # Script model untuk migrasi  
|   ├── rbac/             # setup script roles & permissions  
|   │  
|   ├── tests/            # Tes kode Yii2 (unit, functional, acceptance)  
|   │   ├── codeception/  # Framework testing bawaan Yii2  
|   │  
|   ├── vendor/           # Autoload composer (library pihak ketiga)  
|   ├── composer.json     # File dependency PHP composer  
|   ├── yii               # Entrypoint CLI (migrate, serve, dsb)  
|   ├── init              # Skrip untuk menginisialisasi `dev` atau `prod`  
|   ├
|   ├── mobile            # Aplikasi mobile Flutter  
|   │   |    
|   │   ├── android/        # Project Android (Java/Kotlin)  
|   │   ├── ios/            # Project iOS (Swift/ObjC)  
|   │   ├── linux/          # Project Linux Desktop (opsional)  
|   │   ├── macos/          # Project macOS Desktop (opsional)  
|   │   ├── windows/        # Project Windows Desktop (opsional)  
|   │   ├── web/            # Project Web (HTML, JS, manifest)  
|   │   │  
|   │   ├── lib/            # Kode utama aplikasi Flutter  
|   │   │   ├── main.dart   # Entry point (fungsi main)  
|   │   │   ├── ui/         # Widget, tampilan (View)  
|   │   │   ├── models/     # Struktur data, class model  
|   │   │   ├── services/   # API service, logic, repository  
|   │   │   ├── screens/    # Halaman-halaman utama  
|   │   │   ├── routes/     # Pengaturan navigasi & routing  
|   │   │   └── utils/      # Helper, constant, theme, dsb  
|   │   │  
|   │   ├── assets/         # Gambar, ikon, font, dll  
|   │   │   ├── images/     # Gambar  
|   │   │   ├── fonts/      # Font  
|   │   │   └── lottie/     #   
|   │   │  
|   │   ├── test/           # Unit test dan widget test  
|   │   │   └── widget_test.dart  
|   │   │  
|   │   ├── .dart_tool/           # File internal build (otomatis)  
|   │   ├── .idea/                # Pengaturan IDE (JetBrains, opsional)  
|   │   ├── build/                # Output hasil build (otomatis)  
|   │   ├── .vscode/              # Pengaturan VS Code (opsional)  
|   │   │  
|   │   ├── pubspec.yaml          # Konfigurasi dependencies, assets  
|   │   ├── analysis_options.yaml # Linter & pedoman kode  
|   │   ├── README.md             # Deskripsi proyek  
|   │   ├── .gitignore  
|   │   └── .metadata  
|   │
├── docs/              # Dokumentasi proyek  
│   ├── user-manual/   # Panduan pengguna (DOCX, PDF)  
│   ├── tech-docs/     # Dokumentasi teknis (API, sistem)  
│   ├── api-specs/     # OpenAPI/Swagger, Postman, ERD  
│   └── releases/      # Release notes, changelog, etc.  
│   └── architecture.md  
│  
├── infra/  
│   ├── docker/        # Dockerfiles, docker-compose  
│   ├── helm-chart/    # Deployment Helm Charts (K8s)  
│   ├── terraform/     # Jika menggunakan IaaC  
│   └── firebase/      # firebase.json, hosting config  
│  
├── tests/  
│   ├── api/           # REST API tests  
│   ├── backend/       # PHPUnit for backend tests  
│   ├── frontend/      # PHPUnit for frontend tests  
│   └── mobile/        # Flutter unit/widget tests  
│  
├── .gitlab-ci.yml     # GitLab CI/CD pipeline utama    
├── README.md          # Readme first for your brief guidance
└── .editorconfig      # Standarisasi editor  
```

## 📋 PERSYARATAN

The minimum requirement by this project template that your Web server supports PHP 5.4.0.


## 🎯 REPOSITORY DI GITLAB

>
> 1. Remote - Clone with SSH: [git@gitlab.com:div.it.petrolab/ut2017.git](git@gitlab.com:div.it.petrolab/ut2017.git)  
> 2. Remote - Clone with HTTPS: [https://gitlab.com/div.it.petrolab/ut2017.git](https://gitlab.com/div.it.petrolab/ut2017.git)  
> 


## 🌿 GITLAB BRANCHING STRATEGY

|   Tipe Branch   |         Nama          |                Keterangan             |
|-----------------|-----------------------|---------------------------------------|
| Main Branch     | master                  | Branch produksi/stabil. Production-ready code. Otomatis deploy ke server. |
| Development Branch   | develop               | Branch pengembangan aktif. Integrasi dari semua fitur. Otomatis build dan testing. |
| Feature Branch  | feature/*  | Untuk pengembangan fitur baru. Basis: develop, ex: feature/add-animal-tracking |
| Bugfix Branch   | bugfix/*     | Untuk memperbaiki bug kecil (minor) [bug-desc]. Basis: develop. ex: bugfix/rev-full-name |
| Hotfix Branch   | hotfix/* | Perbaikan kritis langsung ke produksi [critical-fix]. Perbaikan kritis di production. Basis: main, merge ke main & develop. |
| Release Branch  | release/<versi>       | Persiapan rilis besar, ex: release/v1.0.0 |


🧱 INSTALLATION
------------

### Install via Composer

If you do not have [Composer](http://getcomposer.org/), you may install it by following the instructions
at [getcomposer.org](http://getcomposer.org/doc/00-intro.md#installation-nix).

You can then install this project template using the following command:

~~~
php composer.phar global require "fxp/composer-asset-plugin:^1.3.1"
php composer.phar create-project --prefer-dist --stability=dev yiisoft/yii2-app-basic basic
~~~

Now you should be able to access the application through the following URL, assuming `basic` is the directory
directly under the Web root.

~~~
http://localhost/basic/web/
~~~


### Install from an Archive File

Extract the archive file downloaded from [yiiframework.com](http://www.yiiframework.com/download/) to
a directory named `basic` that is directly under the Web root.

Set cookie validation key in `config/web.php` file to some random secret string:

```php
'request' => [
    // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
    'cookieValidationKey' => '<secret random string goes here>',
],
```

You can then access the application through the following URL:

~~~
http://localhost/basic/web/
~~~


⚙️ CONFIGURATION
-------------

### Database

Edit the file `config/db.php` with real data, for example:

```php
return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=yii2basic',
    'username' => 'root',
    'password' => '1234',
    'charset' => 'utf8',
];
```

**NOTES:**
- Yii won't create the database for you, this has to be done manually before you can access it.
- Check and edit the other files in the `config/` directory to customize your application as required.
- Refer to the README in the `tests` directory for information specific to basic application tests.



TESTING
-------

Tests are located in `tests` directory. They are developed with [Codeception PHP Testing Framework](http://codeception.com/).
By default there are 3 test suites:

- `unit`
- `functional`
- `acceptance`

Tests can be executed by running

```
vendor/bin/codecept run
``` 

The command above will execute unit and functional tests. Unit tests are testing the system components, while functional
tests are for testing user interaction. Acceptance tests are disabled by default as they require additional setup since
they perform testing in real browser. 


### Running  acceptance tests

To execute acceptance tests do the following:  

1. Rename `tests/acceptance.suite.yml.example` to `tests/acceptance.suite.yml` to enable suite configuration

2. Replace `codeception/base` package in `composer.json` with `codeception/codeception` to install full featured
   version of Codeception

3. Update dependencies with Composer 

    ```
    composer update  
    ```

4. Download [Selenium Server](http://www.seleniumhq.org/download/) and launch it:

    ```
    java -jar ~/selenium-server-standalone-x.xx.x.jar
    ```

    In case of using Selenium Server 3.0 with Firefox browser since v48 or Google Chrome since v53 you must download [GeckoDriver](https://github.com/mozilla/geckodriver/releases) or [ChromeDriver](https://sites.google.com/a/chromium.org/chromedriver/downloads) and launch Selenium with it:

    ```
    # for Firefox
    java -jar -Dwebdriver.gecko.driver=~/geckodriver ~/selenium-server-standalone-3.xx.x.jar
    
    # for Google Chrome
    java -jar -Dwebdriver.chrome.driver=~/chromedriver ~/selenium-server-standalone-3.xx.x.jar
    ``` 
    
    As an alternative way you can use already configured Docker container with older versions of Selenium and Firefox:
    
    ```
    docker run --net=host selenium/standalone-firefox:2.53.0
    ```

5. (Optional) Create `yii2_basic_tests` database and update it by applying migrations if you have them.

   ```
   tests/bin/yii migrate
   ```

   The database configuration can be found at `config/test_db.php`.


6. Start web server:

    ```
    tests/bin/yii serve
    ```

7. Now you can run all available tests

   ```
   # run all available tests
   vendor/bin/codecept run

   # run acceptance tests
   vendor/bin/codecept run acceptance

   # run only unit and functional tests
   vendor/bin/codecept run unit,functional
   ```

### Code coverage support

By default, code coverage is disabled in `codeception.yml` configuration file, you should uncomment needed rows to be able
to collect code coverage. You can run your tests and collect coverage with the following command:

```
#collect coverage for all tests
vendor/bin/codecept run -- --coverage-html --coverage-xml

#collect coverage only for unit tests
vendor/bin/codecept run unit -- --coverage-html --coverage-xml

#collect coverage for unit and functional tests
vendor/bin/codecept run functional,unit -- --coverage-html --coverage-xml
```

You can see code coverage output under the `tests/_output` directory.




