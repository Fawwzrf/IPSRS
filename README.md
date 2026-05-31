# IPSRS (Instalasi Pemeliharaan Sarana dan Prasarana Rumah Sakit)

IPSRS adalah sistem informasi manajemen pemeliharaan dan perbaikan aset rumah sakit berbasis web yang dikembangkan menggunakan framework PHP Laravel dan MySQL. Sistem ini dirancang untuk memfasilitasi pelaporan komplain, penjadwalan pemeliharaan preventif, dan pemantauan log kerja teknisi secara terintegrasi.

> **Pemberitahuan Penting**: Repositori ini merupakan salinan (*copy*) dari repositori asli yang dibuat untuk project Kerja Praktik sekitar 11 bulan yang lalu. Repositori aslinya saat ini sudah di-private dan saya (sebagai kolaborator) sudah tidak memiliki akses ke sana. Repositori ini dibuat kembali sebagai arsip dan portofolio pribadi.

## Fitur Utama Sistem
- **Manajemen Aset & Lokasi**: Pendataan aset rumah sakit yang komprehensif.
- **Jadwal Pemeliharaan (Preventive Maintenance)**: Penjadwalan otomatis untuk perawatan rutin.
- **Pelaporan Komplain**: Perekaman dan pelacakan kerusakan atau perbaikan yang dibutuhkan.
- **Penugasan & Order Kerja**: Alokasi tugas kepada teknisi secara efisien.
- **Log Kerja & Penggunaan Sparepart**: Pencatatan riwayat penanganan dan stok komponen.
- **Dashboard & Laporan**: Visualisasi analitik kinerja teknisi dan status aset.

## Cara Menjalankan (Local Development)
1. Silahkan clone repositori ini.
2. Salin file `.env.example` menjadi `.env`.
3. Sesuaikan konfigurasi database pada file `.env`.
4. Jalankan perintah berikut:
   ```bash
   composer install
   php artisan key:generate
   php artisan migrate
   ```
5. Untuk aset frontend, jalankan:
   ```bash
   npm install
   npm run dev
   ```
6. Jalankan server pengembangan lokal:
   ```bash
   php artisan serve
   ```
