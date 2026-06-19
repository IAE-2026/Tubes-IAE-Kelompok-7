# AI Prompting Log - Tugas Besar IAE
## Fuel Monitoring Service
**Nama:** Adwitiya Tikta Pramasti  
**NIM:** 102022400179  
**Mata Kuliah:** BBK2HAB3 - Integrasi Aplikasi Enterprise  
**Service:** Monitoring BBM (Fuel Monitoring Service)

---

## [17/6/2026 - 10.00] Memahami Gambaran Umum Tugas Besar

**Prompt:**
Aku ingin memahami lebih lanjut mengenai tugas ini. Service punyaku itu adalah Fuel Monitoring Service yang sudah selesai di Tugas 2 dan 3. Sekarang harus digabungkan dengan service anggota kelompok lain. Apa perbedaan mendasar antara menjalankan service secara mandiri vs terintegrasi dalam satu sistem?

**Respons AI:**
AI menjelaskan bahwa perbedaan utamanya ada pada tiga hal: jaringan Docker (service yang dulu berjalan di network isolasi masing-masing kini harus berada dalam satu Docker network bersama), API Gateway sebagai pintu masuk tunggal (tidak ada service yang boleh diakses langsung dari luar), dan inter-service communication (service bisa saling memanggil satu sama lain via nama container sebagai hostname). AI juga menjelaskan bahwa dalam arsitektur ini, Nginx berperan sebagai reverse proxy yang meneruskan request berdasarkan path prefix ke service yang tepat.

---

## [17/6/2026 - 11.00] Memahami Peran API Gateway dengan Nginx

**Prompt:**
Saya ingin tau bagaimana Nginx bisa berfungsi sebagai API Gateway untuk tiga service Laravel yang berjalan di port berbeda. Bagaimana Nginx tahu harus meneruskan request ke service mana?

**Respons AI:**
AI menjelaskan konsep routing berbasis path prefix di Nginx. Misalnya request ke `/fuel/` diteruskan ke container Fuel Monitoring Service, `/driver/` ke service penjadwalan driver, dan `/vehicle/` ke service kendaraan. AI menjelaskan konfigurasi `location` block di nginx.conf dan pentingnya direktif `proxy_pass` yang mengarah ke nama service Docker (bukan `localhost`), karena dalam Docker network antar container berkomunikasi menggunakan nama service sebagai hostname.

---

## [17/6/2026 - 13.00] Mempelajari Struktur docker-compose Gabungan

**Prompt:**
Saya ingin tau bagaimana docker-compose.yml gabungan kelompok bekerja. Apa perbedaannya dengan docker-compose.yml milik service saya sendiri di Tugas 2? Apa yang perlu diperhatikan agar Fuel Monitoring Service saya bisa berjalan di dalam sistem gabungan ini?

**Respons AI:**
AI menjelaskan beberapa hal kritis yang harus diperhatikan: pertama, semua service harus berada dalam satu `networks` yang sama di docker-compose.yml agar bisa saling berkomunikasi. Kedua, port yang diekspos tidak boleh konflik antar service. Ketiga, environment variable seperti `DB_HOST` harus mengacu ke nama service database masing-masing, bukan `localhost`. Keempat, urutan `depends_on` harus diatur agar database siap sebelum aplikasi dijalankan. AI juga menyarankan untuk tidak mengekspos port service Laravel secara langsung ke host jika sudah ada Nginx sebagai gateway.

---

## [17/6/2026 - 14.00] Memahami Alur Inter-Service Communication

**Prompt:**
Dalam Tugas Besar, ada skenario di mana Fuel Monitoring Service perlu memvalidasi data kendaraan ke service lain sebelum menyimpan data BBM. Bagaimana cara satu service memanggil service lain secara internal dalam Docker network?

**Respons AI:**
AI menjelaskan bahwa dalam Docker network, service bisa dipanggil menggunakan nama container sebagai hostname. Misalnya jika service kendaraan bernama `vehicle-service` di docker-compose.yml, maka Fuel Monitoring Service bisa melakukan HTTP request ke `http://vehicle-service:8000/api/v1/vehicles/{id}`. AI juga menjelaskan penggunaan Laravel HTTP Client (`Http::get()`) untuk melakukan panggilan antar service, serta pentingnya menangani response error dengan baik menggunakan blok `try-catch` agar jika service yang dipanggil sedang down, service kita tidak ikut crash.

---

## [19/6/2026 - 10.00] Pengujian Integrasi dan Debugging

**Prompt:**
Saya ingin membantu proses pengujian sistem gabungan kelompok. Bagaimana cara menguji apakah API Gateway sudah bekerja dengan benar dan semua service bisa diakses melalui Nginx?

**Respons AI:**
AI memberikan panduan pengujian sistematis: pertama jalankan `docker compose up -d` dan cek semua container berjalan dengan `docker compose ps`. Kedua, uji routing Nginx dengan mengirim request ke masing-masing path prefix via Postman, misalnya `GET http://localhost/fuel/api/v1/fuel-logs` untuk memastikan request sampai ke Fuel Monitoring Service. Ketiga, cek log Nginx dengan `docker compose logs nginx` jika ada request yang mengembalikan 502 Bad Gateway. AI juga menjelaskan bahwa 502 biasanya terjadi karena nama service di `proxy_pass` tidak cocok dengan nama yang didefinisikan di `docker-compose.yml`.

---

## [19/6/2026 - 15.30] Memahami Alur End-to-End Bisnis Kelompok

**Prompt:**
Saya ingin memahami bagaimana alur bisnis end-to-end sistem kelompok kami bekerja dari sisi teknis. Dimulai dari driver login hingga data BBM tersimpan dengan melibatkan ketiga service. Bagaimana urutannya?

**Respons AI:**
AI membantu memetakan alur end-to-end: pertama driver melakukan login SSO melalui API Gateway, JWT diterima dan dikirim ke masing-masing service untuk validasi. Kemudian driver mengisi form BBM, request masuk ke Fuel Monitoring Service melalui gateway. Sebelum menyimpan, Fuel Monitoring Service memanggil service kendaraan secara internal untuk memvalidasi apakah `vehicle_id` yang dikirim valid dan aktif. Setelah validasi berhasil, data disimpan ke database, SOAP dikirim ke sistem audit dosen, dan event dipublish ke RabbitMQ. AI menjelaskan bahwa alur ini menunjukkan pola orkestratif di mana satu service menjadi koordinator yang memanggil service lain secara berurutan.