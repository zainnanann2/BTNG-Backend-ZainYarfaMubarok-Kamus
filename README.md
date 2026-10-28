# Katalog Kamus Bahasa Daerah — REST API

REST API untuk mengelola katalog kosakata bahasa daerah: kata, bahasa daerah, arti
Indonesia, contoh kalimat, dan tingkat kesopanan (undak-usuk).

Dibangun dengan **Laravel 12**, **MySQL**, **Laravel Sanctum**, dan **Scramble** untuk
dokumentasi API interaktif.

> **Tugas Akhir BTNG Kelas Backend** — Zain Yarfa Mubarok

---

## Daftar Isi

- [Fitur](#fitur)
- [Teknologi](#teknologi)
- [Struktur Data](#struktur-data)
- [Bentuk Respons](#bentuk-respons)
- [Prasyarat](#prasyarat)
- [Instalasi Lokal](#instalasi-lokal)
- [Daftar Endpoint](#daftar-endpoint)
- [Contoh Pemakaian](#contoh-pemakaian)
- [Aturan Validasi](#aturan-validasi)
- [Dokumentasi API](#dokumentasi-api)
- [Koleksi Postman](#koleksi-postman)
- [Pengujian](#pengujian)
- [Deploy ke Clever Cloud](#deploy-ke-clever-cloud)
- [Deploy ke Railway](#deploy-ke-railway)
- [Struktur Proyek](#struktur-proyek)

---

## Fitur

**Wajib (sesuai rubrik)**

- CRUD penuh pada entitas `kamus` (Create, Read, Update, Delete)
- Migration database dengan kunci unik gabungan dan indeks
- API Resource yang membungkus setiap respons dalam amplop `message` + `data`
- Validasi request lewat Form Request dengan pesan berbahasa Indonesia
- Dokumentasi API interaktif (Scramble) dengan tombol *Try it out*

**Tambahan**

- Autentikasi token Bearer (Sanctum): register, login, profil, logout
- Pagination, pencarian, filter, dan pengurutan
- Endpoint statistik katalog untuk dashboard
- Amplop JSON seragam untuk **semua** kondisi gagal (401, 404, 422, 500)
- 35 feature test (163 assertion) yang semuanya lulus
- Koleksi Postman 17 request yang idempoten (aman dijalankan berulang)

---

## Teknologi

| Komponen | Versi / Keterangan |
| --- | --- |
| PHP | 8.2 |
| Framework | Laravel 12 |
| Database | MySQL 8 (utf8mb4) |
| Autentikasi | Laravel Sanctum |
| Dokumentasi | dedoc/scramble |
| Pengujian | PHPUnit (bawaan Laravel) |

---

## Struktur Data

Satu tabel utama: **`kamus`**

| Kolom | Tipe | Nullable | Keterangan |
| --- | --- | --- | --- |
| `id` | bigint unsigned | tidak | Primary key |
| `kata` | varchar(100) | tidak | Kosakata bahasa daerah |
| `bahasa_daerah` | varchar(50) | tidak | `Jawa`, `Sunda`, `Madura`, dan lainnya |
| `arti_indonesia` | varchar(255) | tidak | Padanan dalam bahasa Indonesia |
| `contoh_kalimat` | text | **ya** | Kalimat contoh pemakaian |
| `tingkat_kesopanan` | varchar(30) | tidak | Default `netral` |
| `created_at` | timestamp | ya | — |
| `updated_at` | timestamp | ya | — |

**Kunci unik gabungan:** `(kata, bahasa_daerah, tingkat_kesopanan)`

Artinya satu kata boleh muncul berkali-kali asalkan tingkat kesopanannya berbeda.
Contoh yang sah:

| kata | bahasa_daerah | tingkat_kesopanan | arti_indonesia |
| --- | --- | --- | --- |
| mangan | Jawa | ngoko | makan |
| nedha | Jawa | krama_madya | makan (halus) |
| dhahar | Jawa | krama_inggil | makan (untuk orang yang dihormati) |

Tetapi mendaftarkan `mangan / Jawa / ngoko` dua kali akan ditolak dengan **422**.

**Nilai `tingkat_kesopanan` yang dikenal:**

| Nilai | Keterangan |
| --- | --- |
| `ngoko` | Jawa: akrab, untuk teman sebaya |
| `ngoko_alus` | Jawa: ngoko yang menghormati lawan bicara |
| `krama_madya` | Jawa: sopan, untuk percakapan umum |
| `krama_inggil` | Jawa: halus, untuk orang yang dihormati |
| `loma` | Sunda: akrab |
| `lemes` | Sunda: halus |
| `netral` | Bahasa daerah tanpa tingkatan tutur (mis. Madura) |

---

## Bentuk Respons

Setiap respons — sukses maupun gagal — memakai amplop yang sama.

**Sukses**

```json
{
    "message": "Detail kata berhasil diambil",
    "data": {
        "id": 1,
        "kata": "mangan",
        "bahasa_daerah": "Jawa",
        "arti_indonesia": "makan",
        "contoh_kalimat": "Aku mangan sega goreng ing warung.",
        "tingkat_kesopanan": "ngoko",
        "dibuat_pada": "2026-10-27T15:52:08+00:00",
        "diperbarui_pada": "2026-10-27T15:52:08+00:00"
    }
}
```

**Sukses terpaginasi** — menambahkan `meta` dan `links`

```json
{
    "message": "Daftar kata berhasil diambil",
    "data": [ ... ],
    "meta": {
        "current_page": 1,
        "per_page": 10,
        "total": 68,
        "last_page": 7,
        "from": 1,
        "to": 10
    },
    "links": {
        "first": "...",
        "prev": null,
        "next": "...",
        "last": "..."
    }
}
```

**Gagal validasi — 422**

```json
{
    "message": "Kolom kata wajib diisi. (and 3 more errors)",
    "data": null,
    "errors": {
        "kata": ["Kolom kata wajib diisi."],
        "bahasa_daerah": ["Kolom bahasa daerah wajib diisi."]
    }
}
```

**Tidak terautentikasi — 401**

```json
{
    "message": "Tidak terautentikasi. Sertakan header Authorization: Bearer <token>.",
    "data": null
}
```

**Tidak ditemukan — 404**

```json
{
    "message": "Data tidak ditemukan.",
    "data": null
}
```

Karena `data` selalu ada (bernilai `null` saat gagal), klien cukup menulis satu jalur
pembacaan tanpa perlu memeriksa bentuk respons.

---

## Prasyarat

- PHP >= 8.2 dengan ekstensi `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`
- Composer 2
- MySQL 8 (atau MariaDB 10.6+)

---

## Instalasi Lokal

```bash
# 1. Ambil kode
git clone https://github.com/zainnanann2/BTNG-Backend-ZainYarfaMubarok-Kamus.git
cd BTNG-Backend-ZainYarfaMubarok-Kamus

# 2. Pasang dependensi
composer install

# 3. Siapkan konfigurasi
cp .env.example .env
php artisan key:generate

# 4. Buat database
mysql -u root -e "CREATE DATABASE btng_kamus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 5. Sesuaikan .env bila perlu
#    DB_DATABASE=btng_kamus
#    DB_USERNAME=root
#    DB_PASSWORD=

# 6. Jalankan migration dan seeder
php artisan migrate --seed

# 7. Jalankan server
php artisan serve
```

Aplikasi berjalan di `http://127.0.0.1:8000`. Membuka akar `/` akan langsung
dialihkan ke dokumentasi di `/docs/api`.

**Akun demo** dari seeder:

| Email | Password |
| --- | --- |
| `admin@kamus.test` | `password123` |

Seeder mengisi **68 entri** contoh: 53 Jawa, 12 Sunda, 3 Madura. Seeder bersifat
idempoten — menjalankan `php artisan db:seed` berkali-kali tidak menggandakan data.

---

## Daftar Endpoint

Semua endpoint berada di bawah prefix `/api/v1`.

### Umum

| Method | Endpoint | Keterangan |
| --- | --- | --- |
| GET | `/api/v1/health` | Status layanan |
| GET | `/api/v1/statistik` | Ringkasan katalog |

### Autentikasi

| Method | Endpoint | Butuh token | Keterangan |
| --- | --- | --- | --- |
| POST | `/api/v1/auth/register` | tidak | Daftar akun, terbit token |
| POST | `/api/v1/auth/login` | tidak | Login, terbit token |
| GET | `/api/v1/auth/me` | **ya** | Profil pengguna aktif |
| POST | `/api/v1/auth/logout` | **ya** | Cabut token aktif |

### Katalog Kamus

| Method | Endpoint | Keterangan |
| --- | --- | --- |
| GET | `/api/v1/kamus` | Daftar kata (paginasi, cari, filter, urut) |
| POST | `/api/v1/kamus` | Tambah kata |
| GET | `/api/v1/kamus/{id}` | Detail satu kata |
| PUT | `/api/v1/kamus/{id}` | Ubah seluruh field |
| PATCH | `/api/v1/kamus/{id}` | Ubah sebagian field |
| DELETE | `/api/v1/kamus/{id}` | Hapus kata |

**Parameter query untuk `GET /api/v1/kamus`:**

| Parameter | Nilai | Keterangan |
| --- | --- | --- |
| `search` | teks | Dicari pada `kata`, `arti_indonesia`, `contoh_kalimat` |
| `bahasa_daerah` | `Jawa`, `Sunda`, `Madura` | Filter persis |
| `tingkat_kesopanan` | lihat tabel tingkat | Filter persis |
| `sort_by` | `kata`, `arti_indonesia`, `bahasa_daerah`, `tingkat_kesopanan`, `created_at` | Default `kata` |
| `sort_dir` | `asc`, `desc` | Default `asc` |
| `per_page` | 1–100 | Default 10 |

Nilai `sort_by` di luar daftar putih akan diabaikan dan kembali ke `kata`, sehingga
klien tidak bisa mengurutkan berdasarkan kolom sembarang.

---

## Contoh Pemakaian

**Login**

```bash
curl -X POST http://127.0.0.1:8000/api/v1/auth/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@kamus.test","password":"password123"}'
```

**Ambil profil dengan token**

```bash
curl http://127.0.0.1:8000/api/v1/auth/me \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|xxxxxxxx"
```

**Cari kata "makan"**

```bash
curl "http://127.0.0.1:8000/api/v1/kamus?search=makan" \
  -H "Accept: application/json"
```

**Tambah kata**

```bash
curl -X POST http://127.0.0.1:8000/api/v1/kamus \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
        "kata": "weruh",
        "bahasa_daerah": "Jawa",
        "arti_indonesia": "melihat, mengetahui",
        "contoh_kalimat": "Aku weruh lintang ing langit.",
        "tingkat_kesopanan": "ngoko"
      }'
```

> **Catatan PowerShell:** `curl -d '{"..."}'` merusak JSON karena tanda kutip
> diproses shell. Simpan body ke berkas lalu pakai `--data-binary "@body.json"`.

---

## Aturan Validasi

**Tambah kata (`POST`)**

| Field | Aturan |
| --- | --- |
| `kata` | wajib, teks, maks 100, unik per kombinasi bahasa + tingkat |
| `bahasa_daerah` | wajib, teks, maks 50 |
| `arti_indonesia` | wajib, teks, maks 255 |
| `contoh_kalimat` | boleh kosong, teks, maks 500 |
| `tingkat_kesopanan` | opsional, salah satu nilai enum, default `netral` |

**Ubah kata (`PUT` / `PATCH`)**

Aturan sama, tetapi setiap field memakai `sometimes` — field yang tidak dikirim
tidak diubah. Pada `PUT`, klien dianjurkan mengirim seluruh field; pada `PATCH`,
cukup field yang ingin diubah.

**Normalisasi otomatis**

- `kata` dan `arti_indonesia` dipangkas spasi di awal/akhir
- `bahasa_daerah` dirapikan kapitalisasinya: `"  mADURA "` → `"Madura"`

---

## Dokumentasi API

| Alamat | Isi |
| --- | --- |
| `/docs/api` | Dokumentasi interaktif (Stoplight Elements) |
| `/docs/api.json` | Kontrak OpenAPI 3.1 (bisa di-import ke Postman/Insomnia) |

Di halaman `/docs/api` tersedia tombol **Try it out** untuk memanggil endpoint
langsung dari browser, dan tombol **Authorize** untuk menempelkan token Bearer.

Pada `APP_ENV=local`, dokumentasi selalu terbuka. Di produksi, akses dijaga oleh
gate `viewApiDocs` yang didefinisikan di `app/Providers/AppServiceProvider.php`.
Untuk membatasinya pada admin, ubah menjadi:

```php
Gate::define('viewApiDocs', fn ($user = null) => $user?->is_admin);
```

---

## Koleksi Postman

Berkas: `postman/Kamus-Bahasa-Daerah.postman_collection.json`

**Isi:** 17 request dalam 5 folder.

| Folder | Jumlah | Isi |
| --- | --- | --- |
| Health | 1 | Cek status |
| Auth | 4 | Register, Login, Profil, Logout |
| Kamus | 9 | Daftar, Cari, Detail, Tambah, Perbarui, Tambah (hapus), Hapus, 422 validasi, 422 duplikat |
| Statistik | 1 | Ringkasan katalog |
| Error Handling | 2 | 404, 401 |

**Cara pakai**

1. Import berkas tersebut ke Postman (`Ctrl+O` → Files)
2. Buka tab **Variables**, sesuaikan `baseUrl` (default `http://127.0.0.1:8000`)
3. Jalankan folder **Auth** → **Login** (token tersimpan otomatis)
4. Jalankan folder **Kamus** → **Statistik**

**Idempoten.** Kata unik dihasilkan pre-request script pada request yang
membutuhkannya, dan request **Hapus kata** hanya menghapus entri yang baru dibuatnya
sendiri. Koleksi aman dijalankan berulang tanpa bentrok dengan data seeder.

**Hasil uji:** 17 request, 69 assertion, **0 gagal** — dan tetap 0 gagal pada
eksekusi kedua tanpa reset database.

---

## Pengujian

```bash
php artisan test
```

Hasil saat ini:

```
Tests:    35 passed (163 assertions)
Duration: 2.7s
```

| Berkas | Cakupan |
| --- | --- |
| `tests/Feature/KamusApiTest.php` | 20 test — CRUD, paginasi, pencarian, filter, pengurutan, validasi, duplikat, 404 |
| `tests/Feature/AuthApiTest.php` | 8 test — register, login, token salah, profil, 401, logout mencabut token |
| `tests/Feature/StatistikApiTest.php` | 2 test — ringkasan terisi dan katalog kosong |

Pengujian memakai SQLite in-memory (`phpunit.xml`), sehingga tidak menyentuh
database pengembangan.

---

## Deploy ke Clever Cloud

**1. Buat aplikasi**

Di [console.clever-cloud.com](https://console.clever-cloud.com) → **Create** →
**an application** → pilih **PHP** → hubungkan repositori GitHub.

**2. Tambahkan MySQL add-on**

Pada aplikasi → **Add-ons** → **Add an add-on** → **MySQL**.

Add-on **tidak** mengisi `DB_HOST` / `DB_DATABASE` / `DB_USERNAME`. Ia menyuntikkan
variabel berawalan `MYSQL_ADDON_`:

| Variabel add-on | Isi |
| --- | --- |
| `MYSQL_ADDON_HOST` | host database |
| `MYSQL_ADDON_PORT` | port database |
| `MYSQL_ADDON_DB` | nama database |
| `MYSQL_ADDON_USER` | user database |
| `MYSQL_ADDON_PASSWORD` | password database |
| `MYSQL_ADDON_ROLE` | peran kredensial |

`config/database.php` sudah membaca `MYSQL_ADDON_*` sebagai cadangan bila `DB_*`
tidak ada, sehingga tidak perlu menyalin nilainya secara manual. Di lokal `DB_*`
tetap dipakai, jadi satu berkas konfigurasi berlaku untuk dua lingkungan.

**3. Isi environment variables**

| Variabel | Nilai |
| --- | --- |
| `APP_NAME` | `Kamus Bahasa Daerah API` |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_KEY` | hasil `php artisan key:generate --show` |
| `APP_URL` | `https://<app-anda>.cleverapps.io` |
| `APP_LOCALE` | `id` |
| `LOG_CHANNEL` | `stderr` |
| `CC_PHP_VERSION` | `8.2` |
| `CC_WEBROOT` | `/public` |
| `CC_PRE_RUN_HOOK` | `php artisan config:cache && php artisan migrate --force` |
| `CC_HEALTH_CHECK_PATH` | `/api/v1/health` |

Catatan tiap baris:

- `CC_WEBROOT=/public` wajib. Tanpa itu Clever Cloud menyajikan akar repositori,
  bukan direktori `public/`, sehingga aplikasi tidak dapat diakses.
  Berkas `.clever.cloud/php.json` juga disertakan dengan isi
  `{"webroot": "/public"}` sebagai lapisan kedua. Berkas itu tidak disebut dalam
  dokumentasi resmi Clever Cloud — mekanisme yang terdokumentasi adalah
  `CC_WEBROOT` — tetapi tetap disertakan karena diminta pada ketentuan tugas.
- `CC_PHP_VERSION` dipatok ke `8.2` agar sama dengan versi pengembangan
  (`8.2.12`). Tanpa itu Clever Cloud memakai versi PHP default yang lebih baru.
  Nilainya harus berbentuk `8` atau `8.2`, bukan `8.2.12`.
- `CC_PRE_RUN_HOOK` berjalan setelah build selesai dan sebelum aplikasi menerima
  trafik — saat itu variabel add-on sudah tersedia, jadi migrasi aman dijalankan
  di sini. `config:cache` juga diletakkan di sini, bukan di hook build, karena
  nilainya perlu membaca kredensial database yang baru ada pada tahap ini.
- `CC_HEALTH_CHECK_PATH` membuat health check Clever Cloud menembak endpoint
  aplikasi, bukan halaman dokumentasi.
- `LOG_CHANNEL=stderr` membuat log Laravel muncul di tab **Logs** Clever Cloud.

**4. Deploy**

Repositori sudah terhubung ke GitHub, jadi setiap `git push` ke `main`
memicu build dan deploy otomatis:

```bash
git push origin main
```

**5. Isi data awal** (sekali saja, dari tab **Console** Clever Cloud)

```bash
php artisan db:seed --force
```

Seeder bersifat idempoten (`updateOrCreate`), jadi menjalankannya dua kali tidak
menggandakan data.

**6. Verifikasi**

```bash
curl https://<app-anda>.cleverapps.io/api/v1/health
curl https://<app-anda>.cleverapps.io/docs/api
curl -o /dev/null -w "%{http_code}\n" https://<app-anda>.cleverapps.io/
```

Tiga hal yang diperiksa: health mengembalikan `200` dengan `{"status":"ok"}`,
dokumentasi terbuka tanpa 403, dan `/` membalas `302` ke `/docs/api`.

---

## Deploy ke Railway

Rubrik tugas mengizinkan layanan cloud selain Clever Cloud (Railway, Render,
Fly.io). Berkas `railway.json` di akar repositori sudah menyiapkan seluruh
kebutuhan deploy, jadi tidak ada Dockerfile dan tidak ada konfigurasi manual
yang rumit.

Railway membangun aplikasi ini dengan [Railpack](https://railpack.com/languages/php),
yang mendeteksi Laravel lewat keberadaan berkas `artisan` lalu secara otomatis:

- memasang dependensi Composer
- menetapkan document root ke `/app/public`
- membuat direktori `storage` dapat ditulis
- menjalankan server FrankenPHP pada port dari variabel `PORT`

**1. Buat proyek dan layanan database**

1. Buka [railway.com](https://railway.com) → **New Project** → **Deploy MySQL**.
   Tunggu sampai layanan MySQL selesai dibuat.
2. Pada kanvas yang sama, klik **Create** → **GitHub Repo** → pilih
   `zainnanann2/BTNG-Backend-ZainYarfaMubarok-Kamus`.

**2. Isi environment variables**

Buka layanan aplikasi (bukan MySQL) → tab **Variables** → tambahkan:

| Variabel | Nilai |
| --- | --- |
| `APP_NAME` | `Kamus Bahasa Daerah API` |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_KEY` | hasil `php artisan key:generate --show` di lokal |
| `APP_LOCALE` | `id` |
| `LOG_CHANNEL` | `stderr` |
| `DB_CONNECTION` | `mysql` |

`DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` **tidak
perlu diisi manual**. `config/database.php` sudah membaca variabel milik layanan
MySQL Railway (`MYSQLHOST`, `MYSQLPORT`, `MYSQLDATABASE`, `MYSQLUSER`,
`MYSQLPASSWORD`) sebagai cadangan.

Kalau ingin lebih eksplisit, Anda bisa mengisinya dengan *reference variable*
agar nilainya selalu mengikuti layanan MySQL:

```
DB_HOST     = ${{MySQL.MYSQLHOST}}
DB_PORT     = ${{MySQL.MYSQLPORT}}
DB_DATABASE = ${{MySQL.MYSQLDATABASE}}
DB_USERNAME = ${{MySQL.MYSQLUSER}}
DB_PASSWORD = ${{MySQL.MYSQLPASSWORD}}
```

Gunakan `MYSQLHOST` (host privat), bukan `MYSQL_PUBLIC_HOST`, karena aplikasi dan
database berada dalam satu proyek Railway.

**3. Deploy**

Railway langsung membangun begitu repositori terhubung. Setelah itu setiap
`git push` ke `main` memicu deploy baru secara otomatis.

**4. Migrasi dan data awal**

`railway.json` sudah menjalankan keduanya otomatis lewat `preDeployCommand`:

```
php artisan config:clear && php artisan migrate --force && php artisan db:seed --force
```

Ada dua hal yang perlu dipahami di sini:

- `config:clear` **wajib** dan bukan sekadar formalitas. Saat build, Railpack
  menjalankan `php artisan config:cache`, sehingga nilai konfigurasi — termasuk
  `APP_KEY` dan kredensial database — ikut terkunci ke dalam
  `bootstrap/cache/config.php` pada saat itu. Variabel lingkungan yang baru ada
  saat runtime tidak akan terbaca selama cache itu masih ada. Menghapusnya di
  tahap pre-deploy membuat aplikasi membaca variabel yang benar.
- Perintah pre-deploy berjalan setiap kali deploy, jadi seeder harus idempoten.
  Seeder di proyek ini memakai `updateOrCreate`, sehingga data tidak tergandakan.

**5. Buka domain publik**

Layanan aplikasi → **Settings** → **Networking** → **Generate Domain**.
Salin domain yang muncul (bentuknya `<nama>.up.railway.app`).

**6. Verifikasi**

```bash
curl https://<domain-anda>.up.railway.app/api/v1/health
curl -o /dev/null -w "%{http_code}\n" https://<domain-anda>.up.railway.app/docs/api
curl -o /dev/null -w "%{http_code}\n" https://<domain-anda>.up.railway.app/
```

Yang diharapkan: health `200` dengan `{"status":"ok"}`, dokumentasi `200`, dan
`/` membalas `302` ke `/docs/api`.

Health check Railway juga sudah diarahkan ke `/api/v1/health` lewat
`railway.json`, sehingga deploy dianggap gagal kalau aplikasi tidak benar-benar
hidup.

---

## Struktur Proyek

```
app/
├── Http/
│   ├── Controllers/Api/
│   │   ├── AuthController.php       register, login, me, logout
│   │   ├── KamusController.php      CRUD katalog
│   │   └── StatistikController.php  ringkasan dashboard
│   ├── Requests/
│   │   ├── LoginRequest.php
│   │   ├── RegisterRequest.php
│   │   ├── StoreKamusRequest.php
│   │   └── UpdateKamusRequest.php
│   ├── Resources/
│   │   └── KamusResource.php        bentuk data yang dikirim ke klien
│   └── Responses/
│       └── ApiResponse.php          trait amplop message + data
├── Models/
│   ├── Kamus.php                    scope cari, bahasa, tingkat
│   └── User.php
└── Providers/
    └── AppServiceProvider.php       gate dokumentasi, security scheme

bootstrap/app.php                    penanganan error JSON global
config/scramble.php                  judul, versi, deskripsi dokumentasi
config/cors.php                      izin lintas domain
database/
├── migrations/…_create_kamus_table.php
└── seeders/
    ├── DatabaseSeeder.php
    ├── KamusSeeder.php              68 entri contoh
    └── UserSeeder.php               akun demo
routes/api.php                       seluruh rute /api/v1
tests/Feature/                       35 feature test
postman/                             koleksi Postman
```

---

## Lisensi

MIT
