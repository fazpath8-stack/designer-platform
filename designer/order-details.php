<?php
require_once '../config.php';
requireUserType('designer');

$user_id = getUserId();
$error = '';
$success = '';

// التحقق من معرف الطلب
if (!isset($_GET['id'])) {
    redirect('orders.php');
}

$order_id = (int)$_GET['id'];

// جلب تفاصيل الطلب
$stmt = $pdo->prepare("
    SELECT o.*, s.name as service_name, s.description as service_description,
           u.username as client_name, u.email as client_email, u.phone as client_phone
    FROM orders o 
    JOIN services s ON o.service_id = s.id 
    JOIN users u ON o.client_id = u.id 
    WHERE o.id = ?
");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    redirect('orders.php');
}

// جلب بيانات المصمم إذا كان الطلب مقبولاً
if ($order['designer_id']) {
    $stmt = $pdo->prepare("
        SELECT u.*, d.* 
        FROM users u 
        JOIN designers d ON u.id = d.user_id 
        WHERE u.id = ?
    ");
    $stmt->execute([$order['designer_id']]);
    $designer = $stmt->fetch();
}

// معالجة قبول الطلب
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accept_order'])) {
    $price = (float)$_POST['price'];
    $designer_note = sanitize($_POST['designer_note']);
    
    if ($price <= 0) {
        $error = 'يرجى إدخال سعر صحيح';
    } else {
        $stmt = $pdo->prepare("
            UPDATE orders SET 
            designer_id = ?, 
            status = 'accepted', 
            price = ?, 
            designer_note = ?,
            accepted_at = NOW()
            WHERE id = ? AND designer_id IS NULL
        ");
        
        if ($stmt->execute([$user_id, $price, $designer_note, $order_id])) {
            // إرسال إشعار للعميل
            $message = "تم قبول طلبك: " . $order['service_name'];
            $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message, related_order_id) VALUES (?, ?, ?)");
            $stmt->execute([$order['client_id'], $message, $order_id]);
            
            $success = 'تم قبول الطلب بنجاح!';
            header("refresh:1");
        } else {
            $error = 'حدث خطأ أو تم قبول الطلب من مصمم آخر';
        }
    }
}

// معالجة رفض الطلب
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reject_order'])) {
    $stmt = $pdo->prepare("UPDATE orders SET status = 'rejected' WHERE id = ? AND designer_id IS NULL");
    
    if ($stmt->execute([$order_id])) {
        $success = 'تم رفض الطلب';
        header("refresh:1;url=orders.php");
    } else {
        $error = 'حدث خطأ';
    }
}

// معالجة إتمام الطلب
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete_order'])) {
    $stmt = $pdo->prepare("
        UPDATE orders SET status = 'completed', completed_at = NOW() 
        WHERE id = ? AND designer_id = ? AND status = 'in_progress'
    ");
    
    if ($stmt->execute([$order_id, $user_id])) {
        // إرسال إشعار للعميل
        $message = "تم إتمام طلبك: " . $order['service_name'] . ". يرجى تأكيد الاستلام";
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message, related_order_id) VALUES (?, ?, ?)");
        $stmt->execute([$order['client_id'], $message, $order_id]);
        
        $success = 'تم إتمام الطلب! في انتظار تأكيد العميل';
        header("refresh:1");
    } else {
        $error = 'حدث خطأ';
    }
}

// معالجة بدء العمل
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['start_work'])) {
    $stmt = $pdo->prepare("
        UPDATE orders SET status = 'in_progress' 
        WHERE id = ? AND designer_id = ? AND status = 'accepted'
    ");
    
    if ($stmt->execute([$order_id, $user_id])) {
        $success = 'تم بدء العمل على الطلب';
        header("refresh:1");
    } else {
        $error = 'حدث خطأ';
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تفاصيل الطلب - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="dashboard-main">
            <div class="dashboard-header">
                <h1>تفاصيل الطلب #<?php echo $order['id']; ?></h1>
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
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <div class="order-details-container">
                <!-- معلومات الطلب -->
                <div class="detail-section">
                    <h2>معلومات الطلب</h2>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">الخدمة:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($order['service_name']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">تاريخ الطلب:</span>
                            <span class="detail-value"><?php echo date('Y-m-d H:i', strtotime($order['created_at'])); ?></span>
                        </div>
                        <?php if ($order['price']): ?>
                        <div class="detail-item">
                            <span class="detail-label">السعر:</span>
                            <span class="detail-value"><?php echo number_format($order['price'], 2); ?> $</span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- وصف الطلب -->
                <div class="detail-section">
                    <h2>وصف الطلب</h2>
                    <div class="description-box">
                        <?php echo nl2br(htmlspecialchars($order['description'])); ?>
                    </div>
                </div>
                
                <!-- معلومات العميل -->
                <div class="detail-section">
                    <h2>معلومات العميل</h2>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">الاسم:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($order['client_name']); ?></span>
                        </div>
                        <?php if ($order['designer_id'] == $user_id && in_array($order['status'], ['accepted', 'in_progress', 'completed'])): ?>
                        <div class="detail-item">
                            <span class="detail-label">البريد الإلكتروني:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($order['client_email']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">الهاتف:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($order['client_phone'] ?? 'غير متوفر'); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- ملاحظة المصمم -->
                <?php if ($order['designer_note']): ?>
                <div class="detail-section">
                    <h2>ملاحظة المصمم</h2>
                    <div class="description-box">
                        <?php echo nl2br(htmlspecialchars($order['designer_note'])); ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- الإجراءات -->
                <div class="detail-section">
                    <h2>الإجراءات</h2>
                    
                    <?php if ($order['status'] === 'pending' && !$order['designer_id']): ?>
                        <!-- نموذج قبول الطلب -->
                        <form method="POST" class="action-form">
                            <div class="form-group">
                                <label for="price">السعر ($)</label>
                                <input type="number" id="price" name="price" step="0.01" min="1" required>
                            </div>
                            <div class="form-group">
                                <label for="designer_note">ملاحظة للعميل (اختياري)</label>
                                <textarea id="designer_note" name="designer_note" rows="3"></textarea>
                            </div>
                            <div class="form-actions">
                                <button type="submit" name="accept_order" class="btn btn-success">قبول الطلب</button>
                                <button type="submit" name="reject_order" class="btn btn-danger" 
                                        onclick="return confirm('هل أنت متأكد من رفض هذا الطلب؟')">رفض الطلب</button>
                            </div>
                        </form>
                    <?php elseif ($order['status'] === 'accepted' && $order['designer_id'] == $user_id): ?>
                        <form method="POST">
                            <button type="submit" name="start_work" class="btn btn-primary">بدء العمل</button>
                        </form>
                    <?php elseif ($order['status'] === 'in_progress' && $order['designer_id'] == $user_id): ?>
                        <form method="POST">
                            <button type="submit" name="complete_order" class="btn btn-success" 
                                    onclick="return confirm('هل أنت متأكد من إتمام هذا الطلب؟')">إتمام الطلب</button>
                        </form>
                    <?php elseif ($order['status'] === 'completed'): ?>
                        <div class="info-box">
                            <p>تم إتمام الطلب. في انتظار تأكيد العميل للاستلام والدفع.</p>
                        </div>
                    <?php elseif ($order['status'] === 'delivered'): ?>
                        <div class="success-box">
                            <p>✓ تم تسليم الطلب بنجاح!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>
