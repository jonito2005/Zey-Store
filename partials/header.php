<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Hitung jumlah item di keranjang
$cartItemCount = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cartItemCount += $item['quantity'];
    }
}

// Tentukan root URL
$rootUrl = (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
$rootPath = dirname($_SERVER['PHP_SELF']);
$rootPath = str_replace('/checkout', '', $rootPath); // Hapus '/checkout' jika ada
$rootPath = str_replace('/auth', '', $rootPath); // Hapus '/auth' jika ada
$rootPath = str_replace('/dashboard', '', $rootPath); // Hapus '/dashboard' jika ada
$rootUrl .= $rootPath;
$rootUrl = rtrim($rootUrl, '/');
?>
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zey Store</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.9.1/gsap.min.js"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            color: #333;
            position: relative;
            overflow-x: hidden;
        }
        .content {
            position: relative;
            z-index: 1;
        }
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 0;
        }
        .particle {
            position: absolute;
            background-color: rgba(255, 255, 255, 0.6);
            border-radius: 50%;
        }
        .glassmorphism {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
    </style>
    <!-- Tambahkan ini sebelum tag </head> -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gradient-to-r from-purple-500 to-indigo-600 flex flex-col min-h-screen">
    <div class="particles" id="particles"></div>
    <nav class="bg-white bg-opacity-20 backdrop-filter backdrop-blur-lg text-white p-4 sticky top-0 z-50">
        <div class="container mx-auto flex flex-wrap justify-between items-center">
            <h1 class="text-2xl font-bold">ZeyStore</h1>
            <button class="lg:hidden" onclick="toggleMenu()">
                <i class="fas fa-bars"></i>
            </button>
            <div id="menu" class="hidden w-full lg:flex lg:w-auto lg:items-center">
                <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                    <span class="block mt-4 lg:inline-block lg:mt-0 mr-4">Halo, <?php echo htmlspecialchars($_SESSION['name'] ?? ''); ?>!</span>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <a href="<?php echo $rootUrl; ?>/dashboard/admin_dashboard.php" class="block mt-4 lg:inline-block lg:mt-0 mr-4"><i class="fas fa-tachometer-alt"></i> Dashboard Admin</a>
                    <?php endif; ?>
                    <a href="<?php echo $rootUrl; ?>/index.php" class="block mt-4 lg:inline-block lg:mt-0 mr-4"><i class="fas fa-home"></i> Beranda</a>
                    <a href="<?php echo $rootUrl; ?>/checkout/cart.php" class="block mt-4 lg:inline-block lg:mt-0 mr-4">
                        <i class="fas fa-shopping-cart"></i> Keranjang
                        <span id="cartItemCount" class="bg-red-500 text-white rounded-full px-2 py-1 text-xs"><?php echo $cartItemCount; ?></span>
                    </a>
                    <a href="<?php echo $rootUrl; ?>/checkout/purchase_history.php" class="block mt-4 lg:inline-block lg:mt-0 mr-4"><i class="fas fa-history"></i> Riwayat Pembelian</a>
                    <a href="<?php echo $rootUrl; ?>/auth/logout.php" class="block mt-4 lg:inline-block lg:mt-0"><i class="fas fa-sign-out-alt"></i> Logout</a>
                <?php else: ?>
                    <a href="<?php echo $rootUrl; ?>/index.php" class="block mt-4 lg:inline-block lg:mt-0 mr-4"><i class="fas fa-home"></i> Beranda</a>
                    <a href="<?php echo $rootUrl; ?>/checkout/cart.php" class="block mt-4 lg:inline-block lg:mt-0 mr-4">
                        <i class="fas fa-shopping-cart"></i> Keranjang
                        <span id="cartItemCount" class="bg-red-500 text-white rounded-full px-2 py-1 text-xs"><?php echo $cartItemCount; ?></span>
                    </a>
                    <a href="<?php echo $rootUrl; ?>/auth/login.php" class="block mt-4 lg:inline-block lg:mt-0 mr-4"><i class="fas fa-sign-in-alt"></i> Login</a>
                    <a href="<?php echo $rootUrl; ?>/auth/register.php" class="block mt-4 lg:inline-block lg:mt-0"><i class="fas fa-user-plus"></i> Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <div class="content flex-grow">

    <script>
        function createParticles() {
            const particlesContainer = document.getElementById('particles');
            const particleCount = 100;

            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.classList.add('particle');
                particle.style.width = `${Math.random() * 5 + 1}px`;
                particle.style.height = particle.style.width;
                particle.style.left = `${Math.random() * 100}vw`;
                particle.style.top = `${Math.random() * 100}vh`;
                
                particlesContainer.appendChild(particle);

                gsap.to(particle, {
                    x: `random(-100, 100)`,
                    y: `random(-100, 100)`,
                    duration: `random(3, 6)`,
                    repeat: -1,
                    yoyo: true,
                    ease: "power1.inOut"
                });
            }
        }

        window.addEventListener('load', createParticles);

        function toggleMenu() {
            const menu = document.getElementById('menu');
            menu.classList.toggle('hidden');
        }
    </script>