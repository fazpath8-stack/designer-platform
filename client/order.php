<?php
require_once '../config.php';
requireUserType('client');

$user_id = getUserId();
$error = '';
$success = '';

// التحقق من وجود معرف الخدمة
if (!isset($_GET['service'])) {
    redirect('services.php');
}

$service_id = (int)$_GET['service'];

// جلب بيانات الخدمة
$stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
$stmt->execute([$service_id]);
$service = $stmt->fetch();

if (!$service) {
    redirect('services.php');
}

// معالجة إرسال الطلب
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = sanitize($_POST['description']);
    
    if (empty($description)) {
        $error = 'يرجى كتابة وصف للطلب';
    } else {
        // إدراج الطلب
        $stmt = $pdo->prepare("
            INSERT INTO orders (client_id, service_id, description, status) 
            VALUES (?, ?, ?, 'pending')
        ");
        
        if ($stmt->execute([$user_id, $service_id, $description])) {
            $order_id = $pdo->lastInsertId();
            
            // إرسال إشعار للمصممين المتخصصين في هذه الخدمة
            $programs = json_decode($service['related_programs'], true);
            if ($programs) {
                $placeholders = str_repeat('?,', count($programs) - 1) . '?';
                $stmt = $pdo->prepare("
                    SELECT DISTINCT user_id FROM designers 
                    WHERE programs REGEXP ?
                ");
                $pattern = implode('|', array_map('preg_quote', $programs));
                $stmt->execute([$pattern]);
                $designers = $stmt->fetchAll();
                
                foreach ($designers as $designer) {
                    $message = "طلب جديد: " . $service['name'];
                    $stmt = $pdo->prepare("
                        INSERT INTO notifications (user_id, message, related_order_id) 
                        VALUES (?, ?, ?)
                    ");
                    $stmt->execute([$designer['user_id'], $message, $order_id]);
                }
            }
            
            $success = 'تم إرسال الطلب بنجاح! سيتم إشعارك عند قبول أحد المصممين للطلب';
            header("refresh:2;url=orders.php");
        } else {
            $error = 'حدث خطأ أثناء إرسال الطلب';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طلب خدمة - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="dashboard-main">
            <div class="dashboard-header">
                <h1>طلب خدمة: <?php echo htmlspecialchars($service['name']); ?></h1>
                <p><?php echo htmlspecialchars($service['description']); ?></p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <div class="order-form-section">
                <form method="POST" class="order-form">
                    <div class="form-group">
                        <label for="description">وصف الطلب</label>
                        <textarea id="description" name="description" rows="8" required 
                                  placeholder="اكتب تفاصيل الطلب بدقة...&#10;&#10;مثال:&#10;- نوع التصميم المطلوب&#10;- الألوان المفضلة&#10;- الحجم والأبعاد&#10;- أي ملاحظات إضافية"></textarea>
                        <small>كن دقيقاً في وصف احتياجاتك لتحصل على أفضل النتائج</small>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">إرسال الطلب</button>
                        <a href="services.php" class="btn btn-outline">إلغاء</a>
                    </div>
                </form>
            </div>
            
            <div class="info-box">
                <h3>كيف يعمل النظام؟</h3>
                <ol>
                    <li>اكتب وصفاً دقيقاً لطلبك</li>
                    <li>سيتم إرسال الطلب للمصممين المتخصصين</li>
                    <li>سيقوم المصممون بمراجعة الطلب ووضع أسعارهم</li>
                    <li>يمكنك اختيار المصمم المناسب بناءً على السعر والتقييمات</li>
                    <li>بعد إتمام العمل، ستدفع المبلغ المتفق عليه</li>
                </ol>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>
