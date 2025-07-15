<?php
session_start();
$conn = new mysqli("localhost", "root", "", "rad");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Session check
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Get the search parameters
$search = $_GET['search'] ?? '';

// Prepare and execute query based on search
if ($search) {
    $stmt = $conn->prepare("SELECT asset_id, asset_name, category, brand_model, serial_number, location, assigned_to, purchase_date, Warranty_Expiry, asset_value, status, supplier FROM assets WHERE asset_name LIKE ? OR serial_number LIKE ? OR category LIKE ?");
    $like = "%$search%";
    $stmt->bind_param("sss", $like, $like, $like);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $stmt = $conn->prepare("SELECT asset_id, asset_name, category, brand_model, serial_number, location, assigned_to, purchase_date, Warranty_Expiry, asset_value, status, supplier FROM assets");
    $stmt->execute();
    $result = $stmt->get_result();
}

$devices = [];
while ($row = $result->fetch_assoc()) {
    $devices[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Device Management</title>
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
            margin-left: 220px;
            padding: 30px;
            background-color: #fff;
            min-height: 100vh;
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

        .search-form {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            justify-content: flex-start;
            align-items: center;
        }

        .search-input {
            padding: 10px 14px;
            width: 280px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 5px rgba(52, 152, 219, 0.5);
        }

        .search-button {
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .search-button:hover {
            background-color: #2980b9;
        }

        /* Edit button */
        .edit-btn {
            background-color: #3498db;
            color: white;
        }

        .edit-btn:hover {
            background-color: #2980b9;
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

            .content {
                margin-left: 0;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="profile-section">
            <div class="profile-image">👤</div>
        </div>
        
        <nav class="nav-menu">
            <div class="nav-item">
                <span class="nav-icon"></span>
                <a href="dashboard.php">Dashboard</a>
            </div>
            <div class="nav-item active">
                <span class="nav-icon"></span>
                <a href="AdminDM.php">Device Management</a>
            </div>
            <div class="nav-item">
                <span class="nav-icon"></span>
                <a href="alert.php">Alert</a>
            </div>
        </nav>
        
        <div class="logout-section">
            <button class="logout-btn" onclick="logout()">Log Out</button>
        </div>
    </div>
    <div class="content">
        <h2>Admin Device Management</h2>
        <div>
            <a href="add-asset.php"><button class="button">Add Asset</button></a>
            <a href="view-asset.php"><button class="button">View Asset</button></a>
            <a href="delete-asset.php"><button class="button">Delete Asset</button></a>
        </div>
        <h3>Asset Table</h3>
        <br>
        <form class="search-form" method="GET">
            <input type="text" name="search" class="search-input" placeholder="Search device..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            <button type="submit" class="search-button">Search</button>
        </form>
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
            <?php foreach ($devices as $device): ?>
            <tr>
                <td><?= htmlspecialchars($device['asset_id']) ?></td>
                <td><?= htmlspecialchars($device['asset_name']) ?></td>
                <td><?= htmlspecialchars($device['category']) ?></td>
                <td><?= htmlspecialchars($device['brand_model']) ?></td>
                <td><?= htmlspecialchars($device['serial_number']) ?></td>
                <td><?= htmlspecialchars($device['location']) ?></td>
                <td><?= htmlspecialchars($device['assigned_to']) ?></td>
                <td><?= htmlspecialchars($device['purchase_date']) ?></td>
                <td><?= htmlspecialchars($device['Warranty_Expiry']) ?></td>
                <td><?= htmlspecialchars($device['asset_value']) ?></td>
                <td><?= htmlspecialchars($device['status']) ?></td>
                <td><?= htmlspecialchars($device['supplier']) ?></td>
                <td> <a href="update-asset.php?id=<?= urlencode($device['asset_id']) ?>"> <button class="button">Update</button></a>
</td>

            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <script>
        function logout() {
            if (confirm('Are you sure you want to log out?')) {
                // Clear session data
                window.location.href = 'logout.php';
            }
        }
    </script>
</body>
</html>