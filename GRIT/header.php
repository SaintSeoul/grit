<?php
// Fetch categories for navigation
$conn = getDBConnection();
$categoriesQuery = "SELECT * FROM categories";
$categoriesResult = $conn->query($categoriesQuery);

// Fetch cart count for logged in users
$cartCount = 0;
if (isLoggedIn() && !isAdmin()) {
    $user_id = $_SESSION['user_id'];
    $cartStmt = $conn->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
    $cartStmt->bind_param("i", $user_id);
    $cartStmt->execute();
    $cartResult = $cartStmt->get_result();
    $cartCount = $cartResult->fetch_assoc()['total'] ?? 0;
    $cartStmt->close();
}
?>

<!-- Header -->
<header class="main-header">
    <div class="container">
        <div class="header-top">
            <div class="logo">
                <h1><a href="index.php"><?php echo SITE_NAME; ?></a></h1>
                <p><?php echo SITE_TAGLINE; ?></p>
            </div>
            <div class="header-actions">
                <div class="search-box">
                    <input type="text" placeholder="Search...">
                    <button>🔍</button>
                </div>
                <div class="user-actions">
                    <?php if (isLoggedIn()): ?>
                        <a href="profile.php">Profile</a>
                        <a href="logout.php">Logout</a>
                        <?php if (isAdmin()): ?>
                            <a href="admin/dashboard.php">Admin Dashboard</a>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="login.php">Login</a>
                        <a href="register.php">Register</a>
                    <?php endif; ?>
                </div>
                <div class="cart-icon">
                    <a href="cart.php">🛒 <span id="cart-count"><?php echo $cartCount; ?></span></a>
                </div>
            </div>
        </div>
        
        <!-- Navigation -->
        <nav class="main-nav">
            <ul>
                <li><a href="index.php">Home</a></li>
                <?php 
                $categoriesResult->data_seek(0); // Reset pointer
                while ($category = $categoriesResult->fetch_assoc()): ?>
                    <li><a href="category.php?id=<?php echo $category['id']; ?>"><?php echo $category['name']; ?></a></li>
                <?php endwhile; ?>
                <li><a href="about.php">About</a></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>
            
            <!-- Mobile menu toggle -->
            <div class="mobile-menu-toggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </nav>
    </div>
</header>