<?php
require_once '../config.php';
requireUserType('admin');

// جلب الإحصائيات
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE user_type = 'designer'");
$total_designers = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE user_type = 'client'");
$total_clients = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM orders");
$total_orders = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
$pending_orders = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM services");
$total_services = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT SUM(price) FROM orders WHERE status = 'delivered'");
$total_revenue = $stmt->fetchColumn() ?: 0;

// آخر المستخدمين
$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
$recent_users = $stmt->fetchAll();

// آخر الطلبات
$stmt = $pdo->query("
    SELECT o.*, s.name as service_name, 
           u1.username as client_name, u2.username as designer_name
    FROM orders o 
    JOIN services s ON o.service_id = s.id 
    JOIN users u1 ON o.client_id = u1.id 
    LEFT JOIN users u2 ON o.designer_id = u2.id 
    ORDER BY o.created_at DESC 
    LIMIT 5
");
$recent_orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة تحكم الأدمن - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="dashboard-main">
            <div class="dashboard-header">
                <h1>لوحة تحكم الأدمن</h1>
                <p>نظرة شاملة على المنصة</p>
            </div>
            
            <!-- الإحصائيات الرئيسية -->
            <div class="stats-grid stats-grid-6">
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo $total_designers; ?></div>
                        <div class="stat-label">مصممون</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">👤</div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo $total_clients; ?></div>
                        <div class="stat-label">عملاء</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📋</div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo $total_orders; ?></div>
                        <div class="stat-label">إجمالي الطلبات</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo $pending_orders; ?></div>
                        <div class="stat-label">طلبات معلقة</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🎨</div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo $total_services; ?></div>
                        <div class="stat-label">الخدمات</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">💰</div>
                    <div class="stat-info">
                        <div class="stat-number">$<?php echo number_format($total_revenue, 2); ?></div>
                        <div class="stat-label">إجمالي الإيرادات</div>
                    </div>
                </div>
            </div>
            
            <div class="admin-grid">
                <!-- آخر المستخدمين -->
                <div class="section">
                    <div class="section-header">
                        <h2>آخر المستخدمين</h2>
                        <a href="users.php" class="btn btn-secondary btn-sm">عرض الكل</a>
                    </div>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>الاسم</th>
                                    <th>النوع</th>
                                    <th>البريد</th>
                                    <th>التاريخ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_users as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $user['user_type']; ?>">
                                            <?php
                                            $types = ['designer' => 'مصمم', 'client' => 'عميل', 'admin' => 'أدمن'];
                                            echo $types[$user['user_type']];
                                            ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- آخر الطلبات -->
                <div class="section">
                    <div class="section-header">
                        <h2>آخر الطلبات</h2>
                        <a href="orders.php" class="btn btn-secondary btn-sm">عرض الكل</a>
                    </div>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>رقم الطلب</th>
                                    <th>الخدمة</th>
                                    <th>العميل</th>
                                    <th>الحالة</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_orders as $order): ?>
                                <tr>
                                    <td>#<?php echo $order['id']; ?></td>
                                    <td><?php echo htmlspecialchars($order['service_name']); ?></td>
                                    <td><?php echo htmlspecialchars($order['client_name']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $order['status']; ?>">
                                            <?php
                                            $statuses = [
                                                'pending' => 'معلق',
                                                'accepted' => 'مقبول',
                                                'in_progress' => 'قيد التنفيذ',
                                                'completed' => 'مكتمل',
                                                'delivered' => 'مسلم'
                                            ];
                                            echo $statuses[$order['status']] ?? $order['status'];
                                            ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>
