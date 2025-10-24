# **Nama Proyek Laravel**
> Deskripsi singkat proyek Anda dalam 1–2 kalimat. Misalnya: _Aplikasi manajemen ...._

![PHP](https://img.shields.io/badge/PHP-%5E8.1-blue)
![Laravel](https://img.shields.io/badge/Laravel-10.x-red)
![License](https://img.shields.io/badge/License-MIT-green)
![CI](https://img.shields.io/badge/CI-GitHub%20Actions-gray)

---

## ✨ Fitur Utama
- [ ] Autentikasi & Otorisasi (Laravel Breeze/Fortify)
- [ ] Manajemen Master Data (CRUD)
- [ ] Impor/Ekspor (Excel/PDF)
- [ ] Notifikasi (Email/WA gateway opsional)
- [ ] Dashboard & Laporan
- [ ] Integrasi API (opsional)

> Tandai fitur yang sudah ada, hapus yang tidak perlu.

---

## 🧰 Teknologi
- **Framework**: Laravel 10.x (PHP ^8.1)
- **Database**: MySQL/MariaDB
- **Cache/Queue**: Redis/Database
- **Front-end**: Blade + Vite (Bootstrap/Tailwind)
- **Testing**: Pest/PHPUnit
- **Dev Env**: Laragon (Windows) / PHP-FPM + Nginx (Linux/macOS)

---

## ✅ Persyaratan Sistem
- PHP **8.1+** dengan ekstensi: `OpenSSL`, `PDO`, `Mbstring`, `Tokenizer`, `XML`, `Ctype`, `JSON`, `BCMath`, `Fileinfo`
- Composer **2.x**
- MySQL/MariaDB **10.4+**
- Node.js **18+** & npm **9+** (untuk build aset)
- (Opsional) Redis **6+**

> **Laragon (Windows)** sangat direkomendasikan untuk pengembangan lokal.

---

## 🚀 Instalasi

### 1) Kloning Proyek
```bash
# via HTTPS
git clone https://github.com/<username>/<repo>.git
cd <repo>
# atau via SSH
# git clone git@github.com:<username>/<repo>.git
```

### 2) Pasang Dependensi
```bash
composer install
npm install
```

### 3) Konfigurasi Lingkungan
```bash
cp .env.example .env
php artisan key:generate
```
Ubah variabel di `.env` sesuai kebutuhan (lihat tabel di bawah).

### 4) Siapkan Database
```bash
php artisan migrate --seed
```

### 5) Buat Symlink Storage
```bash
php artisan storage:link
```

### 6) Jalankan Aplikasi
```bash
# Development server
php artisan serve
# atau, jika butuh host/port tertentu
# php artisan serve --host=127.0.0.1 --port=8000
# atau contoh di jaringan lokal:
# php artisan serve --host=192.168.0.22 --port=1212
```

### 7) Jalankan Vite (Aset Front-end)
```bash
# mode dev (HMR)
npm run dev
# build produksi
npm run build
```

---

## ⚙️ Variabel Lingkungan (.env)

| Key | Contoh | Keterangan |
|---|---|---|
| `APP_NAME` | `Laravel App` | Nama aplikasi |
| `APP_ENV` | `local` | `local`, `staging`, `production` |
| `APP_KEY` | _(otomatis)_ | Kunci enkripsi |
| `APP_DEBUG` | `true` | Matikan di produksi |
| `APP_URL` | `http://localhost:8000` | URL aplikasi |
| `TIMEZONE` | `Asia/Jakarta` | Zona waktu aplikasi |
| `LOG_CHANNEL` | `stack` | Kanal log |
| `DB_CONNECTION` | `mysql` | Driver DB |
| `DB_HOST` | `127.0.0.1` | Host DB |
| `DB_PORT` | `3306` | Port DB |
| `DB_DATABASE` | `db_app` | Nama DB |
| `DB_USERNAME` | `root` | User DB |
| `DB_PASSWORD` | `` | Password DB |
| `CACHE_DRIVER` | `file` | `file`, `redis`, dll |
| `SESSION_DRIVER` | `file` | `file`, `redis` |
| `QUEUE_CONNECTION` | `database` | `sync`, `database`, `redis` |
| `FILESYSTEM_DISK` | `public` | Disk default |
| `MAIL_MAILER` | `smtp` | Mailer |
| `MAIL_HOST` | `smtp.mailtrap.io` | Host SMTP |
| `MAIL_PORT` | `2525` | Port SMTP |
| `MAIL_USERNAME` | `` | Username |
| `MAIL_PASSWORD` | `` | Password |
| `MAIL_ENCRYPTION` | `tls` | TLS/SSL |
| `MAIL_FROM_ADDRESS` | `no-reply@example.com` | Sender |
| `MAIL_FROM_NAME` | `${APP_NAME}` | Nama pengirim |

> Tambahkan variabel spesifik proyek (API key, dsb) bila diperlukan.

---

## 🗃️ Struktur Folder Singkat
```
app/            # kode aplikasi (Models, Services, etc)
bootstrap/      # bootstrap framework
config/         # konfigurasi
database/       # migrations, seeders, factories
public/         # web root (index.php, aset build)
resources/      # views (Blade), CSS/JS (Vite)
routes/         # web.php, api.php, console.php
storage/        # logs, cache, uploads
tests/          # test unit/feature
```

---

## 🧵 Queue & Scheduler

### Queue Worker
```bash
# gunakan database/redis sesuai QUEUE_CONNECTION
php artisan queue:work
# atau agar robust
php artisan queue:work --tries=3 --max-time=3600
```

### Scheduler (Cron)
**Linux/macOS**:
Tambahkan ke crontab:
```
* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1
```

**Windows (Laragon/Task Scheduler)**:
Buat _Basic Task_ yang menjalankan per menit:
```
php C:\laragon\www\<repo>\artisan schedule:run
```

---

## 🧪 Testing
```bash
# PHPUnit / Pest (pilih salah satu)
php artisan test
# atau
vendor/bin/pest
```

Contoh membuat test:
```bash
php artisan make:test UserCanLoginTest
```

---

## 🧹 Code Style (Opsional)
```bash
# PHP-CS-Fixer
composer require --dev friendsofphp/php-cs-fixer
vendor/bin/php-cs-fixer fix
```

---

## 📦 Deployment Ringkas
1. `git pull` / deploy artefak
2. `composer install --no-dev --prefer-dist --optimize-autoloader`
3. `php artisan config:cache && php artisan route:cache && php artisan view:cache`
4. Jalankan migrasi jika perlu `php artisan migrate --force`
5. `npm ci && npm run build` (jika server build aset)
6. Pastikan queue worker & scheduler aktif

> Gunakan `.env.production` yang aman & matikan `APP_DEBUG`.

---

## 🌿 Alur Git yang Disarankan
```bash
# buat branch fitur
git checkout -b feat/nama-fitur
# commit kecil & deskriptif
git add .
git commit -m "feat: tambah modul laporan retur obat"
# dorong ke GitHub
git push -u origin feat/nama-fitur
# buat Pull Request di GitHub
```

---

## 🛠️ Troubleshooting Umum
- **`Failed opening required 'vendor/autoload.php'`**  
  Jalankan `composer install` di root proyek.
- **`APP_KEY` kosong / enkripsi gagal**  
  Jalankan `php artisan key:generate` lalu bersihkan cache: `php artisan config:clear`.
- **Port sudah dipakai saat `artisan serve`**  
  Ganti port: `php artisan serve --port=8001`.
- **Zona waktu salah**  
  Set `TIMEZONE=Asia/Jakarta` di `.env` dan atur `date.timezone` pada `php.ini`.
- **Storage 404 saat akses upload**  
  Pastikan `php artisan storage:link` dan gunakan `asset('storage/...')`.
- **Migrasi gagal karena versi MySQL**  
  Cek kolom `string` panjang & `engine`, sesuaikan `config/database.php` (charset `utf8mb4`).

---

## 📜 Lisensi
Distribusi mengikuti lisensi **MIT** (atau lisensi lain yang Anda pilih).

---

## 🤝 Kontribusi
- Fork repo, buat branch fitur, ajukan Pull Request.
- Ikuti standar commit: `feat:`, `fix:`, `docs:`, `refactor:`, `chore:`.

---

## 📧 Kontak
- Author: _Nama Anda_
- Email: _email@domain.com_
- URL: _https://domain.com_

> Hapus bagian yang tidak relevan dan ganti placeholder sesuai proyek Anda.
