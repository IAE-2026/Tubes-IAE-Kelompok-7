# Resume Kontribusi Tugas Besar IAE

**Nama:** Didit Aditya Rahman
**NIM:** 102022400066
**Service Induk:** Data Kendaraan

## Deskripsi Kontribusi

Pada tahap integrasi Tugas Besar IAE ini, saya ikut membantu tim untuk memastikan bahwa service-service (Tikta dan Hafizh) dapat terhubung ke service saya yaitu Data Kendaraan ke dalam satu arsitektur terpusat, selanjutnya saya memastikan agar Proses Bisnis yang kami gunakan menjadi sesuai dengan tujuan utama pada tema kami yaitu fleet management.

Dalam proses penegrjaan saya turut aktif dakam membantu dalam menyiapkan beberapa konfigurasi file yang dibutuhkan untuk penyambungan antarservice tersebut, yaitu:

1. **Turut membantu menyusun `nginx.conf`**: Ikut berdiskusi dan menyesuaikan route pada Nginx agar permintaan eksternal yang masuk ke service Data Kendaraan dapat lewat dan dibungkus oleh API Gateway utama kelompok.
2. **Membantu _setup_ `Dockerfile`**: Melakukan pengecekan instruksi Docker agar Nginx API Gateway dapat di build/generate dengan lancar dan sesuai dengan kebutuhan probis kita.
3. **Membantu konfigurasi `docker-compose.yml`**: Ikut mengonfigurasi pengaturan, agar container bisa masuk ke jaringan Docker yang sama dengan service teman-teman, sehingga API kita bisa saling panggil.

Selain ikut membantu konfigurasi file-file penyambungan di atas, saya juga ikut memvalidasi alur komunikasi agar jalur dari service Data Kendaraan menuju Cloud Pusat (SSO, SOAP, RabbitMQ) aman tanpa kendala saat digabungkan. Saya juga turut aktif dan berdiskusi secara kelompok.
