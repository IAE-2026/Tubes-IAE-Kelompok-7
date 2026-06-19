# AI Prompting Log - Tugas 2 IAE
## Fuel Monitoring Service
**Nama:** Adwitiya Tikta Pramasti  
**NIM:** 102022400179  
**Mata Kuliah:** BBK2HAB3 - Integrasi Aplikasi Enterprise  
**Service:** Monitoring BBM (Fuel Monitoring Service)

---

## [15/5/2026 - 09.00] Eksplorasi Awal Arsitektur Service

**Prompt:**
Saya sedang membangun Fuel Monitoring Service untuk tugas IAE. Saya ingin memahami bagaimana arsitektur REST API yang baik untuk service ini. Apa saja komponen utama yang perlu saya siapkan?

**Respons AI:**
AI menjelaskan komponen utama yang dibutuhkan: Model & Migration untuk database, Controller untuk logika bisnis, Middleware untuk keamanan, Routes untuk endpoint, serta Dockerfile untuk containerisasi. AI juga menjelaskan pentingnya konsistensi format JSON response antar service.

---

## [15/5/2026 - 09.30] Perancangan Skema Database

**Prompt:**
Saya ingin merancang tabel fuel_logs untuk mencatat pengisian BBM armada. Data apa saja yang relevan untuk disimpan agar service saya bisa berkomunikasi dengan service penjadwalan driver dan service kendaraan?

**Respons AI:**
AI membantu mengidentifikasi kolom yang dibutuhkan: `vehicle_id` sebagai referensi kendaraan, `driver_name` sebagai referensi driver, `liters` untuk jumlah BBM, `total_cost` untuk biaya, `fuel_station` untuk lokasi SPBU, dan `filled_at` untuk timestamp pengisian.

---

## [15/5/2026 - 10.30] Implementasi REST API Endpoint

**Prompt:**
Saya ingin membuat 3 endpoint sesuai Standard Integration Contract: GET semua data, GET by ID, dan POST tambah data. Bagaimana struktur controller yang baik dan format response yang harus saya gunakan?

**Respons AI:**
AI menjelaskan struktur controller dengan tiga method: `index()`, `show()`, dan `store()`. Juga menjelaskan format JSON wrapper yang wajib digunakan sesuai kontrak: `status`, `message`, `data`, dan `meta` untuk response sukses, serta `status`, `message`, `errors` untuk response gagal.

---

## [15/5/2026 - 12.00] Konfigurasi Docker

**Prompt:**
Saya perlu membuat Dockerfile dan docker-compose.yml untuk service ini. Bagaimana konfigurasi yang tepat agar Laravel bisa berjalan di Docker dengan database MySQL terpisah?

**Respons AI:**
AI menjelaskan konsep multi-container Docker: satu container untuk aplikasi Laravel dan satu untuk MySQL, dihubungkan melalui Docker network internal. AI memberikan konfigurasi Dockerfile menggunakan base image PHP 8.2-cli dan docker-compose.yml dengan service `app` di port 8001 dan `db` di port 3307.

---

## [15/5/2026 - 13.40] Setup Dokumentasi Swagger

**Prompt:**
Saya mencoba setup L5-Swagger tapi mendapatkan error "Required @OA\Info() not found". Saya sudah menambahkan anotasi di Controller.php tapi masih error. Apa penyebabnya?

**Respons AI:**
AI menjelaskan bahwa L5-Swagger perlu menemukan anotasi `@OA\Info()` di file yang berada dalam path scan-nya. AI menyarankan membuat file `SwaggerController.php` terpisah khusus untuk anotasi info, dan memastikan `securityScheme` bernama `apiKey` agar konsisten dengan anotasi di controller endpoint.

---

## [16/5/2026 - 15.10] Penyesuaian Integrasi Antar Service

**Prompt:**
Saya perlu memastikan kolom database saya kompatibel dengan service penjadwalan driver milik anggota kelompok saya. Bagaimana cara menyesuaikan skema agar data bisa saling terhubung antar service?

**Respons AI:**
AI menjelaskan pentingnya konsistensi tipe data antar service dalam ekosistem microservice. Setelah menganalisis skema kedua service, AI merekomendasikan penggunaan `driver_name` (string) sebagai penghubung ke service penjadwalan dan `vehicle_id` dengan tipe integer agar konsisten dengan referensi di service kendaraan.

---

## [16/5/2026 - 16.00] Penambahan Anotasi Swagger Lengkap

**Prompt:**
Dokumentasi Swagger saya belum menampilkan parameter input untuk endpoint POST dan belum bisa mengirim API Key secara otomatis. Bagaimana cara menambahkan anotasi yang lengkap?

**Respons AI:**
AI menjelaskan penggunaan PHP 8 Attributes untuk anotasi Swagger, termasuk `#[OA\Post]` dengan `requestBody` untuk mendefinisikan field input, `#[OA\Parameter]` untuk path parameter, dan `security: [["apiKey" => []]]` agar tombol Authorize di Swagger UI bisa mengirim header X-IAE-KEY secara otomatis.
