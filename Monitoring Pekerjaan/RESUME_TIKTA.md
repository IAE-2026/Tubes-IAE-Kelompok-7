# Resume Kontribusi Tugas Besar IAE

**Nama:** Adwitiya Tikta Pramasti  
**NIM:** 102022400179  
**Service Induk:** Fuel Monitoring Service

## Deskripsi Kontribusi

Dalam pengerjaan Tugas Besar IAE, saya terlibat dalam proses penggabungan service Fuel Monitoring ke dalam sistem kelompok. Karena proses integrasi keseluruhan dipusatkan pada perangkat salah satu anggota tim, peran saya lebih bersifat kolaboratif  membantu memastikan service saya siap secara teknis.

Beberapa hal yang saya kerjakan selama proses integrasi berlangsung:

1. **Menyesuaikan konfigurasi `docker-compose.yml` service saya**: Memastikan service Fuel Monitoring dan database MySQL-nya dapat bergabung ke dalam Docker network kelompok tanpa konflik nama container maupun port dengan service milik anggota lain.

2. **Membantu verifikasi konfigurasi `nginx.conf`**: Ikut mengecek dan mendiskusikan pengaturan routing Nginx agar path prefix untuk Fuel Monitoring Service diteruskan dengan benar ke container yang sesuai oleh API Gateway.

3. **Memeriksa kompatibilitas environment variable**: Meninjau ulang file `.env` service saya untuk memastikan tidak ada nilai yang masih mengacu ke `localhost` sehingga komunikasi antar-container dalam satu network berjalan normal.

4. **Membantu pengujian alur integrasi**: Ikut melakukan pengujian via Postman untuk memvalidasi bahwa endpoint Fuel Monitoring dapat diakses melalui gateway, serta memastikan alur SSO, SOAP, dan RabbitMQ tetap berjalan setelah service digabungkan ke sistem kelompok.
