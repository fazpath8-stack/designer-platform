<?php
require_once '../config.php';
requireUserType('client');

$user_id = getUserId();

// جلب جميع الطلبات
$stmt = $pdo->prepare("
    SELECT o.*, s.name as service_name, u.username as designer_name
    FROM orders o 
    JOIN services s ON o.service_id = s.id 
    LEFT JOIN users u ON o.designer_id = u.id 
    WHERE o.client_id = ? 
    ORDER BY o.created_at DESC
");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طلباتي - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="dashboard-main">
            <div class="dashboard-header">
                <h1>طلباتي</h1>
                <p>جميع طلباتك في مكان واحد</p>
            </div>
            
            <div class="orders-list">
                <?php if (empty($orders)): ?>
                    <div class="empty-state">
                        <p>لا توجد طلبات حالياً</p>
                        <a href="services.php" class="btn btn-primary">ابدأ بطلب خدمة</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
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
        </main>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>
