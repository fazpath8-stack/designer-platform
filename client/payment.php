<?php
require_once '../config.php';
requireUserType('client');

$user_id = getUserId();
$error = '';
$success = '';

// التحقق من معرف الطلب
if (!isset($_GET['order'])) {
    redirect('orders.php');
}

$order_id = (int)$_GET['order'];

// جلب تفاصيل الطلب
$stmt = $pdo->prepare("
    SELECT o.*, s.name as service_name, 
           u.username as designer_name, d.paypal_email
    FROM orders o 
    JOIN services s ON o.service_id = s.id 
    JOIN users u ON o.designer_id = u.id 
    JOIN designers d ON u.id = d.user_id
    WHERE o.id = ? AND o.client_id = ? AND o.status = 'delivered'
");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    redirect('orders.php');
}

// معالجة تأكيد الدفع
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_payment'])) {
    $rating = (int)$_POST['rating'];
    $comment = sanitize($_POST['comment']);
    
    if ($rating < 1 || $rating > 5) {
        $error = 'التقييم غير صحيح';
    } else {
        // إضافة التقييم
        $stmt = $pdo->prepare("
            INSERT INTO reviews (order_id, designer_id, client_id, rating, comment) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$order_id, $order['designer_id'], $user_id, $rating, $comment]);
        
        // تحديث متوسط تقييم المصمم
        $stmt = $pdo->prepare("
            UPDATE designers SET rating = (
                SELECT AVG(rating) FROM reviews WHERE designer_id = ?
            ) WHERE user_id = ?
        ");
        $stmt->execute([$order['designer_id'], $order['designer_id']]);
        
        $success = 'شكراً لك! تم إرسال التقييم بنجاح';
        header("refresh:2;url=orders.php");
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الدفع - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="dashboard-main">
            <div class="dashboard-header">
                <h1>الدفع للمصمم</h1>
                <p>إتمام عملية الدفع</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <div class="payment-container">
                <!-- ملخص الطلب -->
                <div class="payment-section">
                    <h2>ملخص الطلب</h2>
                    <div class="payment-summary">
                        <div class="summary-item">
                            <span>الخدمة:</span>
                            <span><?php echo htmlspecialchars($order['service_name']); ?></span>
                        </div>
                        <div class="summary-item">
                            <span>المصمم:</span>
                            <span><?php echo htmlspecialchars($order['designer_name']); ?></span>
                        </div>
                        <div class="summary-item total">
                            <span>المبلغ الإجمالي:</span>
                            <span class="price"><?php echo number_format($order['price'], 2); ?> $</span>
                        </div>
                    </div>
                </div>
                
                <!-- الدفع عبر PayPal -->
                <div class="payment-section">
                    <h2>الدفع عبر PayPal</h2>
                    <div class="paypal-info">
                        <p>سيتم تحويلك إلى PayPal لإتمام عملية الدفع</p>
                        <p><strong>البريد الإلكتروني للمصمم:</strong> <?php echo htmlspecialchars($order['paypal_email']); ?></p>
                        <p><strong>المبلغ:</strong> $<?php echo number_format($order['price'], 2); ?></p>
                        
                        <div class="paypal-button-container">
                            <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
                                <input type="hidden" name="cmd" value="_xclick">
                                <input type="hidden" name="business" value="<?php echo htmlspecialchars($order['paypal_email']); ?>">
                                <input type="hidden" name="item_name" value="<?php echo htmlspecialchars($order['service_name']); ?>">
                                <input type="hidden" name="item_number" value="<?php echo $order_id; ?>">
                                <input type="hidden" name="amount" value="<?php echo number_format($order['price'], 2, '.', ''); ?>">
                                <input type="hidden" name="currency_code" value="USD">
                                <input type="hidden" name="return" value="<?php echo SITE_URL; ?>/client/payment.php?order=<?php echo $order_id; ?>&success=1">
                                <input type="hidden" name="cancel_return" value="<?php echo SITE_URL; ?>/client/payment.php?order=<?php echo $order_id; ?>&cancel=1">
                                <button type="submit" class="btn btn-paypal">
                                    <img src="https://www.paypalobjects.com/webstatic/mktg/logo/pp_cc_mark_37x23.jpg" alt="PayPal">
                                    الدفع عبر PayPal
                                </button>
                            </form>
                        </div>
                        
                        <div class="payment-note">
                            <p><strong>ملاحظة:</strong> بعد إتمام الدفع، يرجى العودة لهذه الصفحة لتقييم المصمم</p>
                        </div>
                    </div>
                </div>
                
                <!-- التقييم -->
                <div class="payment-section">
                    <h2>تقييم المصمم</h2>
                    <p>بعد إتمام الدفع، يرجى تقييم المصمم</p>
                    
                    <form method="POST" class="rating-form">
                        <div class="form-group">
                            <label>التقييم</label>
                            <div class="star-rating">
                                <input type="radio" name="rating" value="5" id="star5" required>
                                <label for="star5">⭐</label>
                                <input type="radio" name="rating" value="4" id="star4">
                                <label for="star4">⭐</label>
                                <input type="radio" name="rating" value="3" id="star3">
                                <label for="star3">⭐</label>
                                <input type="radio" name="rating" value="2" id="star2">
                                <label for="star2">⭐</label>
                                <input type="radio" name="rating" value="1" id="star1">
                                <label for="star1">⭐</label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="comment">تعليقك (اختياري)</label>
                            <textarea id="comment" name="comment" rows="4" 
                                      placeholder="شارك تجربتك مع المصمم..."></textarea>
                        </div>
                        
                        <button type="submit" name="confirm_payment" class="btn btn-primary">
                            إرسال التقييم وإنهاء الطلب
                        </button>
                    </form>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/main.js"></script>
    <script>
        // عكس ترتيب النجوم للعرض الصحيح
        document.querySelectorAll('.star-rating label').forEach(label => {
            label.addEventListener('click', function() {
                const rating = this.previousElementSibling.value;
                console.log('Rating:', rating);
            });
        });
    </script>
</body>
</html>
