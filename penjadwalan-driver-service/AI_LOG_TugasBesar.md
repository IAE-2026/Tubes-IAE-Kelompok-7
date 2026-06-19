# AI Prompting Log - Tugas Besar IAE 2026
## Service: Penjadwalan Driver (Hafizh / 102022400210)
## Team: TEAM-07 (Fleet Management)

### Waktu Mulai: 16 Juni 2026
- **09:00 WIB**: "Halo AI, ini kan kita mau menggabungkan tugas service individu ke tugas besar dengan arsitektur API Gateway. Dari *source code* Laravel penjadwalan driver saya sebelumnya, kira-kira apa saja yang harus disesuaikan atau ditambahkan ya untuk pembuatan `Dockerfile` dan persiapan containerization?"
- **10:30 WIB**: "Saya sedang merevisi `ScheduleController` dari tugas 2. Nantinya service Monitoring BBM (Tikta) butuh respon dari endpoint GET `/api/v1/schedules/{id}` punyaku untuk memvalidasi status dinas driver. Apakah logic controller saya ini sudah cukup aman dan sesuai best practice untuk komunikasi *internal* antar microservices?"

### Waktu Mulai: 17 Juni 2026
- **13:00 WIB**: "AI, untuk syarat SSO M2M tugas besar ini kan ada penyesuaian, payload-nya sekarang wajib mengirimkan `api_key` dan `nim`. Kalau saya menggunakan implementasi HTTP Client di service saya seperti ini untuk memanggil endpoint `/api/v1/auth/token`, apakah format body request-nya sudah benar dan optimal?"
- **14:30 WIB**: "Pas saya coba tembak SSO M2M menggunakan payload `{"api_key":"KEY-MHS-270","nim":"102022400210"}`, saya malah tidak dapat response JWT yang valid. Menurutmu, apakah ini masalah di format JSON, setting header `Content-Type`, atau ada yang terlewat dari sisi logic saya?"
- **16:00 WIB**: "Selanjutnya untuk orkestrasi SOAP Audit, saya terus-terusan mendapatkan *400 Bad Request* dari server dosen. Ini contoh *raw* XML yang saya *generate*. Kira-kira, apa ada yang salah dengan penulisan tag namespace `<iae:AuditRequest>`, `TeamID`, atau `ActivityName` nya?"

### Waktu Mulai: 18 Juni 2026
- **19:00 WIB**: "Untuk syarat integrasi RabbitMQ, saya berencana membuat service Penjadwalan Driver ini mem-publish event ketika jadwal baru dibuat. Apakah flow *publisher* yang saya rancang ini sudah sesuai untuk event-driven architecture di kasus Fleet Management kita?"
- **20:30 WIB**: "Terakhir AI, tolong review keseluruhan alur komunikasi integrasi grup saya. Jadi *frontend* nembak Service Tikta, lalu backend Tikta akan memvalidasi ke service saya (`/api/v1/schedules/{id}`) secara internal sebelum *save* data. Apakah skema perlindungan Nginx API Gateway yang memastikan endpoint saya tidak bisa di-bypass langsung dari luar ini sudah tepat?"
