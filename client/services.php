<?php
require_once '../config.php';
requireUserType('client');

// البحث عن خدمة
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

if ($search) {
    $stmt = $pdo->prepare("SELECT * FROM services WHERE name LIKE ? OR description LIKE ? ORDER BY id ASC");
    $stmt->execute(["%$search%", "%$search%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM services ORDER BY id ASC");
}
$services = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الخدمات - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="dashboard-main">
            <div class="dashboard-header">
                <h1>الخدمات المتاحة</h1>
                <p>اختر الخدمة التي تريدها وابدأ بطلبك</p>
            </div>
            
            <!-- البحث -->
            <div class="search-section">
                <form method="GET" class="search-form">
                    <input type="text" name="search" placeholder="ابحث عن خدمة..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-primary">بحث</button>
                </form>
            </div>
            
            <!-- قائمة الخدمات -->
            <div class="services-grid">
                <?php if (empty($services)): ?>
                    <div class="empty-state">
                        <p>لا توجد خدمات متاحة</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($services as $service): ?>
                        <div class="service-card">
                            <div class="service-icon">🎨</div>
                            <h3><?php echo htmlspecialchars($service['name']); ?></h3>
                            <p><?php echo htmlspecialchars($service['description']); ?></p>
                            <div class="service-footer">
                                <a href="order.php?service=<?php echo $service['id']; ?>" class="btn btn-primary">اطلب الآن</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>
