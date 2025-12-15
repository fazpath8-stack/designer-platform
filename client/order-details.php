<?php
require_once '../config.php';
requireUserType('client');

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
           u.username as designer_name, u.email as designer_email, u.phone as designer_phone
    FROM orders o 
    JOIN services s ON o.service_id = s.id 
    LEFT JOIN users u ON o.designer_id = u.id 
    WHERE o.id = ? AND o.client_id = ?
");
$stmt->execute([$order_id, $user_id]);
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

// معالجة تأكيد الاستلام
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delivery'])) {
    $stmt = $pdo->prepare("
        UPDATE orders SET status = 'delivered', delivered_at = NOW() 
        WHERE id = ? AND client_id = ? AND status = 'completed'
    ");
    
    if ($stmt->execute([$order_id, $user_id])) {
        // تحديث عدد الطلبات للمصمم والعميل
        $stmt = $pdo->prepare("UPDATE designers SET total_orders = total_orders + 1 WHERE user_id = ?");
        $stmt->execute([$order['designer_id']]);
        
        $stmt = $pdo->prepare("UPDATE clients SET total_orders = total_orders + 1 WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        $success = 'تم تأكيد الاستلام! يرجى الانتقال لصفحة الدفع';
        header("refresh:2;url=payment.php?order=" . $order_id);
    } else {
        $error = 'حدث خطأ';
    }
}

// معالجة عدم التسليم
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['not_delivered'])) {
    $feedback = sanitize($_POST['client_feedback']);
    
    $stmt = $pdo->prepare("
        UPDATE orders SET status = 'in_progress', client_feedback = ? 
        WHERE id = ? AND client_id = ? AND status = 'completed'
    ");
    
    if ($stmt->execute([$feedback, $order_id, $user_id])) {
        // إرسال إشعار للمصمم
        $message = "العميل أبلغ عن عدم استلام الطلب: " . $order['service_name'];
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message, related_order_id) VALUES (?, ?, ?)");
        $stmt->execute([$order['designer_id'], $message, $order_id]);
        
        $success = 'تم إرسال الملاحظات للمصمم';
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
                
                <!-- معلومات المصمم -->
                <?php if ($order['designer_id'] && isset($designer)): ?>
                <div class="detail-section">
                    <h2>معلومات المصمم</h2>
                    <div class="designer-info">
                        <div class="designer-profile">
                            <img src="<?php echo UPLOAD_URL . 'profiles/' . ($designer['profile_image'] ?: 'default-avatar.png'); ?>" 
                                 alt="<?php echo htmlspecialchars($designer['username']); ?>" class="designer-avatar">
                            <div>
                                <h3><?php echo htmlspecialchars($designer['username']); ?></h3>
                                <p class="designer-rating">⭐ <?php echo number_format($designer['rating'], 1); ?> (<?php echo $designer['total_orders']; ?> طلب)</p>
                            </div>
                        </div>
                        
                        <?php if (in_array($order['status'], ['accepted', 'in_progress', 'completed', 'delivered'])): ?>
                        <div class="contact-info">
                            <h4>بيانات التواصل</h4>
                            <div class="contact-grid">
                                <?php if ($designer['whatsapp']): ?>
                                <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $designer['whatsapp']); ?>" 
                                   target="_blank" class="contact-btn whatsapp">
                                    واتساب: <?php echo htmlspecialchars($designer['whatsapp']); ?>
                                </a>
                                <?php endif; ?>
                                
                                <?php if ($designer['instagram']): ?>
                                <a href="https://instagram.com/<?php echo htmlspecialchars(ltrim($designer['instagram'], '@')); ?>" 
                                   target="_blank" class="contact-btn instagram">
                                    إنستغرام: <?php echo htmlspecialchars($designer['instagram']); ?>
                                </a>
                                <?php endif; ?>
                                
                                <?php if ($designer['email']): ?>
                                <a href="mailto:<?php echo htmlspecialchars($designer['email']); ?>" class="contact-btn email">
                                    البريد: <?php echo htmlspecialchars($designer['email']); ?>
                                </a>
                                <?php endif; ?>
                                
                                <?php if ($designer['phone']): ?>
                                <a href="tel:<?php echo htmlspecialchars($designer['phone']); ?>" class="contact-btn phone">
                                    الهاتف: <?php echo htmlspecialchars($designer['phone']); ?>
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php else: ?>
                <div class="detail-section">
                    <div class="info-box">
                        <p>في انتظار قبول أحد المصممين للطلب...</p>
                    </div>
                </div>
                <?php endif; ?>
                
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
                <?php if ($order['status'] === 'completed'): ?>
                <div class="detail-section">
                    <h2>تأكيد الاستلام</h2>
                    <p>هل استلمت الطلب بشكل صحيح؟</p>
                    
                    <form method="POST" class="action-form">
                        <div class="form-actions">
                            <button type="submit" name="confirm_delivery" class="btn btn-success">
                                نعم، تم الاستلام - الانتقال للدفع
                            </button>
                        </div>
                    </form>
                    
                    <form method="POST" class="action-form" style="margin-top: 20px;">
                        <div class="form-group">
                            <label for="client_feedback">لم يتم التسليم؟ اكتب ملاحظاتك:</label>
                            <textarea id="client_feedback" name="client_feedback" rows="3" required></textarea>
                        </div>
                        <button type="submit" name="not_delivered" class="btn btn-danger">
                            لم يتم التسليم
                        </button>
                    </form>
                </div>
                <?php elseif ($order['status'] === 'delivered'): ?>
                <div class="detail-section">
                    <div class="success-box">
                        <p>✓ تم تأكيد الاستلام</p>
                        <a href="payment.php?order=<?php echo $order_id; ?>" class="btn btn-primary">
                            الانتقال للدفع
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>
