<div class="dashboard-sidebar">
    <h2 style="color: #d4af37; padding: 0 20px 20px; border-bottom: 1px solid rgba(255,255,255,0.1);">Admin Panel</h2>
    <ul>
        <li><a href="dashboard.php" <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'class="active"' : ''; ?>>Dashboard</a></li>
        <li><a href="products.php" <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'class="active"' : ''; ?>>Products</a></li>
        <li><a href="orders.php" <?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'class="active"' : ''; ?>>Orders</a></li>
        <li><a href="users.php" <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'class="active"' : ''; ?>>Users</a></li>
        <li><a href="categories.php" <?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'class="active"' : ''; ?>>Categories</a></li>
        <li><a href="messages.php" <?php echo basename($_SERVER['PHP_SELF']) == 'messages.php' ? 'class="active"' : ''; ?>>Messages</a></li>
        <li><a href="../index.php">View Site</a></li>
        <li><a href="../register.php" target="_blank">Register New User</a></li>
        <li><a href="../logout.php">Logout</a></li>
    </ul>
</div>