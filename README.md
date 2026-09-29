# Sistem Informasi & Portal Layanan Warga RT - Backend API ðŸ‡®ðŸ‡©

Backend RESTful API untuk Sistem Informasi dan Pengelolaan Rukun Tetangga (RT). Dibangun menggunakan **Laravel 10**, menyediakan layanan autentikasi multi-role, pendataan warga, pencatatan keuangan/kas RT, verifikasi bukti bayar iuran, penerbitan surat pengantar, serta papan informasi digital warga.

---

## ðŸš€ Fitur Utama

- ðŸ” **Autentikasi & Multi-Role Akses (Laravel Sanctum)**:
  - **Ketua RT**: Akses penuh, persetujuan (*approval*) dan penolakan surat pengantar, monitoring seluruh modul.
  - **Sekretaris**: Manajemen data warga, arsip surat pengantar, pengelolaan pengumuman & agenda kegiatan.
  - **Bendahara**: Manajemen kas masuk/keluar, verifikasi bukti transfer iuran warga, rekapitulasi keuangan RT.
  - **Warga**: Portal mandiri pengajuan surat, pembayaran/upload bukti transfer iuran, dan melihat pengumuman.
- ðŸ‘¥ **Manajemen Data Warga**:
  - Pendataan kepala keluarga & anggota keluarga (NIK, No. KK, No. Rumah, Blok, Kontak, Status Iuran).
- ðŸ’° **Pengelolaan Kas & Iuran Warga**:
  - Pencatatan transaksi pemasukan dan pengeluaran kas RT.
  - Verifikasi bukti transfer bank warga (Menunggu Verifikasi, Terverifikasi, Ditolak).
  - Laporan & rekapitulasi kas berkala.
- ðŸ“„ **Layanan Surat Pengantar Digital**:
  - Alur pengajuan surat dari warga.
  - Persetujuan/penolakan dilengkapi catatan administratif RT.
  - Format cetak surat resmi siap unduh/cetak.
- ðŸ“¢ **Papan Informasi & Agenda Kegiatan**:
  - Publikasi pengumuman RT, jadwal kerja bakti, pertemuan warga, dan posyandu.

---

## ðŸ› ï¸ Tech Stack & Prasyarat

- **PHP**: `^8.1`
- **Framework**: [Laravel 10](https://laravel.com)
- **Database**: MySQL / MariaDB
- **Autentikasi**: Laravel Sanctum
- **Environment Lokal**: Laragon / XAMPP / Native CLI
- **Package Manager**: [Composer](https://getcomposer.org/)

---

## âš™ï¸ Panduan Instalasi Lokal

Ikuti langkah-langkah di bawah untuk menjalankan backend ini di komputer lokal:

### 1. Masuk ke Direktori Project
```bash
cd c:/laragon/www/warga-be
```

### 2. Install Dependensi PHP
```bash
composer install
```

### 3. Konfigurasi Environment (`.env`)
Salin file `.env.example` menjadi `.env`:
```bash
cp .env.example .env
```
Sesuaikan konfigurasi koneksi database pada file `.env`:
```env
APP_NAME="Warga RT App"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8001

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5433
DB_DATABASE=db_warga
DB_USERNAME=postgres
DB_PASSWORD=warga07
```

### 4. Generate Application Key
```bash
php artisan key:generate
```

### 5. Buat Database & Jalankan Migrasi
Pastikan PostgreSQL sudah berjalan (misal melalui Laragon/XAMPP), buat database bernama `warga_db`, lalu jalankan migrasi:
```bash
php artisan migrate
```
*(Opsional)* Jika ada seeder data pengurus & mock warga:
```bash
php artisan db:seed
```

### 6. Storage Symlink (Untuk File Bukti Transfer & Lampiran)
```bash
php artisan storage:link
```

### 7. Jalankan Server Development
```bash
php artisan serve
```
Aplikasi backend API dapat diakses di: **`http://localhost:8001`** atau `http://127.0.0.1:8000`.

---

## ðŸ›£ï¸ Rangkuman Endpoint API (Direncanakan / Arsitektur)

| Modul | Method | Endpoint | Keterangan |
|---|---|---|---|
| **Auth** | `POST` | `/api/login` | Login pengurus / warga |
| | `POST` | `/api/logout` | Logout & revoke token |
| | `GET` | `/api/user` | Profil pengguna aktif |
| **Warga** | `GET` | `/api/citizens` | Ambil daftar data seluruh warga |
| | `POST` | `/api/citizens` | Tambah data warga baru |
| | `PUT` | `/api/citizens/{id}` | Perbarui data warga |
| | `DELETE` | `/api/citizens/{id}` | Hapus data warga |
| **Keuangan** | `GET` | `/api/transactions` | Rekap transaksi kas RT |
| | `POST` | `/api/transactions` | Catat pemasukan / pengeluaran baru |
| | `GET` | `/api/transfers` | Daftar antrean verifikasi bukti transfer |
| | `POST` | `/api/transfers/verify/{id}` | Setujui / tolak verifikasi transfer |
| **Surat** | `GET` | `/api/surat` | Daftar pengajuan surat pengantar |
| | `POST` | `/api/surat` | Pengajuan surat pengantar baru |
| | `PATCH` | `/api/surat/{id}/status` | Approval / rejection surat pengantar |
| **Informasi** | `GET` | `/api/announcements` | Daftar pengumuman & agenda kegiatan |
| | `POST` | `/api/announcements` | Buat pengumuman baru |

---

## ðŸ“ Struktur Direktori Utama

```
warga-be/
â”œâ”€â”€ app/
â”‚   â”œâ”€â”€ Http/
â”‚   â”‚   â”œâ”€â”€ Controllers/   # Controller endpoint API (Warga, Keuangan, Surat, Informasi)
â”‚   â”‚   â””â”€â”€ Middleware/    # Role check & autentikasi
â”‚   â””â”€â”€ Models/            # Model Eloquent (User, Citizen, Transaction, Surat, Announcement)
â”œâ”€â”€ config/                # Konfigurasi aplikasi, database, sanctum, cors
â”œâ”€â”€ database/
â”‚   â”œâ”€â”€ migrations/        # Skema tabel database
â”‚   â””â”€â”€ seeders/           # Data dummy awal
â”œâ”€â”€ routes/
â”‚   â”œâ”€â”€ api.php            # Routing RESTful API
â”‚   â””â”€â”€ web.php            # Routing web
â””â”€â”€ storage/
    â””â”€â”€ app/public/        # Media upload (bukti transfer, berkas surat)
```

---

## ðŸ¤ Hubungan dengan Frontend

Backend ini dirancang berpasangan dengan frontend **`warga-fe`** (React + Vite). Pastikan CORS diaktifkan (`config/cors.php`) agar frontend dapat mengakses endpoint backend tanpa kendala lintas domain (*cross-origin*).

---

## ðŸ“„ Lisensi
Proyek ini dibuat untuk keperluan internal pengelolaan RT. Dilisensikan di bawah [MIT License](https://opensource.org/licenses/MIT).
