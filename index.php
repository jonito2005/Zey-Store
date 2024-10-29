<?php 
include 'partials/header.php'; ?>

<div class="container mx-auto p-8">
    <?php if (isset($_GET['welcome']) && $_GET['welcome'] == 1): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Selamat datang, <?php echo htmlspecialchars($_SESSION['name'] ?? ''); ?>!</strong>
        </div>
    <?php endif; ?>
    <h2 class="text-3xl font-bold mb-8 text-center text-white">Produk Digital Terbaik</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php
        include_once 'classes/Database.php';
        include_once 'classes/Product.php';

        $database = new Database();
        $db = $database->getConnection();

        $product = new Product($db);
        $stmt = $product->readAll();
        $imageIndex = 1; // Inisialisasi indeks gambar

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            // Gunakan jalur gambar dari database
            $image = $row['image'];
            // Format harga
            $formatted_price = number_format($price, 0, ',', '.');
            // ... existing code ...
            echo "
            <div class='bg-white bg-opacity-20 backdrop-filter backdrop-blur-lg rounded-lg shadow-lg p-6 overflow-hidden transition-transform duration-300 hover:scale-105'>
                <img src='{$image}' alt='{$name}' class='w-full h-48 object-cover rounded-lg mb-4'>
                <h3 class='text-xl font-bold mb-2 text-white'>{$name}</h3>
                <p class='text-gray-200 mb-4'>{$description}</p>
                <div class='flex justify-between items-center'>
                    <span class='text-2xl font-bold text-white'>Rp {$formatted_price}</span>
                    <button onclick=\"addToCart({$id})\" class='bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600'>
                        Tambah ke Keranjang
                    </button>
                </div>
            </div>
            ";
        }
        ?>
    </div>
</div>

<?php include 'partials/footer.php'; ?>

<!-- Di bagian bawah file, sebelum </body> -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function addToCart(productId) {
    fetch(`checkout/add_to_cart.php?id=${productId}&action=add`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: data.message,
                    showConfirmButton: false,
                    timer: 1500
                });
                // Update jumlah item di keranjang
                document.getElementById('cartItemCount').textContent = data.cartItemCount;
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
                text: 'Terjadi kesalahan saat menambahkan ke keranjang.',
            });
        });
}
</script>