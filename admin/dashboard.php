<?php
session_start();
if (!isset($_SESSION['adminID']) || !isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

require_once '../database/auto_database_check.php';
include 'db_connect.php';

// Current date for calculations
$currentDate = new DateTime('now', new DateTimeZone('Asia/Singapore'));

// Query to get all assets
$sql = "SELECT * FROM assets";
$result = $conn->query($sql);

// Calculate stats
$totalAssets = $result->num_rows;
$assetsUnderWarranty = 0;
$assetsNearingEndOfLife = 0;
$recentlyAdded = 0;

if ($result->num_rows > 0) {
    $result->data_seek(0); // Reset pointer
    while ($row = $result->fetch_assoc()) {
        $warrantyExpiry = new DateTime($row['Warranty_Expiry']);
        $purchaseDate = new DateTime($row['Purchase_Date']);
        $oneYearAgo = (new DateTime())->modify('-1 year');
        $threeMonthsFromNow = (new DateTime())->modify('+3 months');

        // Count assets under warranty (warranty not expired)
        if ($warrantyExpiry >= $currentDate) {
            $assetsUnderWarranty++;
        }

        // Count assets nearing end of life (within 3 months of warranty expiry)
        if ($warrantyExpiry >= $currentDate && $warrantyExpiry <= $threeMonthsFromNow) {
            $assetsNearingEndOfLife++;
        }

        // Count recently added (within last year)
        if ($purchaseDate >= $oneYearAgo) {
            $recentlyAdded++;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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
        .activity-section {
            background: white;
            border: 2px solid #333;
            border-radius: 8px;
            overflow: hidden;
        }
        .activity-header {
            background: white;
            padding: 20px 25px;
            border-bottom: 2px solid #333;
            font-size: 20px;
            font-weight: bold;
            color: #333;
        }
        .activity-table {
            width: 100%;
            border-collapse: collapse;
        }
        .activity-table th {
            background-color: #f8f9fa;
            padding: 15px 25px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 1px solid #dee2e6;
            font-size: 16px;
        }
        .activity-table td {
            padding: 15px 25px;
            border-bottom: 1px solid #dee2e6;
            color: #333;
        }
        .activity-table tr {
            background-color: #e3f2fd;
        }
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
                <p>Welcome, <?php echo htmlspecialchars($_SESSION['adminID']); ?>!</p>
            </div>
            
            <nav class="nav-menu">
                <a href="dashboard.php" class="nav-item active">
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
            <h1 class="dashboard-header">Admin Dashboard</h1>
            
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $totalAssets; ?></div>
                    <div class="stat-label">Total<br>Assets</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $assetsUnderWarranty; ?></div>
                    <div class="stat-label">Asset Under<br>Warranty</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $assetsNearingEndOfLife; ?></div>
                    <div class="stat-label">Assets Nearing<br>End of Life</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $recentlyAdded; ?></div>
                    <div class="stat-label">Recently Added<br>or Update</div>
                </div>
            </div>

            <!-- Activity Log -->
            <div class="activity-section">
                <div class="activity-header">
                    Activity Log
                </div>
                <table class="activity-table">
                    <thead>
                        <tr>
                            <th>Asset Name</th> 
                            <th>Date</th> 
                            <th>Description</th> 
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result->data_seek(0); // Reset pointer
                        while ($row = $result->fetch_assoc()) {
                            $warrantyExpiry = new DateTime($row['Warranty_Expiry']);
                            $purchaseDate = new DateTime($row['Purchase_Date']);
                            $twoYearsAgo = (new DateTime())->modify('-2 years');
                            $oneYearAgo = (new DateTime())->modify('-1 year');
                            $threeMonthsFromNow = (new DateTime())->modify('+3 months');

                            // Generate activity log entries
                            if ($warrantyExpiry <= $currentDate) {
                                echo "<tr><td>" . htmlspecialchars($row['Asset_Name']) . "</td><td>" . $warrantyExpiry->format('M, d, Y') . "</td><td>Warranty Expired</td></tr>";
                            } elseif ($warrantyExpiry <= $threeMonthsFromNow) {
                                echo "<tr><td>" . htmlspecialchars($row['Asset_Name']) . "</td><td>" . $warrantyExpiry->format('M, d, Y') . "</td><td>Warranty is about to expire</td></tr>";
                            }
                            if ($purchaseDate >= $twoYearsAgo && $purchaseDate <= $oneYearAgo) {
                                echo "<tr><td>" . htmlspecialchars($row['Asset_Name']) . "</td><td>" . $purchaseDate->format('M, d, Y') . "</td><td>Warranty Expires in 1 year</td></tr>";
                            }
                        }
                        ?>
                    </tbody>
                </table>
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
                document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
                this.classList.add('active');
            });
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>