# Woelandari Coffee Lab

Website coffeeshop berbasis PHP native dan MySQL untuk Woelandari Coffee Lab.

## Demo

Lihat rekaman demo penggunaan website:

<video src="docs/woelandari-demo.mp4" controls width="720"></video>

Jika video tidak tampil di GitHub, buka langsung file ini: [docs/woelandari-demo.mp4](docs/woelandari-demo.mp4).

## Fitur

- Halaman publik: homepage, about, menu, gallery/event, lokasi, community, dan rating pelanggan.
- Login staf/admin.
- Admin panel untuk dashboard, menu, gallery/event, feedback, dan user.
- Halaman kasir sederhana dengan checkout dan nota transaksi.

## Setup Lokal XAMPP

1. Simpan folder proyek di `C:\xampp\htdocs\woelandari_coffeshop`.
2. Jalankan Apache dan MySQL dari XAMPP.
3. Import database:

```bash
C:\xampp\mysql\bin\mysql.exe -u root < database\db_cafe.sql
```

4. Buka website:

```text
http://localhost/woelandari_coffeshop/
```

## Akun Awal

```text
username: admin
password: admin123
role: superadmin

username: kasir
password: kasir123
role: admin
```

## Catatan

- Konfigurasi database ada di `config/koneksi.php`.
- Login mendukung hash lama MD5 untuk akun bawaan, lalu otomatis memigrasikan password ke `password_hash()` setelah berhasil login.
- Query CRUD masih memakai mysqli manual. Untuk produksi, gunakan prepared statements.
