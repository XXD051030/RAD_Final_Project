<?php
session_start();
$conn = new mysqli("localhost", "root", "", "rad");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$alertMessages = [];

// Warranty alert logic
$today = new DateTime();
$inSixMonths = (clone $today)->modify('+6 months');

$result = $conn->query("SELECT asset_name, serial_number, Warranty_Expiry FROM assets");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $warrantyEnd = new DateTime($row['Warranty_Expiry']);
        if ($warrantyEnd >= $today && $warrantyEnd <= $inSixMonths) {
            $daysLeft = $today->diff($warrantyEnd)->days;
            $alertMessages[] = [
                'name' => $row['asset_name'],
                'serial_number' => $row['serial_number'],
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
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
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
        a {
            color: white;
            text-decoration: none;
            transition: color 0.5s ease;
        }
        a:hover {
            color: red; 
        }

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

        .nav-item {
            padding: 15px 30px;
            color: white;
            cursor: pointer;
        }

        .nav-item:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .nav-item.active {
            background-color: rgba(255, 255, 255, 0.2);
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
    </style>
</head>
<body>
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