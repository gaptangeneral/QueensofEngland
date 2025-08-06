<?php
// includes/config.php - Çalışan versiyon

// Veritabanı bağlantı bilgileri
define('DB_HOST', 'localhost');
define('DB_NAME', 'queens_england_db');
define('DB_USER', 'root');
define('DB_PASSWORD', '');

// Oturum başlatma
session_start();

// Hata raporlama
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Yol tanımlamaları
define('ROOT_PATH', realpath(dirname(__FILE__) . '/..'));

// Upload klasörü tanımlamaları - Test sonuçlarına göre düzeltildi
define('UPLOAD_DIR', ROOT_PATH . '/public/uploads/');
define('UPLOAD_URL', 'public/uploads/'); // Relative path - En iyi çalışan format

// Create uploads directory if not exists
if (!is_dir(UPLOAD_DIR)) {
    if (mkdir(UPLOAD_DIR, 0755, true)) {
        // .htaccess dosyası oluştur
        $htaccess_content = "Options -Indexes\n";
        $htaccess_content .= "<FilesMatch \"\.(php|php3|php4|php5|phtml|pl|py|jsp|asp|sh|cgi)$\">\n";
        $htaccess_content .= "    Require all denied\n";
        $htaccess_content .= "</FilesMatch>\n";
        $htaccess_content .= "<FilesMatch \"\.(jpg|jpeg|png|gif|webp|svg|ico)$\">\n";
        $htaccess_content .= "    Require all granted\n";
        $htaccess_content .= "</FilesMatch>";
        
        file_put_contents(UPLOAD_DIR . '.htaccess', $htaccess_content);
    }
}

// Debug
error_log("UPLOAD_DIR: " . UPLOAD_DIR);
error_log("UPLOAD_URL: " . UPLOAD_URL);
?>