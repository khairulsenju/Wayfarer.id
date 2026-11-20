WAYFARER - PRIORITAS 2 / PATCH 1

File dalam paket ini adalah pengganti lengkap untuk:
1. app/Services/OrderService.php
2. app/Services/RentalAvailabilityService.php

Cara menerapkan:
- Backup kedua file lama terlebih dahulu.
- Salin file dari folder app/Services pada paket ini ke lokasi yang sama di project Wayfarer.
- Jangan jalankan migration; patch ini tidak mengubah struktur database.
- Jalankan pemeriksaan sintaks:
  php -l app/Services/OrderService.php
  php -l app/Services/RentalAvailabilityService.php

Perubahan utama:
- Stok jual pending dihitung sebagai reservasi, bukan dikurangi dari stok fisik.
- Stok fisik jual dikurangi satu kali setelah status pembayaran terverifikasi.
- Pesanan gagal/kedaluwarsa melepaskan reservasi melalui perubahan status, tanpa menambah stok fisik.
- Pemeriksaan ketersediaan rental menggunakan interval tanggal dan tetap menahan unit rental yang sudah overdue sampai pengembalian dikonfirmasi.
- Query reservasi mendukung locking saat checkout agar pemeriksaan stok lebih aman terhadap checkout bersamaan.
- Respons status dari Midtrans diverifikasi kecocokan order_id dan gross_amount sebelum diterapkan.

CATATAN:
Ini patch pertama untuk service transaksi. Jangan lakukan uji transaksi akhir sebelum patch lanjutan untuk perlindungan perubahan stok oleh admin dan sinkronisasi pesanan pending terjadwal selesai diterapkan.
