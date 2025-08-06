<?php
// admin/index.php - Tüm bölümlerle düzeltilmiş versiyon

// İnclude dosyaları
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Giriş kontrolü
require_admin_login();

// Değişkenleri başlangıçta tanımla
$success = '';
$error = '';
$current_section = isset($_GET['section']) ? $_GET['section'] : 'general';

// İşlemleri yönet
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_content'])) {
        // İçerik güncelleme - RESİM YÜKLEMESİ EKLENDİ
        $has_error = false;
        
        // Önce resim dosyalarını işle
        if (isset($_FILES) && !empty($_FILES)) {
            foreach ($_FILES as $field_name => $file) {
                if ($file['error'] === UPLOAD_ERR_OK) {
                    $image_url = handle_file_upload($_FILES, $field_name);
                    if ($image_url) {
                        // Resim başarıyla yüklendi, content key'ini belirle
                        $content_key = '';
                        switch($field_name) {
                            case 'about_image':
                                $content_key = 'about_image';
                                break;
                            case 'featured_image':
                                $content_key = 'featured_image';
                                break;
                            case 'hero_image':
                                $content_key = 'hero_image';
                                break;
                            default:
                                $content_key = $field_name;
                        }
                        
                        if ($content_key && !update_content($pdo, $content_key, $image_url)) {
                            $error = 'Resim güncelleme hatası: ' . $field_name;
                            $has_error = true;
                            break;
                        }
                    }
                }
            }
        }
        
        // Metin içeriklerini güncelle
        if (!$has_error) {
            foreach ($_POST as $key => $value) {
                if (strpos($key, 'content_') === 0) {
                    $content_key = substr($key, 8);
                    if (!update_content($pdo, $content_key, $value)) {
                        $error = 'İçerik güncelleme hatası: ' . $content_key;
                        $has_error = true;
                        break;
                    }
                }
            }
        }
        
        if (!$has_error) {
            $success = 'İçerik başarıyla güncellendi!';
        }
    } 
    elseif (isset($_POST['update_logo'])) {
        // Logo güncelleme
        $image_url = handle_file_upload($_FILES, 'site_logo');
        if ($image_url && update_content($pdo, 'site_logo', $image_url)) {
            $success = 'Logo başarıyla güncellendi!';
        } else {
            $error = 'Logo güncelleme hatası!';
        }
    }
    elseif (isset($_POST['add_product'])) {
        // Ürün ekleme
        if (add_product($pdo, $_POST, $_FILES)) {
            $success = 'Ürün başarıyla eklendi!';
        } else {
            $error = 'Ürün ekleme hatası!';
        }
    }
    elseif (isset($_POST['update_product'])) {
        // Ürün güncelleme
        $id = $_POST['product_id'];
        if (update_product($pdo, $id, $_POST, $_FILES)) {
            $success = 'Ürün başarıyla güncellendi!';
        } else {
            $error = 'Ürün güncelleme hatası!';
        }
    }
    elseif (isset($_POST['update_hero'])) {
        // Hero görsel güncelleme
        $image_url = handle_file_upload($_FILES, 'hero_image');
        
        if ($image_url) {
            update_content($pdo, 'hero_image', $image_url);
        }
        
        // Diğer içerikleri güncelle
        update_content($pdo, 'hero_title', $_POST['content_hero_title']);
        update_content($pdo, 'hero_subtitle', $_POST['content_hero_subtitle']);
        
        $success = 'Hero bölümü başarıyla güncellendi!';
    }
}

// Silme işlemi
if (isset($_GET['delete_product'])) {
    $id = $_GET['delete_product'];
    if (delete_product($pdo, $id)) {
        $success = 'Ürün başarıyla silindi!';
    } else {
        $error = 'Ürün silme hatası!';
    }
}

// Site içeriğini al
$content = get_site_content($pdo);
$products = get_products($pdo);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Paneli | Queen's England</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600;700&family=Montserrat:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        /* Genel Stiller */
        body {
            background-color: #f5f7fa;
            color: #333;
            font-family: 'Montserrat', sans-serif;
        }
        
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Başlık Stilleri */
        .section-title {
            color: #6a0c0c;
            font-family: 'Cormorant Garamond', serif;
            font-weight: 700;
            border-bottom: 2px solid #d4af37;
            padding-bottom: 10px;
            margin-bottom: 25px;
        }
        
        /* Form Stilleri */
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #444;
        }
        
        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: #fff;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #d4af37;
            box-shadow: 0 0 0 2px rgba(212, 175, 55, 0.2);
        }
        
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }
        
        /* Buton Stilleri */
        .btn {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 4px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }
        
        .btn-primary {
            background-color: #d4af37;
            color: white;
            border: none;
        }
        
        .btn-primary:hover {
            background-color: #c19d30;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .btn-outline {
            background-color: transparent;
            border: 1px solid #d4af37;
            color: #d4af37;
        }
        
        .btn-outline:hover {
            background-color: #d4af37;
            color: white;
        }
        
        /* Tablo Stilleri */
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border-radius: 8px;
            overflow: hidden;
        }
        
        .admin-table th {
            background-color: #6a0c0c;
            color: white;
            text-align: left;
            padding: 12px 15px;
            font-weight: 500;
        }
        
        .admin-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
        }
        
        .admin-table tr:last-child td {
            border-bottom: none;
        }
        
        .admin-table tr:hover {
            background-color: #f9f9f9;
        }
        
        /* Kart Stilleri */
        .admin-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 25px;
            margin-bottom: 25px;
        }
        
        /* Yan Menü */
        .admin-sidebar {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 15px;
        }
        
        .admin-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .admin-menu li {
            margin-bottom: 5px;
        }
        
        .admin-menu a {
            display: block;
            padding: 10px 15px;
            border-radius: 4px;
            color: #555;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .admin-menu a:hover {
            background-color: #f8f5f0;
            color: #6a0c0c;
        }
        
        .admin-menu a.active {
            background-color: #d4af37;
            color: white;
        }
        
        /* Uyarılar */
        .alert {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        /* Grid Sistemi */
        .admin-grid {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 25px;
        }
        
        /* Önizleme Alanı */
        .preview-box {
            border: 1px dashed #d4af37;
            padding: 15px;
            border-radius: 4px;
            background-color: #f8f5f0;
            margin-top: 10px;
        }
        
        /* Logo Önizleme */
        .logo-preview {
            max-width: 200px;
            max-height: 100px;
            margin-top: 10px;
            border: 1px solid #eee;
            padding: 5px;
            background-color: #fff;
        }
    </style>
</head>
<body>
    <!-- Admin Üst Menü -->
    <nav class="bg-deep-burgundy text-white shadow-md">
        <div class="admin-container">
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <a href="index.php" class="text-xl font-serif font-bold">Admin Paneli</a>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="hidden sm:block"><?= $_SESSION['admin_username'] ?></span>
                    <a href="logout.php" class="btn btn-outline">Çıkış</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="admin-container">
        <!-- Hata/Success Mesajları -->
        <?php if ($success): ?>
        <div class="alert alert-success">
            <?= $success ?>
        </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="alert alert-error">
            <?= $error ?>
        </div>
        <?php endif; ?>
        
        <div class="admin-grid">
            <!-- Yan Menü -->
            <div class="admin-sidebar">
                <h3 class="section-title">Yönetim Paneli</h3>
                <ul class="admin-menu">
                    <li>
                        <a href="?section=general" class="<?= $current_section == 'general' ? 'active' : '' ?>">
                            <i class="fas fa-cog mr-2"></i> Genel Ayarlar
                        </a>
                    </li>
                    <li>
                        <a href="?section=logo" class="<?= $current_section == 'logo' ? 'active' : '' ?>">
                            <i class="fas fa-image mr-2"></i> Logo Yönetimi
                        </a>
                    </li>
                    <li>
                        <a href="?section=hero" class="<?= $current_section == 'hero' ? 'active' : '' ?>">
                            <i class="fas fa-image mr-2"></i> Hero Bölümü
                        </a>
                    </li>
                    <li>
                        <a href="?section=about" class="<?= $current_section == 'about' ? 'active' : '' ?>">
                            <i class="fas fa-info-circle mr-2"></i> Hakkımızda
                        </a>
                    </li>
                    <li>
                        <a href="?section=featured" class="<?= $current_section == 'featured' ? 'active' : '' ?>">
                            <i class="fas fa-star mr-2"></i> Öne Çıkan Ürün
                        </a>
                    </li>
                    <li>
                        <a href="?section=products" class="<?= $current_section == 'products' ? 'active' : '' ?>">
                            <i class="fas fa-box mr-2"></i> Ürün Yönetimi
                        </a>
                    </li>
                    <li>
                        <a href="?section=contact" class="<?= $current_section == 'contact' ? 'active' : '' ?>">
                            <i class="fas fa-envelope mr-2"></i> İletişim Bilgileri
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Ana İçerik -->
            <div>
                <?php if (!$current_section || $current_section == 'general'): ?>
                    <!-- Genel Ayarlar -->
                    <div class="admin-card">
                        <h2 class="section-title">Genel Site Ayarları</h2>
                        <form method="POST" enctype="multipart/form-data">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="form-group">
                                    <label class="form-label" for="site_title">Site Başlığı</label>
                                    <input class="form-control" 
                                           type="text" id="site_title" name="content_site_title" 
                                           value="<?= htmlspecialchars($content['site_title'] ?? 'Queen\'s England') ?>" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="company_name">Şirket Adı</label>
                                    <input class="form-control" 
                                           type="text" id="company_name" name="content_company_name" 
                                           value="<?= htmlspecialchars($content['company_name'] ?? 'TILSIM FOREIGN TRADE INC.') ?>" required>
                                </div>
                                <div class="form-group md:col-span-2">
                                    <label class="form-label" for="footer_description">Footer Açıklama</label>
                                    <textarea class="form-control" 
                                              id="footer_description" name="content_footer_description" rows="3"><?= htmlspecialchars($content['footer_description'] ?? 'Lüks ve zarafetin senfonisi. Seçkin zevklere özel ince işçilikle üretilmiş porselen koleksiyonları.') ?></textarea>
                                </div>
                            </div>
                            <div class="mt-6">
                                <button type="submit" name="update_content" class="btn btn-primary">
                                    Ayarları Kaydet
                                </button>
                            </div>
                        </form>
                    </div>
                
                <?php elseif ($current_section == 'logo'): ?>
                    <!-- Logo Yönetimi -->
                    <div class="admin-card">
                        <h2 class="section-title">Logo Yönetimi</h2>
                        <form method="POST" enctype="multipart/form-data" id="logoForm">
                            <div class="form-group">
                                <label class="form-label" for="site_logo">Site Logosu</label>
                                
                                <?php if (!empty($content['site_logo'])): ?>
                                    <div class="mb-4">
                                        <p class="form-label">Mevcut Logo:</p>
                                        <img src="<?= htmlspecialchars($content['site_logo']) ?>" 
                                             alt="Site Logo" 
                                             class="logo-preview"
                                             id="currentLogo">
                                    </div>
                                <?php endif; ?>
                                
                                <input class="form-control" 
                                       type="file" 
                                       id="site_logo" 
                                       name="site_logo"
                                       accept="image/*"
                                       onchange="previewLogo(event)">
                                <p class="text-sm text-gray-500 mt-2">PNG, JPG veya SVG formatında logo yükleyin (Max 2MB)</p>
                                
                                <!-- Yeni logo önizleme -->
                                <div id="newLogoPreview" class="mt-4 hidden">
                                    <p class="form-label">Yeni Logo Önizleme:</p>
                                    <img id="previewImage" class="logo-preview">
                                </div>
                            </div>
                            
                            <div class="mt-6 flex items-center">
                                <button type="submit" name="update_logo" class="btn btn-primary mr-3">
                                    Logoyu Güncelle
                                </button>
                                <div id="uploadStatus" class="text-sm text-gray-600 hidden">
                                    <i class="fas fa-spinner fa-spin mr-2"></i> Yükleniyor...
                                </div>
                            </div>
                        </form>
                    </div>
                    
                <?php elseif ($current_section == 'hero'): ?>
                    <!-- Hero Bölümü -->
                    <div class="admin-card">
                        <h2 class="section-title">Hero Bölümü Yönetimi</h2>
                        <form method="POST" enctype="multipart/form-data" id="heroForm">
                            <div class="form-group">
                                <label class="form-label" for="hero_title">Başlık</label>
                                <input class="form-control" 
                                       type="text" id="hero_title" name="content_hero_title" 
                                       value="<?= htmlspecialchars($content['hero_title'] ?? 'MAJESTİK ZARAFET') ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="hero_subtitle">Alt Başlık</label>
                                <textarea class="form-control" 
                                          id="hero_subtitle" name="content_hero_subtitle" rows="2"><?= htmlspecialchars($content['hero_subtitle'] ?? 'Lüks ve Zarafetin Senfonisi - Seçkin Zevklere Özel İnce İşçilik') ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="hero_image">Arkaplan Görseli</label>
                                
                                <?php if (!empty($content['hero_image'])): ?>
                                    <div class="mb-4">
                                        <p class="form-label">Mevcut Görsel:</p>
                                        <img src="<?= htmlspecialchars($content['hero_image']) ?>" 
                                             alt="Hero Background" 
                                             class="w-full h-64 object-cover rounded-lg mb-4"
                                             id="currentHeroImage">
                                    </div>
                                <?php endif; ?>
                                
                                <input class="form-control" 
                                       type="file" 
                                       id="hero_image" 
                                       name="hero_image"
                                       accept="image/*"
                                       onchange="previewHeroImage(event)">
                                
                                <!-- Yeni görsel önizleme -->
                                <div id="newHeroPreview" class="mt-4 hidden">
                                    <p class="form-label">Yeni Görsel Önizleme:</p>
                                    <img id="heroPreviewImage" class="w-full h-64 object-cover rounded-lg">
                                </div>
                            </div>
                            
                            <div class="mt-6 flex items-center">
                                <button type="submit" name="update_hero" class="btn btn-primary mr-3">
                                    Kaydet
                                </button>
                                <div id="heroUploadStatus" class="text-sm text-gray-600 hidden">
                                    <i class="fas fa-spinner fa-spin mr-2"></i> Yükleniyor...
                                </div>
                            </div>
                        </form>
                    </div>

                <?php elseif ($current_section == 'about'): ?>
                    <!-- Hakkımızda Düzenleme Formu -->
                    <div class="admin-card">
                        <h2 class="section-title">Hakkımızda Düzenleme</h2>
                        <form method="POST" enctype="multipart/form-data">
                            <div class="form-group">
                                <label class="form-label" for="about_content">İçerik</label>
                                <textarea class="form-control" id="about_content" name="content_about_content" rows="8"><?= htmlspecialchars($content['about_content'] ?? 'Şirketimiz, yirmi yılı aşkın süredir Türkiye\'de perakende ihtiyaçlarını karşılayarak ev eşyası üretiminde öncü konumdadır. Yenilikçiliğe olan bağlılığımız, pazar trendlerinin önünde kalmak için modellerimizi sürekli olarak yenilememizle belirginleşmiştir. Benzersiz tasarımlarımız bizi sadece farklı kılmakla kalmamış, aynı zamanda Türkiye pazarında en çok satan modellerin lider üreticisi konumumuzu da sağlamlaştırmıştır. Gücümüz, en iyi malzemeleri ve eşsiz kalitede el sanatları ürünlerini tedarik etmemizi sağlayan güçlü uluslararası bağlantılarımız sayesinde geleneksel işçiliği modern estetikle harmanlama yeteneğimizde yatmaktadır. Ev eşyalarında lüks ve zarafetle eş anlamlı hale gelen özgün tasarımlarımızla gurur duyuyoruz. Mükemmelliğe olan bağlılığımız, yarattığımız her parçada açıkça görülmekte ve bizi \'Ev Eşyalarında Yenilikçi Mükemmellik\'in simgesi haline getirmektedir.') ?></textarea>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="about_image">Görsel</label>
                                <?php if (!empty($content['about_image'])): ?>
                                    <div class="mb-2">
                                        <p class="form-label">Mevcut Görsel:</p>
                                        <img src="<?= htmlspecialchars($content['about_image']) ?>" alt="Hakkımızda Görsel" class="max-w-xs rounded">
                                    </div>
                                <?php endif; ?>
                                <input class="form-control" 
                                       type="file" id="about_image" name="about_image" accept="image/*">
                                <p class="text-sm text-gray-500 mt-2">PNG, JPG, GIF veya WEBP formatında görsel yükleyin</p>
                            </div>
                            <div class="mt-6">
                                <button type="submit" name="update_content" class="btn btn-primary">
                                    Kaydet
                                </button>
                            </div>
                        </form>
                    </div>
                
                <?php elseif ($current_section == 'featured'): ?>
                    <!-- Öne Çıkan Ürün Düzenleme -->
                    <div class="admin-card">
                        <h2 class="section-title">Öne Çıkan Ürün Düzenleme</h2>
                        <form method="POST" enctype="multipart/form-data">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="form-group">
                                    <label class="form-label" for="featured_name">Ürün Adı</label>
                                    <input class="form-control" 
                                           type="text" id="featured_name" name="content_featured_name" 
                                           value="<?= htmlspecialchars($content['featured_name'] ?? 'NUANCE') ?>" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="featured_quote">Alıntı</label>
                                    <input class="form-control" 
                                           type="text" id="featured_quote" name="content_featured_quote" 
                                           value="<?= htmlspecialchars($content['featured_quote'] ?? 'Nuance, zarafet ve kaliteye olan bağlılığımızın bir kanıtıdır, koleksiyonumuzda imza parçası olarak öne çıkar') ?>" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="featured_price">Fiyat ($)</label>
                                    <input class="form-control" 
                                           type="number" step="0.01" id="featured_price" name="content_featured_price" 
                                           value="<?= htmlspecialchars($content['featured_price'] ?? '989.00') ?>" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="featured_code">Ürün Kodu</label>
                                    <input class="form-control" 
                                           type="text" id="featured_code" name="content_featured_code" 
                                           value="<?= htmlspecialchars($content['featured_code'] ?? 'NUA075') ?>" required>
                                </div>
                                <div class="form-group md:col-span-2">
                                    <label class="form-label" for="featured_description">Açıklama</label>
                                    <textarea class="form-control" 
                                              id="featured_description" name="content_featured_description" rows="4"><?= htmlspecialchars($content['featured_description'] ?? 'Sofra zarafetinin başyapıtı: Zarif setimizle yemek sanatını deneyimleyin. Her bir parça, 24 ayar altın detaylarla süslenmiş nefis Bone China porselenden üretilmiştir.') ?></textarea>
                                </div>
                                <div class="form-group md:col-span-2">
                                    <label class="form-label" for="featured_image">Ürün Görseli</label>
                                    <?php if (!empty($content['featured_image'])): ?>
                                        <div class="mb-2">
                                            <p class="form-label">Mevcut Görsel:</p>
                                            <img src="<?= htmlspecialchars($content['featured_image']) ?>" alt="Öne Çıkan Ürün" class="max-w-xs rounded">
                                        </div>
                                    <?php endif; ?>
                                    <input class="form-control" 
                                           type="file" id="featured_image" name="featured_image" accept="image/*">
                                    <p class="text-sm text-gray-500 mt-2">PNG, JPG, GIF veya WEBP formatında görsel yükleyin</p>
                                </div>
                            </div>
                            <div class="mt-6">
                                <button type="submit" name="update_content" class="btn btn-primary">
                                    Kaydet
                                </button>
                            </div>
                        </form>
                    </div>
                
                <?php elseif ($current_section == 'contact'): ?>
                    <!-- İletişim Bilgileri -->
                    <div class="admin-card">
                        <h2 class="section-title">İletişim Bilgileri</h2>
                        <form method="POST">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="form-group">
                                    <label class="form-label" for="contact_address">Adres</label>
                                    <textarea class="form-control" 
                                              id="contact_address" name="content_contact_address" rows="3"><?= htmlspecialchars($content['contact_address'] ?? 'Tilsim Foreign Trade Inc.<br>İstanbul, Türkiye') ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="contact_phone">Telefon</label>
                                    <input class="form-control" 
                                           type="text" id="contact_phone" name="content_contact_phone" 
                                           value="<?= htmlspecialchars($content['contact_phone'] ?? '+90 212 123 4567') ?>" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="contact_email">E-posta</label>
                                    <input class="form-control" 
                                           type="email" id="contact_email" name="content_contact_email" 
                                           value="<?= htmlspecialchars($content['contact_email'] ?? 'info@queensengland.com') ?>" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="contact_hours">Çalışma Saatleri</label>
                                    <input class="form-control" 
                                           type="text" id="contact_hours" name="content_contact_hours" 
                                           value="<?= htmlspecialchars($content['contact_hours'] ?? 'Pazartesi - Cuma: 09:00 - 18:00') ?>" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="social_instagram">Instagram</label>
                                    <input class="form-control" 
                                           type="text" id="social_instagram" name="content_social_instagram" 
                                           value="<?= htmlspecialchars($content['social_instagram'] ?? '#') ?>" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="social_facebook">Facebook</label>
                                    <input class="form-control" 
                                           type="text" id="social_facebook" name="content_social_facebook" 
                                           value="<?= htmlspecialchars($content['social_facebook'] ?? '#') ?>" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="social_pinterest">Pinterest</label>
                                    <input class="form-control" 
                                           type="text" id="social_pinterest" name="content_social_pinterest" 
                                           value="<?= htmlspecialchars($content['social_pinterest'] ?? '#') ?>" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="social_youtube">YouTube</label>
                                    <input class="form-control" 
                                           type="text" id="social_youtube" name="content_social_youtube" 
                                           value="<?= htmlspecialchars($content['social_youtube'] ?? '#') ?>" required>
                                </div>
                            </div>
                            <div class="mt-6">
                                <button type="submit" name="update_content" class="btn btn-primary">
                                    Kaydet
                                </button>
                            </div>
                        </form>
                    </div>
                
                <?php elseif ($current_section == 'products'): ?>
                    <!-- Ürün Yönetimi -->
                    <div class="admin-card">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="section-title">Ürün Yönetimi</h2>
                            <button id="addProductBtn" class="btn btn-primary">
                                <i class="fas fa-plus mr-2"></i> Yeni Ürün Ekle
                            </button>
                        </div>
                        
                        <!-- Ürün Listesi -->
                        <div class="overflow-x-auto">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Ürün</th>
                                        <th>Fiyat</th>
                                        <th>Kod</th>
                                        <th class="text-center">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td>
                                            <div class="flex items-center">
                                                <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="w-16 h-16 object-cover rounded mr-4">
                                                <span><?= htmlspecialchars($product['name']) ?></span>
                                            </div>
                                        </td>
                                        <td>$<?= number_format($product['price'], 2) ?></td>
                                        <td>#<?= htmlspecialchars($product['code']) ?></td>
                                        <td class="text-center">
                                            <a href="?section=products&edit_product=<?= $product['id'] ?>" class="text-luxury-gold hover:text-deep-burgundy mr-3">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?section=products&delete_product=<?= $product['id'] ?>" class="text-red-600 hover:text-red-800" onclick="return confirm('Bu ürünü silmek istediğinize emin misiniz?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Ürün Ekleme/Düzenleme Formu -->
                        <?php if (isset($_GET['edit_product']) || isset($_GET['add_product'])): ?>
                            <?php
                            $product = ['id' => '', 'name' => '', 'price' => '', 'code' => '', 'image' => ''];
                            $form_title = 'Yeni Ürün Ekle';
                            $form_action = 'add_product';
                            
                            if (isset($_GET['edit_product'])) {
                                $id = $_GET['edit_product'];
                                $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
                                $stmt->execute([$id]);
                                $product = $stmt->fetch();
                                
                                if ($product) {
                                    $form_title = 'Ürünü Düzenle';
                                    $form_action = 'update_product';
                                }
                            }
                            ?>
                            <div class="mt-8 admin-card">
                                <h3 class="section-title"><?= $form_title ?></h3>
                                <form method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div class="form-group">
                                            <label class="form-label" for="product_name">Ürün Adı</label>
                                            <input class="form-control" 
                                                   type="text" id="product_name" name="name" 
                                                   value="<?= htmlspecialchars($product['name']) ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="product_price">Fiyat ($)</label>
                                            <input class="form-control" 
                                                   type="number" step="0.01" id="product_price" name="price" 
                                                   value="<?= htmlspecialchars($product['price']) ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="product_code">Ürün Kodu</label>
                                            <input class="form-control" 
                                                   type="text" id="product_code" name="code" 
                                                   value="<?= htmlspecialchars($product['code']) ?>" required>
                                        </div>
                                        <div class="form-group md:col-span-2">
                                            <label class="form-label" for="product_image">Ürün Görseli</label>
                                            <?php if (!empty($product['image'])): ?>
                                                <div class="mb-2">
                                                    <p class="form-label">Mevcut Görsel:</p>
                                                    <img src="<?= htmlspecialchars($product['image']) ?>" alt="Ürün Görseli" class="max-w-xs rounded">
                                                </div>
                                            <?php endif; ?>
                                            <input class="form-control" 
                                                   type="file" id="product_image" name="image">
                                        </div>
                                    </div>
                                    
                                    <div class="mt-6">
                                        <button type="submit" name="<?= $form_action ?>" class="btn btn-primary mr-3">
                                            Kaydet
                                        </button>
                                        <a href="?section=products" class="btn btn-outline">
                                            İptal
                                        </a>
                                    </div>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Hero görsel önizleme
        function previewHeroImage(event) {
    const input = event.target;
    const previewContainer = document.getElementById('newHeroPreview');
    const previewImage = document.getElementById('heroPreviewImage');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            previewImage.src = e.target.result;
            previewContainer.classList.remove('hidden');
            
            // Mevcut görseli gizle
            const currentImage = document.getElementById('currentHeroImage');
            if (currentImage) {
                currentImage.style.opacity = '0.3';
            }
        }
        
        reader.readAsDataURL(input.files[0]);
    }
}
    function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById(previewId);
            if (preview) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                preview.style.maxWidth = '200px';
                preview.style.margin = '10px 0';
                preview.style.border = '2px solid #d4af37';
                preview.style.borderRadius = '4px';
            }
        }
        reader.readAsDataURL(input.files[0]);
    }
}

        // Logo önizleme
        function previewLogo(event) {
    const input = event.target;
    const previewContainer = document.getElementById('newLogoPreview');
    const previewImage = document.getElementById('previewImage');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            previewImage.src = e.target.result;
            previewContainer.classList.remove('hidden');
            
            // Mevcut logoyu gizle
            const currentLogo = document.getElementById('currentLogo');
            if (currentLogo) {
                currentLogo.style.opacity = '0.3';
            }
        }
        
        reader.readAsDataURL(input.files[0]);
    } else {
        previewContainer.classList.add('hidden');
        const currentLogo = document.getElementById('currentLogo');
        if (currentLogo) {
            currentLogo.style.opacity = '1';
        }
    }
}

        // AJAX ile logo form gönderimi
        document.getElementById('logoForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const form = this;
            const status = document.getElementById('uploadStatus');
            const submitBtn = form.querySelector('button[type="submit"]');
            
            // Butonu devre dışı bırak ve yükleme göstergesini aç
            submitBtn.disabled = true;
            status.classList.remove('hidden');
            
            const formData = new FormData(form);
            
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                // Sayfayı yenile
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            })
            .catch(error => {
                console.error('Error:', error);
                status.innerHTML = '<i class="fas fa-exclamation-circle text-red-500 mr-2"></i> Hata oluştu!';
                setTimeout(() => {
                    status.classList.add('hidden');
                    submitBtn.disabled = false;
                }, 3000);
            });
        });

        // AJAX ile hero form gönderimi
        document.getElementById('heroForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const form = this;
            const status = document.getElementById('heroUploadStatus');
            const submitBtn = form.querySelector('button[type="submit"]');
            
            submitBtn.disabled = true;
            status.classList.remove('hidden');
            
            const formData = new FormData(form);
            formData.append('update_hero', '1'); // İşlem tipini belirt
            
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            })
            .catch(error => {
                console.error('Error:', error);
                status.innerHTML = '<i class="fas fa-exclamation-circle text-red-500 mr-2"></i> Hata oluştu!';
                setTimeout(() => {
                    status.classList.add('hidden');
                    submitBtn.disabled = false;
                }, 3000);
            });
        });

        // Ürün ekleme butonu
        document.getElementById('addProductBtn')?.addEventListener('click', function() {
            window.location.href = '?section=products&add_product=1';
        });
    </script>
</body>
</html>