# Analisis Tugas 3 - Fuel Monitoring Service

Nama: Adwitiya Tikta Pramasti
NIM: 102022400179
Mata Kuliah: BBK2HAB3 - Integrasi Aplikasi Enterprise
Service: Monitoring BBM (fuel-logs)
Tema: Fleet Management

---

## 1. Penjelasan Transaksi Kritis

Pada Service yang saya kerjakan, terdapat tiga endpoint yaitu `GET /api/v1/fuel-logs`, `GET /api/v1/fuel-logs/{id}`, dan `POST /api/v1/fuel-logs`. Dari ketiga endpoint tersebut, transaksi yang paling kritis menurut analisis saya adalah `POST /api/v1/fuel-logs` karena endpoint ini satu-satunya yang benar-benar menulis data baru ke database, sedangkan dua endpoint GET lainnya hanya membaca data tanpa mengubah apapun.

Transaksi ini kritis karena menyangkut pengeluaran uang perusahaan secara langsung. Setiap kali driver menginput nota BBM, artinya ada biaya yang tercatat atas nama perusahaan. Kalau data yang masuk tidak valid, misalnya kendaraan tidak terdaftar atau driver tidak sedang bertugas, catatan pengeluaran bisa menjadi tidak akurat. Oleh karena itu, sebelum data boleh disimpan, sistem wajib memvalidasi ke Service 'Data Kendaraan' untuk memastikan kendaraannya terdaftar, dan ke Service 'Penjadwalan Driver' untuk memastikan driver memang sedang bekerja hari itu.

Selain itu, transaksi ini juga perlu dicatat ke sistem audit resmi melalui SOAP dan disebarkan melalui RabbitMQ, yang menjadi alasan tambahan mengapa endpoint ini diperlakukan sebagai transaksi kritis.

---

## 2. Mengapa Transaksi Ini Wajib Dicatat via SOAP?  
Seperti yang diketahui, SOAP adalah protokol pengiriman pesan internet ringan yang memfasilitasi pertukaran pesan antara aplikasi dan sistem backend sehingga cocok digunakan untuk melakukan audit log. Pencatatan pengisian BBM berkaitan dengan pengeluaran aset perusahaan, sehingga setiap transaksi yang berhasil harus memiliki jejak audit yang resmi dan tidak bisa dimanipulasi. Dengan adanya pencatatan via SOAP, setiap transaksi BBM mendapat `ReceiptNumber` sebagai bukti resmi bahwa data sudah diterima oleh sistem pusat. Jika di kemudian hari ada selisih data atau pencatatan yang kurang, nomor ini bisa dijadikan referensi bukti lebih lanjut.  

---  

## 3. Mengapa Transaksi Ini Perlu Disebarkan via RabbitMQ?  
Setelah saya telusuri diketahui RabbitMQ ini berkomunikasi dengan cara Microservices, selain itu salah satu fungsi utamanya adalah Sistem yang menggunakan RabbitMQ dapat mengirimkan pesan tanpa harus menunggu balasan dari sistem penerima secara langsung. Pesan yang dikirimkan nantinya adakan disimpan di dalam antrian (queue) sampai pesan yang dikirimkan siap untuk diambil oleh penerima.

Dari sinilah saya menganalisis bahwa data pengisian BBM bukan hanya urusan di service ini saja, ada bagian/divisi dari perusahaan yang butuh data ini untuk rekap pengeluaran operasional, biasanya untuk memantau efisiensi konsumsi BBM per kendaraan. Kalau setiap bagian harus meminta data langsung ke service ini satu per satu, sistem akan menjadi lambat dan saling bergantung satu sama lain. Dengan RabbitMQ, service cukup mengirim satu notifikasi ke message broker, dan semua pihak yang berkepentingan bisa mengambil informasinya kapan saja tanpa mengganggu proses utama. Selain itu, kalau salah satu departemen sedang bermasalah, data tetap aman di antrian dan akan diproses ketika sistemnya sudah pulih tanpa mempengaruhi proses pencatatan BBM sama sekali.