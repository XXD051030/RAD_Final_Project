<?php
session_start();
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header("Location: ../../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Track</title>
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

        /* Asset Tracking Styles */
        .asset-tracking {
            background: white;
            border: 2px solid #333;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .tracking-header {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
        }

        .back-button {
            background-color: white;
            border: 1px solid #333;
            border-radius: 3px;
            padding: 5px 10px;
            text-decoration: none;
            color: #333;
            margin-bottom: 10px;
            display: inline-block;
        }

        .tracking-list {
            list-style: none;
        }

        .tracking-item {
            padding: 10px;
            border-bottom: 1px solid #dee2e6;
        }

        .tracking-item:last-child {
            border-bottom: none;
        }

        /* Activity Log */
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
        }
    </style>
</head>

<body>
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
                <a href="../View.php" class="nav-item active">
                    <span class="nav-icon"></span>
                    View
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
            <h1 class="dashboard-header">User Dashboard</h1>

            <!-- Asset Tracking Section -->
            <div class="asset-tracking">
                <div class="tracking-header">Track Asset</div>
                <a href="../View.php" class="back-button">Back to Asset</a>
                <ul class="tracking-list">
                    <li class="tracking-item">
                        <img src="package-icon.png" alt="Package Icon" style="width:20px;height:20px;margin-right:10px;">
                        Seller are packing your parcel
                    </li>
                    <li class="tracking-item">
                        <img src="package-icon.png" alt="Package Icon" style="width:20px;height:20px;margin-right:10px;">
                        Your parcel is out for delivery
                    </li>
                    <li class="tracking-item">
                        <img src="package-icon.png" alt="Package Icon" style="width:20px;height:20px;margin-right:10px;">
                        Arrived Destination
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        function logout() {
            if (confirm('Are you sure you want to log out?')) {
                // Clear session data
                window.location.href = '../logout.php';
            }
        }

        // Mobile menu toggle (if needed)
        function toggleMobileMenu() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('mobile-open');
        }

        // Add click events for navigation items
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', function () {
                // Remove active class from all items
                document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
                // Add active class to clicked item
                this.classList.add('active');
            });
        });
    </script>
</body>

</html>