<?php
class Order {
    private $conn; // Properti untuk menyimpan koneksi database
    private $table_name = "orders"; // Nama tabel

    // Properti untuk menyimpan data order
    public $id;
    public $product_id;
    public $customer_id;
    public $customer_name;
    public $customer_phone;
    public $status;

    // Konstruktor untuk menginisialisasi koneksi database
    public function __construct($db) {
        $this->conn = $db;
    }

    // Fungsi untuk membuat order baru
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (product_id, customer_id, customer_name, customer_phone, status) 
                  VALUES 
                  (:product_id, :customer_id, :customer_name, :customer_phone, :status)";

        $stmt = $this->conn->prepare($query);

        // Sanitasi data
        $this->product_id = htmlspecialchars(strip_tags($this->product_id));
        $this->customer_id = htmlspecialchars(strip_tags($this->customer_id));
        $this->customer_name = htmlspecialchars(strip_tags($this->customer_name));
        $this->customer_phone = htmlspecialchars(strip_tags($this->customer_phone));
        $this->status = htmlspecialchars(strip_tags($this->status));

        // Bind data
        $stmt->bindParam(":product_id", $this->product_id);
        $stmt->bindParam(":customer_id", $this->customer_id);
        $stmt->bindParam(":customer_name", $this->customer_name);
        $stmt->bindParam(":customer_phone", $this->customer_phone);
        $stmt->bindParam(":status", $this->status);

        // Eksekusi query
        if ($stmt->execute()) {
            return true;
        }

        return false;
    }
}
?>