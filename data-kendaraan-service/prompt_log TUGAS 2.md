# AI Prompt Log - Development of Data Kendaraan Service
**Developer:** Didit Aditya Rahman (102022400066)

- **[11:15:20]** Berikan panduan teknis berdasarkan dokumen Tugas 2 dan Standard Integration Contract ini. Saya ingin tahu poin-poin utama apa saja yang wajib diimplementasikan agar service Data Kendaraan ini memenuhi standar integrasi enterprise yang diminta.
- **[11:55:10]** Mari diskusikan arsitektur response di Laravel 11. Saya butuh panduan untuk membuat 'Standard Integration Wrapper' agar semua output JSON (termasuk validasi error) otomatis mengikuti format: status, message, data, dan meta tanpa perlu redundansi kode di controller.
- **[13:30:45]** Untuk keamanan, saya berencana menggunakan header X-IAE-KEY dengan nilai NIM 102022400066. Berikan panduan cara membuat middleware yang tepat untuk memproteksi seluruh rute kendaraan secara global.
- **[14:15:30]** Saya menemui kendala pada Swagger saat menggunakan docblock. Sepertinya lebih baik jika kita berdiskusi tentang migrasi ke PHP 8 Attributes. Bantu saya menyesuaikan konfigurasi SecurityScheme 'ApiKeyAuth' agar otentikasi bisa berjalan lancar di UI Swagger.
- **[14:50:15]** Mari tinjau kembali schema.graphql untuk Lighthouse. Saya ingin memastikan query 'vehicles' sudah sinkron dengan model dan memungkinkan klien memilih field secara fleksibel sesuai prinsip GraphQL.
- **[15:35:55]** Ada isu keamanan di mana header X-IAE-KEY belum tervalidasi pada layer GraphQL. Mari diskusikan solusi untuk mengintegrasikan ApiAuthMiddleware ke dalam pipeline Lighthouse agar standar keamanan konsisten di REST dan GraphQL.
- **[16:05:20]** Berikan panduan untuk mengonfigurasi global exception handling pada bootstrap/app.php. Tujuannya agar saat terjadi error sistem (5xx), respon tetap dikembalikan dalam format JSON standar integrasi, bukan halaman HTML default Laravel.
- **[16:25:00]** Mari lakukan refactoring kode secara menyeluruh untuk membersihkan komentar penjelas yang tidak perlu agar kodingan terlihat lebih profesional dan siap untuk di-publish ke repository.
- **[16:40:15]** Bantu saya menyusun Dockerfile dan docker-compose.yml yang tepat untuk standarisasi environment. Terakhir, saya butuh panduan untuk membuat seeder database agar tersedia data sampel kendaraan untuk mempermudah proses validasi fungsionalitas sistem.
