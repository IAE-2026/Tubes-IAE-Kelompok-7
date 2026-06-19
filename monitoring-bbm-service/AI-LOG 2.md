# AI Prompting Log - Tugas 3 IAE
## Fuel Monitoring Service
**Nama:** Adwitiya Tikta Pramasti
**NIM:** 102022400179
**Mata Kuliah:** BBK2HAB3 - Integrasi Aplikasi Enterprise
**Service:** Monitoring BBM (Fuel Monitoring Service)

---

## [10/6/2026 - 17.00] Memahami Gambaran Umum Tugas 3

**Prompt:**
Saya ingin memahami apa saja yang perlu dikerjakan di Tugas 3. Service saya adalah Fuel Monitoring Service. Sekarang service ini harus terhubung ke SSO Dosen, sistem audit SOAP, dan RabbitMQ. Dari mana saya harus mulai?

**Respons AI:**
AI menjelaskan bahwa Tugas 3 terdiri dari 3 modul teknis utama yang harus diimplementasikan ke dalam service yang sudah ada. Modul 1 adalah Federated SSO dengan bobot 30%, Modul 2 adalah SOAP XML Client dengan bobot 40%, dan Modul 3 adalah AMQP Publisher dengan bobot 20%. AI menyarankan untuk mulai dari Modul 2 karena bobotnya paling besar, namun sebelum itu perlu memastikan struktur database sudah siap dengan menambahkan kolom `soap_receipt_number` di tabel `fuel_logs`.

---

## [10/6/2026 - 17.40] Persiapan Database dan Pembaruan Migration

**Prompt:**
Saya perlu menambahkan kolom `soap_receipt_number` di tabel fuel_logs untuk menyimpan nomor bukti dari sistem audit SOAP. Bagaimana cara menambahkan kolom ini tanpa menghapus data yang sudah ada?

**Respons AI:**
AI menjelaskan dua pilihan: membuat migration baru dengan `php artisan make:migration` untuk menambah kolom, atau langsung mengubah migration yang sudah ada lalu jalankan `migrate:fresh`. Karena masih dalam tahap pengembangan, AI menyarankan untuk langsung ubah migration yang ada dan tambahkan kolom `soap_receipt_number` dengan tipe `string()->nullable()` agar tidak wajib diisi saat data lama sudah ada.

---

## [10/6/2026 - 19.00] Implementasi Modul 1: Federated SSO

**Prompt:**
Saya ingin membuat endpoint `POST /api/v1/auth/sso` yang menerima JWT dari SSO Dosen dan memetakan user ke tabel lokal. Bagaimana cara membuat controller dan migration yang dibutuhkan?

**Respons AI:**
AI menjelaskan bahwa untuk Modul SSO dibutuhkan dua hal: migration baru untuk menambah kolom `role` dan `sso_id` ke tabel users, serta SsoController baru yang bertugas menerima JWT, mendecode payload-nya, memetakan role dari SSO ke role lokal, lalu menyimpan atau memperbarui data user di database. AI juga menjelaskan logika pemetaan role: jika role SSO adalah `admin` maka role lokal juga `admin`, jika `manager` maka `manager`, dan selain itu defaultnya adalah `viewer`.

---

## [10/6/2026 - 20.00] Implementasi Modul 2: SOAP XML Client

**Prompt:**
Saya ingin membuat service yang otomatis mengirim laporan ke Legacy Audit System menggunakan SOAP setiap kali ada transaksi BBM baru. Bagaimana cara membuat XML Envelope yang sesuai dengan format yang diminta dan cara mengambil token M2M dari SSO Dosen?

**Respons AI:**
AI menjelaskan bahwa harus membuat file di folder `app/Services/`. Service ini bekerja dalam dua langkah: pertama ambil M2M token dari `POST /api/v1/auth/token` menggunakan `api_key`, lalu kirim XML Envelope ke `POST /soap/v1/audit` dengan header Authorization Bearer. AI juga menjelaskan format XML yang wajib dipakai termasuk tag `<TeamID>`, `<ActivityName>`, dan `<LogContent>` dengan data JSON dibungkus CDATA. Setelah berhasil, service mengekstrak `ReceiptNumber` dari response XML menggunakan regex.

---

## [10/6/2026 - 21.30] Debugging SOAP: Response "NO-RECEIPT"

**Prompt:**
Setelah menjalankan POST fuel-logs, field `soap_receipt_number` di response berisi "NO-RECEIPT". Artinya SOAP berhasil dipanggil tapi ReceiptNumber tidak bisa diambil. Apa yang salah?

**Respons AI:**
AI menjelaskan bahwa "NO-RECEIPT" muncul ketika salah satu dari dua hal terjadi: token M2M tidak valid sehingga request ke SOAP ditolak, atau format regex untuk mengekstrak ReceiptNumber dari response XML tidak cocok. AI menyarankan untuk test token M2M terlebih dahulu dengan mengirim request langsung ke server dosen menggunakan Postman, lalu cek apakah `api_key` yang dipakai sudah benar. Setelah dicek, ternyata `KEY-MHS-18` tidak valid dan perlu diganti ke `KEY-MHS-206` yang sesuai dengan nomor mahasiswa yang diberikan dosen.

---

## [10/6/2026 - 22.00] Pengujian Akhir Semua Modul

**Prompt:**
Bagaimana cara menguji apakah ketiga modul sudah berjalan dengan benar menggunakan Postman? Saya ingin memastikan SSO bisa menerima JWT, SOAP berhasil dapat ReceiptNumber, dan RabbitMQ berhasil publish event.

**Respons AI:**
AI memberikan urutan pengujian yang sistematis. Pertama test SSO dengan login ke server dosen menggunakan email untuk mendapat JWT, lalu kirim JWT tersebut ke endpoint `POST /api/v1/auth/sso` milik service sendiri. Kedua test SOAP dengan melakukan `POST /api/v1/fuel-logs` dan mengecek apakah field `soap_receipt_number` di response berisi nilai selain "NO-RECEIPT". Ketiga cek log Docker dengan `docker compose logs app` untuk memastikan tidak ada error dari RabbitMQ Publisher. Semua pengujian berhasil: SSO mengembalikan response `local_role: viewer`, SOAP mengembalikan `IAE-LOG-2026-9260A506`, dan log Docker tidak menunjukkan error apapun.

## [12/6/2026 - 10.00] Pembuatan Sequence Diagram

**Prompt:**
Saya perlu membuat sequence diagram yang menggambarkan alur interaksi service saya dengan sistem terpusat dosen. Komponen apa saja yang harus ada dan bagaimana urutannya?

**Respons AI:**
AI membantu merancang sequence diagram dengan 7 komponen utama: Driver, Frontend, Service Tikta, Service Didit, Service Hafizh, Database MySQL, dan SSO Dosen. Diagram dibagi menjadi beberapa fase: autentifikasi SSO, input nota BBM, validasi ke Service Didit dan Hafizh, penyimpanan data, audit SOAP, dan publish RabbitMQ. AI juga menjelaskan pentingnya menambahkan blok alt untuk menggambarkan alur alternatif ketika validasi gagal atau ketika SOAP/RabbitMQ mengalami error.