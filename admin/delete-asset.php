<?php
session_start();

// Connect to database
$conn = new mysqli("localhost", "root", "", "rad");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Session check
if (!isset($_SESSION['adminID']) || !isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Handle delete
if (isset($_GET['id'])) {
    $id = $_GET['id']; // Using string ID (e.g., 'A001')
    $stmt = $conn->prepare("DELETE FROM assets WHERE asset_id = ?");
    if ($stmt === false) {
        error_log("Prepare failed: " . $conn->error);
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("s", $id); // 's' for string
    if (!$stmt->execute()) {
        error_log("Delete failed: " . $conn->error);
        die("Delete failed: " . $conn->error);
    }
    $stmt->close();
    header("Location: delete-asset.php?deleted=1"); // Reload the same page
    exit();
}

// Fetch all assets from the database
$result = $conn->query("SELECT * FROM assets");
$error_message = null;
$assets = [];
if ($result === false) {
    error_log("Query failed: " . $conn->error);
    $error_message = "Error fetching assets: " . $conn->error;
} else {
    while ($row = $result->fetch_assoc()) {
        $assets[] = $row;
    }
    error_log("Query executed, rows found: " . count($assets));
if (!empty($assets)) {
    error_log("Sample row data: " . print_r($assets[0], true));
} else {
    error_log("No rows returned from assets table.");
}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Asset</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background-color: #f4f6f8;
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
        /*content*/
        .content {
    margin-left: 240px;
    padding: 30px;
    background-color: #fff;
    min-height: 100vh;
    overflow-y: auto;
}
        h2 {
            color: #2c3e50;
        }
        h3 {
            color: #2c3e50;
        }
        .actions {
            margin-bottom: 20px;
        }
        .button {
            padding: 8px 16px;
            margin-right: 30px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        .button:hover {
            background-color: #2980b9;
        }
        table {
    width: 100%;
    border-collapse: collapse;
    background-color: #fff;
    display: block;
    overflow-x: auto; /* Allow horizontal scrolling if needed */
}
        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #e0e7f7;
            color: #2c3e50;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #e6f3ff;
        }
        .action-btn {
            padding: 6px 12px;
            margin: 2px;
            font-weight: bold;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        /* Delete button */
        .delete-btn {
            background-color: #d9534f;
            color: white;
        }

        .delete-btn:hover {
            background-color: #c9302c;
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
            <a href="AdminDM.php" class="nav-item active">
                <span class="nav-icon"></span>
                Device Management
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

    <div class="content">
        <?php if (isset($_GET['deleted'])): ?>
            <p style="color: green; font-weight: bold;">✅ Asset successfully deleted!</p>
        <?php endif; ?>
        <?php if (isset($error_message)): ?>
            <p style="color: red; font-weight: bold;"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>
        <h2><span class="highlight">Admin</span> Device Management</h2>
        <div>
            <a href="AdminDM.php"><button class="button">Back to Asset</button></a>
            <br><br>
        </div>
        <h3>Delete Asset</h3>
        <table>
            <tr>
                <th>ID</th>
                <th>Asset Name</th>
                <th>Category</th>
                <th>Brand_Model</th>
                <th>Serial Number</th>
                <th>Location</th>
                <th>Assigned_To</th>
                <th>Purchase_Date</th>
                <th>Warranty_Expiry</th>
                <th>Asset_Value</th>
                <th>Status</th>
                <th>Supplier</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($assets as $device): ?>
            <tr>
                <td><?= htmlspecialchars($device['asset_id'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($device['asset_name'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($device['category'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($device['brand_model'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($device['serial_number'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($device['location'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($device['assigned_to'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($device['purchase_date'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($device['Warranty_Expiry'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($device['asset_value'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($device['status'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($device['supplier'] ?? 'N/A') ?></td>
                <td>
                    <a href="delete-asset.php?id=<?= htmlspecialchars($device['asset_id'] ?? '') ?>" onclick="return confirm('Are you sure?')">
                        <button class="action-btn delete-btn">Delete</button>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($assets)): ?>
                <tr><td colspan="13">No assets found.</td></tr>
            <?php endif; ?>
        </table>
    </div>

    <script>
        function logout() {
            if (confirm("Are you sure you want to log out?")) {
                window.location.href = 'logout.php';
            }
        }
    </script>
</body>
</html>
<?php
$conn->close();
?>