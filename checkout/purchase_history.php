<?php
session_start();
require_once '../classes/Database.php';

// Cek apakah pengguna sudah login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: auth/login.php");
    exit();
}

// Mendapatkan ID pengguna dari sesi
$user_id = $_SESSION['id'];

// Membuat instance dari kelas Database
$database = new Database();
$db = $database->getConnection();

// Query untuk mendapatkan riwayat pembelian pengguna
$query = "SELECT orders.id, products.name, products.price, orders.status, orders.created_at 
          FROM orders 
          JOIN products ON orders.product_id = products.id 
          WHERE orders.customer_id = :user_id 
          ORDER BY orders.created_at DESC";

$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../partials/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h2 class="text-3xl font-bold mb-8 text-center text-white">Riwayat Pembelian</h2>
    <?php if (count($orders) > 0): ?>
        <div class="bg-white bg-opacity-20 backdrop-filter backdrop-blur-lg rounded-lg shadow-lg p-6 overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-gray-200 bg-opacity-50">
                        <th class="border border-gray-300 px-4 py-2">ID Order</th>
                        <th class="border border-gray-300 px-4 py-2">Nama Produk</th>
                        <th class="border border-gray-300 px-4 py-2">Harga</th>
                        <th class="border border-gray-300 px-4 py-2">Status</th>
                        <th class="border border-gray-300 px-4 py-2">Tanggal Pembelian</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr class="hover:bg-gray-100 hover:bg-opacity-50">
                        <td class="border border-gray-300 px-4 py-2"><?php echo htmlspecialchars($order['id']); ?></td>
                        <td class="border border-gray-300 px-4 py-2"><?php echo htmlspecialchars($order['name']); ?></td>
                        <td class="border border-gray-300 px-4 py-2">Rp <?php echo number_format($order['price'], 0, ',', '.'); ?></td>
                        <td class="border border-gray-300 px-4 py-2"><?php echo htmlspecialchars($order['status']); ?></td>
                        <td class="border border-gray-300 px-4 py-2"><?php echo htmlspecialchars($order['created_at']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-center text-gray-200">Anda belum memiliki riwayat pembelian.</p>
    <?php endif; ?>
</div>

<?php include '../partials/footer.php'; ?>