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

// Inisialisasi variabel
$name = $email = $phone = $role = "";
$name_err = $email_err = $phone_err = $role_err = "";

// Memeriksa apakah metode permintaan adalah POST untuk insert atau update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validasi nama
    if (empty(trim($_POST["name"]))) {
        $name_err = "Silakan masukkan nama.";
    } else {
        $name = trim($_POST["name"]);
    }

    // Validasi email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Silakan masukkan email.";
    } elseif (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
        $email_err = "Format email tidak valid.";
    } else {
        $email = trim($_POST["email"]);
    }

    // Validasi telepon
    if (empty(trim($_POST["phone"]))) {
        $phone_err = "Silakan masukkan nomor telepon.";
    } else {
        $phone = trim($_POST["phone"]);
    }

    // Validasi role
    if (empty(trim($_POST["role"]))) {
        $role_err = "Silakan pilih role.";
    } else {
        $role = trim($_POST["role"]);
    }

    // Cek error sebelum memasukkan ke database
    if (empty($name_err) && empty($email_err) && empty($phone_err) && empty($role_err)) {
        if (isset($_POST['update'])) {
            // Update user
            $stmt = $db->prepare("UPDATE users SET name = :name, email = :email, phone = :phone, role = :role WHERE id = :id");
            $stmt->bindParam(':id', $_POST['id']);
        } else {
            // Insert user
            $stmt = $db->prepare("INSERT INTO users (name, email, phone, role) VALUES (:name, :email, :phone, :role)");
        }
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':role', $role);

        if ($stmt->execute()) {
            $message = isset($_POST['update']) ? "Akun berhasil diperbarui!" : "Akun berhasil ditambahkan!";
            // Reset variabel setelah berhasil
            $name = $email = $phone = $role = "";
        } else {
            $message = "Terjadi kesalahan. Silakan coba lagi.";
        }
    }
}

// Memeriksa apakah metode permintaan adalah GET untuk update atau delete
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['action'])) {
    $action = $_GET['action'];
    $user_id = $_GET['id'];

    if ($action == 'delete') {
        $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
        $stmt->bindParam(':id', $user_id);
        if ($stmt->execute()) {
            $message = "Akun berhasil dihapus!";
        } else {
            $message = "Terjadi kesalahan. Silakan coba lagi.";
        }
    } elseif ($action == 'edit') {
        $stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
        $name = $user_data['name'];
        $email = $user_data['email'];
        $phone = $user_data['phone'];
        $role = $user_data['role'];
    }
}

$stmt = $db->prepare("SELECT * FROM users"); // Menyiapkan query untuk mengambil semua pengguna
$stmt->execute(); // Menjalankan query
$users = $stmt->fetchAll(PDO::FETCH_ASSOC); // Mengambil semua hasil query

include '../partials/header.php';
?>

<div class="content container mx-auto px-4 py-8">
    <h2 class="text-2xl font-bold mb-6 text-center">Kelola Akun</h2>
    <div class="bg-white p-6 rounded-lg shadow-md mb-6">
        <h3 class="text-xl font-semibold mb-4"><?php echo isset($_GET['action']) && $_GET['action'] == 'edit' ? 'Edit Akun' : 'Tambah Akun Baru'; ?></h3>
        <?php if (!empty($message)): ?>
            <div class="mb-4 text-green-500 text-sm text-center"><?php echo $message; ?></div>
        <?php endif; ?>
        <form action="manage_accounts.php" method="POST">
            <?php if (isset($_GET['action']) && $_GET['action'] == 'edit'): ?>
                <input type="hidden" name="id" value="<?php echo $_GET['id']; ?>">
            <?php endif; ?>
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium mb-2">Nama</label>
                <input type="text" name="name" id="name" class="w-full px-3 py-2 border rounded-md" value="<?php echo htmlspecialchars($name); ?>" required>
                <span class="text-red-500 text-xs mt-1"><?php echo $name_err; ?></span>
            </div>
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium mb-2">Email</label>
                <input type="email" name="email" id="email" class="w-full px-3 py-2 border rounded-md" value="<?php echo htmlspecialchars($email); ?>" required>
                <span class="text-red-500 text-xs mt-1"><?php echo $email_err; ?></span>
            </div>
            <div class="mb-4">
                <label for="phone" class="block text-sm font-medium mb-2">Telepon</label>
                <input type="text" name="phone" id="phone" class="w-full px-3 py-2 border rounded-md" value="<?php echo htmlspecialchars($phone); ?>" required>
                <span class="text-red-500 text-xs mt-1"><?php echo $phone_err; ?></span>
            </div>
            <div class="mb-4">
                <label for="role" class="block text-sm font-medium mb-2">Role</label>
                <select name="role" id="role" class="w-full px-3 py-2 border rounded-md" required>
                    <option value="">Pilih Role</option>
                    <option value="admin" <?php echo $role == 'admin' ? 'selected' : ''; ?>>Admin</option>
                    <option value="user" <?php echo $role == 'user' ? 'selected' : ''; ?>>User</option>
                </select>
                <span class="text-red-500 text-xs mt-1"><?php echo $role_err; ?></span>
            </div>
            <div class="flex items-center justify-between">
                <button type="submit" name="<?php echo isset($_GET['action']) && $_GET['action'] == 'edit' ? 'update' : 'insert'; ?>" class="bg-blue-600 text-white px-6 py-2 rounded-md font-semibold hover:bg-blue-700 transition duration-300">
                    <?php echo isset($_GET['action']) && $_GET['action'] == 'edit' ? 'Update Akun' : 'Tambah Akun'; ?>
                </button>
            </div>
        </form>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-md overflow-x-auto">
        <h3 class="text-xl font-semibold mb-4">Daftar Akun</h3>
        <table class="w-full">
            <thead>
                <tr>
                    <th class="text-left px-4 py-2">ID</th>
                    <th class="text-left px-4 py-2">Nama</th>
                    <th class="text-left px-4 py-2">Email</th>
                    <th class="text-left px-4 py-2">Telepon</th>
                    <th class="text-left px-4 py-2">Role</th>
                    <th class="text-left px-4 py-2">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td class="px-4 py-2"><?php echo $user['id']; ?></td>
                    <td class="px-4 py-2"><?php echo htmlspecialchars($user['name']); ?></td>
                    <td class="px-4 py-2"><?php echo htmlspecialchars($user['email']); ?></td>
                    <td class="px-4 py-2"><?php echo htmlspecialchars($user['phone']); ?></td>
                    <td class="px-4 py-2"><?php echo htmlspecialchars($user['role']); ?></td>
                    <td class="px-4 py-2">
                        <a href="manage_accounts.php?action=edit&id=<?php echo $user['id']; ?>" class="text-blue-600 hover:underline">Edit</a>
                        <a href="manage_accounts.php?action=delete&id=<?php echo $user['id']; ?>" class="text-red-600 hover:underline" onclick="return confirm('Apakah Anda yakin ingin menghapus akun ini?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../partials/footer.php'; ?>