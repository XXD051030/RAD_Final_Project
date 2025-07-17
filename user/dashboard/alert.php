<?php
session_start();
if (!isset($_SESSION['userid']) || !isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
    header("Location: ../login.php");
    exit();
}

require_once '../../database/auto_database_check.php';
include '../db_connect.php';

// Calculate date six months from now - 计算从现在起六个月的日期
$current_date = new DateTime('now', new DateTimeZone('Asia/Singapore'));
$six_months_later = clone $current_date;
$six_months_later->modify('+6 months'); // Changed from +12 months to +6 months

$current_date_str = $current_date->format('Y-m-d');
$six_months_later_str = $six_months_later->format('Y-m-d');

// Get detailed device information instead of just count - 获取详细设备信息而不只是计数
$devices_nearing_expiry = [];
$sql = "SELECT Asset_Name, Serial_Number, Warranty_Expiry FROM assets WHERE Warranty_Expiry BETWEEN ? AND ? AND Status = 'Active' ORDER BY Warranty_Expiry ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $current_date_str, $six_months_later_str);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    // Calculate days remaining - 计算剩余天数
    $warranty_date = new DateTime($row['Warranty_Expiry']);
    $days_remaining = $current_date->diff($warranty_date)->days;
    
    $row['days_remaining'] = $days_remaining;
    $devices_nearing_expiry[] = $row;
}

$three_months_count = count($devices_nearing_expiry); // Update variable name for consistency

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User alert</title>
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

        /* Alert Section */
        .alert-section {
            background-color: #fff;
            border: 2px solid #333;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .alert-header {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
        }

        .alert-content {
            border: 1px solid #ccc;
            border-radius: 4px;
            padding: 15px;
            position: relative;
        }

        .alert-icon {
            position: absolute;
            left: 10px;
            top: 10px;
            font-size: 24px;
            color: #f0ad4e;
        }

        .alert-message {
            margin-left: 40px;
            font-size: 16px;
            line-height: 1.4;
        }

        /* New styles for device details table - 设备详情表格的新样式 */
        .warranty-section {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .warranty-header {
            background-color: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #ffeaa7;
        }

        .warranty-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .devices-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .devices-table th,
        .devices-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .devices-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #333;
        }

        .devices-table tr:hover {
            background-color: #f5f5f5;
        }

        .activity-section {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .activity-header {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
        }

        .activity-table {
            width: 100%;
            border-collapse: collapse;
        }

        .activity-table th,
        .activity-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .activity-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #333;
        }

        .no-activity {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 20px;
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

            .devices-table,
            .activity-table {
                font-size: 14px;
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
        <img src="../../images/logo/infinecsfull.png" alt="Infinecs - Innovate Frontier Together">
    </div>

    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="profile-section">
                <div class="profile-image">👤</div>
                <p>Welcome, <?php echo htmlspecialchars($_SESSION['userid']); ?>!</p>
            </div>
            
            <nav class="nav-menu">
                <a href="dashboard.php" class="nav-item">
                    <span class="nav-icon"></span>
                    Dashboard
                </a>
                <a href="../View.php" class="nav-item">
                    <span class="nav-icon"></span>
                    View
                </a>
                <a href="../borrow-status.php" class="nav-item">
                    <span class="nav-icon"></span>
                    Borrow Status
                </a>
                <a href="alert.php" class="nav-item active">
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
            <div class="alert-section">
                <h2 class="alert-header">User Alerts</h2>
                <div class="alert-content">
                    <i class="fas fa-exclamation-triangle alert-icon"></i>
                    <p class="alert-message">There <?php echo $three_months_count == 1 ? 'is' : 'are'; ?> <?php echo $three_months_count; ?> device<?php echo $three_months_count != 1 ? 's' : ''; ?> nearing warranty expiry within six months. Please expand your warranty.</p>
                </div>
            </div>

            <!-- New Warranty Alerts Section - 新的保修警报部分 -->
            <div class="warranty-section">
                <div class="warranty-header">
                    <div class="warranty-title">Devices with Warranty Ending in 6 Months</div>
                </div>
                
                <?php if (count($devices_nearing_expiry) > 0): ?>
                    <table class="devices-table">
                        <thead>
                            <tr>
                                <th>Device Name</th>
                                <th>Serial Number</th>
                                <th>Warranty End Date</th>
                                <th>Days Left</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($devices_nearing_expiry as $device): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($device['Asset_Name']); ?></td>
                                    <td><?php echo htmlspecialchars($device['Serial_Number']); ?></td>
                                    <td><?php echo htmlspecialchars($device['Warranty_Expiry']); ?></td>
                                    <td><?php echo $device['days_remaining']; ?> day(s)</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="text-align: center; color: #666; padding: 20px;">No devices with warranty ending in the next 6 months.</p>
                <?php endif; ?>
            </div>

            <!-- Recent Activity Log Section - 最近活动日志部分 -->
            <div class="activity-section">
                <h3 class="activity-header">Recent Activity Log</h3>
                <table class="activity-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Serial Number</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="3" class="no-activity">No recent activity.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function logout() {
            if (confirm('Are you sure you want to log out?')) {
                window.location.href = '../logout.php';
            }
        }

        // Mobile menu toggle
        function toggleMobileMenu() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('mobile-open');
        }

        // Add click events for navigation items
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', function() {
                document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
                this.classList.add('active');
            });
        });
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</body>
</html>