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

// Ambil data untuk grafik pendapatan
$order_stmt = $db->prepare("SELECT DATE(created_at) as date, SUM(products.price) as revenue 
                            FROM orders 
                            JOIN products ON orders.product_id = products.id 
                            WHERE orders.status = 'success' 
                            GROUP BY DATE(created_at)");
$order_stmt->execute();
$order_data = $order_stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil data untuk grafik pengguna
$user_stmt = $db->prepare("SELECT DATE(created_at) as date, COUNT(*) as count FROM users GROUP BY DATE(created_at)");
$user_stmt->execute();
$user_data = $user_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include '../partials/header.php'; ?>

<div class="container mx-auto px-4 py-8">
    <h2 class="text-2xl font-bold mb-6 text-center">Dashboard Admin</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-8">
        <a href="manage_orders.php" class="bg-white p-6 rounded-lg shadow-md hover:bg-gray-100 transition duration-300">
            <h3 class="text-xl font-semibold mb-2">Kelola Orderan</h3>
            <p>Kelola orderan pending atau sukses.</p>
        </a>
        <a href="manage_accounts.php" class="bg-white p-6 rounded-lg shadow-md hover:bg-gray-100 transition duration-300">
            <h3 class="text-xl font-semibold mb-2">Kelola Akun</h3>
            <p>Kelola akun pengguna.</p>
        </a>
        <a href="manage_products.php" class="bg-white p-6 rounded-lg shadow-md hover:bg-gray-100 transition duration-300">
            <h3 class="text-xl font-semibold mb-2">Kelola Produk</h3>
            <p>Tambah, edit, dan hapus produk.</p>
        </a>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-xl font-semibold mb-4">Pendapatan</h3>
            <div style="height: 300px;">
                <canvas id="orderChart"></canvas>
            </div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-xl font-semibold mb-4">User</h3>
            <div style="height: 300px;">
                <canvas id="userChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const orderData = <?php echo json_encode($order_data); ?>;
    const userData = <?php echo json_encode($user_data); ?>;

    const orderLabels = orderData.map(data => data.date);
    const orderRevenues = orderData.map(data => data.revenue);

    const userLabels = userData.map(data => data.date);
    const userCounts = userData.map(data => data.count);

    const orderCtx = document.getElementById('orderChart').getContext('2d');
    const userCtx = document.getElementById('userChart').getContext('2d');

    new Chart(orderCtx, {
        type: 'pie',
        data: {
            labels: orderLabels,
            datasets: [{
                label: 'Pendapatan',
                data: orderRevenues,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(54, 162, 235, 0.2)',
                    'rgba(255, 206, 86, 0.2)',
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(153, 102, 255, 0.2)',
                    'rgba(255, 159, 64, 0.2)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed !== null) {
                                label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(context.parsed);
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });

    new Chart(userCtx, {
        type: 'line',
        data: {
            labels: userLabels,
            datasets: [{
                label: 'User Registrations',
                data: userCounts,
                borderColor: 'rgba(153, 102, 255, 1)',
                backgroundColor: 'rgba(153, 102, 255, 0.2)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    beginAtZero: true
                },
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>

<?php include '../partials/footer.php'; ?>