<?php
// إعدادات قاعدة البيانات
define('DB_HOST', 'sql103.infinityfree.com');
define('DB_USER', 'if0_40684492');
define('DB_PASS', 'WPUR313HFQO8M'); 
define('DB_NAME', 'if0_40684492_designer_platform'); 
define('SITE_URL', 'http://designerplatform.rf.gd' ); 

// إعدادات الموقع
define('SITE_NAME', 'منصة المصممين');
define('SITE_URL', 'http://localhost');
define('UPLOAD_PATH', __DIR__ . '/uploads/');
define('UPLOAD_URL', SITE_URL . '/uploads/');

// إعدادات PayPal
define('PAYPAL_MODE', 'sandbox'); // sandbox أو live
define('PAYPAL_CLIENT_ID', 'YOUR_PAYPAL_CLIENT_ID');
define('PAYPAL_SECRET', 'YOUR_PAYPAL_SECRET');

// الاتصال بقاعدة البيانات
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage());
}

// بدء الجلسة
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// دوال مساعدة
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUserType() {
    return $_SESSION['user_type'] ?? null;
}

function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function showError($message) {
    return "<div class='alert alert-error'>$message</div>";
}

function showSuccess($message) {
    return "<div class='alert alert-success'>$message</div>";
}

// التحقق من تسجيل الدخول
function requireLogin() {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}

// التحقق من نوع المستخدم
function requireUserType($type) {
    requireLogin();
    if (getUserType() !== $type) {
        redirect('index.php');
    }
}

// إنشاء مجلد الرفع إذا لم يكن موجوداً
if (!file_exists(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0777, true);
    mkdir(UPLOAD_PATH . 'profiles/', 0777, true);
    mkdir(UPLOAD_PATH . 'orders/', 0777, true);
}
?>
