<?php
require_once '../config.php';
requireUserType('admin');

$success = '';
$error = '';

// حذف مستخدم
if (isset($_GET['delete']) && $_GET['delete'] != getUserId()) {
    $user_id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND user_type != 'admin'");
    if ($stmt->execute([$user_id])) {
        $success = 'تم حذف المستخدم بنجاح';
    } else {
        $error = 'حدث خطأ أثناء الحذف';
    }
}

// البحث والفلترة
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$filter = isset($_GET['filter']) ? sanitize($_GET['filter']) : 'all';

$sql = "SELECT * FROM users WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (username LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($filter !== 'all') {
    $sql .= " AND user_type = ?";
    $params[] = $filter;
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المستخدمين - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="dashboard-main">
            <div class="dashboard-header">
                <h1>إدارة المستخدمين</h1>
                <p>عرض وإدارة جميع المستخدمين</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <!-- البحث والفلترة -->
            <div class="filters-section">
                <form method="GET" class="filters-form">
                    <input type="text" name="search" placeholder="البحث عن مستخدم..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                    <select name="filter">
                        <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>الكل</option>
                        <option value="designer" <?php echo $filter === 'designer' ? 'selected' : ''; ?>>مصممون</option>
                        <option value="client" <?php echo $filter === 'client' ? 'selected' : ''; ?>>عملاء</option>
                        <option value="admin" <?php echo $filter === 'admin' ? 'selected' : ''; ?>>أدمن</option>
                    </select>
                    <button type="submit" class="btn btn-primary">بحث</button>
                </form>
            </div>
            
            <!-- جدول المستخدمين -->
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>رقم</th>
                            <th>اسم المستخدم</th>
                            <th>البريد الإلكتروني</th>
                            <th>النوع</th>
                            <th>الهاتف</th>
                            <th>تاريخ التسجيل</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="7" class="text-center">لا توجد نتائج</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $user['user_type']; ?>">
                                        <?php
                                        $types = ['designer' => 'مصمم', 'client' => 'عميل', 'admin' => 'أدمن'];
                                        echo $types[$user['user_type']];
                                        ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($user['phone'] ?? '-'); ?></td>
                                <td><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <?php if ($user['id'] != getUserId() && $user['user_type'] != 'admin'): ?>
                                        <a href="?delete=<?php echo $user['id']; ?>" 
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('هل أنت متأكد من حذف هذا المستخدم؟')">
                                            حذف
                                        </a>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>
