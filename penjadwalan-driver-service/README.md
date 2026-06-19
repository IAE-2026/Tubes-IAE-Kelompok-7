# 🚐 Penjadwalan Driver Service (Service B)

**Tugas 2 — Integrasi Aplikasi Enterprise (EAI)**

| Info | Detail |
|------|--------|
| **Mahasiswa** | Hafizh Rafi Maulana Suyufi |
| **NIM** | 102022400210 |
| **Service** | Service B: Penjadwalan Driver |
| **Resource** | `schedules` |
| **Framework** | Laravel 13 (PHP 8.4) |
| **Database** | MySQL 8.0 (Docker) / SQLite (Lokal) |
| **API Docs** | Swagger (OpenAPI 3.0) + GraphQL (Lighthouse) |

---

## 📋 Deskripsi

Service ini bertanggung jawab untuk mengelola **jadwal operasional driver**. Dalam ekosistem Group 7 (Pencatatan Operasional / Pengisian BBM), service ini dipanggil oleh **Service C (Tikta - Monitoring BBM)** untuk memvalidasi bahwa driver yang mengisi BBM **memang benar sedang bertugas** pada hari tersebut.

### Alur Integrasi

```
[Frontend] → POST /api/v1/fuel-logs → [Service C: Tikta]
                                            │
                    ┌───────────────────────┤
                    ▼                       ▼
          GET /api/v1/vehicles/{id}   GET /api/v1/schedules/{id}
          [Service A: Didit]          [Service B: Hafizh] ← INI
```

### Teknologi yang Digunakan

| Komponen | Teknologi | Versi |
|----------|-----------|-------|
| Backend Framework | Laravel | 13.x |
| Bahasa | PHP | >= 8.3 |
| Database (Docker) | MySQL | 8.0 |
| Database (Lokal) | SQLite | - |
| REST API Docs | L5-Swagger (OpenAPI) | 11.x |
| GraphQL Engine | Lighthouse (nuwave) | 6.66 |
| GraphQL IDE | Laravel GraphiQL | 4.1 |
| Auth Token | Laravel Sanctum | 4.0 |
| Container | Docker + Docker Compose | - |

---

## 🚀 Cara Menjalankan (Lokal)

### Prasyarat

- **PHP** >= 8.3
- **Composer** >= 2.x
- **Git**

### Langkah-langkah

```bash
# 1. Clone / masuk ke folder project
cd schedule-service

# 2. Install dependencies
composer install

# 3. Copy file environment
cp .env.example .env

# 4. Generate application key
php artisan key:generate

# 5. Buat file database SQLite (jika menggunakan SQLite)
# Windows:
New-Item -Path database\database.sqlite -ItemType File -Force
# Linux/Mac:
touch database/database.sqlite

# 6. Ubah DB_CONNECTION di .env menjadi sqlite (jika menggunakan SQLite)

# 7. Jalankan migrasi database
php artisan migrate

# 8. Isi data sampel (seeder)
php artisan db:seed

# 9. Generate dokumentasi Swagger
php artisan l5-swagger:generate

# 10. Jalankan server
php artisan serve --port=8000
```

Server akan berjalan di: **http://127.0.0.1:8000**

---

## 🐳 Cara Menjalankan (Docker) — **Rekomendasi**

Docker Compose akan otomatis menjalankan MySQL 8.0, migrasi, seeder, generate Swagger docs, dan serve aplikasi.

```bash
# Build dan jalankan
docker-compose up --build -d

# Cek status
docker-compose ps

# Lihat log
docker-compose logs -f app

# Stop
docker-compose down
```

| Service | Port | Keterangan |
|---------|------|------------|
| `app` (Laravel) | `8000` → `80` | Aplikasi utama |
| `db` (MySQL 8.0) | `3306` → `3306` | Database MySQL |

Service berjalan di: **http://localhost:8000**

### Environment Variables (Docker)

| Variable | Value | Keterangan |
|----------|-------|------------|
| `DB_CONNECTION` | `mysql` | Koneksi database |
| `DB_HOST` | `db` | Hostname container MySQL |
| `DB_PORT` | `3306` | Port MySQL |
| `DB_DATABASE` | `db_schedule` | Nama database |
| `DB_USERNAME` | `root` | Username MySQL |
| `DB_PASSWORD` | `secret` | Password MySQL |

---

## 🔐 Autentikasi (API Key)

**Semua endpoint** (REST API **dan** GraphQL) **wajib** menyertakan header `X-IAE-KEY` di setiap request.

| Header | Value |
|--------|-------|
| `X-IAE-KEY` | `102022400210` |

### Contoh Header

```
X-IAE-KEY: 102022400210
Content-Type: application/json
Accept: application/json
```

Jika **tidak menyertakan** atau **salah** API Key, akan mendapat respons:

```json
{
  "status": "error",
  "message": "Unauthorized. Invalid or missing API Key (X-IAE-KEY).",
  "errors": null
}
```

**Status Code:** `401 Unauthorized`

---

## 📡 REST API Endpoints

Base URL: `http://localhost:8000/api/v1`

### 1. GET `/api/v1/schedules` — Ambil Semua Jadwal

Mengambil daftar seluruh jadwal operasional driver.

**Request:**

```bash
# PowerShell
Invoke-RestMethod -Uri "http://localhost:8000/api/v1/schedules" `
  -Headers @{"X-IAE-KEY"="102022400210"; "Accept"="application/json"}

# cURL
curl -X GET http://localhost:8000/api/v1/schedules \
  -H "X-IAE-KEY: 102022400210" \
  -H "Accept: application/json"
```

**Response (200 OK):**

```json
{
  "status": "success",
  "message": "Data retrieved successfully",
  "data": [
    {
      "id": 1,
      "driver_name": "Budi Santoso",
      "vehicle_id": 1,
      "plate_number": "B 1234 ABC",
      "schedule_date": "2026-05-15T00:00:00.000000Z",
      "shift": "pagi",
      "status": "active",
      "notes": "Rute Jakarta - Bandung",
      "created_at": "2026-05-14T17:33:53.000000Z",
      "updated_at": "2026-05-14T17:33:53.000000Z"
    }
  ],
  "meta": {
    "service_name": "Penjadwalan-Driver-Service",
    "api_version": "v1"
  }
}
```

---

### 2. GET `/api/v1/schedules/{id}` — Ambil Jadwal Spesifik

Mengambil data spesifik jadwal shift berdasarkan ID.

> **🔗 Endpoint ini dipanggil oleh Service C (Tikta)** untuk validasi driver.

**Request:**

```bash
# PowerShell
Invoke-RestMethod -Uri "http://localhost:8000/api/v1/schedules/1" `
  -Headers @{"X-IAE-KEY"="102022400210"; "Accept"="application/json"}

# cURL
curl -X GET http://localhost:8000/api/v1/schedules/1 \
  -H "X-IAE-KEY: 102022400210" \
  -H "Accept: application/json"
```

**Response (200 OK):**

```json
{
  "status": "success",
  "message": "Data retrieved successfully",
  "data": {
    "id": 1,
    "driver_name": "Budi Santoso",
    "vehicle_id": 1,
    "plate_number": "B 1234 ABC",
    "schedule_date": "2026-05-15T00:00:00.000000Z",
    "shift": "pagi",
    "status": "active",
    "notes": "Rute Jakarta - Bandung",
    "created_at": "2026-05-14T17:33:53.000000Z",
    "updated_at": "2026-05-14T17:33:53.000000Z"
  },
  "meta": {
    "service_name": "Penjadwalan-Driver-Service",
    "api_version": "v1"
  }
}
```

**Response (404 Not Found):**

```json
{
  "status": "error",
  "message": "Schedule not found",
  "errors": null
}
```

---

### 3. POST `/api/v1/schedules` — Tambah Jadwal Baru

Menambah data penugasan atau jadwal baru untuk driver.

**Request:**

```bash
# PowerShell
$body = @{
    driver_name   = "Fajar Nugroho"
    vehicle_id    = 2
    plate_number  = "B 5678 DEF"
    schedule_date = "2026-05-16"
    shift         = "siang"
    status        = "active"
    notes         = "Rute Jakarta - Yogyakarta"
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://localhost:8000/api/v1/schedules" `
  -Method POST `
  -Headers @{"X-IAE-KEY"="102022400210"; "Accept"="application/json"; "Content-Type"="application/json"} `
  -Body $body

# cURL
curl -X POST http://localhost:8000/api/v1/schedules \
  -H "X-IAE-KEY: 102022400210" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "driver_name": "Fajar Nugroho",
    "vehicle_id": 2,
    "plate_number": "B 5678 DEF",
    "schedule_date": "2026-05-16",
    "shift": "siang",
    "status": "active",
    "notes": "Rute Jakarta - Yogyakarta"
  }'
```

**Request Body Fields:**

| Field | Type | Wajib | Keterangan |
|-------|------|-------|------------|
| `driver_name` | string | ✅ | Nama driver |
| `vehicle_id` | integer | ✅ | ID kendaraan (referensi Service A) |
| `plate_number` | string | ✅ | Plat nomor kendaraan |
| `schedule_date` | date (YYYY-MM-DD) | ✅ | Tanggal jadwal |
| `shift` | string | ✅ | `pagi`, `siang`, atau `malam` |
| `status` | string | ❌ | `active` (default), `completed`, `cancelled` |
| `notes` | string | ❌ | Catatan tambahan |

**Validasi:**

| Rule | Keterangan |
|------|------------|
| `driver_name` | Wajib, string, maksimal 255 karakter |
| `vehicle_id` | Wajib, harus integer |
| `plate_number` | Wajib, string, maksimal 20 karakter |
| `schedule_date` | Wajib, format tanggal valid |
| `shift` | Wajib, hanya: `pagi`, `siang`, `malam` |
| `status` | Opsional, hanya: `active`, `completed`, `cancelled` |
| `notes` | Opsional, nullable |

**Response (201 Created):**

```json
{
  "status": "success",
  "message": "Schedule created successfully",
  "data": {
    "id": 6,
    "driver_name": "Fajar Nugroho",
    "vehicle_id": 2,
    "plate_number": "B 5678 DEF",
    "schedule_date": "2026-05-16",
    "shift": "siang",
    "status": "active",
    "notes": "Rute Jakarta - Yogyakarta",
    "created_at": "2026-05-15T00:00:00.000000Z",
    "updated_at": "2026-05-15T00:00:00.000000Z"
  },
  "meta": {
    "service_name": "Penjadwalan-Driver-Service",
    "api_version": "v1"
  }
}
```

**Response (422 Validation Error):**

```json
{
  "status": "error",
  "message": "Validation failed",
  "errors": {
    "driver_name": ["The driver name field is required."],
    "shift": ["The selected shift is invalid."]
  }
}
```

---

## 📖 Swagger / OpenAPI Documentation

Dokumentasi interaktif tersedia di:

**🔗 http://localhost:8000/api/documentation**

Fitur:
- Lihat semua endpoint yang tersedia
- Coba langsung dari browser (klik **"Authorize"** → masukkan `102022400210`)
- Lihat schema data request & response

### Generate ulang Swagger docs:

```bash
php artisan l5-swagger:generate
```

---

## 🔮 GraphQL

### GraphQL Playground (GraphiQL)

**🔗 http://localhost:8000/graphiql**

> **⚠️ Catatan:** GraphQL endpoint **juga membutuhkan** API Key (`X-IAE-KEY: 102022400210`). Masukkan di HTTP Headers pada GraphiQL.

### GraphQL Endpoint

```
POST http://localhost:8000/graphql
```

### Contoh Query — Ambil Semua Jadwal

```graphql
{
  schedules {
    id
    driver_name
    vehicle_id
    plate_number
    schedule_date
    shift
    status
    notes
  }
}
```

### Contoh Query — Ambil Jadwal Spesifik

```graphql
{
  schedule(id: 1) {
    id
    driver_name
    plate_number
    shift
    status
  }
}
```

### Contoh Query — Pilih Field Tertentu Saja

Kelebihan GraphQL: klien bisa **memilih field** yang dibutuhkan saja.

```graphql
{
  schedules {
    driver_name
    shift
    status
  }
}
```

### Contoh via PowerShell

```powershell
$body = '{"query":"{ schedules { id driver_name plate_number shift status } }"}'

Invoke-RestMethod -Uri "http://localhost:8000/graphql" `
  -Method POST `
  -ContentType "application/json" `
  -Headers @{"X-IAE-KEY"="102022400210"} `
  -Body $body
```

### Contoh via cURL

```bash
curl -X POST http://localhost:8000/graphql \
  -H "Content-Type: application/json" \
  -H "X-IAE-KEY: 102022400210" \
  -d '{"query":"{ schedules { id driver_name plate_number shift status } }"}'
```

### GraphQL Schema

```graphql
type Query {
    schedules: [Schedule!]!    # Ambil semua jadwal
    schedule(id: ID!): Schedule # Ambil jadwal by ID
}

type Schedule {
    id: ID!
    driver_name: String!
    vehicle_id: Int!
    plate_number: String!
    schedule_date: Date!
    shift: String!
    status: String!
    notes: String
    created_at: DateTime!
    updated_at: DateTime!
}
```

---

## 📦 Struktur Respon (Standard Wrapper)

Mengikuti **Standard Integration Contract (IAE-T2)**.

### ✅ Sukses (2xx)

```json
{
  "status": "success",
  "message": "...",
  "data": { ... },
  "meta": {
    "service_name": "Penjadwalan-Driver-Service",
    "api_version": "v1"
  }
}
```

### ❌ Error (4xx / 5xx)

```json
{
  "status": "error",
  "message": "...",
  "errors": null
}
```

### HTTP Status Codes

| Code | Keterangan |
|------|------------|
| `200` | OK — Data berhasil diambil |
| `201` | Created — Data berhasil dibuat |
| `401` | Unauthorized — API Key tidak valid / tidak ada |
| `404` | Not Found — Data tidak ditemukan |
| `422` | Validation Error — Input tidak valid |

---

## 🗂️ Struktur Database

### Tabel `schedules`

| Kolom | Tipe | Deskripsi |
|-------|------|-----------|
| `id` | bigint (PK) | Auto increment |
| `driver_name` | string | Nama driver |
| `vehicle_id` | unsignedBigInteger | ID kendaraan (ref. Service A) |
| `plate_number` | string | Plat nomor kendaraan |
| `schedule_date` | date | Tanggal jadwal operasional |
| `shift` | string | `pagi` / `siang` / `malam` |
| `status` | string (default: `active`) | `active` / `completed` / `cancelled` |
| `notes` | text (nullable) | Catatan tambahan |
| `created_at` | timestamp | Waktu dibuat |
| `updated_at` | timestamp | Waktu diperbarui |

### Data Sampel (Seeder)

| # | Driver | Plat Nomor | Tanggal | Shift | Status | Rute |
|---|--------|-----------|---------|-------|--------|------|
| 1 | Budi Santoso | B 1234 ABC | 2026-05-15 | pagi | active | Jakarta - Bandung |
| 2 | Andi Wijaya | B 5678 DEF | 2026-05-15 | siang | active | Jakarta - Semarang |
| 3 | Citra Dewi | B 9012 GHI | 2026-05-15 | malam | active | Jakarta - Surabaya |
| 4 | Dedi Prasetyo | B 1234 ABC | 2026-05-14 | pagi | completed | Jakarta - Cirebon |
| 5 | Eka Putri | B 3456 JKL | 2026-05-16 | pagi | active | - |

---

## 📂 Struktur Project

```
schedule-service/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/V1/
│   │   │       └── ScheduleController.php    # Controller utama (REST + Swagger)
│   │   └── Middleware/
│   │       └── VerifyApiKey.php               # Middleware X-IAE-KEY
│   ├── Models/
│   │   └── Schedule.php                       # Model Eloquent
│   ├── Providers/
│   └── Swagger/
│       └── ScheduleSchema.php                 # Schema OpenAPI
├── config/
│   ├── l5-swagger.php                         # Config Swagger
│   └── lighthouse.php                         # Config GraphQL
├── database/
│   ├── migrations/
│   │   └── 2026_05_14_..._create_schedules_table.php
│   └── seeders/
│       ├── DatabaseSeeder.php
│       └── ScheduleSeeder.php                 # 5 data sampel
├── graphql/
│   └── schema.graphql                         # Schema GraphQL
├── routes/
│   └── api.php                                # Route REST API (v1)
├── .dockerignore
├── .env.example                               # Template environment
├── Dockerfile                                 # PHP 8.4-cli container
├── docker-compose.yml                         # App + MySQL 8.0
├── composer.json                              # Dependencies PHP
└── README.md                                  # Dokumentasi ini
```

---

## ⚡ Perintah Berguna

```bash
# Jalankan server development
php artisan serve --port=8000

# Jalankan migrasi
php artisan migrate

# Reset & seed ulang database
php artisan migrate:fresh --seed

# Generate Swagger docs
php artisan l5-swagger:generate

# Clear cache
php artisan cache:clear
php artisan config:clear

# Lihat daftar route
php artisan route:list --path=api

# Docker - Build dan jalankan
docker-compose up --build -d

# Docker - Lihat log aplikasi
docker-compose logs -f app

# Docker - Stop semua container
docker-compose down
```

---

## 👥 Tim Group 7

| NIM | Nama | Service |
|-----|------|---------|
| 102022400066 | Didit Aditya Rahman | Service A: Data Kendaraan |
| **102022400210** | **Hafizh Rafi Maulana Suyufi** | **Service B: Penjadwalan Driver** |
| 102022400179 | Adwitiya Tikta Pramasti | Service C: Monitoring BBM/Servis |
