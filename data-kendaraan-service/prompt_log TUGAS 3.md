# Prompt Engineering Log
## Service: Data Kendaraan (Didit)

## Waktu Mulai: 08 Juni 2026
- **11:47 WIB**: "Bantuin dong ngerjain Tugas 3 IAE. Disuruh gabungin service ke Cloud Pusat dosen nih. Ada 4 tugas: dokumen analisis, SSO pake JWT, nembak SOAP XML, sama ngirim antrian ke RabbitMQ. Enaknya mulai ngerjain dari mana ya?"
- **12:24 WIB**: "Oke mulai dari analisisnya dulu ya. Kasih tau dong kenapa fitur `Create Vehicle` tuh jadi inti banget dan ngaruh ke database. Terus buatin juga *sequence diagram* pake Mermaid buat alur pas login SSO dari dosennya."
- **13:30 WIB**: "Lanjut ke bagian coding nih, Modul 1 SSO. Gimana cara nangkep token JWT dari dosen terus datanya disimpen ke tabel lokal kita di Laravel? Bikinin *middleware*-nya dong."
- **14:15 WIB**: "Buat Modul 2 disuruh bikin SOAP XML Client. Jadi tiap abis nambah data *vehicle*, kita harus kirim format XML kaku ke sistem dosen biar dapet balasan `ReceiptNumber`. Bikinin kodenya pake fungsi bawaan PHP aja ya biar ga ribet install macem-macem."
- **15:00 WIB**: "Terakhir yang Modul 3 RabbitMQ. Kalau nomor resinya udah dapet, datanya harus di-*broadcast* bentuk JSON ke RabbitMQ dosen. Minta tolong bikinin kode buat ngirim pesannya dong, tapi kasih pengaman biar aplikasi ga *crash* pas server rabbit-nya lagi *down*."

## Waktu Mulai: 12 Juni 2026

- **18:56 WIB**: "Coba cek implementasi audit XML SOAP-nya, lokasinya di mana dan pastiin format pengirimannya udah sesuai sama standar WSDL pusat ya."
- **19:15 WIB**: "Biar sesuai kontrak tugas, tolong pastiin Team ID-nya pakai TEAM-07, terus untuk nama aktivitas bisnisnya set ke VehicleCreated aja."
- **19:30 WIB**: "Aku ngeliat di referensi dosen ada banyak file *service*, pastiin aja `M2MAuthService` kita udah rapi dan nge-cover semua urusan JWT biar arsitekturnya nggak kebanyakan file."
- **19:45 WIB**: "Gimana nih formatnya kalau aku mau ngetes nembak endpoint SOAP manual di Postman pakai token JWT yang udah ke-*decrypt*? Aku mau *make sure* validasinya jalan."
- **20:00 WIB**: "Tadi nyoba nembak SOAP manual malah dapet 403 Forbidden, coba bantu analisis bagian *header authorization*-nya, pastiin token M2M-nya udah beneran ke-*inject*."
- **20:15 WIB**: "Sekarang malah 400 Bad Request, tolong tinjau ulang format XML *envelope*-nya sama struktur JSON di dalam CDATA, pastiin udah sesuai sama data kendaraan aslinya."
- **20:30 WIB**: "Coba *generate*-in lagi 10 data kendaraan baru (5 *active*, 5 *inactive*), aku mau ngetes *flow* integrasinya langsung dari Postman biar beneran ke-*hit* semua."
