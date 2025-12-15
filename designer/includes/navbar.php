<nav class="navbar dashboard-navbar">
    <div class="container">
        <div class="nav-brand">
            <h2><a href="../index.php"><?php echo SITE_NAME; ?></a></h2>
        </div>
        <div class="nav-menu">
            <span class="user-name">مرحباً، <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="../logout.php" class="btn btn-outline btn-sm">تسجيل الخروج</a>
        </div>
    </div>
</nav>
