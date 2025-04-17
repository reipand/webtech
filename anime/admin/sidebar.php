
<link rel="stylesheet" href="../../assets/css/admin.css">
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar Toggle Button (Mobile Only) -->
        <button class="navbar-toggler d-md-none position-fixed" type="button" 
                data-bs-toggle="collapse" data-bs-target="#sidebarCollapse"
                style="z-index: 1000; left: 10px; top: 10px;">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Sidebar -->
        <nav id="sidebarCollapse" class="col-md-2 col-lg-2 d-md-block sidebar collapse">
            <div class="sidebar-sticky pt-3">
                <div class="d-flex justify-content-between align-items-center d-md-none mb-4">
                    <h4 class="text-white">Anime Universe</h4>
                    <button class="btn btn-link text-white" type="button" 
                            data-bs-toggle="collapse" data-bs-target="#sidebarCollapse">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                
                <ul class="nav flex-column">
                    <li class="nav-item text-center mb-4 d-none d-md-block">
                        <h4 class="text-white">Anime Universe</h4>
                        <small class="text-muted">Admin Panel</small>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= in_array(basename($_SERVER['PHP_SELF']), ['manage_anime.php', 'add_anime.php', 'edit_anime.php']) ? 'active' : '' ?>" href="manage_anime.php">
                            <i class="bi bi-film"></i> Manage Anime
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= in_array(basename($_SERVER['PHP_SELF']), ['manage_users.php', 'add_user.php', 'edit_user.php']) ? 'active' : '' ?>" href="manage_users.php">
                            <i class="bi bi-people"></i> Manage Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'reports.php' ? 'active' : '' ?>" href="reports.php">
                            <i class="bi bi-graph-up"></i> Reports
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'active' : '' ?>" href="settings.php">
                            <i class="bi bi-gear"></i> Settings
                        </a>
                    </li>
                    <li class="nav-item mt-4">
                        <a class="nav-link" href="../../logout.php">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <main role="main" class="col-md-10 col-lg-10 ms-sm-auto px-md-4 main-content">
            <!-- Your main content here -->
        </main>
    </div>
</div>
