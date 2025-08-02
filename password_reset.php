<?php
require_once 'includes/config.php';
require_once 'includes/db_connect.php';

$username = 'admin';
$new_password = 'admin123';
$hash = password_hash($new_password, PASSWORD_BCRYPT);

$stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE username = ?");
$stmt->execute([$hash, $username]);

echo "Yeni şifre oluşturuldu: $new_password<br>";
echo "Hash: $hash";
?>