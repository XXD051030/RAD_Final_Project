<?php
session_start();
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
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
            padding: 20px;
        }

       .asset-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

       .asset-button {
            padding: 8px 16px;
            background-color: #f0f0f0;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

       .asset-button:hover {
            background-color: #e0e0e0;
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
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 1px solid #dee2e6;
            font-size: 16px;
        }

       .asset-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #dee2e6;
            color: #333;
        }

       .asset-table tr:nth-child(even) {
            background-color: #f9f9f9;
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
            </div>
            
            <nav class="nav-menu">
                <a href="../user/dashboard/dashboard.php" class="nav-item active">
                    <span class="nav-icon"></span>
                    Dashboard
                </a>
                <a href="View.php" class="nav-item">
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
            <h1 class="dashboard-header">User Dashboard</h1>
            
            <!-- Asset Section -->
            <div class="asset-section">
                <div class="asset-buttons">
                    <a href="../user/dashboard/package.php" class="asset-button">Track Asset</a>
                    <a href="../user/dashboard/borrow.php" class="asset-button">Borrow Asset</a>
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
                                    echo "<td>" . htmlspecialchars($row['Status']) . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5'>No assets found</td></tr>";
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
