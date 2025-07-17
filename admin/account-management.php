<?php
session_start();
if (!isset($_SESSION['adminID']) || !isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

require_once '../database/auto_database_check.php';
include 'db_connect.php';

$message = '';
$error_message = '';

// Handle account status updates and deletions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        $account_type = $_POST['account_type'];
        $account_id = $_POST['account_id'];
        
        if ($action === 'toggle_status') {
            $new_status = $_POST['new_status'];
            $table = ($account_type === 'user') ? 'users' : 'admin';
            $id_field = ($account_type === 'user') ? 'userID' : 'adminID';
            
            $sql = "UPDATE $table SET status = ? WHERE $id_field = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $new_status, $account_id);
            
            if ($stmt->execute()) {
                $message = "Account status updated successfully.";
            } else {
                $error_message = "Error updating account status.";
            }
            $stmt->close();
        }
        
        if ($action === 'delete_account') {
            // Prevent deletion of current admin
            if ($account_type === 'admin' && $account_id === $_SESSION['adminID']) {
                $error_message = "Cannot delete your own account.";
            } else {
                $table = ($account_type === 'user') ? 'users' : 'admin';
                $id_field = ($account_type === 'user') ? 'userID' : 'adminID';
                
                $sql = "DELETE FROM $table WHERE $id_field = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $account_id);
                
                if ($stmt->execute()) {
                    $message = "Account deleted successfully.";
                } else {
                    $error_message = "Error deleting account.";
                }
                $stmt->close();
            }
        }
    }
}

// Get search parameter
$search = $_GET['search'] ?? '';

// Get users
$users_sql = "SELECT userID, email, created_at, status FROM users";
if ($search) {
    $users_sql .= " WHERE userID LIKE ? OR email LIKE ?";
}
$users_sql .= " ORDER BY created_at DESC";

$users_stmt = $conn->prepare($users_sql);
if ($search) {
    $search_param = "%$search%";
    $users_stmt->bind_param("ss", $search_param, $search_param);
}
$users_stmt->execute();
$users_result = $users_stmt->get_result();

// Get admins
$admins_sql = "SELECT adminID, email, created_at, status FROM admin";
if ($search) {
    $admins_sql .= " WHERE adminID LIKE ? OR email LIKE ?";
}
$admins_sql .= " ORDER BY created_at DESC";

$admins_stmt = $conn->prepare($admins_sql);
if ($search) {
    $search_param = "%$search%";
    $admins_stmt->bind_param("ss", $search_param, $search_param);
}
$admins_stmt->execute();
$admins_result = $admins_stmt->get_result();

// Get statistics
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$active_users = $conn->query("SELECT COUNT(*) as count FROM users WHERE status = 'active'")->fetch_assoc()['count'];
$total_admins = $conn->query("SELECT COUNT(*) as count FROM admin")->fetch_assoc()['count'];
$active_admins = $conn->query("SELECT COUNT(*) as count FROM admin WHERE status = 'active'")->fetch_assoc()['count'];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Management - Admin Panel</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #f5f7fa;
            color: #333;
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 240px;
            background-color: #6b7c93;
            color: white;
            display: flex;
            flex-direction: column;
            position: fixed;
            left: 0;
            top: 70px;
            height: calc(100vh - 70px);
            z-index: 1000;
        }

        .profile-section {
            padding: 30px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .profile-image {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(45deg, #4a90e2, #357abd);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 24px;
            font-weight: bold;
        }

        .nav-menu {
            flex: 1;
            padding: 20px 0;
        }

        .nav-item {
            display: flex;
            align-items: center;
            padding: 15px 30px;
            color: white;
            text-decoration: none;
            transition: background-color 0.2s ease;
            cursor: pointer;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
            font-size: 16px;
        }

        .nav-item:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .nav-item.active {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .nav-icon {
            width: 20px;
            height: 20px;
            margin-right: 15px;
            background-color: white;
            border-radius: 3px;
            display: inline-block;
        }

        .logout-section {
            padding: 20px;
            margin-top: auto;
        }

        .logout-btn {
            width: 100%;
            padding: 12px 20px;
            background-color: #d9534f;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .logout-btn:hover {
            background-color: #c9302c;
        }

        /* Main Content */
        .main-content {
            margin-left: 240px;
            flex: 1;
            padding: 30px;
            min-height: 100vh;
        }

        .page-header {
            margin-bottom: 30px;
        }

        .page-title {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        .breadcrumb {
            color: #6c757d;
            font-size: 14px;
        }

        .breadcrumb a {
            color: #4a90e2;
            text-decoration: none;
        }

        /* Statistics Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #4a90e2;
            margin-bottom: 8px;
        }

        .stat-label {
            font-size: 14px;
            color: #6c757d;
            line-height: 1.2;
        }

        /* Search and Controls */
        .controls-section {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .search-container {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .search-input {
            flex: 1;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 14px;
        }

        .search-btn {
            padding: 12px 20px;
            background-color: #4a90e2;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
        }

        .search-btn:hover {
            background-color: #357abd;
        }

        /* Tabs */
        .tabs-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .tab-buttons {
            display: flex;
            border-bottom: 1px solid #e9ecef;
        }

        .tab-btn {
            flex: 1;
            padding: 15px 20px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            color: #6c757d;
            transition: all 0.2s ease;
        }

        .tab-btn.active {
            color: #4a90e2;
            background-color: #f8f9fa;
            border-bottom: 3px solid #4a90e2;
        }

        .tab-content {
            padding: 20px;
        }

        .tab-panel {
            display: none;
        }

        .tab-panel.active {
            display: block;
        }

        /* Table Styles */
        .accounts-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .accounts-table th {
            background-color: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #495057;
            border-bottom: 2px solid #dee2e6;
        }

        .accounts-table td {
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
            vertical-align: middle;
        }

        .accounts-table tr:hover {
            background-color: #f8f9fa;
        }

        /* Status Badge */
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-active {
            background-color: #d4edda;
            color: #155724;
        }

        .status-inactive {
            background-color: #f8d7da;
            color: #721c24;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .toggle-btn {
            background-color: #ffc107;
            color: #212529;
        }

        .toggle-btn:hover {
            background-color: #e0a800;
        }

        .delete-btn {
            background-color: #dc3545;
            color: white;
        }

        .delete-btn:hover {
            background-color: #c82333;
        }

        /* Messages */
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Top Logo Bar */
        .top-logo-bar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 70px;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border-bottom: 1px solid #e9ecef;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            padding: 0 30px;
            z-index: 1001;
        }

        .top-logo-bar img {
            height: 40px;
            width: auto;
        }

        /* Adjust existing layout */
        .dashboard-container {
            padding-top: 70px;
        }

        @media (max-width: 768px) {
            .top-logo-bar {
                padding: 0 20px;
            }
            .top-logo-bar img {
                height: 35px;
            }
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            .sidebar.mobile-open {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Top Logo Bar -->
    <div class="top-logo-bar">
        <img src="../images/logo/infinecsfull.png" alt="Infinecs - Innovate Frontier Together">
    </div>

    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="profile-section">
                <div class="profile-image">👤</div>
                <p>Welcome, <?php echo htmlspecialchars($_SESSION['adminID']); ?>!</p>
            </div>
            
            <nav class="nav-menu">
                <a href="dashboard.php" class="nav-item">
                    <span class="nav-icon"></span>
                    Dashboard
                </a>
                <a href="AdminDM.php" class="nav-item">
                    <span class="nav-icon"></span>
                    Device Management
                </a>
                <a href="borrow-requests.php" class="nav-item">
                    <span class="nav-icon"></span>
                    Borrow Requests
                </a>
                <a href="account-management.php" class="nav-item active">
                    <span class="nav-icon"></span>
                    Account Management
                </a>
                <a href="alert.php" class="nav-item">
                    <span class="nav-icon"></span>
                    Alert
                </a>
            </nav>

            <div class="logout-section">
                <button class="logout-btn" onclick="logout()">Log Out</button>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="page-header">
                <h1 class="page-title">Account Management</h1>
                <div class="breadcrumb">
                    <a href="dashboard.php">Dashboard</a> / Account Management
                </div>
            </div>

            <?php if ($message): ?>
                <div class="message success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="message error"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_users; ?></div>
                    <div class="stat-label">Total<br>Users</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $active_users; ?></div>
                    <div class="stat-label">Active<br>Users</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_admins; ?></div>
                    <div class="stat-label">Total<br>Admins</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $active_admins; ?></div>
                    <div class="stat-label">Active<br>Admins</div>
                </div>
            </div>

            <!-- Search Controls -->
            <div class="controls-section">
                <form method="GET" class="search-container">
                    <input type="text" 
                           name="search" 
                           value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Search by ID or Email..." 
                           class="search-input">
                    <button type="submit" class="search-btn">Search</button>
                    <?php if ($search): ?>
                        <a href="account-management.php" class="search-btn" style="background-color: #6c757d; text-decoration: none;">Clear</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Tabs Container -->
            <div class="tabs-container">
                <div class="tab-buttons">
                    <button class="tab-btn active" onclick="switchTab('users')">Users (<?php echo $total_users; ?>)</button>
                    <button class="tab-btn" onclick="switchTab('admins')">Administrators (<?php echo $total_admins; ?>)</button>
                </div>

                <div class="tab-content">
                    <!-- Users Tab -->
                    <div id="users-tab" class="tab-panel active">
                        <table class="accounts-table">
                            <thead>
                                <tr>
                                    <th>User ID</th>
                                    <th>Email</th>
                                    <th>Created Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($users_result->num_rows > 0): ?>
                                    <?php while ($user = $users_result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($user['userID']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email'] ?? 'Not set'); ?></td>
                                            <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                            <td>
                                                <span class="status-badge status-<?php echo $user['status']; ?>">
                                                    <?php echo ucfirst($user['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="action" value="toggle_status">
                                                        <input type="hidden" name="account_type" value="user">
                                                        <input type="hidden" name="account_id" value="<?php echo $user['userID']; ?>">
                                                        <input type="hidden" name="new_status" value="<?php echo $user['status'] === 'active' ? 'inactive' : 'active'; ?>">
                                                        <button type="submit" class="action-btn toggle-btn">
                                                            <?php echo $user['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>
                                                        </button>
                                                    </form>
                                                    <button class="action-btn delete-btn" onclick="deleteAccount('user', '<?php echo $user['userID']; ?>')">
                                                        Delete
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" style="text-align: center; color: #6c757d; padding: 40px;">
                                            No users found.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Admins Tab -->
                    <div id="admins-tab" class="tab-panel">
                        <table class="accounts-table">
                            <thead>
                                <tr>
                                    <th>Admin ID</th>
                                    <th>Email</th>
                                    <th>Created Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($admins_result->num_rows > 0): ?>
                                    <?php while ($admin = $admins_result->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <?php echo htmlspecialchars($admin['adminID']); ?>
                                                <?php if ($admin['adminID'] === $_SESSION['adminID']): ?>
                                                    <span style="color: #28a745; font-size: 12px;">(You)</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($admin['email'] ?? 'Not set'); ?></td>
                                            <td><?php echo date('M j, Y', strtotime($admin['created_at'])); ?></td>
                                            <td>
                                                <span class="status-badge status-<?php echo $admin['status']; ?>">
                                                    <?php echo ucfirst($admin['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <?php if ($admin['adminID'] !== $_SESSION['adminID']): ?>
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="action" value="toggle_status">
                                                            <input type="hidden" name="account_type" value="admin">
                                                            <input type="hidden" name="account_id" value="<?php echo $admin['adminID']; ?>">
                                                            <input type="hidden" name="new_status" value="<?php echo $admin['status'] === 'active' ? 'inactive' : 'active'; ?>">
                                                            <button type="submit" class="action-btn toggle-btn">
                                                                <?php echo $admin['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>
                                                            </button>
                                                        </form>
                                                        <button class="action-btn delete-btn" onclick="deleteAccount('admin', '<?php echo $admin['adminID']; ?>')">
                                                            Delete
                                                        </button>
                                                    <?php else: ?>
                                                        <span style="color: #6c757d; font-size: 12px;">Current User</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" style="text-align: center; color: #6c757d; padding: 40px;">
                                            No administrators found.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function logout() {
            if (confirm('Are you sure you want to log out?')) {
                window.location.href = 'logout.php';
            }
        }

        function switchTab(tabName) {
            // Hide all tab panels
            document.querySelectorAll('.tab-panel').forEach(panel => {
                panel.classList.remove('active');
            });
            
            // Remove active class from all tab buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Show selected tab panel
            document.getElementById(tabName + '-tab').classList.add('active');
            
            // Add active class to clicked button
            event.target.classList.add('active');
        }

        function deleteAccount(accountType, accountId) {
            if (confirm(`Are you sure you want to delete this ${accountType} account? This action cannot be undone.`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_account">
                    <input type="hidden" name="account_type" value="${accountType}">
                    <input type="hidden" name="account_id" value="${accountId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Mobile menu toggle
        function toggleMobileMenu() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('mobile-open');
        }
    </script>
</body>
</html> 