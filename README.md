# IPSRS (Instalasi Pemeliharaan Sarana dan Prasarana Rumah Sakit)

> **Pemberitahuan Penting**: Repositori ini merupakan salinan (*copy*) dari repositori asli yang dibuat untuk project Kerja Praktik sekitar 11 bulan yang lalu. Repositori aslinya saat ini sudah di-private dan saya (sebagai kolaborator) sudah tidak memiliki akses ke sana. Repositori ini dibuat kembali sebagai arsip dan portofolio pribadi.

## Tentang Sistem
Sistem Informasi IPSRS ini dikembangkan menggunakan framework Laravel. Sistem ini digunakan untuk mengelola pemeliharaan dan perbaikan aset rumah sakit, yang mencakup beberapa fitur utama:
- Manajemen Aset & Lokasi
- Manajemen Jadwal Pemeliharaan (Preventive Maintenance)
- Pelaporan Komplain/Kerusakan
- Penugasan Teknisi & Order Kerja
- Pencatatan Log Kerja & Penggunaan Sparepart
- Dashboard & Laporan Kinerja Teknisi

## Teknologi yang Digunakan
- PHP & Laravel Framework
- MySQL
- Bootstrap/Tailwind & Vanilla JS (Sesuai Konfigurasi Frontend)

## Cara Menjalankan (Local Development)
1. Clone repositori ini.
2. Salin file `.env.example` menjadi `.env`.
3. Sesuaikan konfigurasi database pada file `.env`.
4. Jalankan perintah berikut:
   ```bash
   composer install
   php artisan key:generate
   php artisan migrate
   ```
5. Untuk frontend assets (jika diperlukan), jalankan:
   ```bash
   npm install
   npm run dev
   ```
6. Jalankan server:
   ```bash
   php artisan serve
   ```