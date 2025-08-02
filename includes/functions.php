<?php


function db_connect() {
    require_once 'config.php';
    
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8";
        $pdo = new PDO($dsn, DB_USER, DB_PASSWORD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // ← DÜZELTİLDİ
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        die("Veritabanı bağlantı hatası: " . $e->getMessage());
    }
}
function is_admin_logged_in() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}
function get_site_content($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM site_content");
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $content = [];
        foreach ($result as $row) {
            $content[$row['key_name']] = $row['value'];
        }
        
        return $content;
    } catch (PDOException $e) {
        error_log("get_site_content hatası: " . $e->getMessage());
        return [];
    }
}

function get_products($pdo) {
    $stmt = $pdo->query("SELECT * FROM products");
    return $stmt->fetchAll();
}

function handle_file_upload($file, $field_name) {
    if ($file[$field_name]['error'] !== UPLOAD_ERR_OK) {
        error_log("Upload error: " . $file[$field_name]['error']);
        return null;
    }
    
    // Strict file type validation
    $allowed_mimes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
        'image/svg+xml' => 'svg'
    ];
    
    $file_info = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($file_info, $file[$field_name]['tmp_name']);
    finfo_close($file_info);
    
    if (!isset($allowed_mimes[$mime_type])) {
        error_log("Invalid MIME type: " . $mime_type);
        return null;
    }
    
    // Generate unique filename with timestamp
    $extension = $allowed_mimes[$mime_type];
    $filename = 'img_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    $destination = UPLOAD_DIR . $filename;
    
    if (move_uploaded_file($file[$field_name]['tmp_name'], $destination)) {
        return UPLOAD_URL . $filename;
    }
    
    error_log("Failed to move uploaded file to: " . $destination);
    return null;
}
function update_content($pdo, $key, $value) {
    try {
        // Önce anahtarı kontrol et
        $stmt = $pdo->prepare("SELECT id FROM site_content WHERE key_name = ?");
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        
        if ($row) {
            // Varsa güncelle
            $stmt = $pdo->prepare("UPDATE site_content SET value = ? WHERE key_name = ?");
            $stmt->execute([$value, $key]);
        } else {
            // Yoksa ekle
            $stmt = $pdo->prepare("INSERT INTO site_content (key_name, value) VALUES (?, ?)");
            $stmt->execute([$key, $value]);
        }
        
        return true;
    } catch (PDOException $e) {
        error_log("update_content hatası: " . $e->getMessage());
        return false;
    }
}

function add_product($pdo, $data, $file) {
    $image_url = handle_file_upload($file, 'image');
    if (!$image_url) {
        return false;
    }
    
    $stmt = $pdo->prepare("INSERT INTO products (name, price, code, image) 
                           VALUES (:name, :price, :code, :image)");
    return $stmt->execute([
        'name' => $data['name'],
        'price' => $data['price'],
        'code' => $data['code'],
        'image' => $image_url
    ]);
}

function update_product($pdo, $id, $data, $file) {
    $update_data = [
        'id' => $id,
        'name' => $data['name'],
        'price' => $data['price'],
        'code' => $data['code']
    ];
    
    if ($file['image']['error'] === UPLOAD_ERR_OK) {
        $image_url = handle_file_upload($file, 'image');
        if ($image_url) {
            $update_data['image'] = $image_url;
        }
    }
    
    $sql = "UPDATE products SET 
            name = :name, 
            price = :price, 
            code = :code";
    
    if (isset($update_data['image'])) {
        $sql .= ", image = :image";
    }
    
    $sql .= " WHERE id = :id";
    
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($update_data);
}

function delete_product($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    return $stmt->execute([$id]);
}
?>