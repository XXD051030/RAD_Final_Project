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

$assets = [];

$search = $_GET['search'] ?? '';

if ($search) {
    $stmt = $conn->prepare("SELECT asset_id, asset_name, category, brand_model, serial_number, location, assigned_to, purchase_date, Warranty_Expiry, asset_value, status, supplier FROM assets WHERE asset_name LIKE ? OR category LIKE ? OR serial_number LIKE ?");
    $like = "%" . $search . "%";
    $stmt->bind_param("sss", $like, $like, $like);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT asset_id, asset_name, category, brand_model, serial_number, location, assigned_to, purchase_date, Warranty_Expiry, asset_value, status, supplier FROM assets");
}

while ($row = $result->fetch_assoc()) {
    $assets[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Asset</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background-color: #f4f6f8;
            height: 100vh; /* Ensure body takes full height */
            overflow: hidden; /* Prevent body from scrolling */
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
            margin-left: 240px; /* Adjusted to match sidebar width */
            padding: 30px;
            background-color: #fff;
            min-height: calc(100vh - 60px); /* Adjust for padding or header */
            max-height: calc(100vh - 60px); /* Adjust for padding or header */
            overflow-y: auto; /* Enable vertical scrolling */
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
            max-height: calc(100vh - 200px); /* Adjust based on header and form height */
            overflow-y: auto; /* Enable scrolling within the table */
            display: block; /* Allow table to have its own scroll */
        }
        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
            white-space: nowrap; /* Prevent text wrapping */
        }
        th {
            background-color: #e0e7f7;
            color: #2c3e50;
            position: sticky;
            top: 0; /* Keep headers visible while scrolling */
            z-index: 1;
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

            .content {
                margin-left: 0;
                padding: 20px;
                min-height: calc(100vh - 40px);
                max-height: calc(100vh - 40px);
            }

            table {
                max-height: calc(100vh - 150px); /* Adjust for smaller screens */
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
        <h2><span class="highlight">Admin</span> Device Management</h2>
        <div>
            <a href="AdminDM.php"><button class="button">Back to Asset</button></a>
            <br>
        </div>
        <h3>View Asset</h3>
        <br>
        <form class="search-form" method="GET">
            <input type="text" name="search" class="search-input" placeholder="Search device..." value="<?= htmlspecialchars($search) ?>">
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
            <?php foreach ($assets as $device): ?>
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
                <td>
                    <a href="update-asset.php?id=<?= $device['asset_id'] ?>">
                        <button class="action-btn edit-btn">Edit</button>
                    </a>
                    <a href="delete-asset.php?id=<?= $device['asset_id'] ?>" onclick="return confirm('Are you sure you want to delete this asset?');">
                        <button class="action-btn delete-btn">Delete</button>
                    </a>
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