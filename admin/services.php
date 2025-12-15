<?php
require_once '../config.php';
requireUserType('admin');

$success = '';
$error = '';

// إضافة خدمة جديدة
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_service'])) {
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $category = sanitize($_POST['category']);
    $programs = isset($_POST['programs']) ? json_encode($_POST['programs']) : '[]';
    
    if (empty($name)) {
        $error = 'اسم الخدمة مطلوب';
    } else {
        $stmt = $pdo->prepare("INSERT INTO services (name, description, category, related_programs) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$name, $description, $category, $programs])) {
            $success = 'تم إضافة الخدمة بنجاح';
        } else {
            $error = 'حدث خطأ أثناء الإضافة';
        }
    }
}

// حذف خدمة
if (isset($_GET['delete'])) {
    $service_id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
    if ($stmt->execute([$service_id])) {
        $success = 'تم حذف الخدمة بنجاح';
    } else {
        $error = 'حدث خطأ أثناء الحذف';
    }
}

// جلب جميع الخدمات
$stmt = $pdo->query("SELECT * FROM services ORDER BY id DESC");
$services = $stmt->fetchAll();

$programs_list = ['Photoshop', 'Illustrator', 'InDesign', 'After Effects', 'Premiere Pro', 'Figma', 'Adobe XD', 'Sketch', 'CorelDRAW', 'Lightroom', 'Canva'];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الخدمات - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="dashboard-main">
            <div class="dashboard-header">
                <h1>إدارة الخدمات</h1>
                <p>إضافة وتعديل الخدمات المتاحة</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <!-- نموذج إضافة خدمة -->
            <div class="section">
                <h2>إضافة خدمة جديدة</h2>
                <form method="POST" class="service-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">اسم الخدمة</label>
                            <input type="text" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="category">الفئة</label>
                            <input type="text" id="category" name="category" placeholder="تصميم جرافيكي">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">الوصف</label>
                        <textarea id="description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>البرامج المرتبطة</label>
                        <div class="checkbox-grid">
                            <?php foreach ($programs_list as $program): ?>
                                <label class="checkbox-label">
                                    <input type="checkbox" name="programs[]" value="<?php echo $program; ?>">
                                    <span><?php echo $program; ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <button type="submit" name="add_service" class="btn btn-primary">إضافة الخدمة</button>
                </form>
            </div>
            
            <!-- قائمة الخدمات -->
            <div class="section">
                <h2>الخدمات الحالية</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>رقم</th>
                                <th>الاسم</th>
                                <th>الوصف</th>
                                <th>الفئة</th>
                                <th>البرامج</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($services)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">لا توجد خدمات</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($services as $service): ?>
                                <tr>
                                    <td><?php echo $service['id']; ?></td>
                                    <td><?php echo htmlspecialchars($service['name']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($service['description'], 0, 50)) . '...'; ?></td>
                                    <td><?php echo htmlspecialchars($service['category'] ?? '-'); ?></td>
                                    <td>
                                        <?php
                                        $programs = json_decode($service['related_programs'], true);
                                        echo $programs ? implode(', ', array_slice($programs, 0, 3)) : '-';
                                        ?>
                                    </td>
                                    <td>
                                        <a href="?delete=<?php echo $service['id']; ?>" 
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('هل أنت متأكد من حذف هذه الخدمة؟')">
                                            حذف
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>
