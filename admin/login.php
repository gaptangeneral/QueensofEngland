<?php
session_start();

// 2. Dosyaları include et (sıralama önemli!)
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/functions.php'; // is_admin_logged_in burada
require_once __DIR__ . '/../includes/auth.php';      // login_admin burada

// Zaten giriş yapmışsa yönlendir
if (is_admin_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = '';

// Form gönderilmişse
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (login_admin($username, $password)) {
        header('Location: index.php');
        exit;
    } else {
        $error = 'Geçersiz kullanıcı adı veya şifre!';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Girişi | Queen's England</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .login-bg {
            background: linear-gradient(135deg, #f8f5f0 0%, #e6e2d9 100%);
        }
    </style>
</head>
<body class="login-bg min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full bg-white p-10 rounded-xl shadow-lg border border-luxury-gold">
        <div class="text-center mb-10">
            <h2 class="text-3xl font-serif font-bold text-deep-burgundy mb-2">ADMİN GİRİŞİ</h2>
            <div class="gold-divider w-32 mx-auto"></div>
            <p class="text-gray-600 mt-4">Lütfen yetkili hesabınızla giriş yapın</p>
        </div>
        
        <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <?= $error ?>
        </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-6">
                <label class="block text-charcoal text-sm font-medium mb-2" for="username">Kullanıcı Adı</label>
                <input class="w-full px-4 py-3 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-luxury-gold" 
                       type="text" id="username" name="username" placeholder="admin" required>
            </div>
            <div class="mb-8">
                <label class="block text-charcoal text-sm font-medium mb-2" for="password">Şifre</label>
                <input class="w-full px-4 py-3 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-luxury-gold" 
                       type="password" id="password" name="password" placeholder="••••••••" required>
            </div>
            <button type="submit" class="bg-luxury-gold text-white px-8 py-3 font-medium rounded hover:bg-opacity-90 transition w-full">
                GİRİŞ YAP
            </button>
        </form>
    </div>
</body>
</html>