<?php 
session_start();
include '../partials/header.php';
include_once '../classes/Database.php';
include_once '../classes/Order.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
        $database = new Database();
        $db = $database->getConnection();

        $order = new Order($db);
        $order->customer_id = $_SESSION['id'];
        $order->customer_name = $_SESSION['name'];
        $order->customer_phone = $_SESSION['phone'] ?? ''; // Tambahkan pengecekan
        $orderSuccess = true;

        if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $item) {
                $order->product_id = $item['id'];
                $order->status = 'pending';

                if (!$order->create()) {
                    $orderSuccess = false;
                    echo "<script>
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Error: Gagal menyimpan pesanan ke database.',
                        });
                    </script>";
                    break;
                }
            }

            if ($orderSuccess) {
                // Simpan cart ke variabel temporary sebelum dihapus
                $tempCart = $_SESSION['cart'];
                // Tambahkan logging
                error_log("Saving cart to sessionStorage: " . json_encode($tempCart));
                echo "<script>
                    console.log('Saving cart to sessionStorage:', " . json_encode($tempCart) . ");
                    sessionStorage.setItem('tempCart', '" . json_encode($tempCart) . "');
                    window.location.href='checkout.php';
                </script>";
                exit();
            }
        } else {
            echo "<script>
                Swal.fire({
                    icon: 'info',
                    title: 'Keranjang Belanja Kosong',
                    text: 'Keranjang belanja Anda kosong.',
                    showConfirmButton: false,
                    timer: 1500
                }).then(function() {
                    window.location.href='index.php';
                });
            </script>";
            exit();
        }
    } else {
        // Jika pengguna tidak login, arahkan ke halaman checkout tanpa menyimpan ke database
        echo "<script>window.location.href='checkout.php';</script>";
        exit();
    }
}
?>

<div class="container mx-auto p-8">
    <h2 class="text-3xl font-bold mb-8 text-center text-white">Keranjang Belanja</h2>
    <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
        <div class="bg-white bg-opacity-20 backdrop-filter backdrop-blur-lg rounded-lg shadow-lg p-6">
            <table class="w-full">
                <thead>
                    <tr class="border-b-2 border-gray-300">
                        <th class="text-left py-3">Produk</th>
                        <th class="text-left py-3">Harga</th>
                        <th class="text-left py-3">Jumlah</th>
                        <th class="text-left py-3">Total</th>
                        <th class="text-left py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $total = 0;
                    foreach ($_SESSION['cart'] as $item):
                        $subtotal = $item['price'] * $item['quantity'];
                        $total += $subtotal;
                    ?>
                    <tr class="border-b border-gray-200">
                        <td class="py-4">
                            <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="w-16 h-16 object-cover">
                            <?php echo htmlspecialchars($item['name']); ?>
                        </td>
                        <td class="py-4">Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></td>
                        <td class="py-4">
                            <div class="flex items-center">
                                <button onclick="updateCart(<?php echo $item['id']; ?>, 'remove')" class="bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600 transition-colors duration-300">-</button>
                                <span class="mx-2"><?php echo $item['quantity']; ?></span>
                                <button onclick="updateCart(<?php echo $item['id']; ?>, 'add')" class="bg-green-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-green-600 transition-colors duration-300">+</button>
                            </div>
                        </td>
                        <td class="py-4">Rp <?php echo number_format($subtotal, 0, ',', '.'); ?></td>
                        <td class="py-4">
                            <button onclick="removeItem(<?php echo $item['id']; ?>)" class="text-red-500 hover:text-red-700 transition-colors duration-300">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-right font-bold py-4">Total:</td>
                        <td class="font-bold py-4">Rp <?php echo number_format($total, 0, ',', '.'); ?></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
            <div class="mt-6 text-right">
                <form method="POST">
                    <button type="submit" name="checkout" class="bg-blue-600 text-white px-6 py-2 rounded-full hover:bg-blue-700 transition-colors duration-300">Lanjutkan ke Pembayaran</button>
                </form>
            </div>
        </div>
    <?php else: ?>
        <p class="text-center text-white text-xl">Keranjang belanja Anda kosong.</p>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function updateCart(productId, action) {
    fetch(`add_to_cart.php?id=${productId}&action=${action}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: data.message,
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: data.message,
                });
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Terjadi kesalahan saat memperbarui keranjang.',
            });
        });
}

function removeItem(productId) {
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Anda akan menghapus item ini dari keranjang.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`add_to_cart.php?id=${productId}&action=remove_all`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire(
                            'Terhapus!',
                            data.message,
                            'success'
                        ).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: data.message,
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Terjadi kesalahan saat menghapus item.',
                    });
                });
        }
    });
}
</script>

<?php include '../partials/footer.php'; ?>