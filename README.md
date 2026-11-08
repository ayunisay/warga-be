# SI-WARGA 07 — Backend RESTful API 🇮🇩

[![Laravel Version](https://img.shields.io/badge/Laravel-10.x-FF2D20?style=flat&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP Version](https://img.shields.io/badge/PHP-%5E8.1-777BB4?style=flat&logo=php&logoColor=white)](https://www.php.net)
[![Database](https://img.shields.io/badge/Database-PostgreSQL%20%7C%20Supabase-4169E1?style=flat&logo=postgresql&logoColor=white)](https://supabase.com)
[![Security](https://img.shields.io/badge/Security-UU%20PDP%20No.%2027%20Tahun%202022-2ea44f?style=flat&logo=shield)](https://peraturan.bpk.go.id/Details/229798/uu-no-27-tahun-2022)
[![Payments](https://img.shields.io/badge/Payments-Midtrans%20QRIS%20%26%20VA-0081A7?style=flat)](https://midtrans.com)
[![License](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)

Backend RESTful API untuk **Sistem Informasi dan Pengelolaan Layanan Warga RT 007 / RW 030, Kelurahan Teluk Pucung**. Dibangun dengan arsitektur modern berbasis **Laravel 10**, sistem ini mengintegrasikan perlindungan data pribadi (UU PDP), pembayaran digital multi-channel, penerbitan surat pengantar ber-QR code anti-pemalsuan, serta tata kelola administrasi kas lingkungan yang transparan.

---

## 📑 Daftar Isi

- [Fitur Utama](#-fitur-utama)
- [Keamanan & Kepatuhan UU PDP](#-keamanan--kepatuhan-uu-pdp)
- [Tech Stack](#-tech-stack)
- [Panduan Instalasi Lokal](#-panduan-instalasi-lokal)
- [Akun Demo & Hak Akses Role](#-akun-demo--hak-akses-role)
- [Dokumentasi Endpoint RESTful API](#-dokumentasi-endpoint-restful-api)
- [Simulasi & Pengujian Payment Gateway](#-simulasi--pengujian-payment-gateway)
- [Struktur Direktori](#-struktur-direktori)
- [Integrasi Frontend](#-integrasi-frontend)
- [Lisensi](#-lisensi)

---

## 🚀 Fitur Utama

### 1. 🔐 Autentikasi & Multi-Role Granular
- **Ketua RT**: Approval/penolakan surat pengantar, pengawasan mutasi kas, monitoring kependudukan & statistik wilayah.
- **Sekretaris**: Manajemen pendataan warga, penerbitan draf surat pengantar, publikasi informasi agenda & pengumuman.
- **Bendahara**: Verifikasi bukti transfer iuran warga, rekapitulasi kas masuk/keluar, dan monitoring payment gateway.
- **Warga**: Pengajuan permohonan surat pengantar, pembayaran iuran digital, pantau status pembayaran, dan tracking pengumuman RT.
- **Dual Login Identifiers**: Login fleksibel menggunakan kombinasi **Email** atau **NIK** terdaftar.

### 2. 💳 Dual-Mode Pembayaran Iuran Warga
- **Otomatis (Payment Gateway)**: Integrasi Midtrans / Tripay dengan dukungan **Dynamic QRIS** dan **Virtual Account** (BCA, BNI, BRI, Mandiri, Permata). Terintegrasi webhook otomatis dan simulator testing lokal.
- **Manual (Transfer Bank)**: Warga dapat mengunggah bukti struk transfer, yang selanjutnya masuk ke antrean verifikasi Bendahara RT.

### 3. 📄 Layanan Surat Pengantar Digital & Validasi QR Code
- Alur pengajuan surat online mandiri dengan tracking status (*Menunggu Verifikasi*, *Disetujui*, *Ditolak*).
- Setiap surat yang disetujui memiliki **Token Kriptografi Unik** (`verify_token`) dan QR Code yang dapat dipindai publik untuk memvalidasi keaslian dokumen secara instan tanpa login.

### 4. 👥 Manajemen Kependudukan & Demografi
- Pencatatan kepala keluarga & anggota keluarga (NIK, No. KK, alamat, blok, nomor rumah, pekerjaan, status iuran).
- Statistik agregasi otomatis: distribusi warga per blok hunian, piramida usia (balita, usia produktif, lansia), dan rasio gender.

### 5. 💰 Transparansi Kas & Finansial RT
- Pencatatan seluruh arus kas pemasukan dan pengeluaran secara terperinci.
- Summary saldo kas real-time yang dapat diakses oleh pengurus maupun warga.

---

## 🛡️ Keamanan & Kepatuhan UU PDP

Aplikasi ini mengadopsi standar **Undang-Undang Pelindungan Data Pribadi (UU PDP No. 27 Tahun 2022)** dalam penanganan data sensitif kependudukan:

1. **Field-Level Encryption (FLE) at Rest**:
   - Kolom data sensitif seperti `nik` dan `no_kk` dienkripsi di level database menggunakan algoritma **AES-256-CBC** (`Crypt::encryptString`).
2. **Blind Indexing (HMAC-SHA256)**:
   - Untuk memfasilitasi pencarian exact-match data NIK/KK terenkripsi tanpa membocorkan kunci enkripsi, sistem memanfaatkan kolom hash blind index (`nik_bindex`, `no_kk_bindex`) yang digenerate dengan `hash_hmac('sha256', $value, APP_KEY)`.
3. **Automatic Data Masking**:
   - Respon API secara default menyertakan atribut sensor data privasi (`nikMasked`, `noKkMasked`, `teleponMasked`) untuk melindungi privasi di tampilan antarmuka.

---

## 🛠️ Tech Stack

| Komponen | Spesifikasi / Library |
|---|---|
| **Runtime & Framework** | PHP `^8.1`, Laravel `10.10+` |
| **Autentikasi** | Laravel Sanctum |
| **Database** | PostgreSQL / Supabase Cloud (kompatibel juga dengan MySQL) |
| **Enkripsi** | OpenSSL AES-256-CBC & HMAC-SHA256 |
| **Payment Gateway** | Midtrans Core API (Dynamic QRIS, Bank Virtual Account) |
| **Tooling & Linter** | Composer, Artisan CLI, Laravel Pint |

---

## ⚙️ Panduan Instalasi Lokal

Ikuti langkah-langkah berikut untuk menjalankan backend ini di lingkungan komputer lokal:

### 1. Masuk ke Direktori Proyek
```bash
cd c:/laragon/www/warga-be
```

### 2. Install Dependensi PHP
```bash
composer install
```

### 3. Konfigurasi Environment (`.env`)
Salin berkas `.env.example` menjadi `.env`:
```bash
cp .env.example .env
```

Buka berkas `.env` dan konfigurasikan koneksi database Anda:

#### Opsi A: Menggunakan Supabase Cloud (Bawaan Default)
```env
DB_CONNECTION=pgsql
DB_HOST=aws-0-ap-northeast-1.pooler.supabase.com
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres.your_supabase_user
DB_PASSWORD=your_supabase_password_here
DB_SSLMODE=require
```

#### Opsi B: Menggunakan PostgreSQL / MySQL Lokal (Laragon / Docker)
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=warga_db
DB_USERNAME=postgres
DB_PASSWORD=secret
DB_SSLMODE=prefer
```

### 4. Generate Kunci Aplikasi (Application Key)
```bash
php artisan key:generate
```
> [!IMPORTANT]
> `APP_KEY` digunakan sebagai salt enkripsi data NIK/KK dan verifikasi token surat. Pastikan file `.env` aman dan tidak ter-commit ke public repository!

### 5. Buat Storage Symlink
Dibutuhkan agar berkas bukti transfer dan lampiran surat dapat diakses publik melalui URL:
```bash
php artisan storage:link
```

### 6. Jalankan Migrasi & Database Seeder
Jalankan migrasi skema tabel serta pengisian data demo awal (pengurus, sample warga, transaksi, dan pengumuman):
```bash
php artisan migrate:fresh --seed
```

### 7. Jalankan Server Development
```bash
php artisan serve --port=8001
```
Aplikasi backend API akan berjalan di: **`http://localhost:8001`** (atau `http://127.0.0.1:8000`).

---

## 🔑 Akun Demo & Hak Akses Role

Semua akun pengurus dan warga contoh hasil seeding menggunakan kata sandi default: **`password`**.

| Role | Email Login | Alternatif NIK | Password | Keterangan Jabatan |
|---|---|---|---|---|
| **Ketua RT** | `ketua.rt007@gmail.com` | `3275011204850001` | `password` | H. Bambang Sugiarto, S.T. |
| **Sekretaris** | `sekretaris.rt007@gmail.com` | `3275018809910008` | `password` | Ahmad Fauzi |
| **Bendahara** | `bendahara.rt007@gmail.com` | `3275016703800007` | `password` | Hj. Endang Suryaningsih |
| **Warga** | `andi.pratama@gmail.com` | `3275012301900003` | `password` | Andi Pratama (Blok B2 No. 14) |

> [!TIP]
> Endpoint `/api/login` juga mendukung payload `{ "role": "Ketua RT" }` untuk kemudahan pengujian cepat/demo instan di lingkungan pengembangan lokal.

---

## 📡 Dokumentasi Endpoint RESTful API

Semua endpoint API diawali dengan prefiks `/api`.

### 1. Autentikasi & Manajemen Pengguna
| Method | Endpoint | Deskripsi | Parameter / Body |
|---|---|---|---|
| `POST` | `/api/login` | Login user (via email, NIK, atau quick role) | `{ login, password }` atau `{ role }` |
| `POST` | `/api/logout` | Revoke token dan logout | - |
| `GET` | `/api/user` | Informasi profil pengguna yang sedang login | Bearer Token |
| `GET` | `/api/users` | Daftar seluruh akun pengurus & pengguna | - |
| `POST` | `/api/users` | Tambah akun pengguna baru | `{ name, email, nik, role, password, ... }` |
| `PUT` | `/api/users/{id}` | Edit profil dan role pengguna | Data update pengguna |
| `DELETE`| `/api/users/{id}` | Hapus akun pengguna | - |

### 2. Kependudukan (Data Warga)
| Method | Endpoint | Deskripsi | Parameter / Body |
|---|---|---|---|
| `GET` | `/api/citizens` | Ambil seluruh data warga (terfilter/search) | Query: `?search=&blok=&status_iuran=` |
| `POST` | `/api/citizens` | Tambah data warga baru | NIK, No. KK, Nama, Blok, No. Rumah, dll |
| `GET` | `/api/citizens/{id}` | Detail warga berdasarkan ID | - |
| `PUT` | `/api/citizens/{id}` | Perbarui data warga | Data update warga |
| `DELETE`| `/api/citizens/{id}` | Hapus data warga | - |
| `PATCH`| `/api/citizens/{id}/toggle-iuran` | Ganti status lunas/belum iuran bulanan | - |

### 3. Keuangan & Kas RT
| Method | Endpoint | Deskripsi | Parameter / Body |
|---|---|---|---|
| `GET` | `/api/transactions` | Riwayat transaksi kas masuk & keluar | Query: `?kategori=&bulan=&tahun=` |
| `POST` | `/api/transactions` | Catat transaksi keuangan baru | `{ judul, kategori, nominal, tanggal, keterangan }` |
| `GET` | `/api/transactions/summary`| Ringkasan total pemasukan, pengeluaran, saldo | - |

### 4. Verifikasi Bukti Transfer Iuran (Manual)
| Method | Endpoint | Deskripsi | Parameter / Body |
|---|---|---|---|
| `GET` | `/api/transfers` | Daftar antrean verifikasi bukti transfer | Query: `?status=Menunggu Verifikasi` |
| `POST` | `/api/transfers` | Warga unggah bukti transfer baru | `multipart/form-data`: `citizen_id, nominal, bukti_transfer` |
| `POST` | `/api/transfers/verify/{id}` | Bendahara menyetujui / menolak transfer | `{ status: "Terverifikasi" \| "Ditolak", catatan }` |

### 5. Payment Gateway Otomatis (QRIS & Virtual Account)
| Method | Endpoint | Deskripsi | Parameter / Body |
|---|---|---|---|
| `GET` | `/api/payments` | Monitoring status seluruh tagihan gateway | Query: `?status=&citizen_id=` |
| `POST` | `/api/payments/charge` | Generate Dynamic QRIS atau Virtual Account | `{ citizen_id, payment_type, bank, nominal, bulan }` |
| `GET` | `/api/payments/status/{orderId}` | Cek status pembayaran ke gateway | URL param: `orderId` |
| `POST` | `/api/payments/webhook` | Webhook receiver callback dari Midtrans | Midtrans Webhook Payload JSON |
| `POST` | `/api/payments/simulate/{orderId}`| **Simulator**: Simulasikan pelunasan instan | - |

### 6. Layanan E-Surat Pengantar RT
| Method | Endpoint | Deskripsi | Parameter / Body |
|---|---|---|---|
| `GET` | `/api/surat` | Daftar pengajuan surat warga | Query: `?nik=&status=` |
| `POST` | `/api/surat` | Pengajuan surat pengantar baru oleh warga | `{ pemohon, nik, jenis_surat, tujuan, keperluan }` |
| `PATCH`| `/api/surat/{id}/status` | Approval / Penolakan surat oleh Ketua RT | `{ status: "Disetujui" \| "Ditolak", catatan }` |
| `GET` | `/api/surat/verify/{token}` | **Verifikasi Publik**: Validasi keaslian surat via token QR Code | URL param: `token` |

### 7. Papan Informasi, Dashboard & Info RT
| Method | Endpoint | Deskripsi |
|---|---|---|
| `GET` | `/api/dashboard/stats` | Statistik demografi warga, piramida usia, dan saldo kas |
| `GET` | `/api/rt-info` | Informasi profil kepengurusan RT 007 / RW 030 |
| `GET` | `/api/announcements` | Daftar agenda, rapat, dan pengumuman kegiatan RT |
| `POST` | `/api/announcements` | Tambah pengumuman baru |
| `DELETE`| `/api/announcements/{id}` | Hapus pengumuman |

---

## 🧪 Simulasi & Pengujian Payment Gateway

Aplikasi menyediakan simulator bawaan untuk menguji alur pembayaran digital tanpa memerlukan kredensial gateway live:

1. **Buat Tagihan QRIS / Virtual Account**:
   ```bash
   curl -X POST http://localhost:8001/api/payments/charge \
     -H "Content-Type: application/json" \
     -d '{
       "citizen_id": 1,
       "payment_type": "qris",
       "nominal": 50000,
       "bulan": "November 2026"
     }'
   ```
   *Response akan mengembalikan `order_id` (misal: `INV-202611-0001`) dan `qris_url`.*

2. **Jalankan Simulasi Pelunasan Pembayaran**:
   ```bash
   curl -X POST http://localhost:8001/api/payments/simulate/INV-202611-0001
   ```
   *Sistem akan langsung mengubah status transaksi menjadi `settlement`, menambahkan saldo kas pemasukan RT, dan memperbarui status iuran warga terkait menjadi `Lunas`.*

---

## 📂 Struktur Direktori

```text
warga-be/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       ├── AuthController.php               # Login, NIK HMAC search, logout, users CRUD
│   │   │       ├── CitizenController.php            # Manajemen warga & toggle status iuran
│   │   │       ├── DashboardController.php          # Statistik ringkasan, demografi & info RT
│   │   │       ├── PaymentController.php            # Midtrans Dynamic QRIS, VA, webhook & simulator
│   │   │       ├── SuratController.php              # Pengajuan e-surat & verifikasi token QR
│   │   │       ├── TransactionController.php        # Pencatatan mutasi kas & rekapitulasi
│   │   │       └── TransferVerificationController.php # Verifikasi bukti transfer struk bank
│   │   └── Middleware/                              # Autentikasi Sanctum & validasi role
│   └── Models/
│       ├── Announcement.php                         # Model pengumuman & agenda
│       ├── Citizen.php                              # Model warga (FLE AES-256 + Blind Index)
│       ├── PaymentTransaction.php                   # Model transaksi gateway (QRIS/VA)
│       ├── RtInfo.php                               # Model profil identitas RT
│       ├── Surat.php                                # Model pengantar surat digital
│       ├── Transaction.php                          # Model kas RT (Pemasukan/Pengeluaran)
│       ├── TransferVerification.php                 # Model verifikasi transfer manual
│       └── User.php                                 # Model user pengurus & warga
├── config/                                          # Konfigurasi aplikasi, cors, database, sanctum
├── database/
│   ├── migrations/                                  # Skema migrasi database & relasi tabel
│   └── seeders/
│       └── DatabaseSeeder.php                       # Data awal RT, pengurus, warga, & riwayat kas
├── routes/
│   └── api.php                                      # Seluruh rute RESTful API sistem
└── storage/
    └── app/public/                                  # Berkas bukti transfer & lampiran publik
```

---

## 🔗 Integrasi Frontend

Backend API ini dirancang untuk berpasangan secara mulus dengan **`warga-fe`** (React + Vite + Tailwind CSS):

- Konfigurasi **CORS** telah disetujui di `config/cors.php` untuk menerima request dari origin aplikasi frontend.
- Pastikan berkas `.env` pada project `warga-fe` mengarahkan URL API ke backend ini:
  ```env
  VITE_API_URL=http://localhost:8001/api
  ```

---

## 📜 Lisensi

Proyek ini dilisensikan di bawah [MIT License](https://opensource.org/licenses/MIT). Dikembangkan untuk mendigitalisasi dan meningkatkan transparansi tata kelola pelayanan warga RT/RW di Indonesia.
