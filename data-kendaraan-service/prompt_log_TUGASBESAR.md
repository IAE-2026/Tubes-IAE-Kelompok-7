# Prompt Engineering Log
## Service: Data Kendaraan (Didit)

## Waktu Mulai: 18 Juni 2026
- **09:15 WIB**: "Kemarin aku udah beres ngerjain Tugas 3 IAE buat service Data Kendaraan nih. Sekarang masuk ke Tugas Besar, aku mau ngecek validitas service-ku supaya bisa nyambung sama service punya Tikta dan Hafizh. Ada arahan nggak kira-kira aku harus mulai ngecek dari bagian mana dulu?"
- **10:30 WIB**: "Oke, berarti langkah pertamanya mastiin service kita aman di belakang API Gateway kelompok ya. Bantu aku ngecek konfigurasi *routing*-nya dong, biar service Data Kendaraan ini nggak bisa ditembak langsung dari luar tapi harus lewat gateway utama."
- **14:00 WIB**: "Terus buat alur *end-to-end* bisnisnya gimana nih? Service Data Kendaraan kan harus manggil API dari service-nya Tikta atau Hafizh secara otomatis pas ada data baru. Tolong kasih arahan".

## Waktu Mulai: 19 Juni 2026
- **13:45 WIB**: "Sekarang mau mastiin urusan integrasi ke Cloud Pusat nih. Service-ku kan udah beres urusan SSO, SOAP Audit, sama kirim ke RabbitMQ di tugas sebelumnya. Tolong dong kasih panduan buat *test* dan validasi jalurnya secara berurutan, biar pas digabung sama sistem Tikta dan Hafizh nggak tiba-tiba *error*."
- **15:10 WIB**: "Barusan aku coba jalurin dari SSO ke RabbitMQ, kelihatannya udah oke. Tapi gimana ya cara kita ngetes *flow* akhirnya kalau digabungin beneran sama *service* mereka? Kira-kira ada *best practice* buat ngetes *response* antar-*service* sebelum integrasi penuh?"
- **16:20 WIB**: "Oh iya, bahas urusan repositori sekalian nih. Aku kan udah ngerasa cukup valid di bagianku, dan pengen *push* ke repo GitHub/GitLab kelompok biar kerjaku ke-rekam. Baiknya aku langsung *push* ke *main branch* atau bikin *pull request* aja ya biar bisa di-*review* sama Tikta atau Hafizh? Takutnya kalau langsung *merge* malah nabrak kodingan mereka."
- **17:05 WIB**: "Sip, masuk akal. Nanti tinggal aku kabarin mereka aja buat *review* sebelum di-*merge*. Makasih ya udah nemenin diskusi buat mastiin *service* Data Kendaraan ini bener-bener *ready* buat digabungin." 
