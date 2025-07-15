<?php
session_start();
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header("Location: ../login.php");
    exit();
}

include '../db_connect.php';

// Calculate date three months from now
$current_date = new DateTime('now', new DateTimeZone('Asia/Singapore')); // Adjust timezone as needed
$three_months_later = clone $current_date;
$three_months_later->modify('+12 months');
$three_months_count = 0;

$current_date_str = $current_date->format('Y-m-d');
$three_months_later_str = $three_months_later->format('Y-m-d');

$sql = "SELECT COUNT(*) as count FROM assets WHERE Warranty_Expiry BETWEEN ? AND ? AND Status = 'Active'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $current_date_str, $three_months_later_str);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $three_months_count = $row['count'];
}

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
            color: #f0ad4e; /* Yellow color for warning icon */
        }

        .alert-message {
            margin-left: 40px;
            font-size: 16px;
            line-height: 1.4;
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
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="profile-section">
                <div class="profile-image">👤</div>
            </div>
            
            <nav class="nav-menu">
                <a href="dashboard.php" class="nav-item active">
                    <span class="nav-icon"></span>
                    Dashboard
                </a>
                <a href="../View.php" class="nav-item">
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
            <div class="alert-section">
                <h2 class="alert-header">User Alerts</h2>
                <div class="alert-content">
                    <i class="fas fa-exclamation-triangle alert-icon"></i>
                    <p class="alert-message">There <?php echo $three_months_count == 1 ? 'is' : 'are'; ?> <?php echo $three_months_count; ?> device<?php echo $three_months_count != 1 ? 's' : ''; ?> nearing warranty expiry within three months. Please expand your warranty.</p>
                </div>
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