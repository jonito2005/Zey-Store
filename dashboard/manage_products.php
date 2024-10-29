<?php
session_start(); // Memulai sesi
require_once '../classes/Database.php'; // Memuat kelas Database
require_once '../classes/Product.php'; // Memuat kelas Product

// Cek apakah pengguna adalah admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    header("location: ../auth/login.php");
    exit();
}

$database = new Database(); // Membuat instance dari kelas Database
$db = $database->getConnection(); // Mendapatkan koneksi database

$product = new Product($db); // Membuat instance dari kelas Product

// Inisialisasi variabel
$name = $description = $price = "";
$name_err = $description_err = $price_err = $image_err = "";

// Direktori untuk menyimpan gambar yang diupload
$target_dir = "../images/";

// Memeriksa apakah metode permintaan adalah POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validasi nama produk
    if (empty(trim($_POST["name"]))) {
        $name_err = "Silakan masukkan nama produk.";
    } else {
        $name = trim($_POST["name"]);
    }

    // Validasi deskripsi produk
    if (empty(trim($_POST["description"]))) {
        $description_err = "Silakan masukkan deskripsi produk.";
    } else {
        $description = trim($_POST["description"]);
    }

    // Validasi harga produk
    if (empty(trim($_POST["price"]))) {
        $price_err = "Silakan masukkan harga produk.";
    } elseif (!is_numeric($_POST["price"])) {
        $price_err = "Harga produk harus berupa angka.";
    } else {
        $price = trim($_POST["price"]);
    }

    // Validasi gambar produk
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $allowed = ["jpg" => "image/jpeg", "jpeg" => "image/jpeg", "png" => "image/png"];
        $filename = $_FILES["image"]["name"];
        $filetype = $_FILES["image"]["type"];
        $filesize = $_FILES["image"]["size"];

        // Verifikasi ekstensi file
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if (!array_key_exists(strtolower($ext), $allowed)) {
            $image_err = "Ekstensi file tidak diperbolehkan. Hanya JPG, JPEG, dan PNG yang diperbolehkan.";
        }

        // Verifikasi tipe MIME
        if (in_array($filetype, $allowed)) {
            // Verifikasi ukuran file (maks 5MB)
            if ($filesize > 5 * 1024 * 1024) {
                $image_err = "Ukuran file terlalu besar. Maksimal 5MB.";
            }
        } else {
            $image_err = "Tipe file tidak valid.";
        }

        // Jika tidak ada error, proses upload
        if (empty($image_err)) {
            // Buat nama file unik
            $new_filename = uniqid() . "." . $ext;
            $target_file = $target_dir . $new_filename;

            // Move uploaded file
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                // Simpan path relatif ke database
                $image = "images/" . $new_filename;
            } else {
                $image_err = "Terjadi kesalahan saat mengupload file.";
            }
        }
    } else {
        $image_err = "Silakan unggah gambar produk.";
    }

    // Cek error sebelum memasukkan ke database
    if (empty($name_err) && empty($description_err) && empty($price_err) && empty($image_err)) {
        $product->name = $name;
        $product->description = $description;
        $product->price = $price;
        $product->image = $image;

        if ($product->create()) {
            $message = "Produk berhasil ditambahkan!";
            // Reset variabel setelah berhasil
            $name = $description = $price = "";
        } else {
            $message = "Terjadi kesalahan. Silakan coba lagi.";
        }
    }
}

// Memeriksa apakah metode permintaan adalah GET untuk update atau delete
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['action'])) {
    $action = $_GET['action'];
    $product_id = $_GET['id'];

    if ($action == 'delete') {
        $product->id = $product_id;
        if ($product->delete()) {
            $message = "Produk berhasil dihapus!";
        } else {
            $message = "Terjadi kesalahan. Silakan coba lagi.";
        }
    } elseif ($action == 'edit') {
        $product->id = $product_id;
        $product_data = $product->readOne();
        $name = $product_data['name'];
        $description = $product_data['description'];
        $price = $product_data['price'];
        $image = $product_data['image'];
    }
}

// Memeriksa apakah metode permintaan adalah POST untuk update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $product->id = $_POST['id'];
    $product->name = $_POST['name'];
    $product->description = $_POST['description'];
    $product->price = $_POST['price'];
    $product->image = $_POST['image'];

    if ($product->update()) {
        $message = "Produk berhasil diperbarui!";
    } else {
        $message = "Terjadi kesalahan. Silakan coba lagi.";
    }
}

$stmt = $product->readAll(); // Mengambil semua produk
$products = $stmt->fetchAll(PDO::FETCH_ASSOC); // Mengambil semua hasil query

include '../partials/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h2 class="text-2xl font-bold mb-6 text-center">Kelola Produk</h2>
    <div class="bg-white p-6 rounded-lg shadow-md mb-6">
        <h3 class="text-xl font-semibold mb-4"><?php echo isset($_GET['action']) && $_GET['action'] == 'edit' ? 'Edit Produk' : 'Tambah Produk Baru'; ?></h3>
        <?php if (!empty($message)): ?>
            <div class="mb-4 text-green-500 text-sm text-center"><?php echo $message; ?></div>
        <?php endif; ?>
        <form action="manage_products.php" method="POST" enctype="multipart/form-data">
            <?php if (isset($_GET['action']) && $_GET['action'] == 'edit'): ?>
                <input type="hidden" name="id" value="<?php echo $_GET['id']; ?>">
            <?php endif; ?>
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium mb-2">Nama Produk</label>
                <input type="text" name="name" id="name" class="w-full px-3 py-2 border rounded-md" value="<?php echo htmlspecialchars($name); ?>" required>
                <span class="text-red-500 text-xs mt-1"><?php echo $name_err; ?></span>
            </div>
            <div class="mb-4">
                <label for="description" class="block text-sm font-medium mb-2">Deskripsi Produk</label>
                <textarea name="description" id="description" class="w-full px-3 py-2 border rounded-md" required><?php echo htmlspecialchars($description); ?></textarea>
                <span class="text-red-500 text-xs mt-1"><?php echo $description_err; ?></span>
            </div>
            <div class="mb-4">
                <label for="price" class="block text-sm font-medium mb-2">Harga Produk</label>
                <input type="text" name="price" id="price" class="w-full px-3 py-2 border rounded-md" value="<?php echo htmlspecialchars($price); ?>" required>
                <span class="text-red-500 text-xs mt-1"><?php echo $price_err; ?></span>
            </div>
            <div class="mb-4">
                <label for="image" class="block text-sm font-medium mb-2">Gambar Produk</label>
                <input type="file" name="image" id="image" accept="image/*" class="w-full px-3 py-2 border rounded-md" required>
                <span class="text-red-500 text-xs mt-1"><?php echo $image_err; ?></span>
            </div>
            <div class="flex items-center justify-between">
                <button type="submit" name="<?php echo isset($_GET['action']) && $_GET['action'] == 'edit' ? 'update' : 'insert'; ?>" class="bg-blue-600 text-white px-6 py-2 rounded-md font-semibold hover:bg-blue-700 transition duration-300">
                    <?php echo isset($_GET['action']) && $_GET['action'] == 'edit' ? 'Update Produk' : 'Tambah Produk'; ?>
                </button>
            </div>
        </form>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-xl font-semibold mb-4">Daftar Produk</h3>
        <table class="w-full">
            <thead>
                <tr>
                    <th class="text-left px-4 py-2">ID</th>
                    <th class="text-left px-4 py-2">Nama</th>
                    <th class="text-left px-4 py-2">Deskripsi</th>
                    <th class="text-left px-4 py-2">Harga</th>
                    <th class="text-left px-4 py-2">Gambar</th>
                    <th class="text-left px-4 py-2">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                <tr>
                    <td class="px-4 py-2"><?php echo $product['id']; ?></td>
                    <td class="px-4 py-2"><?php echo htmlspecialchars($product['name']); ?></td>
                    <td class="px-4 py-2"><?php echo htmlspecialchars($product['description']); ?></td>
                    <td class="px-4 py-2">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></td>
                    <td class="px-4 py-2">
                        <img src="../<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-16 h-16 object-cover">
                    </td>
                    <td class="px-4 py-2">
                        <a href="manage_products.php?action=edit&id=<?php echo $product['id']; ?>" class="text-blue-600 hover:underline">Edit</a>
                        <a href="manage_products.php?action=delete&id=<?php echo $product['id']; ?>" class="text-red-600 hover:underline" onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../partials/footer.php'; ?>