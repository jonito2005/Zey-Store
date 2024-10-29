<?php
session_start(); // Memulai sesi
require_once '../classes/Database.php'; // Memuat kelas Database

// Cek apakah pengguna adalah admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    header("location: ../auth/login.php");
    exit();
}

$database = new Database(); // Membuat instance dari kelas Database
$db = $database->getConnection(); // Mendapatkan koneksi database

// Memeriksa apakah metode permintaan adalah POST dan parameter order_id serta status ada
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['order_id']) && isset($_POST['status'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];

    $stmt = $db->prepare("UPDATE orders SET status = :status WHERE id = :id");
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':id', $order_id);
    $stmt->execute();
}

$stmt = $db->prepare("SELECT * FROM orders"); // Menyiapkan query untuk mengambil semua order
$stmt->execute(); // Menjalankan query
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC); // Mengambil semua hasil query

include '../partials/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h2 class="text-2xl font-bold mb-6 text-center">Kelola Orderan</h2>
    <div class="bg-white p-6 rounded-lg shadow-md">
        <table class="w-full">
            <thead>
                <tr>
                    <th class="text-left">ID</th>
                    <th class="text-left">Nama Pelanggan</th>
                    <th class="text-left">Telepon</th>
                    <th class="text-left">Status</th>
                    <th class="text-left">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?php echo $order['id']; ?></td>
                    <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                    <td><?php echo htmlspecialchars($order['customer_phone']); ?></td>
                    <td><?php echo htmlspecialchars($order['status']); ?></td>
                    <td>
                        <form method="POST" class="inline">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            <select name="status" onchange="this.form.submit()">
                                <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="success" <?php echo $order['status'] == 'success' ? 'selected' : ''; ?>>Success</option>
                            </select>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../partials/footer.php'; ?>