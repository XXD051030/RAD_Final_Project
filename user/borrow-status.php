<?php
session_start();
if (!isset($_SESSION['userid']) || !isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
    header("Location: login.php");
    exit();
}

require_once '../database/auto_database_check.php';
include 'db_connect.php';

// Get the current user's borrow requests
$user_id = $_SESSION['userid'];
$sql = "SELECT * FROM borrow_requests WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Calculate stats for the user
$stats_sql = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
    SUM(CASE WHEN status = 'declined' THEN 1 ELSE 0 END) as declined
    FROM borrow_requests WHERE user_id = ?";
$stats_stmt = $conn->prepare($stats_sql);
$stats_stmt->bind_param("s", $user_id);
$stats_stmt->execute();
$stats_result = $stats_stmt->get_result();
$stats = $stats_result->fetch_assoc();

$stmt->close();
$stats_stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrow Request Status</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #f5f5f5;
            overflow-x: hidden;
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
            top: 0;
            height: 100vh;
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

        /* Main Content Styles */
        .main-content {
            margin-left: 240px;
            flex: 1;
            padding: 30px;
        }

        .dashboard-header {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            margin-bottom: 30px;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            border: 2px solid #333;
            border-radius: 8px;
            padding: 25px 20px;
            text-align: center;
        }

        .stat-number {
            font-size: 48px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        .stat-label {
            font-size: 14px;
            color: #333;
            line-height: 1.3;
        }

        /* Status Section */
        .status-section {
            background: white;
            border: 2px solid #333;
            border-radius: 8px;
            overflow: hidden;
        }

        .status-header {
            background: white;
            padding: 20px 25px;
            border-bottom: 2px solid #333;
            font-size: 20px;
            font-weight: bold;
            color: #333;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .new-request-btn {
            padding: 10px 18px;
            background-color: #4a90e2;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .new-request-btn:hover {
            background-color: #357abd;
            transform: translateY(-1px);
        }

        .status-table-container {
            overflow-x: auto;
        }

        .status-table {
            width: 100%;
            border-collapse: collapse;
        }

        .status-table th {
            background-color: #f8f9fa;
            padding: 15px 25px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 1px solid #dee2e6;
            font-size: 16px;
        }

        .status-table td {
            padding: 15px 25px;
            border-bottom: 1px solid #dee2e6;
            color: #333;
        }

        .status-table tr {
            background-color: #e3f2fd;
            transition: background-color 0.2s ease;
        }

        .status-table tr:hover {
            background-color: #bbdefb;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .status-approved {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-declined {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .admin-notes {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            font-size: 12px;
            color: #6c757d;
            margin-top: 5px;
            border-left: 3px solid #007bff;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
        }

        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.5;
        }

        .empty-state-text {
            font-size: 18px;
            margin-bottom: 8px;
        }

        .empty-state-subtext {
            font-size: 14px;
            opacity: 0.7;
            margin-bottom: 20px;
        }

        .empty-state-btn {
            padding: 12px 24px;
            background-color: #4a90e2;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
            display: inline-block;
        }

        .empty-state-btn:hover {
            background-color: #357abd;
            transform: translateY(-1px);
        }

        /* Info Box */
        .info-box {
            background-color: #e3f2fd;
            border: 1px solid #90caf9;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .info-box h4 {
            color: #1976d2;
            margin-bottom: 8px;
            font-size: 16px;
        }

        .info-box p {
            color: #1565c0;
            font-size: 14px;
            line-height: 1.4;
            margin: 0;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
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

            .dashboard-header {
                font-size: 24px;
            }

            .status-header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
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

        .sidebar {
            top: 70px;
            height: calc(100vh - 70px);
        }

        .main-content {
            margin-top: 0;
        }

        @media (max-width: 768px) {
            .top-logo-bar {
                padding: 0 20px;
            }
            .top-logo-bar img {
                height: 35px;
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
                <p>Welcome, <?php echo htmlspecialchars($_SESSION['userid']); ?>!</p>
            </div>
            
            <nav class="nav-menu">
                <a href="dashboard/dashboard.php" class="nav-item">
                    <span class="nav-icon"></span>
                    Dashboard
                </a>
                <a href="View.php" class="nav-item">
                    <span class="nav-icon"></span>
                    View
                </a>
                <a href="borrow-status.php" class="nav-item active">
                    <span class="nav-icon"></span>
                    Borrow Status
                </a>
                <a href="dashboard/alert.php" class="nav-item">
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
            <h1 class="dashboard-header">Borrow Request Status</h1>
            
            <?php if ($stats['total'] > 0): ?>
                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $stats['total']; ?></div>
                        <div class="stat-label">Total<br>Requests</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $stats['pending']; ?></div>
                        <div class="stat-label">Pending<br>Requests</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $stats['approved']; ?></div>
                        <div class="stat-label">Approved<br>Requests</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $stats['declined']; ?></div>
                        <div class="stat-label">Declined<br>Requests</div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($stats['pending'] > 0): ?>
                <div class="info-box">
                    <h4>⏳ Pending Requests</h4>
                    <p>You have <?php echo $stats['pending']; ?> pending request(s) waiting for admin approval. You will be notified once the status is updated.</p>
                </div>
            <?php endif; ?>
            
            <!-- Status Section -->
            <div class="status-section">
                <div class="status-header">
                    <span>My Borrow Requests</span>
                    <a href="dashboard/borrow.php" class="new-request-btn">
                        ➕ New Request
                    </a>
                </div>
                <div class="status-table-container">
                    <table class="status-table">
                                                    <thead>
                                <tr>
                                    <th>Request ID</th>
                                    <th>Asset Name</th>
                                    <th>Asset ID</th>
                                    <th>Request Date</th>
                                    <th>Status</th>
                                    <th>Admin Response</th>
                                </tr>
                            </thead>
                        <tbody>
                            <?php
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $statusClass = 'status-' . strtolower($row['status']);
                                    echo "<tr>";
                                    echo "<td>#" . $row['id'] . "</td>";
                                    echo "<td>" . htmlspecialchars($row['asset_name']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['asset_id']) . "</td>";
                                    echo "<td>" . date('M j, Y', strtotime($row['created_at'])) . "</td>";
                                    echo "<td><span class='status-badge $statusClass'>" . $row['status'] . "</span></td>";
                                    echo "<td>";
                                    if ($row['updated_at'] != $row['created_at']) {
                                        echo "<small style='color: #6c757d;'>Processed on " . date('M j, Y', strtotime($row['updated_at'])) . "</small>";
                                        if ($row['admin_notes']) {
                                            echo "<div class='admin-notes'><strong>Admin Notes:</strong> " . htmlspecialchars($row['admin_notes']) . "</div>";
                                        }
                                    } else {
                                        echo "<span style='color: #856404; font-size: 12px;'>Awaiting review</span>";
                                    }
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6' class='empty-state'>";
                                echo "<div class='empty-state-icon'>📋</div>";
                                echo "<div class='empty-state-text'>No borrow requests yet</div>";
                                echo "<div class='empty-state-subtext'>Start by submitting your first borrow request</div>";
                                echo "<a href='dashboard/borrow.php' class='empty-state-btn'>Submit Request</a>";
                                echo "</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
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

        // Mobile menu toggle (if needed)
        function toggleMobileMenu() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('mobile-open');
        }

        // Add click events for navigation items
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', function() {
                // Remove active class from all items
                document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
                // Add active class to clicked item
                this.classList.add('active');
            });
        });
    </script>
</body>
</html> 