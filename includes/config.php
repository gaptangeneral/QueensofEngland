<?php
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

define('ROOT_PATH', realpath(dirname(__FILE__) . '/..'));
define('UPLOAD_DIR', ROOT_PATH . '/public/uploads/');
define('UPLOAD_URL', '/uploads/');

// Create uploads directory if not exists
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
    file_put_contents(UPLOAD_DIR . '.htaccess', "Options -Indexes\n<FilesMatch \"\.(php|php\.)$\">\nOrder Allow,Deny\nDeny from all\n</FilesMatch>");
}
?>