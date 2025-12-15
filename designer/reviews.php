<?php
require_once '../config.php';
requireUserType('designer');

$user_id = getUserId();

// جلب التقييمات
$stmt = $pdo->prepare("
    SELECT r.*, u.username as client_name, o.id as order_id, s.name as service_name
    FROM reviews r
    JOIN users u ON r.client_id = u.id
    JOIN orders o ON r.order_id = o.id
    JOIN services s ON o.service_id = s.id
    WHERE r.designer_id = ?
    ORDER BY r.created_at DESC
");
$stmt->execute([$user_id]);
$reviews = $stmt->fetchAll();

// حساب متوسط التقييم
$stmt = $pdo->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total FROM reviews WHERE designer_id = ?");
$stmt->execute([$user_id]);
$rating_stats = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>التقييمات - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="dashboard-main">
            <div class="dashboard-header">
                <h1>التقييمات</h1>
                <p>آراء العملاء عن خدماتك</p>
            </div>
            
            <!-- إحصائيات التقييم -->
            <div class="rating-summary">
                <div class="rating-box">
                    <div class="rating-number"><?php echo number_format($rating_stats['avg_rating'], 1); ?></div>
                    <div class="rating-stars">
                        <?php
                        $avg = round($rating_stats['avg_rating']);
                        for ($i = 1; $i <= 5; $i++) {
                            echo $i <= $avg ? '⭐' : '☆';
                        }
                        ?>
                    </div>
                    <div class="rating-count"><?php echo $rating_stats['total']; ?> تقييم</div>
                </div>
            </div>
            
            <!-- قائمة التقييمات -->
            <div class="reviews-list">
                <?php if (empty($reviews)): ?>
                    <div class="empty-state">
                        <p>لا توجد تقييمات بعد</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($reviews as $review): ?>
                        <div class="review-card">
                            <div class="review-header">
                                <div>
                                    <h3><?php echo htmlspecialchars($review['client_name']); ?></h3>
                                    <p class="review-service">الخدمة: <?php echo htmlspecialchars($review['service_name']); ?></p>
                                </div>
                                <div class="review-rating">
                                    <?php
                                    for ($i = 1; $i <= 5; $i++) {
                                        echo $i <= $review['rating'] ? '⭐' : '☆';
                                    }
                                    ?>
                                </div>
                            </div>
                            <?php if ($review['comment']): ?>
                            <div class="review-body">
                                <p><?php echo htmlspecialchars($review['comment']); ?></p>
                            </div>
                            <?php endif; ?>
                            <div class="review-footer">
                                <span class="review-date"><?php echo date('Y-m-d', strtotime($review['created_at'])); ?></span>
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
