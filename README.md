# **Kerja Bro**
> Sistem Pelaporan Pekerjaan Divisi/Instalasi IT

[![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/manual/en/index.php)
[![Javascript](https://img.shields.io/badge/JavaScript-323330?style=for-the-badge&logo=javascript&logoColor=F7DF1E)](https://developer.mozilla.org/en-US/docs/Learn/Getting_started_with_the_web/JavaScript_basics?retiredLocale=id)
[![Html5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)](https://developer.mozilla.org/en-US/docs/Web/HTML)
[![Css](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)](https://developer.mozilla.org/en-US/docs/Web/CSS)

---

## ✨ Fitur Utama
- [ ] Dashboard & Laporan
- [ ] Manajemen Master Data (CRUD)
- [ ] Manajemen Data Pekerjaan (Input pekerjaan & daftar pekerjaan)
- [ ] Ekspor (Excel/Word)
- [ ] Auto Create User

---

## 🧰 Teknologi
- **Framework**: Laravel 10.x (PHP ^8.1)
- **Database**: MySQL
- **Front-end**: Blade (Bootstrap & Soft UI)
- **Dev Env**: Laragon (Windows)

---

## ✅ Persyaratan Sistem
- PHP **8.1+**
- Composer **2.x**
- MySQL**8.0+**

> **Laragon (Windows)** sangat direkomendasikan untuk pengembangan lokal.

---

## Tangkapan Layar
- Dashboard Admin
![Dashboard](public/img/dashboard.png)



## 🚀 Instalasi


### 1) Pasang Dependensi
```bash
composer install
```

### 2) Konfigurasi
```bash
cp .env.example .env
php artisan key:generate
```
Ubah variabel di `.env` sesuai kebutuhan (lihat tabel di bawah).

### 3) Siapkan Database
```bash
php artisan migrate --seed
```

### 4) Buat Symlink Storage
```bash
php artisan storage:link
```

### 5) Jalankan Aplikasi
```bash
# Development server
php artisan serve

```


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
