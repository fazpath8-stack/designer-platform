<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="dashboard-sidebar">
    <ul class="sidebar-menu">
        <li class="<?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>">
            <a href="dashboard.php">
                <span class="icon">🏠</span>
                <span>الرئيسية</span>
            </a>
        </li>
        <li class="<?php echo $current_page === 'services.php' ? 'active' : ''; ?>">
            <a href="services.php">
                <span class="icon">🎨</span>
                <span>الخدمات</span>
            </a>
        </li>
        <li class="<?php echo $current_page === 'orders.php' ? 'active' : ''; ?>">
            <a href="orders.php">
                <span class="icon">📋</span>
                <span>طلباتي</span>
            </a>
        </li>
        <li class="<?php echo $current_page === 'profile.php' ? 'active' : ''; ?>">
            <a href="profile.php">
                <span class="icon">👤</span>
                <span>الملف الشخصي</span>
            </a>
        </li>
        <li class="<?php echo $current_page === 'settings.php' ? 'active' : ''; ?>">
            <a href="settings.php">
                <span class="icon">⚙️</span>
                <span>الإعدادات</span>
            </a>
        </li>
    </ul>
</aside>
