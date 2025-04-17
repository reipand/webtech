<?php
session_start();
require_once '../config/db.php';
require_once '../config/security.php';

// Check admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: manage_users.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

$error_message = '';
$success_message = '';

// Get user details
try {
    $query = "SELECT id, username, email, role FROM users WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_GET['id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        header('Location: manage_users.php');
        exit;
    }
} catch (PDOException $e) {
    $error_message = "Database error: " . $e->getMessage();
}

// Admin registration keyword
$admin_keyword = "animemaster2023"; // Should match your register.php keyword

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        $error_message = "Invalid CSRF token";
    } else {
        $username = sanitizeInput($_POST['username']);
        $email = sanitizeInput($_POST['email']);
        $password = $_POST['password'];
        $admin_keyword_input = isset($_POST['admin_keyword']) ? sanitizeInput($_POST['admin_keyword']) : '';
        
        // Validate inputs
        if (empty($username) || empty($email)) {
            $error_message = "Please fill in all required fields";
        } elseif (!validateEmail($email)) {
            $error_message = "Invalid email address";
        } else {
            // Determine if role is being changed
            $new_role = $user['role'];
            $change_role = isset($_POST['make_admin']) && $_POST['make_admin'] == 'on';
            
            if ($change_role) {
                if ($admin_keyword_input === $admin_keyword) {
                    $new_role = 'admin';
                } else {
                    $error_message = "Invalid admin keyword";
                }
            }
            
            if (empty($error_message)) {
                try {
                    // Check if username or email already exists (excluding current user)
                    $query = "SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?";
                    $stmt = $db->prepare($query);
                    $stmt->execute([$username, $email, $user['id']]);
                    
                    if ($stmt->fetch()) {
                        $error_message = "Username or email already exists";
                    } else {
                        // Update user (with or without password change)
                        if (!empty($password)) {
                            if (strlen($password) < 8) {
                                $error_message = "Password must be at least 8 characters long";
                            } else {
                                $hashed_password = hashPassword($password);
                                $query = "UPDATE users SET username = ?, email = ?, password = ?, role = ? WHERE id = ?";
                                $stmt = $db->prepare($query);
                                $stmt->execute([$username, $email, $hashed_password, $new_role, $user['id']]);
                            }
                        } else {
                            $query = "UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?";
                            $stmt = $db->prepare($query);
                            $stmt->execute([$username, $email, $new_role, $user['id']]);
                        }
                        
                        if (empty($error_message)) {
                            $success_message = "User updated successfully!";
                            // Refresh user data
                            $query = "SELECT id, username, email, role FROM users WHERE id = ?";
                            $stmt = $db->prepare($query);
                            $stmt->execute([$user['id']]);
                            $user = $stmt->fetch(PDO::FETCH_ASSOC);
                        }
                    }
                } catch (PDOException $e) {
                    $error_message = "Database error: " . $e->getMessage();
                }
            }
        }
    }
}

$csrf_token = generateCSRFToken();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">Edit User</h1>
            <div class="btn-toolbar mb-2 mb-md-0">
                <a href="manage_users.php" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Users
                </a>
            </div>
        </div>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?= safeOutput($error_message) ?></div>
        <?php elseif (isset($success_message)): ?>
            <div class="alert alert-success"><?= safeOutput($success_message) ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">Username *</label>
                        <input type="text" class="form-control" id="username" name="username" 
                               value="<?= safeOutput($user['username']) ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email *</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?= safeOutput($user['email']) ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">New Password (leave blank to keep current)</label>
                        <input type="password" class="form-control" id="password" name="password">
                    </div>
                    
                    <div class="admin-keyword mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="make_admin" name="make_admin" 
                                   <?= $user['role'] === 'admin' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="make_admin">
                                Make this user an admin
                            </label>
                        </div>
                        <div id="adminKeywordField" style="display: <?= $user['role'] === 'admin' ? 'block' : 'none' ?>;">
                            <label for="admin_keyword" class="form-label mt-2">Admin Keyword *</label>
                            <input type="password" class="form-control" id="admin_keyword" name="admin_keyword">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Update User</button>
                </form>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Show/hide admin keyword field
        document.getElementById('make_admin').addEventListener('change', function() {
            document.getElementById('adminKeywordField').style.display = this.checked ? 'block' : 'none';
        });
    </script>
</body>
</html>