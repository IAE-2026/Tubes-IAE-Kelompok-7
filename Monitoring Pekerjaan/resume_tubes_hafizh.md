# Resume Kontribusi Tugas Besar IAE 2026
**Nama**: Hafizh
**NIM**: 102022400210
**Email / Akun Warga**: warga35@ktp.iae.id
**API-KEY**: KEY-MHS-270
**Tim**: TEAM-07
**Tema**: Fleet Management (Pencatatan Operasional - Pengisian BBM)

## Deskripsi Service (Penjadwalan Driver)
Bertanggung jawab atas pengembangan dan pengelolaan service **Penjadwalan Driver**. Service ini mengelola jadwal operasional dan penugasan driver sebagai bagian dari sistem *Fleet Management*.

**Endpoint Utama:**
- `GET /api/v1/schedules`: Mengambil daftar seluruh jadwal operasional (Collection).
- `GET /api/v1/schedules/{id}`: Mengambil data spesifik jadwal shift (Resource). Digunakan oleh Service Monitoring BBM (Tikta) melalui pemanggilan internal untuk memvalidasi apakah driver yang bersangkutan memang sedang bertugas hari ini.
- `POST /api/v1/schedules`: Menambah data penugasan atau jadwal baru (Action).

## Kontribusi dalam Integrasi Tim
1. **Microservice Development**: Mengembangkan service *Penjadwalan Driver* secara independen, membuat implementasi endpoint RESTful API, dan melakukan containerization menggunakan Docker.
2. **API Gateway (Nginx) Integration**: Menyesuaikan arsitektur service agar berada di belakang Nginx API Gateway, memastikan routing ke `/api/v1/schedules` dialihkan dengan benar ke container service ini.
3. **SSO M2M & Central Infrastructure**:
   - Berhasil mengimplementasikan autentikasi SSO M2M dengan mengirimkan POST request menggunakan payload data spesifik (`api_key`: `KEY-MHS-270` dan `nim`: `102022400210`).
   - Menerapkan orkestrasi 3 lapis secara berurutan: Login SSO Dosen, Kirim log aktivitas via layanan SOAP Audit, dan mengirimkan notifikasi event asinkronus ke RabbitMQ.
4. **Inter-Service Communication**: Menyediakan API spesifik (`GET /api/v1/schedules/{id}`) yang diatur agar merespon request REST dari service *Monitoring BBM* dengan kode sukses (2xx) secara internal sehingga proses validasi dan pengisian data nota bensin di Service Tikta dapat berhasil disimpan.
