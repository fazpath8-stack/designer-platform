<?php
require_once '../config.php';
requireUserType('designer');

$user_id = getUserId();

// التبويبات
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'new';

// جلب الطلبات حسب التبويب
if ($tab === 'new') {
    // الطلبات الجديدة (المتاحة للجميع)
    $stmt = $pdo->prepare("
        SELECT o.*, s.name as service_name, u.username as client_name, u.phone as client_phone
        FROM orders o 
        JOIN services s ON o.service_id = s.id 
        JOIN users u ON o.client_id = u.id 
        WHERE o.status = 'pending' AND o.designer_id IS NULL
        ORDER BY o.created_at DESC
    ");
    $stmt->execute();
} elseif ($tab === 'my') {
    // طلباتي (التي قبلتها)
    $stmt = $pdo->prepare("
        SELECT o.*, s.name as service_name, u.username as client_name, u.phone as client_phone
        FROM orders o 
        JOIN services s ON o.service_id = s.id 
        JOIN users u ON o.client_id = u.id 
        WHERE o.designer_id = ? AND o.status IN ('accepted', 'in_progress', 'completed')
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$user_id]);
} else {
    // جميع الطلبات
    $stmt = $pdo->prepare("
        SELECT o.*, s.name as service_name, u.username as client_name, u.phone as client_phone
        FROM orders o 
        JOIN services s ON o.service_id = s.id 
        JOIN users u ON o.client_id = u.id 
        WHERE o.designer_id = ? OR (o.designer_id IS NULL AND o.status = 'pending')
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$user_id]);
}

$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الطلبات - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="dashboard-main">
            <div class="dashboard-header">
                <h1>الطلبات</h1>
                <p>إدارة طلبات العملاء</p>
            </div>
            
            <!-- التبويبات -->
            <div class="tabs">
                <a href="?tab=new" class="tab <?php echo $tab === 'new' ? 'active' : ''; ?>">
                    طلبات جديدة
                </a>
                <a href="?tab=my" class="tab <?php echo $tab === 'my' ? 'active' : ''; ?>">
                    طلباتي
                </a>
                <a href="?tab=all" class="tab <?php echo $tab === 'all' ? 'active' : ''; ?>">
                    جميع الطلبات
                </a>
            </div>
            
            <!-- قائمة الطلبات -->
            <div class="orders-list">
                <?php if (empty($orders)): ?>
                    <div class="empty-state">
                        <p>لا توجد طلبات في هذا القسم</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <div class="order-card">
                            <div class="order-header">
                                <div>
                                    <h3><?php echo htmlspecialchars($order['service_name']); ?></h3>
                                    <p class="order-client">العميل: <?php echo htmlspecialchars($order['client_name']); ?></p>
                                </div>
                                <span class="order-status status-<?php echo $order['status']; ?>">
                                    <?php
                                    $statuses = [
                                        'pending' => 'جديد',
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
                                <p><?php echo htmlspecialchars(substr($order['description'], 0, 150)) . '...'; ?></p>
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
