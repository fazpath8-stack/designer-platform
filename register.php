<?php
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $user_type = sanitize($_POST['user_type']);
    $phone = sanitize($_POST['phone']);
    
    // التحقق من البيانات
    if (empty($username) || empty($email) || empty($password) || empty($user_type)) {
        $error = 'جميع الحقول مطلوبة';
    } elseif ($password !== $confirm_password) {
        $error = 'كلمات المرور غير متطابقة';
    } elseif (strlen($password) < 6) {
        $error = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل';
    } elseif (!in_array($user_type, ['designer', 'client'])) {
        $error = 'نوع المستخدم غير صحيح';
    } else {
        // التحقق من عدم وجود المستخدم مسبقاً
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->fetch()) {
            $error = 'اسم المستخدم أو البريد الإلكتروني موجود مسبقاً';
        } else {
            // تشفير كلمة المرور
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // إدراج المستخدم
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, user_type, phone) VALUES (?, ?, ?, ?, ?)");
            
            if ($stmt->execute([$username, $email, $hashed_password, $user_type, $phone])) {
                $user_id = $pdo->lastInsertId();
                
                // إنشاء سجل في جدول المصممين أو العملاء
                if ($user_type === 'designer') {
                    $stmt = $pdo->prepare("INSERT INTO designers (user_id) VALUES (?)");
                    $stmt->execute([$user_id]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO clients (user_id) VALUES (?)");
                    $stmt->execute([$user_id]);
                }
                
                $success = 'تم إنشاء الحساب بنجاح! يمكنك تسجيل الدخول الآن';
                header("refresh:2;url=login.php");
            } else {
                $error = 'حدث خطأ أثناء إنشاء الحساب';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إنشاء حساب - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">
                <h1>إنشاء حساب جديد</h1>
                <p>انضم إلى منصة المصممين</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="username">اسم المستخدم</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="email">البريد الإلكتروني</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">رقم الهاتف</label>
                    <input type="tel" id="phone" name="phone">
                </div>
                
                <div class="form-group">
                    <label for="password">كلمة المرور</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">تأكيد كلمة المرور</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <div class="form-group">
                    <label>نوع الحساب</label>
                    <div class="radio-group">
                        <label class="radio-label">
                            <input type="radio" name="user_type" value="designer" required>
                            <span>مصمم</span>
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="user_type" value="client" required>
                            <span>عميل</span>
                        </label>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">إنشاء الحساب</button>
            </form>
            
            <div class="auth-footer">
                <p>لديك حساب بالفعل؟ <a href="login.php">تسجيل الدخول</a></p>
            </div>
        </div>
    </div>
</body>
</html>
