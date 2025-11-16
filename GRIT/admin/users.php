<?php
require_once '../config.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

$conn = getDBConnection();

// Handle form submission for adding/editing users
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $email = sanitizeInput($_POST['email']);
    $role = sanitizeInput($_POST['role']);
    $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    
    // Handle password change
    $password = !empty($_POST['password']) ? $_POST['password'] : '';
    
    // Validation
    if (empty($username) || empty($email) || empty($role)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        if ($user_id > 0) {
            // Update existing user
            if ($password) {
                // Update with new password
                if (strlen($password) < 6) {
                    $error = 'Password must be at least 6 characters long.';
                } else {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, password = ?, role = ? WHERE id = ?");
                    $stmt->bind_param("ssssi", $username, $email, $hashed_password, $role, $user_id);
                }
            } else {
                // Update without changing password
                $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?");
                $stmt->bind_param("sssi", $username, $email, $role, $user_id);
            }
            
            if (empty($error)) {
                if ($stmt->execute()) {
                    $message = 'User updated successfully!';
                } else {
                    $error = 'Failed to update user.';
                }
                $stmt->close();
            }
        } else {
            // Add new user
            if (empty($password)) {
                $error = 'Password is required for new users.';
            } elseif (strlen($password) < 6) {
                $error = 'Password must be at least 6 characters long.';
            } else {
                // Check if username or email already exists
                $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
                $stmt->bind_param("ss", $username, $email);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $error = 'Username or email already exists.';
                } else {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("ssss", $username, $email, $hashed_password, $role);
                    
                    if ($stmt->execute()) {
                        // Create user profile entry
                        $new_user_id = $stmt->insert_id;
                        $profile_stmt = $conn->prepare("INSERT INTO user_profiles (user_id) VALUES (?)");
                        $profile_stmt->bind_param("i", $new_user_id);
                        $profile_stmt->execute();
                        $profile_stmt->close();
                        
                        $message = 'User added successfully!';
                    } else {
                        $error = 'Failed to add user.';
                    }
                }
                $stmt->close();
            }
        }
    }
}

// Handle deletion
if (isset($_GET['delete'])) {
    $user_id = (int)$_GET['delete'];
    
    // Prevent deleting the current admin user
    if ($user_id == $_SESSION['user_id']) {
        $error = 'You cannot delete your own account.';
    } else {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            $message = 'User deleted successfully!';
        } else {
            $error = 'Failed to delete user.';
        }
        $stmt->close();
    }
}

// Fetch users with role information
$usersQuery = "SELECT u.*, up.first_name, up.last_name FROM users u LEFT JOIN user_profiles up ON u.id = up.user_id ORDER BY u.created_at DESC";
$usersResult = $conn->query($usersQuery);

// Count admins and customers
$adminCountStmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
$adminCountStmt->execute();
$adminCount = $adminCountStmt->get_result()->fetch_assoc()['count'];
$adminCountStmt->close();

$customerCountStmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'customer'");
$customerCountStmt->execute();
$customerCount = $customerCountStmt->get_result()->fetch_assoc()['count'];
$customerCountStmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users | Admin Panel</title>
    <link rel="icon" type="image/jpeg" href="../logo/gritfavicon.jpg">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="dashboard-content">
            <h2>Manage Users</h2>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <!-- User Statistics -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
                <div style="background: white; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.05); text-align: center;">
                    <h3 style="margin: 0 0 10px; color: #666;">Total Admins</h3>
                    <p style="font-size: 2rem; margin: 0; color: #222;"><?php echo $adminCount; ?></p>
                </div>
                
                <div style="background: white; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.05); text-align: center;">
                    <h3 style="margin: 0 0 10px; color: #666;">Total Customers</h3>
                    <p style="font-size: 2rem; margin: 0; color: #222;"><?php echo $customerCount; ?></p>
                </div>
            </div>
            
            <!-- Add/Edit User Form -->
            <div class="product-form">
                <h3>Add New User</h3>
                <form method="POST" action="" id="user-form">
                    <input type="hidden" name="user_id" id="user_id" value="">
                    
                    <div class="form-group">
                        <label for="username">Username *</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password <?php echo isset($_POST['user_id']) ? '(Leave blank to keep current password)' : '*'; ?></label>
                        <input type="password" id="password" name="password" <?php echo !isset($_POST['user_id']) ? 'required' : ''; ?> minlength="6">
                        <small style="color: #666;">Minimum 6 characters</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="role">Role *</label>
                        <select id="role" name="role" required>
                            <option value="customer">Customer</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn-primary">Save User</button>
                        <button type="button" id="cancel-btn" class="btn-secondary" style="display: none;">Cancel</button>
                    </div>
                </form>
            </div>
            
            <!-- Users List -->
            <h3 style="margin-top: 30px;">Existing Users</h3>
            <?php if ($usersResult->num_rows > 0): ?>
                <table class="responsive-table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user = $usersResult->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')); ?></td>
                                <td>
                                    <span style="padding: 5px 10px; border-radius: 3px; background: <?php echo $user['role'] === 'admin' ? '#d4af37' : '#17a2b8'; ?>; color: white;">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td class="action-buttons">
                                    <a href="#" class="btn-secondary edit-user" 
                                       data-id="<?php echo $user['id']; ?>"
                                       data-username="<?php echo htmlspecialchars($user['username']); ?>"
                                       data-email="<?php echo htmlspecialchars($user['email']); ?>"
                                       data-role="<?php echo $user['role']; ?>">Edit</a>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <a href="?delete=<?php echo $user['id']; ?>" class="btn-cart" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No users found.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Edit user functionality
        document.querySelectorAll('.edit-user').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Populate form with user data
                document.getElementById('user_id').value = this.dataset.id;
                document.getElementById('username').value = this.dataset.username;
                document.getElementById('email').value = this.dataset.email;
                document.getElementById('role').value = this.dataset.role;
                
                // Change form heading
                document.querySelector('.product-form h3').textContent = 'Edit User';
                
                // Show cancel button
                document.getElementById('cancel-btn').style.display = 'inline-block';
                
                // Scroll to form
                document.querySelector('.product-form').scrollIntoView({ behavior: 'smooth' });
                
                // Focus on username field
                document.getElementById('username').focus();
            });
        });
        
        // Cancel edit functionality
        document.getElementById('cancel-btn').addEventListener('click', function() {
            // Reset form
            document.getElementById('user_id').value = '';
            document.getElementById('username').value = '';
            document.getElementById('email').value = '';
            document.getElementById('password').value = '';
            document.getElementById('role').value = 'customer';
            
            // Reset form heading
            document.querySelector('.product-form h3').textContent = 'Add New User';
            
            // Hide cancel button
            this.style.display = 'none';
            
            // Remove focus
            document.activeElement.blur();
        });
        
        // Form validation
        document.getElementById('user-form').addEventListener('submit', function(e) {
            const password = document.getElementById('password');
            const userId = document.getElementById('user_id').value;
            
            // Only validate password length for new users or when changing password for existing users
            if (!userId || password.value) {
                if (password.value && password.value.length < 6) {
                    e.preventDefault();
                    alert('Password must be at least 6 characters long.');
                    password.focus();
                }
            }
        });
    </script>
</body>
</html>