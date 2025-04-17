<?php
session_start();
require_once '../config/db.php';
require_once '../config/security.php';

// Check admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

$error_message = '';
$success_message = '';

// Get current settings
$settings = [];
try {
    $query = "SELECT * FROM settings";
    $stmt = $db->query($query);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (PDOException $e) {
    $error_message = "Database error: " . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        $error_message = "Invalid CSRF token";
    } else {
        try {
            $db->beginTransaction();
            
            // Update each setting
            foreach ($_POST['settings'] as $key => $value) {
                $value = sanitizeInput($value);
                $query = "INSERT INTO settings (setting_key, setting_value) 
                          VALUES (?, ?) 
                          ON DUPLICATE KEY UPDATE setting_value = ?";
                $stmt = $db->prepare($query);
                $stmt->execute([$key, $value, $value]);
            }
            
            $db->commit();
            $success_message = "Settings updated successfully!";
            
            // Refresh settings
            $query = "SELECT * FROM settings";
            $stmt = $db->query($query);
            $settings = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
            
        } catch (PDOException $e) {
            $db->rollBack();
            $error_message = "Database error: " . $e->getMessage();
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
    <title>System Settings - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">System Settings</h1>
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
                        <label for="site_title" class="form-label">Site Title</label>
                        <input type="text" class="form-control" id="site_title" name="settings[site_title]" 
                               value="<?= isset($settings['site_title']) ? safeOutput($settings['site_title']) : 'Anime Universe' ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="admin_email" class="form-label">Admin Email</label>
                        <input type="email" class="form-control" id="admin_email" name="settings[admin_email]" 
                               value="<?= isset($settings['admin_email']) ? safeOutput($settings['admin_email']) : '' ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="items_per_page" class="form-label">Items Per Page</label>
                        <input type="number" class="form-control" id="items_per_page" name="settings[items_per_page]" 
                               value="<?= isset($settings['items_per_page']) ? safeOutput($settings['items_per_page']) : '10' ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="registration_enabled" class="form-label">User Registration</label>
                        <select class="form-select" id="registration_enabled" name="settings[registration_enabled]">
                            <option value="1" <?= (isset($settings['registration_enabled'])) && $settings['registration_enabled'] == '1' ? 'selected' : '' ?>>Enabled</option>
                            <option value="0" <?= (isset($settings['registration_enabled'])) && $settings['registration_enabled'] == '0' ? 'selected' : '' ?>>Disabled</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="default_user_role" class="form-label">Default User Role</label>
                        <select class="form-select" id="default_user_role" name="settings[default_user_role]">
                            <option value="user" <?= (isset($settings['default_user_role']) && $settings['default_user_role'] == 'user') ? 'selected' : '' ?>>User</option>
                            <option value="admin" <?= (isset($settings['default_user_role']) && $settings['default_user_role'] == 'admin') ? 'selected' : '' ?>>Admin</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="admin_keyword" class="form-label">Admin Registration Keyword</label>
                        <input type="text" class="form-control" id="admin_keyword" name="settings[admin_keyword]" 
                               value="<?= isset($settings['admin_keyword']) ? safeOutput($settings['admin_keyword']) : 'animemaster2023' ?>">
                        <div class="form-text">This keyword allows users to register as admin when provided during registration</div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Save Settings</button>
                </form>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>