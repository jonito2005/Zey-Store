<?php
class Database {
    // Properti untuk menyimpan informasi koneksi database
    private $host = 'localhost';
    private $db_name = 'zeystore';
    private $username = 'root';
    private $password = '';
    public $conn;

    // Fungsi untuk mendapatkan koneksi database
    public function getConnection() {
        $this->conn = null; // Inisialisasi koneksi ke null
        try {
            // Membuat koneksi PDO
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            // Mode error PDO
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            // Menangani kesalahan koneksi
            echo "Connection error: " . $exception->getMessage();
            exit();
        }
        return $this->conn; // Mengembalikan koneksi
    }
}
?>