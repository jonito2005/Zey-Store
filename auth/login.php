<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ZeyStore</title>
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
        <div class="max-w-md w-full mx-auto glassmorphism p-6 sm:p-8 shadow-lg opacity-0" id="loginForm">
            <h2 class="text-2xl sm:text-3xl font-bold mb-6 text-center text-white">Login</h2>
            <form action="login.php" method="POST">
                <div class="mb-4">
                    <label for="email" class="block text-white text-sm font-medium mb-2">Email</label>
                    <input type="email" name="email" id="email" class="w-full px-3 py-2 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-md text-white placeholder-white placeholder-opacity-70" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                    <span class="text-red-300 text-xs mt-1"><?php echo isset($email_err) ? $email_err : ''; ?></span>
                </div>
                <div class="mb-6">
                    <label for="password" class="block text-white text-sm font-medium mb-2">Password</label>
                    <input type="password" name="password" id="password" class="w-full px-3 py-2 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-md text-white placeholder-white placeholder-opacity-70" required>
                    <span class="text-red-300 text-xs mt-1"><?php echo isset($password_err) ? $password_err : ''; ?></span>
                </div>
                <div class="flex flex-col sm:flex-row items-center justify-between">
                    <button type="submit" class="w-full sm:w-auto bg-white text-indigo-600 px-6 py-2 rounded-md font-semibold hover:bg-opacity-90 transition duration-300 mb-4 sm:mb-0">Login</button>
                    <a href="register.php" class="text-sm text-white hover:underline">Belum punya akun? Register</a>
                </div>
            </form>
        </div>
    </div>
    <script>
        gsap.to("#loginForm", {duration: 1, opacity: 1, y: 20, ease: "power3.out"});
    </script>
    <?php
    session_start(); // Memulai sesi
    require_once '../classes/Database.php'; // Memuat kelas Database

    $database = new Database(); // Membuat instance dari kelas Database
    $db = $database->getConnection(); // Mendapatkan koneksi database

    // Inisialisasi variabel
    $email = $password = "";
    $email_err = $password_err = $login_err = "";

    // Memeriksa apakah metode permintaan adalah POST
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Validasi email
        if (empty(trim($_POST["email"]))) {
            $email_err = "Silakan masukkan email.";
        } else {
            $email = trim($_POST["email"]);
        }

        // Validasi password
        if (empty(trim($_POST["password"]))) {
            $password_err = "Silakan masukkan password.";
        } else {
            $password = trim($_POST["password"]);
        }

        // Cek kredensial
        if (empty($email_err) && empty($password_err)) {
            $sql = "SELECT id, name, email, password, role FROM users WHERE email = :email";
            if ($stmt = $db->prepare($sql)) {
                $stmt->bindParam(":email", $email, PDO::PARAM_STR);
                if ($stmt->execute()) {
                    if ($stmt->rowCount() == 1) {
                        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $id = $row["id"];
                            $name = $row["name"];
                            $hashed_password = $row["password"];
                            $role = $row["role"];
                            if (password_verify($password, $hashed_password)) {
                                // Password benar, mulai sesi
                                session_regenerate_id();
                                $_SESSION["loggedin"] = true;
                                $_SESSION["id"] = $id;
                                $_SESSION["name"] = $name;
                                $_SESSION["email"] = $email;
                                $_SESSION["role"] = $role;

                                // Menampilkan pesan sukses dan redirect berdasarkan peran
                                echo '<script>
                                        Swal.fire({
                                            icon: "success",
                                            title: "Login Berhasil",
                                            text: "Selamat datang, ' . $name . '!",
                                            showConfirmButton: false,
                                            timer: 1500
                                        }).then(function() {
                                            window.location.href = "' . ($role == 'admin' ? '../dashboard/admin_dashboard.php' : '../index.php?welcome=1') . '";
                                        });
                                      </script>';
                                exit();
                            } else {
                                $login_err = "Password salah.";
                            }
                        }
                    } else {
                        $login_err = "Email tidak ditemukan.";
                    }
                } else {
                    echo "Terjadi kesalahan. Silakan coba lagi.";
                }
                unset($stmt);
            }
        }
        unset($db);
    }

    if (!empty($login_err)) {
        echo '<script>
                Swal.fire({
                    icon: "error",
                    title: "Login Gagal",
                    text: "' . $login_err . '",
                });
              </script>';
    }
    ?>
</body>
</html>