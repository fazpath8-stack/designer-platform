<?php
require_once '../config.php';
requireUserType('client');

$user_id = getUserId();

// جلب بيانات العميل
$stmt = $pdo->prepare("SELECT u.*, c.* FROM users u JOIN clients c ON u.id = c.user_id WHERE u.id = ?");
$stmt->execute([$user_id]);
$client = $stmt->fetch();

// جلب إحصائيات العميل
$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE client_id = ? AND status = 'pending'");
$stmt->execute([$user_id]);
$pending_orders = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE client_id = ? AND status IN ('accepted', 'in_progress')");
$stmt->execute([$user_id]);
$active_orders = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE client_id = ? AND status = 'completed'");
$stmt->execute([$user_id]);
$completed_orders = $stmt->fetchColumn();

// جلب الطلبات الأخيرة
$stmt = $pdo->prepare("
    SELECT o.*, s.name as service_name, u.username as designer_name 
    FROM orders o 
    JOIN services s ON o.service_id = s.id 
    LEFT JOIN users u ON o.designer_id = u.id 
    WHERE o.client_id = ? 
    ORDER BY o.created_at DESC 
    LIMIT 10
");
$stmt->execute([$user_id]);
$recent_orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة تحكم العميل - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="dashboard-main">
            <div class="dashboard-header">
                <h1>مرحباً، <?php echo htmlspecialchars($client['username']); ?></h1>
                <p>إليك نظرة عامة على طلباتك</p>
            </div>
            
            <!-- الإحصائيات -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo $pending_orders; ?></div>
                        <div class="stat-label">طلبات قيد الانتظار</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🔄</div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo $active_orders; ?></div>
                        <div class="stat-label">طلبات نشطة</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">✓</div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo $completed_orders; ?></div>
                        <div class="stat-label">طلبات مكتملة</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📋</div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo $client['total_orders']; ?></div>
                        <div class="stat-label">إجمالي الطلبات</div>
                    </div>
                </div>
            </div>
            
            <!-- زر طلب خدمة جديدة -->
            <div class="action-section">
                <a href="services.php" class="btn btn-primary btn-lg">طلب خدمة جديدة</a>
            </div>
            
            <!-- الطلبات الأخيرة -->
            <div class="section">
                <div class="section-header">
                    <h2>طلباتك الأخيرة</h2>
                    <a href="orders.php" class="btn btn-secondary">عرض الكل</a>
                </div>
                
                <div class="orders-list">
                    <?php if (empty($recent_orders)): ?>
                        <div class="empty-state">
                            <p>لا توجد طلبات حالياً</p>
                            <a href="services.php" class="btn btn-primary">ابدأ بطلب خدمة</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recent_orders as $order): ?>
                            <div class="order-card">
                                <div class="order-header">
                                    <div>
                                        <h3><?php echo htmlspecialchars($order['service_name']); ?></h3>
                                        <?php if ($order['designer_name']): ?>
                                            <p class="order-designer">المصمم: <?php echo htmlspecialchars($order['designer_name']); ?></p>
                                        <?php else: ?>
                                            <p class="order-designer">في انتظار قبول مصمم</p>
                                        <?php endif; ?>
                                    </div>
                                    <span class="order-status status-<?php echo $order['status']; ?>">
                                        <?php
                                        $statuses = [
                                            'pending' => 'في الانتظار',
                                            'accepted' => 'مقبول',
                                            'rejected' => 'مرفوض',
                                            'in_progress' => 'قيد التنفيذ',
                                            'completed' => 'مكتمل',
                                            'delivered' => 'تم التسليم',
                                            'cancelled' => 'ملغي'
                                        ];
                                        echo $statuses[$order['status']];
                                        ?>
                                    </span>
                                </div>
                                <div class="order-body">
                                    <p><?php echo htmlspecialchars(substr($order['description'], 0, 100)) . '...'; ?></p>
                                </div>
                                <div class="order-footer">
                                    <span class="order-date"><?php echo date('Y-m-d H:i', strtotime($order['created_at'])); ?></span>
                                    <?php if ($order['price']): ?>
                                        <span class="order-price"><?php echo number_format($order['price'], 2); ?> $</span>
                                    <?php endif; ?>
                                    <a href="order-details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary">التفاصيل</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>
