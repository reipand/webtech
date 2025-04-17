<?php
session_start();
require_once 'config/db.php';
require_once 'config/security.php';

$database = new Database();
$db = $database->getConnection();

$error_message = '';
$success_message = '';

// Default values for form fields
$username = ''; // Initialize username with an empty string
$email = '';    // Initialize email with an empty string

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Admin registration keyword
$admin_keyword = "animemaster2023"; // Change this to your secret admin keyword

// Process registration form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $admin_keyword_input = isset($_POST['admin_keyword']) ? sanitizeInput($_POST['admin_keyword']) : '';

    // Validate CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error_message = 'Invalid CSRF token. Please try again.';
    } elseif (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_message = 'Please fill in all required fields.';
    } elseif (!validateEmail($email)) {
        $error_message = 'Invalid email address.';
    } elseif ($password !== $confirm_password) {
        $error_message = 'Passwords do not match.';
    } elseif (strlen($password) < 8) {
        $error_message = 'Password must be at least 8 characters long.';
    } else {
        try {
            // Check if username or email already exists
            $query = "SELECT id FROM users WHERE username = ? OR email = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$username, $email]);

            if ($stmt->fetch()) {
                $error_message = 'Username or email already exists.';
            } else {
                // Determine role based on admin keyword
                $role = 'user';
                if (!empty($admin_keyword_input)) {
                    if ($admin_keyword_input === $admin_keyword) {
                        $role = 'admin';
                    } else {
                        $error_message = 'Invalid admin keyword.';
                    }
                }

                // If no errors, proceed with registration
                if (empty($error_message)) {
                    // Hash password securely
                    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                    // Insert new user into the database
                    $query = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
                    $stmt = $db->prepare($query);
                    $stmt->execute([$username, $email, $hashed_password, $role]);

                    // Redirect to login page with success message
                    header('Location: login.php?registered=1');
                    exit;
                }
            }
        } catch (PDOException $e) {
            $error_message = 'Database error: ' . $e->getMessage();
        }
    }
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Anime Universe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Anime Universe</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Anime List</a></li>
                </ul>
                <ul class="navbar-nav ms-3">
                    <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Register Content -->
    <div class="container">
        <div class="register-container">
            <div class="register-header">
                <img src="assets/images/logo.png" alt="Anime Universe Logo">
                <h2>Create Your Account</h2>
                <p class="text-muted">Join Anime Universe today and start exploring!</p>
            </div>

            <?php if ($error_message): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($error_message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form method="POST" action="register.php">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="username" name="username" 
                           placeholder="Username" required value="<?= htmlspecialchars($username) ?>">
                    <label for="username"><i class="fas fa-user me-2"></i>Username</label>
                </div>

                <div class="form-floating mb-3">
                    <input type="email" class="form-control" id="email" name="email" 
                           placeholder="name@example.com" required value="<?= htmlspecialchars($email) ?>">
                    <label for="email"><i class="fas fa-envelope me-2"></i>Email address</label>
                </div>

                <div class="form-floating mb-3">
                    <input type="password" class="form-control" id="password" name="password" 
                           placeholder="Password" required>
                    <label for="password"><i class="fas fa-lock me-2"></i>Password</label>
                </div>

                <div class="form-floating mb-3">
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                           placeholder="Confirm Password" required>
                    <label for="confirm_password"><i class="fas fa-lock me-2"></i>Confirm Password</label>
                </div>

                <!-- <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="admin_keyword" name="admin_keyword" 
                           placeholder="Admin Keyword (Optional)">
                    <label for="admin_keyword"><i class="fas fa-key me-2"></i>Admin Keyword (Optional)</label>
                </div> -->

                <button type="submit" class="btn btn-primary btn-register w-100 mb-3">
                    <i class="fas fa-user-plus me-2"></i>Register
                </button>

                <div class="register-links">
                    <p class="text-muted">Already have an account? <a href="login.php" class="text-decoration-none">Login here</a></p>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>