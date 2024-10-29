<?php
session_start();

include_once '../classes/Database.php';

// Hitung total belanja
$subtotal = 0;

// Cek apakah ada data cart temporary
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo "<script>
        console.log('Checking for tempCart');
        if (sessionStorage.getItem('tempCart')) {
            console.log('tempCart found');
            var tempCart = JSON.parse(sessionStorage.getItem('tempCart'));
            sessionStorage.removeItem('tempCart');
            // Kirim data cart ke server menggunakan AJAX
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'restore_cart.php', true);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.onload = function() {
                console.log('Server response:', xhr.responseText);
                if (xhr.status === 200) {
                    location.reload();
                }
            };
            xhr.onerror = function() {
                console.error('Error in AJAX request');
            };
            console.log('Sending cart data:', JSON.stringify(tempCart));
            xhr.send(JSON.stringify(tempCart));
        } else {
            console.log('No tempCart found');
        }
    </script>";
}

if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
    foreach ($_SESSION['cart'] as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
} else {
    // echo "Debug: Cart is empty or not set<br>";
}

$total = $subtotal; // Pastikan total dihitung di sini

// Fungsi untuk memformat harga
function formatPrice($price) {
    return number_format($price, 0, ',', '.');
}

// Ambil informasi pelanggan jika pengguna sudah login
$name = '';
$phone = '';
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    $database = new Database();
    $db = $database->getConnection();

    $stmt = $db->prepare("SELECT name, phone FROM users WHERE id = :id");
    $stmt->bindParam(':id', $_SESSION['id']);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $name = $user['name'];
        $phone = $user['phone'];
    }
}

// Proses pembayaran ketika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $phone = $_POST['phone'];

    $database = new Database();
    $db = $database->getConnection();

    // Update informasi pengguna jika sudah login
    if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
        $stmt = $db->prepare("UPDATE users SET name = :name, phone = :phone WHERE id = :id");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':id', $_SESSION['id']);
        $stmt->execute();
    }

    // Siapkan pesan WhatsApp
    $whatsappMessage = "Halo Admin, saya ingin melakukan pemesanan:\n\n";
    $whatsappMessage .= "Nama: $name\n";
    $whatsappMessage .= "Nomor Telepon: $phone\n\n";
    $whatsappMessage .= "Detail Pesanan:\n";
    foreach ($_SESSION['cart'] as $item) {
        $whatsappMessage .= "{$item['name']} ({$item['quantity']}x) - Rp " . number_format($item['price'] * $item['quantity'], 0, ',', '.') . "\n";
    }
    $whatsappMessage .= "\nTotal: Rp " . number_format($total, 0, ',', '.');

    // Kosongkan keranjang setelah pesanan dilakukan
    unset($_SESSION['cart']);

    // Arahkan ke WhatsApp
    $whatsappUrl = 'https://api.whatsapp.com/send/?phone=6281244863011&text=' . urlencode($whatsappMessage) . '&type=phone_number&app_absent=0';
    
    // Kirim respons JSON
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'url' => $whatsappUrl]);
    exit();
}
?>

<?php include '../partials/header.php'; ?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6 text-white">Checkout</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Formulir Informasi Pelanggan -->
        <div class="bg-white bg-opacity-20 backdrop-filter backdrop-blur-lg rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-semibold mb-4 text-white">Informasi Pelanggan</h2>
            <form id="checkoutForm" method="POST">
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-white mb-1">Nama</label>
                    <input type="text" id="name" name="name" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?php echo htmlspecialchars($name); ?>" required>
                </div>
                <div class="mb-4">
                    <label for="phone" class="block text-sm font-medium text-white mb-1">Nomor Telepon</label>
                    <input type="tel" id="phone" name="phone" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?php echo htmlspecialchars($phone); ?>" required>
                </div>
                <button type="submit" class="w-full bg-blue-600 text-white font-semibold py-2 px-4 rounded-md mt-6 hover:bg-blue-700 transition duration-300">Bayar Sekarang</button>
            </form>
        </div>
        
        <!-- Ringkasan Pesanan -->
        <div class="bg-white bg-opacity-20 backdrop-filter backdrop-blur-lg rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-semibold mb-4 text-white">Ringkasan Pesanan</h2>
            <div class="space-y-4">
                <div class="flex justify-between text-white">
                    <span>Subtotal</span>
                    <span>Rp <?php echo formatPrice($subtotal); ?></span>
                </div>
                <div class="flex justify-between font-semibold text-white">
                    <span>Total</span>
                    <span>Rp <?php echo formatPrice($total); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkoutForm = document.getElementById('checkoutForm');
    checkoutForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(checkoutForm);
        
        fetch('checkout.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Pesanan Berhasil',
                    text: 'Terima kasih atas pesanan Anda!',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    window.location.href = data.url;
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Terjadi kesalahan saat memproses pesanan Anda.',
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Terjadi kesalahan saat memproses pesanan Anda.',
            });
        });
    });
});
</script>

<?php include '../partials/footer.php'; ?>