# ZeyStore - Online Shop With Whatsapp

## 📑 Deskripsi
ZeyStore adalah sistem penjualan barang online berbasis whatsapp untuk pembayaranya.

## ✨ Fitur
- Registrasi pengguna dengan validasi form
- Validasi email unik
- Enkripsi password menggunakan PHP password_hash
- Antarmuka pengguna modern dengan efek glassmorphism
- Animasi menggunakan GSAP
- Notifikasi interaktif menggunakan SweetAlert2
- Responsif untuk semua ukuran layar
- Validasi input real-time

## 🔧 Teknologi yang Digunakan
- PHP 7.4+
- MySQL/MariaDB
- HTML5
- CSS3 (Tailwind CSS)
- JavaScript
- GSAP (GreenSock Animation Platform)
- SweetAlert2
- PDO (PHP Data Objects)

## 📋 Prasyarat
- PHP 7.4 atau lebih tinggi
- MySQL/MariaDB
- Web Server (Apache/Nginx)
- Composer (untuk manajemen dependensi)

## 🛠️ Instalasi
1. Clone repositori ini
bash
git clone https://github.com/jonito2005/zeystore.git
2. Buat database baru dan import struktur tabel
sql
CREATE TABLE users (
id INT PRIMARY KEY AUTO_INCREMENT,
name VARCHAR(255) NOT NULL,
email VARCHAR(255) UNIQUE NOT NULL,
phone VARCHAR(15) NOT NULL,
password VARCHAR(255) NOT NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
3. Konfigurasi database
- Buka file `classes/Database.php`
- Sesuaikan pengaturan database:
php
private $host = "localhost";
private $db_name = "nama_database";
private $username = "username_database";
private $password = "password_database";

## 🔒 Fitur Keamanan
- Password di-hash menggunakan algoritma bcrypt
- Proteksi terhadap SQL Injection menggunakan PDO Prepared Statements
- Validasi input untuk mencegah XSS
- Penanganan error yang aman
- Validasi email unik

## 🎨 Tampilan dan UX
- Desain modern dengan efek glassmorphism
- Animasi smooth menggunakan GSAP
- Notifikasi interaktif untuk feedback pengguna
- Responsif untuk desktop dan mobile
- Font Poppins untuk keterbacaan yang baik

## 📝 Penggunaan
1. Buka halaman registrasi
2. Isi form dengan data yang valid:
   - Nama lengkap
   - Alamat email (harus unik)
   - Nomor telepon
   - Password (minimal 6 karakter)
   - Konfirmasi password
3. Sistem akan memvalidasi input dan menampilkan pesan sesuai
4. Jika berhasil, pengguna akan diarahkan ke halaman login

## 🤝 Kontribusi
Kontribusi selalu diterima. Untuk perubahan besar, silakan buka issue terlebih dahulu untuk mendiskusikan perubahan yang diinginkan.

## 📜 Lisensi
[MIT License](LICENSE)

## 👨‍💻 Author
Jonito2005

## 📞 Kontak
- Email: joniyogakusuma2005@gmail.com
- Website: zey.moe
- Facebook: facebook.com

## 🙏 Pengakuan
- Tailwind CSS untuk styling
- GSAP untuk animasi
- SweetAlert2 untuk notifikasi
- Font Poppins dari Google Fonts
