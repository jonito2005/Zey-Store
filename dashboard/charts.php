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

// Fetch data for charts
$order_stmt = $db->prepare("SELECT DATE(created_at) as date, SUM(products.price * orders.quantity) as revenue 
                            FROM orders 
                            JOIN products ON orders.product_id = products.id 
                            WHERE orders.status = 'success' 
                            GROUP BY DATE(created_at)");
$order_stmt->execute();
$order_data = $order_stmt->fetchAll(PDO::FETCH_ASSOC);

$user_stmt = $db->prepare("SELECT DATE(created_at) as date, COUNT(*) as count FROM users GROUP BY DATE(created_at)");
$user_stmt->execute();
$user_data = $user_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Chart - DigiStore</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h2 class="text-2xl font-bold mb-6 text-center">Chart</h2>
        <div class="bg-white p-6 rounded-lg shadow-md mb-6">
            <h3 class="text-xl font-semibold mb-4">Chart Pendapatan</h3>
            <canvas id="orderChart"></canvas>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-xl font-semibold mb-4">Chart User</h3>
            <canvas id="userChart"></canvas>
        </div>
    </div>

    <script>
        // Data untuk chart pendapatan
        const orderLabels = <?php echo json_encode(array_column($order_data, 'date')); ?>;
        const orderRevenues = <?php echo json_encode(array_column($order_data, 'revenue')); ?>;

        // Data untuk chart user
        const userLabels = <?php echo json_encode(array_column($user_data, 'date')); ?>;
        const userCounts = <?php echo json_encode(array_column($user_data, 'count')); ?>;

        // Chart Pendapatan
        const orderCtx = document.getElementById('orderChart').getContext('2d');
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

        // Chart User
        const userCtx = document.getElementById('userChart').getContext('2d');
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
</body>
</html>