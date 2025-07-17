<?php
session_start();

require_once '../database/auto_database_check.php';

$conn = new mysqli("localhost", "root", "", "rad");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$alertMessages = [];

// Warranty alert logic
$today = new DateTime();
$inSixMonths = (clone $today)->modify('+6 months');

$result = $conn->query("SELECT Asset_Name, Serial_Number, Warranty_Expiry FROM assets");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $warrantyEnd = new DateTime($row['Warranty_Expiry']);
        if ($warrantyEnd >= $today && $warrantyEnd <= $inSixMonths) {
            $daysLeft = $today->diff($warrantyEnd)->days;
            $alertMessages[] = [
                'name' => $row['Asset_Name'],
                'serial_number' => $row['Serial_Number'],
                'warranty_end' => $warrantyEnd->format('Y-m-d'),
                'days_left' => $daysLeft
            ];
        }
    }
} else {
    error_log("Warranty query failed: " . $conn->error);
}

// Check if column 'updated_at' exists
$columnExists = false;
$check = $conn->query("SHOW COLUMNS FROM assets LIKE 'updated_at'");
if ($check && $check->num_rows > 0) {
    $columnExists = true;
}

$activityLog = false;
if ($columnExists) {
    // Activity Log (last 30 days)
    $thirtyDaysAgo = date('Y-m-d H:i:s', strtotime('-30 days'));
    $activityLog = $conn->query("SELECT updated_at, serial_number, Warranty_Expiry AS warranty_end FROM assets WHERE updated_at >= '$thirtyDaysAgo' ORDER BY updated_at DESC LIMIT 5");
    if ($activityLog === false) {
        error_log("Activity log query failed: " . $conn->error);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Alert Page</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
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
            white-space: normal;
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
            margin-top: 2px;
            background-color: white;
            border-radius: 3px;
            display: inline-block;
            flex-shrink: 0;
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
        /* Remove global link styles that interfere with navigation */

        .profile-section {
            padding: 30px 20px;
            text-align: center;
        }

        .profile-image {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(45deg, #4a90e2, #357abd);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: bold;
            margin: 0 auto 15px;
        }

        .nav-menu {
            flex: 1;
            padding: 20px 0;
        }

        /* Duplicate nav-item styles removed to prevent conflicts */

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
        }

        a {
            color: white;
            text-decoration: none;
        }

        .main-content {
            margin-left: 240px;
            padding: 30px;
        }

        h1 {
            color: #333;
            margin-bottom: 20px;
        }

        .alert-box {
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            padding: 20px;
            border-radius: 5px;
        }

        .alert-box h2 {
            color: #856404;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            border: 1px solid #ffeeba;
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #fff8dc;
        }

        tr:nth-child(even) {
            background-color: #fffdf5;
        }

        .activity-section {
            margin-top: 40px;
            background: #ffffff;
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 20px;
        }

        .activity-section h2 {
            color: #333;
            margin-bottom: 15px;
        }

        .activity-table th {
            background-color: #e3e3e3;
        }

        .activity-table td {
            border-bottom: 1px solid #ccc;
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



        .content {
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
                <a href="account-management.php" class="nav-item">
                    <span class="nav-icon"></span>
                    Account Management
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
        <h1>Warranty Alerts</h1>
        <?php if (count($alertMessages) > 0): ?>
            <div class="alert-box">
                <h2>Devices with Warranty Ending in 6 Months</h2>
                <table>
                    <tr>
                        <th>Device Name</th>
                        <th>Serial Number</th>
                        <th>Warranty End Date</th>
                        <th>Days Left</th>
                    </tr>
                    <?php foreach ($alertMessages as $alert): ?>
                        <tr>
                            <td><?= htmlspecialchars($alert['name']) ?></td>
                            <td><?= htmlspecialchars($alert['serial_number']) ?></td>
                            <td><?= $alert['warranty_end'] ?></td>
                            <td><?= $alert['days_left'] ?> day(s)</td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        <?php else: ?>
            <p>No warranty alerts. All devices are safe for now ✅</p>
        <?php endif; ?>

        <!-- Activity Log -->
        <div class="activity-section">
            <h2>Recent Activity Log</h2>
            <table class="activity-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Serial Number</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($activityLog && $activityLog->num_rows > 0): ?>
                        <?php while ($row = $activityLog->fetch_assoc()): ?>
                            <tr>
                                <td><?= date('M d, Y', strtotime($row['updated_at'])) ?></td>
                                <td><?= htmlspecialchars($row['serial_number']) ?></td>
                                <td><?= 'Warranty ends on: ' . htmlspecialchars($row['warranty_end']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="3">No recent activity.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function logout() {
            if (confirm('Are you sure you want to log out?')) {
                window.location.href = 'logout.php';
            }
        }
    </script>
</body>
</html>
<?php
$conn->close();
?>