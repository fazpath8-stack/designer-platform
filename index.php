<?php
require_once 'config.php';

// جلب إحصائيات الموقع
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE user_type = 'designer'");
$total_designers = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'completed'");
$total_orders = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE user_type = 'client'");
$total_clients = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - منصة ربط المصممين بالعملاء</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- شريط التنقل -->
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <h2><?php echo SITE_NAME; ?></h2>
            </div>
            <div class="nav-menu">
                <a href="index.php" class="nav-link active">الرئيسية</a>
                <a href="#services" class="nav-link">الخدمات</a>
                <a href="#about" class="nav-link">من نحن</a>
                <?php if (isLoggedIn()): ?>
                    <?php if (getUserType() === 'designer'): ?>
                        <a href="designer/dashboard.php" class="btn btn-secondary">لوحة التحكم</a>
                    <?php elseif (getUserType() === 'client'): ?>
                        <a href="client/dashboard.php" class="btn btn-secondary">لوحة التحكم</a>
                    <?php elseif (getUserType() === 'admin'): ?>
                        <a href="admin/dashboard.php" class="btn btn-secondary">لوحة الإدارة</a>
                    <?php endif; ?>
                    <a href="logout.php" class="btn btn-outline">تسجيل الخروج</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-secondary">تسجيل الدخول</a>
                    <a href="register.php" class="btn btn-primary">إنشاء حساب</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- القسم الرئيسي -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">منصة تربط المصممين المحترفين بالعملاء</h1>
                <p class="hero-description">
                    نوفر لك أفضل المصممين المحترفين في مختلف المجالات. احصل على تصاميم احترافية بأسعار مناسبة وجودة عالية.
                </p>
                <div class="hero-buttons">
                    <?php if (!isLoggedIn()): ?>
                        <a href="register.php" class="btn btn-primary btn-lg">ابدأ الآن</a>
                        <a href="#services" class="btn btn-outline btn-lg">تصفح الخدمات</a>
                    <?php else: ?>
                        <?php if (getUserType() === 'client'): ?>
                            <a href="client/services.php" class="btn btn-primary btn-lg">تصفح الخدمات</a>
                        <?php else: ?>
                            <a href="designer/dashboard.php" class="btn btn-primary btn-lg">لوحة التحكم</a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- الإحصائيات -->
    <section class="stats">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_designers; ?></div>
                    <div class="stat-label">مصمم محترف</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_orders; ?></div>
                    <div class="stat-label">طلب مكتمل</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_clients; ?></div>
                    <div class="stat-label">عميل راضٍ</div>
                </div>
            </div>
        </div>
    </section>

    <!-- الخدمات -->
    <section id="services" class="services">
        <div class="container">
            <div class="section-header">
                <h2>خدماتنا</h2>
                <p>نقدم مجموعة واسعة من خدمات التصميم الاحترافية</p>
            </div>
            
            <div class="services-grid">
                <?php
                $stmt = $pdo->query("SELECT * FROM services ORDER BY id ASC LIMIT 6");
                $services = $stmt->fetchAll();
                foreach ($services as $service):
                ?>
                <div class="service-card">
                    <div class="service-icon">🎨</div>
                    <h3><?php echo htmlspecialchars($service['name']); ?></h3>
                    <p><?php echo htmlspecialchars($service['description']); ?></p>
                    <?php if (isLoggedIn() && getUserType() === 'client'): ?>
                        <a href="client/order.php?service=<?php echo $service['id']; ?>" class="btn btn-secondary">اطلب الآن</a>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- من نحن -->
    <section id="about" class="about">
        <div class="container">
            <div class="section-header">
                <h2>من نحن</h2>
            </div>
            <div class="about-content">
                <p>
                    منصة المصممين هي منصة عربية متخصصة في ربط المصممين المحترفين بالعملاء الذين يبحثون عن خدمات تصميم احترافية.
                    نوفر بيئة آمنة وموثوقة لإتمام المشاريع بجودة عالية وأسعار مناسبة.
                </p>
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">✓</div>
                        <h3>مصممون محترفون</h3>
                        <p>نختار أفضل المصممين المحترفين في مختلف المجالات</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">✓</div>
                        <h3>أسعار مناسبة</h3>
                        <p>أسعار تنافسية تناسب جميع الميزانيات</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">✓</div>
                        <h3>دفع آمن</h3>
                        <p>نظام دفع آمن عبر PayPal</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">✓</div>
                        <h3>تقييمات موثوقة</h3>
                        <p>نظام تقييمات شفاف لضمان الجودة</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- الفوتر -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3><?php echo SITE_NAME; ?></h3>
                    <p>منصة ربط المصممين بالعملاء</p>
                </div>
                <div class="footer-section">
                    <h4>روابط سريعة</h4>
                    <ul>
                        <li><a href="index.php">الرئيسية</a></li>
                        <li><a href="#services">الخدمات</a></li>
                        <li><a href="#about">من نحن</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>حسابك</h4>
                    <ul>
                        <?php if (isLoggedIn()): ?>
                            <li><a href="<?php echo getUserType(); ?>/dashboard.php">لوحة التحكم</a></li>
                            <li><a href="logout.php">تسجيل الخروج</a></li>
                        <?php else: ?>
                            <li><a href="login.php">تسجيل الدخول</a></li>
                            <li><a href="register.php">إنشاء حساب</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 <?php echo SITE_NAME; ?>. جميع الحقوق محفوظة.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>
