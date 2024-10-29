<?php
session_start(); // Memulai sesi
require_once '../classes/Database.php'; // Memuat kelas Database

$database = new Database(); // Membuat instance dari kelas Database
$db = $database->getConnection(); // Mendapatkan koneksi database

// Inisialisasi variabel
$name = $email = $phone = $password = $confirm_password = "";
$name_err = $email_err = $phone_err = $password_err = $confirm_password_err = $register_err = "";

// Memeriksa apakah metode permintaan adalah POST
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
    } else {
        $email = trim($_POST["email"]);
    }

    // Validasi nomor telepon
    if (empty(trim($_POST["phone"]))) {
        $phone_err = "Silakan masukkan nomor telepon.";
    } else {
        $phone = trim($_POST["phone"]);
    }

    // Validasi password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Silakan masukkan password.";
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "Password minimal 6 karakter.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validasi konfirmasi password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Silakan konfirmasi password.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Password tidak cocok.";
        }
    }

    // Cek kesalahan input sebelum memasukkan ke database
    if (empty($name_err) && empty($email_err) && empty($phone_err) && empty($password_err) && empty($confirm_password_err)) {
        // Periksa apakah email sudah ada
        $sql = "SELECT id FROM users WHERE email = :email";
        if ($stmt = $db->prepare($sql)) {
            $stmt->bindParam(":email", $email, PDO::PARAM_STR);
            if ($stmt->execute()) {
                if ($stmt->rowCount() == 1) {
                    $email_err = "Email sudah terdaftar.";
                    echo '<script>
                            Swal.fire({
                                icon: "error",
                                title: "Registrasi Gagal",
                                text: "Email sudah terdaftar.",
                            });
                          </script>';
                }
            } else {
                $register_err = "Terjadi kesalahan. Silakan coba lagi.";
            }
            unset($stmt);
        }

        // Jika tidak ada kesalahan, masukkan data ke database
        if (empty($email_err)) {
            $sql = "INSERT INTO users (name, email, phone, password) VALUES (:name, :email, :phone, :password)";
            if ($stmt = $db->prepare($sql)) {
                $stmt->bindParam(":name", $name, PDO::PARAM_STR);
                $stmt->bindParam(":email", $email, PDO::PARAM_STR);
                $stmt->bindParam(":phone", $phone, PDO::PARAM_STR);
                // Hash password sebelum disimpan
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt->bindParam(":password", $hashed_password, PDO::PARAM_STR);

                if ($stmt->execute()) {
                    // Menampilkan pesan sukses dan redirect ke halaman login
                    echo '<script>
                            Swal.fire({
                                icon: "success",
                                title: "Registrasi Berhasil",
                                text: "Akun Anda telah dibuat. Silakan login.",
                                showConfirmButton: false,
                                timer: 1500
                            }).then(function() {
                                window.location.href = "login.php";
                            });
                          </script>';
                    exit();
                } else {
                    $register_err = "Terjadi kesalahan. Silakan coba lagi.";
                }
                unset($stmt);
            }
        }
    }
    unset($db);
}

if (!empty($register_err)) {
    echo '<script>
            Swal.fire({
                icon: "error",
                title: "Registrasi Gagal",
                text: "' . $register_err . '",
            });
          </script>';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - ZeyStore</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.9.1/gsap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        .glassmorphism {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-400 via-indigo-500 to-purple-500 min-h-screen flex items-center justify-center p-4">
    <div class="container mx-auto">
        <div class="max-w-md w-full mx-auto glassmorphism p-6 sm:p-8 shadow-lg opacity-0" id="registerForm">
            <h2 class="text-2xl sm:text-3xl font-bold mb-6 text-center text-white">Register</h2>
            <form action="register.php" method="POST">
                <div class="mb-4">
                    <label for="name" class="block text-white text-sm font-medium mb-2">Nama</label>
                    <input type="text" name="name" id="name" class="w-full px-3 py-2 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-md text-white placeholder-white placeholder-opacity-70" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" required>
                    <span class="text-red-300 text-xs mt-1"><?php echo isset($name_err) ? $name_err : ''; ?></span>
                </div>
                <div class="mb-4">
                    <label for="email" class="block text-white text-sm font-medium mb-2">Email</label>
                    <input type="email" name="email" id="email" class="w-full px-3 py-2 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-md text-white placeholder-white placeholder-opacity-70" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                    <span class="text-red-300 text-xs mt-1"><?php echo isset($email_err) ? $email_err : ''; ?></span>
                </div>
                <div class="mb-4">
                    <label for="phone" class="block text-white text-sm font-medium mb-2">Nomor Telepon</label>
                    <input type="text" name="phone" id="phone" class="w-full px-3 py-2 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-md text-white placeholder-white placeholder-opacity-70" value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>" required>
                    <span class="text-red-300 text-xs mt-1"><?php echo isset($phone_err) ? $phone_err : ''; ?></span>
                </div>
                <div class="mb-4">
                    <label for="password" class="block text-white text-sm font-medium mb-2">Password</label>
                    <input type="password" name="password" id="password" class="w-full px-3 py-2 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-md text-white placeholder-white placeholder-opacity-70" required>
                    <span class="text-red-300 text-xs mt-1"><?php echo isset($password_err) ? $password_err : ''; ?></span>
                </div>
                <div class="mb-6">
                    <label for="confirm_password" class="block text-white text-sm font-medium mb-2">Konfirmasi Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" class="w-full px-3 py-2 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-md text-white placeholder-white placeholder-opacity-70" required>
                    <span class="text-red-300 text-xs mt-1"><?php echo isset($confirm_password_err) ? $confirm_password_err : ''; ?></span>
                </div>
                <div class="flex flex-col sm:flex-row items-center justify-between">
                    <button type="submit" class="w-full sm:w-auto bg-white text-indigo-600 px-6 py-2 rounded-md font-semibold hover:bg-opacity-90 transition duration-300 mb-4 sm:mb-0">Register</button>
                    <a href="login.php" class="text-sm text-white hover:underline">Sudah punya akun? Login</a>
                </div>
            </form>
        </div>
    </div>
    <script>
        gsap.to("#registerForm", {duration: 1, opacity: 1, y: 20, ease: "power3.out"});
    </script>
</body>
</html>