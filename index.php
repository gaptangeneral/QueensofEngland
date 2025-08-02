<?php
require_once 'includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

// Site içeriğini veritabanından al
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASSWORD
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}

// Site içeriğini veritabanından al
$content = get_site_content($pdo);

// Hata ayıklama için içeriği kontrol et
if (empty($content)) {
    echo "<h1>HATA: Site içeriği alınamadı!</h1>";
    echo "<p>Lütfen aşağıdakileri kontrol edin:</p>";
    echo "<ul>";
    echo "<li>Veritabanı bağlantı bilgileri</li>";
    echo "<li>site_content tablosunun varlığı</li>";
    echo "<li>Tablo içindeki veriler</li>";
    echo "</ul>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr" class="scroll-smooth">
<head>
    <?php
function cache_bust($url) {
    if (file_exists($_SERVER['DOCUMENT_ROOT'] . parse_url($url, PHP_URL_PATH))) {
        return $url . '?v=' . filemtime($_SERVER['DOCUMENT_ROOT'] . parse_url($url, PHP_URL_PATH));
    }
    return $url;
}
?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($content['site_title']) ?> | Lüks Porselen ve Altın Detaylı Servis Takımları</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600;700&family=Montserrat:wght@300;400;500&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'luxury-gold': '#d4af37',
                        'deep-burgundy': '#6a0c0c',
                        'ivory': '#f8f5f0',
                        'charcoal': '#333333'
                    },
                    fontFamily: {
                        'serif': ['Cormorant Garamond', 'serif'],
                        'sans': ['Montserrat', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <style>
        .hero-bg {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('<?= htmlspecialchars($content['hero_image']) ?>') no-repeat center center;
            background-size: cover;
        }
        .product-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        .featured-product {
            background: linear-gradient(to right, #f8f5f0 50%, #ffffff 50%);
        }
        .admin-panel {
            display: none;
        }
        .gold-border {
            border: 1px solid #d4af37;
        }
        .gold-divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, #d4af37, transparent);
        }
    </style>
</head>
<body class="font-sans text-charcoal bg-ivory">
    <!-- Üst Navigasyon -->
    <nav class="bg-white shadow-sm fixed w-full z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                <a href="#" class="flex items-center">
                    <?php if (!empty($content['site_logo'])): ?>
                        <!-- Logo varsa görüntüle -->
                        <img src="<?= htmlspecialchars($content['site_logo']) ?>" 
                             alt="<?= htmlspecialchars($content['site_title'] ?? 'Site Logo') ?>" 
                             class="h-10 md:h-12">
                    <?php else: ?>
                        <!-- Logo yoksa metin başlığı göster -->
                        <span class="text-2xl font-serif font-bold text-deep-burgundy">
                            <?= htmlspecialchars($content['site_title'] ?? 'Untitled Site') ?>
                        </span>
                    <?php endif; ?>
                </a>
            </div>
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#about" class="text-charcoal hover:text-luxury-gold transition">Hakkımızda</a>
                    <a href="#collections" class="text-charcoal hover:text-luxury-gold transition">Koleksiyonlar</a>
                    <a href="#featured" class="text-charcoal hover:text-luxury-gold transition">Koleksiyonun Yıldızı</a>
                    <a href="#contact" class="text-charcoal hover:text-luxury-gold transition">İletişim</a>
                    <?php if (is_admin_logged_in()): ?>
                        <a href="admin/" class="text-sm bg-luxury-gold text-white px-4 py-2 rounded hover:bg-opacity-90 transition">Admin Paneli</a>
                        <a href="admin/logout.php" class="text-sm bg-deep-burgundy text-white px-4 py-2 rounded hover:bg-opacity-90 transition">Çıkış</a>
                    <?php else: ?>
                        <a href="admin/login.php" class="text-sm bg-luxury-gold text-white px-4 py-2 rounded hover:bg-opacity-90 transition">Admin Girişi</a>
                    <?php endif; ?>
                </div>
                <div class="md:hidden flex items-center">
                    <button id="mobileMenuBtn" class="text-charcoal">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Mobil Menü -->
        <div id="mobileMenu" class="md:hidden hidden bg-white shadow-lg">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                <a href="#about" class="block px-3 py-2 rounded-md text-base font-medium text-charcoal hover:bg-gray-50">Hakkımızda</a>
                <a href="#collections" class="block px-3 py-2 rounded-md text-base font-medium text-charcoal hover:bg-gray-50">Koleksiyonlar</a>
                <a href="#featured" class="block px-3 py-2 rounded-md text-base font-medium text-charcoal hover:bg-gray-50">Koleksiyonun Yıldızı</a>
                <a href="#contact" class="block px-3 py-2 rounded-md text-base font-medium text-charcoal hover:bg-gray-50">İletişim</a>
                <?php if (is_admin_logged_in()): ?>
                    <a href="admin/" class="block px-3 py-2 rounded-md text-base font-medium text-luxury-gold">Admin Paneli</a>
                    <a href="admin/logout.php" class="block px-3 py-2 rounded-md text-base font-medium text-deep-burgundy">Çıkış</a>
                <?php else: ?>
                    <a href="admin/login.php" class="block px-3 py-2 rounded-md text-base font-medium text-luxury-gold">Admin Girişi</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero Bölümü -->
<section class="hero-bg min-h-screen flex items-center pt-16"
         style="background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('<?= cache_bust(htmlspecialchars($content['hero_image'] ?? 'default-hero.jpg')) ?>') no-repeat center center; background-size: cover;">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
            <h1 class="text-5xl md:text-7xl font-serif font-bold text-white mb-4"><?= htmlspecialchars($content['hero_title']) ?></h1>
            <p class="text-xl md:text-2xl text-luxury-gold font-serif italic max-w-3xl mx-auto mb-10">
                <?= htmlspecialchars($content['hero_subtitle']) ?>
            </p>
            <div class="mt-12">
                <a href="#collections" class="bg-luxury-gold text-white px-8 py-3 font-medium rounded hover:bg-opacity-90 transition mr-4">KEŞFET</a>
                <a href="#featured" class="bg-transparent border border-luxury-gold text-luxury-gold px-8 py-3 font-medium rounded hover:bg-luxury-gold hover:text-white transition">ÖNE ÇIKAN</a>
            </div>
        </div>
    </section>

    <!-- Hakkımızda -->
    <section id="about" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-serif font-bold text-deep-burgundy mb-4">HAKKIMIZDA</h2>
                <div class="gold-divider w-32 mx-auto"></div>
            </div>
            
            <div class="flex flex-col md:flex-row gap-12 items-center">
                <div class="md:w-1/2">
                    <div class="relative">
                        <img src="<?= htmlspecialchars($content['about_image']) ?>" alt="Hakkımızda" class="w-full h-96 object-cover rounded-lg">
                        <div class="absolute -bottom-6 -right-6 w-48 h-48 border-4 border-luxury-gold bg-ivory"></div>
                    </div>
                </div>
                <div class="md:w-1/2">
                    <?= $content['about_content'] ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Koleksiyonlar -->
    <section id="collections" class="py-20 bg-ivory">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-serif font-bold text-deep-burgundy mb-4">KOLEKSİYONLARIMIZ</h2>
                <div class="gold-divider w-32 mx-auto"></div>
                <p class="text-lg text-charcoal max-w-3xl mx-auto mt-6">
                    <?= htmlspecialchars($content['collections_intro']) ?>
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php
                $products = get_products($pdo);
                foreach ($products as $product): 
                ?>
                <div class="product-card bg-white rounded-lg overflow-hidden shadow-md">
                    <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="w-full h-64 object-cover">
                    <div class="p-6">
                        <h3 class="text-xl font-serif font-semibold text-deep-burgundy mb-2"><?= htmlspecialchars($product['name']) ?></h3>
                        <p class="text-lg text-charcoal font-medium mb-2">$<?= number_format($product['price'], 2) ?></p>
                        <p class="text-sm text-gray-500">Ürün Kodu: #<?= htmlspecialchars($product['code']) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Öne Çıkan Ürün -->
    <section id="featured" class="featured-product py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-serif font-bold text-deep-burgundy mb-4">KOLEKSİYONUN YILDIZI</h2>
                <div class="gold-divider w-32 mx-auto"></div>
            </div>
            
            <div class="flex flex-col lg:flex-row items-center">
                <div class="lg:w-1/2 mb-10 lg:mb-0 lg:pr-10">
                    <div class="relative">
                        <img src="<?= htmlspecialchars($content['featured_image']) ?>" alt="Öne Çıkan Ürün" class="w-full h-96 object-cover rounded-lg">
                        <div class="absolute -top-6 -left-6 w-24 h-24 border-4 border-luxury-gold bg-ivory"></div>
                    </div>
                </div>
                <div class="lg:w-1/2">
                    <h3 class="text-3xl font-serif font-bold text-deep-burgundy mb-4"><?= htmlspecialchars($content['featured_name']) ?></h3>
                    <p class="text-lg text-charcoal mb-6 italic">
                        "<?= htmlspecialchars($content['featured_quote']) ?>"
                    </p>
                    <div class="flex items-center mb-6">
                        <p class="text-2xl font-bold text-luxury-gold mr-4">$<?= number_format($content['featured_price'], 2) ?></p>
                        <p class="text-sm text-gray-600">Ürün Kodu: #<?= htmlspecialchars($content['featured_code']) ?></p>
                    </div>
                    <p class="text-lg text-charcoal mb-8">
                        <?= htmlspecialchars($content['featured_description']) ?>
                    </p>
                    <button class="bg-luxury-gold text-white px-8 py-3 font-medium rounded hover:bg-opacity-90 transition">
                        DETAYLARI GÖR
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- İletişim -->
    <section id="contact" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-serif font-bold text-deep-burgundy mb-4">İLETİŞİM</h2>
                <div class="gold-divider w-32 mx-auto"></div>
                <p class="text-lg text-charcoal max-w-3xl mx-auto mt-6">
                    Bizimle iletişime geçmek için aşağıdaki formu doldurabilir veya iletişim bilgilerimizi kullanabilirsiniz.
                </p>
            </div>
            
            <div class="flex flex-col md:flex-row gap-10">
                <div class="md:w-1/2">
                    <form action="send_message.php" method="POST">
                        <div class="mb-6">
                            <label class="block text-charcoal text-sm font-medium mb-2" for="name">Adınız Soyadınız</label>
                            <input class="w-full px-4 py-3 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-luxury-gold" type="text" id="name" name="name" required>
                        </div>
                        <div class="mb-6">
                            <label class="block text-charcoal text-sm font-medium mb-2" for="email">E-posta Adresiniz</label>
                            <input class="w-full px-4 py-3 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-luxury-gold" type="email" id="email" name="email" required>
                        </div>
                        <div class="mb-6">
                            <label class="block text-charcoal text-sm font-medium mb-2" for="message">Mesajınız</label>
                            <textarea class="w-full px-4 py-3 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-luxury-gold" id="message" name="message" rows="5" required></textarea>
                        </div>
                        <button class="bg-luxury-gold text-white px-8 py-3 font-medium rounded hover:bg-opacity-90 transition w-full">
                            GÖNDER
                        </button>
                    </form>
                </div>
                <div class="md:w-1/2">
                    <div class="bg-ivory p-8 rounded-lg h-full">
                        <h3 class="text-xl font-serif font-bold text-deep-burgundy mb-6">İletişim Bilgileri</h3>
                        <div class="space-y-4">
                            <div class="flex items-start">
                                <i class="fas fa-map-marker-alt text-luxury-gold mt-1 mr-4"></i>
                                <p class="text-charcoal"><?= htmlspecialchars($content['contact_address']) ?></p>
                            </div>
                            <div class="flex items-start">
                                <i class="fas fa-phone text-luxury-gold mt-1 mr-4"></i>
                                <p class="text-charcoal"><?= htmlspecialchars($content['contact_phone']) ?></p>
                            </div>
                            <div class="flex items-start">
                                <i class="fas fa-envelope text-luxury-gold mt-1 mr-4"></i>
                                <p class="text-charcoal"><?= htmlspecialchars($content['contact_email']) ?></p>
                            </div>
                            <div class="flex items-start">
                                <i class="fas fa-clock text-luxury-gold mt-1 mr-4"></i>
                                <p class="text-charcoal"><?= htmlspecialchars($content['contact_hours']) ?></p>
                            </div>
                        </div>
                        <div class="mt-8">
                            <h4 class="text-lg font-serif font-bold text-deep-burgundy mb-4">Bizi Takip Edin</h4>
                            <div class="flex space-x-4">
                                <a href="<?= htmlspecialchars($content['social_instagram']) ?>" class="text-charcoal hover:text-luxury-gold transition"><i class="fab fa-instagram text-xl"></i></a>
                                <a href="<?= htmlspecialchars($content['social_facebook']) ?>" class="text-charcoal hover:text-luxury-gold transition"><i class="fab fa-facebook text-xl"></i></a>
                                <a href="<?= htmlspecialchars($content['social_pinterest']) ?>" class="text-charcoal hover:text-luxury-gold transition"><i class="fab fa-pinterest text-xl"></i></a>
                                <a href="<?= htmlspecialchars($content['social_youtube']) ?>" class="text-charcoal hover:text-luxury-gold transition"><i class="fab fa-youtube text-xl"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-charcoal text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-xl font-serif font-bold text-luxury-gold mb-4"><?= htmlspecialchars($content['site_title']) ?></h3>
                    <p class="text-gray-300 mb-4">
                        <?= htmlspecialchars($content['footer_description']) ?>
                    </p>
                    <div class="flex space-x-4">
                        <a href="<?= htmlspecialchars($content['social_instagram']) ?>" class="text-gray-300 hover:text-luxury-gold transition"><i class="fab fa-instagram"></i></a>
                        <a href="<?= htmlspecialchars($content['social_facebook']) ?>" class="text-gray-300 hover:text-luxury-gold transition"><i class="fab fa-facebook"></i></a>
                        <a href="<?= htmlspecialchars($content['social_pinterest']) ?>" class="text-gray-300 hover:text-luxury-gold transition"><i class="fab fa-pinterest"></i></a>
                        <a href="<?= htmlspecialchars($content['social_youtube']) ?>" class="text-gray-300 hover:text-luxury-gold transition"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div>
                    <h4 class="text-lg font-serif font-bold text-luxury-gold mb-4">Koleksiyonlar</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-300 hover:text-luxury-gold transition">Yeni Koleksiyon</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-luxury-gold transition">En Çok Satanlar</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-luxury-gold transition">Sınırlı Seriler</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-luxury-gold transition">Tüm Ürünler</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-serif font-bold text-luxury-gold mb-4">Şirket</h4>
                    <ul class="space-y-2">
                        <li><a href="#about" class="text-gray-300 hover:text-luxury-gold transition">Hakkımızda</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-luxury-gold transition">Sürdürülebilirlik</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-luxury-gold transition">Kariyer</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-luxury-gold transition">Basında Biz</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-serif font-bold text-luxury-gold mb-4">Müşteri Hizmetleri</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-300 hover:text-luxury-gold transition">Sipariş Takibi</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-luxury-gold transition">Teslimat & İade</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-luxury-gold transition">Sıkça Sorulan Sorular</a></li>
                        <li><a href="#contact" class="text-gray-300 hover:text-luxury-gold transition">İletişim</a></li>
                    </ul>
                </div>
            </div>
            <div class="gold-divider w-full my-8"></div>
            <div class="text-center text-gray-400">
                <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($content['site_title']) ?>. Tüm hakları saklıdır. <?= htmlspecialchars($content['company_name']) ?></p>
            </div>
        </div>
    </footer>

    <script>
        // Mobile Menu Toggle
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const mobileMenu = document.getElementById('mobileMenu');
        function refreshImages() {
    // Logoyu yenile
    const logo = document.querySelector('nav img');
    if (logo) {
        logo.classList.add('logo-transition');
        logo.style.opacity = '0';
        
        setTimeout(() => {
            logo.src = '<?= htmlspecialchars($content['site_logo'] ?? '') ?>?' + new Date().getTime();
            logo.style.opacity = '1';
        }, 500);
    }
    
    // Hero görselini yenile
    const heroSection = document.querySelector('.hero-bg');
    if (heroSection) {
        heroSection.classList.add('hero-transition');
        heroSection.style.opacity = '0.5';
        
        setTimeout(() => {
            const newUrl = '<?= htmlspecialchars($content['hero_image'] ?? '') ?>?' + new Date().getTime();
            heroSection.style.backgroundImage = `linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('${newUrl}')`;
            heroSection.style.opacity = '1';
        }, 500);
    }
}

// 30 saniyede bir görselleri kontrol et
setInterval(refreshImages, 30000);

// Sayfa yüklendiğinde de kontrol et
document.addEventListener('DOMContentLoaded', refreshImages);
        
        
        mobileMenuBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    </script>
</body>
</html>