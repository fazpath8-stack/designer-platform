<?php
require_once '../config.php';
requireUserType('designer');

$user_id = getUserId();
$success = '';
$error = '';

// جلب بيانات المصمم
$stmt = $pdo->prepare("
    SELECT u.*, d.* 
    FROM users u 
    JOIN designers d ON u.id = d.user_id 
    WHERE u.id = ?
");
$stmt->execute([$user_id]);
$designer = $stmt->fetch();

// معالجة رفع الصورة الشخصية
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_image'])) {
    $file = $_FILES['profile_image'];
    
    if ($file['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $file['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $new_filename = 'profile_' . $user_id . '_' . time() . '.' . $ext;
            $destination = UPLOAD_PATH . 'profiles/' . $new_filename;
            
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $stmt = $pdo->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
                $stmt->execute([$new_filename, $user_id]);
                $success = 'تم تحديث الصورة الشخصية بنجاح';
                header("refresh:1");
            } else {
                $error = 'فشل رفع الصورة';
            }
        } else {
            $error = 'نوع الملف غير مدعوم';
        }
    }
}

// معالجة تحديث البيانات
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $bio = sanitize($_POST['bio']);
    $phone = sanitize($_POST['phone']);
    $programs = isset($_POST['programs']) ? json_encode($_POST['programs']) : '[]';
    $whatsapp = sanitize($_POST['whatsapp']);
    $instagram = sanitize($_POST['instagram']);
    $twitter = sanitize($_POST['twitter']);
    $facebook = sanitize($_POST['facebook']);
    $linkedin = sanitize($_POST['linkedin']);
    $portfolio_url = sanitize($_POST['portfolio_url']);
    $paypal_email = sanitize($_POST['paypal_email']);
    
    // تحديث بيانات المستخدم
    $stmt = $pdo->prepare("UPDATE users SET bio = ?, phone = ? WHERE id = ?");
    $stmt->execute([$bio, $phone, $user_id]);
    
    // تحديث بيانات المصمم
    $stmt = $pdo->prepare("
        UPDATE designers SET 
        programs = ?, whatsapp = ?, instagram = ?, twitter = ?, 
        facebook = ?, linkedin = ?, portfolio_url = ?, paypal_email = ?
        WHERE user_id = ?
    ");
    $stmt->execute([
        $programs, $whatsapp, $instagram, $twitter, 
        $facebook, $linkedin, $portfolio_url, $paypal_email, $user_id
    ]);
    
    $success = 'تم تحديث البيانات بنجاح';
    header("refresh:1");
}

$programs_list = ['Photoshop', 'Illustrator', 'InDesign', 'After Effects', 'Premiere Pro', 'Figma', 'Adobe XD', 'Sketch', 'CorelDRAW', 'Lightroom', 'Canva'];
$selected_programs = $designer['programs'] ? json_decode($designer['programs'], true) : [];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الملف الشخصي - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="dashboard-main">
            <div class="dashboard-header">
                <h1>الملف الشخصي</h1>
                <p>قم بتحديث معلوماتك الشخصية</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <!-- الصورة الشخصية -->
            <div class="profile-section">
                <h2>الصورة الشخصية</h2>
                <div class="profile-image-container">
                    <img src="<?php echo UPLOAD_URL . 'profiles/' . ($designer['profile_image'] ?: 'default-avatar.png'); ?>" 
                         alt="الصورة الشخصية" class="profile-image-large">
                    <form method="POST" enctype="multipart/form-data" class="upload-form">
                        <input type="file" name="profile_image" accept="image/*" id="profile_image" required>
                        <button type="submit" class="btn btn-primary">تحديث الصورة</button>
                    </form>
                </div>
            </div>
            
            <!-- معلومات الملف الشخصي -->
            <div class="profile-section">
                <h2>معلومات الملف الشخصي</h2>
                <form method="POST" class="profile-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label>اسم المستخدم</label>
                            <input type="text" value="<?php echo htmlspecialchars($designer['username']); ?>" disabled>
                        </div>
                        <div class="form-group">
                            <label>البريد الإلكتروني</label>
                            <input type="email" value="<?php echo htmlspecialchars($designer['email']); ?>" disabled>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">رقم الهاتف</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($designer['phone'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="bio">نبذة عنك</label>
                        <textarea id="bio" name="bio" rows="4"><?php echo htmlspecialchars($designer['bio'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>البرامج التي تستخدمها</label>
                        <div class="checkbox-grid">
                            <?php foreach ($programs_list as $program): ?>
                                <label class="checkbox-label">
                                    <input type="checkbox" name="programs[]" value="<?php echo $program; ?>" 
                                           <?php echo in_array($program, $selected_programs) ? 'checked' : ''; ?>>
                                    <span><?php echo $program; ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <h3>بيانات التواصل</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="whatsapp">واتساب</label>
                            <input type="text" id="whatsapp" name="whatsapp" 
                                   value="<?php echo htmlspecialchars($designer['whatsapp'] ?? ''); ?>" 
                                   placeholder="+966xxxxxxxxx">
                        </div>
                        <div class="form-group">
                            <label for="instagram">إنستغرام</label>
                            <input type="text" id="instagram" name="instagram" 
                                   value="<?php echo htmlspecialchars($designer['instagram'] ?? ''); ?>" 
                                   placeholder="@username">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="twitter">تويتر</label>
                            <input type="text" id="twitter" name="twitter" 
                                   value="<?php echo htmlspecialchars($designer['twitter'] ?? ''); ?>" 
                                   placeholder="@username">
                        </div>
                        <div class="form-group">
                            <label for="facebook">فيسبوك</label>
                            <input type="text" id="facebook" name="facebook" 
                                   value="<?php echo htmlspecialchars($designer['facebook'] ?? ''); ?>" 
                                   placeholder="facebook.com/username">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="linkedin">لينكد إن</label>
                            <input type="text" id="linkedin" name="linkedin" 
                                   value="<?php echo htmlspecialchars($designer['linkedin'] ?? ''); ?>" 
                                   placeholder="linkedin.com/in/username">
                        </div>
                        <div class="form-group">
                            <label for="portfolio_url">رابط معرض الأعمال</label>
                            <input type="url" id="portfolio_url" name="portfolio_url" 
                                   value="<?php echo htmlspecialchars($designer['portfolio_url'] ?? ''); ?>" 
                                   placeholder="https://...">
                        </div>
                    </div>
                    
                    <h3>بيانات الدفع</h3>
                    
                    <div class="form-group">
                        <label for="paypal_email">بريد PayPal الإلكتروني</label>
                        <input type="email" id="paypal_email" name="paypal_email" 
                               value="<?php echo htmlspecialchars($designer['paypal_email'] ?? ''); ?>" 
                               placeholder="your-email@example.com">
                        <small>سيتم استخدام هذا البريد لاستلام المدفوعات</small>
                    </div>
                    
                    <button type="submit" name="update_profile" class="btn btn-primary">حفظ التغييرات</button>
                </form>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>
