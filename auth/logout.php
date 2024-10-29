<?php
session_start(); // Memulai sesi
$_SESSION = array(); // Mengosongkan semua variabel sesi
session_destroy(); // Menghancurkan sesi
header("location: ../"); // Mengarahkan ke halaman utama
exit();
?>