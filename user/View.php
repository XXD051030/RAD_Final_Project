<?php
session_start();
if (!isset($_SESSION['userid']) || !isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
    header("Location: login.php");
    exit();
}

include 'db_connect.php';

// Query to get all assets
$sql = "SELECT Asset_Name, Serial_Number, Purchase_Date, Warranty_Expiry, Status FROM assets WHERE Status = 'Active'";
$result = $conn->query($sql);

// Close connection at the end
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User View</title>
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

        .dashboard-header {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            margin-bottom: 30px;
        }

        /* Asset Section */
        .asset-section {
            background: white;
            border: 2px solid #333;
            border-radius: 8px;
            overflow: hidden;
        }

        .asset-header {
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

        .asset-buttons {
            display: flex;
            gap: 12px;
        }

        .asset-button {
            padding: 10px 18px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .asset-button.track {
            background-color: #4a90e2;
            color: white;
        }

        .asset-button.track:hover {
            background-color: #357abd;
            transform: translateY(-1px);
        }

        .asset-button.borrow {
            background-color: #28a745;
            color: white;
        }

        .asset-button.borrow:hover {
            background-color: #218838;
            transform: translateY(-1px);
        }

        .asset-table-container {
            overflow-x: auto;
        }

        .asset-table {
            width: 100%;
            border-collapse: collapse;
        }

        .asset-table th {
            background-color: #f8f9fa;
            padding: 15px 25px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 1px solid #dee2e6;
            font-size: 16px;
        }

        .asset-table td {
            padding: 15px 25px;
            border-bottom: 1px solid #dee2e6;
            color: #333;
        }

        .asset-table tr {
            background-color: #e3f2fd;
            transition: background-color 0.2s ease;
        }

        .asset-table tr:hover {
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

        .status-active {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
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

           .asset-buttons {
                flex-direction: column;
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
                <a href="../user/dashboard/dashboard.php" class="nav-item">
                    <span class="nav-icon"></span>
                    Dashboard
                </a>
                <a href="View.php" class="nav-item active">
                    <span class="nav-icon"></span>
                    View
                </a>
                <a href="../user/dashboard/alert.php" class="nav-item">
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
            <h1 class="dashboard-header">View</h1>
            
            <!-- Asset Section -->
            <div class="asset-section">
                <div class="asset-header">
                    <span>Assets</span>
                    <div class="asset-buttons">
                        <a href="../user/dashboard/borrow.php" class="asset-button borrow">
                            ➕ Borrow Asset
                        </a>
                    </div>
                </div>
                <div class="asset-table-container">
                    <table class="asset-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Serial Number</th>
                                <th>Purchase Date</th>
                                <th>Warranty Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['Asset_Name']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['Serial_Number']) . "</td>";
                                    echo "<td>" . (new DateTime($row['Purchase_Date']))->format('d/m/Y') . "</td>";
                                    echo "<td>" . (new DateTime($row['Warranty_Expiry']))->format('d/m/Y') . "</td>";
                                    echo "<td><span class='status-badge status-active'>" . htmlspecialchars($row['Status']) . "</span></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5' class='empty-state'><div class='empty-state-icon'>⚠️</div><div class='empty-state-text'>No assets found</div><div class='empty-state-subtext'>Please check back later or add new assets.</div></td></tr>";
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
                // Clear session data
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
