<?php
session_start();

require_once '../database/auto_database_check.php';

$conn = new mysqli("localhost", "root", "", "rad");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Session check
if (!isset($_SESSION['adminID']) || !isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Get the search parameters
$search = $_GET['search'] ?? '';

// Prepare and execute query based on search
if ($search) {
    // Enhanced search - case insensitive and searches across all relevant fields
    $stmt = $conn->prepare("
        SELECT Asset_ID, Asset_Name, Category, Brand_Model, Serial_Number, Location, Assigned_To, Purchase_Date, Warranty_Expiry, Asset_Value, Status, Supplier 
        FROM assets 
        WHERE LOWER(Asset_ID) LIKE LOWER(?) 
           OR LOWER(Asset_Name) LIKE LOWER(?) 
           OR LOWER(Category) LIKE LOWER(?) 
           OR LOWER(Brand_Model) LIKE LOWER(?) 
           OR LOWER(Serial_Number) LIKE LOWER(?) 
           OR LOWER(Location) LIKE LOWER(?) 
           OR LOWER(Assigned_To) LIKE LOWER(?) 
           OR LOWER(Status) LIKE LOWER(?) 
           OR LOWER(Supplier) LIKE LOWER(?)
           OR LOWER(Asset_Value) LIKE LOWER(?)
           OR LOWER(Purchase_Date) LIKE LOWER(?)
           OR LOWER(Warranty_Expiry) LIKE LOWER(?)
        ORDER BY Asset_Name ASC
    ");
    $like = "%$search%";
    $stmt->bind_param("ssssssssssss", $like, $like, $like, $like, $like, $like, $like, $like, $like, $like, $like, $like);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $stmt = $conn->prepare("SELECT Asset_ID, Asset_Name, Category, Brand_Model, Serial_Number, Location, Assigned_To, Purchase_Date, Warranty_Expiry, Asset_Value, Status, Supplier FROM assets ORDER BY Asset_Name ASC");
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
        /* Main Content */
        .main-content {
            margin-left: 240px;
            flex: 1;
            padding: 30px;
            height: 100vh;
            overflow: hidden; /* Prevent main content from scrolling */
            display: flex;
            flex-direction: column;
        }
        .dashboard-header {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            margin-bottom: 30px;
            flex-shrink: 0; /* Prevent header from shrinking */
        }
        
        /* Action Buttons Section */
        .action-buttons-section {
            background: white;
            border: 2px solid #333;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 20px;
            flex-shrink: 0; /* Prevent section from shrinking */
        }
        .action-buttons-header {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
        }
        .action-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        .action-btn {
            padding: 12px 24px;
            background-color: #4a90e2;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .action-btn:hover {
            background-color: #357abd;
            transform: translateY(-1px);
        }
        /* Assets Table Section */
        .assets-section {
            background: white;
            border: 2px solid #333;
            border-radius: 8px;
            overflow: hidden;
            height: calc(100vh - 420px); /* Fixed height to prevent page scrolling */
            display: flex;
            flex-direction: column;
        }
        .assets-header {
            background: white;
            padding: 20px 25px;
            border-bottom: 2px solid #333;
            font-size: 20px;
            font-weight: bold;
            color: #333;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0; /* Prevent header from shrinking */
        }
        .assets-table-container {
            flex: 1;
            overflow-y: auto; /* Enable vertical scrolling */
            overflow-x: auto; /* Keep horizontal scrolling for wide tables */
            max-height: 100%;
        }
        .assets-table {
            width: 100%;
            border-collapse: collapse;
            position: relative;
        }
        .assets-table th {
            background-color: #f8f9fa;
            padding: 15px 20px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 1px solid #dee2e6;
            font-size: 14px;
            position: sticky; /* Keep headers visible while scrolling */
            top: 0;
            z-index: 10;
        }
        .assets-table td {
            padding: 15px 20px;
            border-bottom: 1px solid #dee2e6;
            color: #333;
            font-size: 14px;
        }
        /* Specific width adjustments for certain columns */
        .assets-table th:nth-child(4), /* Brand/Model column header */
        .assets-table td:nth-child(4) { /* Brand/Model column data */
            width: 150px;
            min-width: 150px;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        .assets-table th:nth-child(5), /* Serial Number column header */
        .assets-table td:nth-child(5) { /* Serial Number column data */
            width: 140px;
            min-width: 140px;
        }
        .assets-table td:nth-child(5) code {
            font-family: 'Courier New', monospace;
            background-color: transparent;
            padding: 0;
            font-size: 13px;
            color: inherit;
        }
        .assets-table th:nth-child(6), /* Location column header */
        .assets-table td:nth-child(6) { /* Location column data */
            width: 130px;
            min-width: 130px;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        .assets-table th:nth-child(7), /* Assigned To column header */
        .assets-table td:nth-child(7) { /* Assigned To column data */
            width: 130px;
            min-width: 130px;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        .assets-table th:nth-child(8), /* Purchase Date column header */
        .assets-table td:nth-child(8) { /* Purchase Date column data */
            width: 140px;
            min-width: 140px;
            text-align: center;
        }
        .assets-table th:nth-child(9), /* Warranty Expiry column header */
        .assets-table td:nth-child(9) { /* Warranty Expiry column data */
            width: 140px;
            min-width: 140px;
            text-align: center;
        }
        .assets-table th:nth-child(10), /* Value column header */
        .assets-table td:nth-child(10) { /* Value column data */
            width: 120px;
            min-width: 120px;
            text-align: right;
        }
        .assets-table th:nth-child(11), /* Status column header */
        .assets-table td:nth-child(11) { /* Status column data */
            width: 140px;
            min-width: 140px;
            text-align: center;
        }
        .assets-table th:nth-child(13), /* Actions column header */
        .assets-table td:nth-child(13) { /* Actions column data */
            width: 140px;
            min-width: 140px;
            text-align: center;
        }
        .assets-table tr {
            background-color: #e3f2fd;
            transition: background-color 0.2s ease;
        }
        .assets-table tr:hover {
            background-color: #bbdefb;
        }
        
        /* Custom Scrollbar Styling */
        .assets-table-container::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        .assets-table-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }
        .assets-table-container::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }
        .assets-table-container::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
        .update-btn {
            padding: 8px 16px;
            background-color: #4a90e2;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            min-width: 100px;
            justify-content: center;
        }
        .update-btn:hover {
            background-color: #357abd;
            transform: translateY(-1px);
        }

        /* Search Section */
        .search-section {
            background: white;
            border: 2px solid #333;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            flex-shrink: 0; /* Prevent section from shrinking */
        }
        .search-form {
            display: flex;
            gap: 12px;
            align-items: center;
        }
        .search-input {
            flex: 1;
            max-width: 400px;
            padding: 12px 16px;
            border: 2px solid #e9ecef;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.2s ease;
        }
        .search-input:focus {
            border-color: #4a90e2;
            outline: none;
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
        }
        .search-button {
            padding: 12px 24px;
            background-color: #4a90e2;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }
        .search-button:hover {
            background-color: #357abd;
        }
        .clear-search-btn {
            padding: 12px 20px;
            background-color: #6c757d;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .clear-search-btn:hover {
            background-color: #5a6268;
        }

        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            min-width: 80px;
            text-align: center;
            white-space: nowrap;
        }
        .status-active {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status-retired {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .status-repair {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        /* Handle longer status text like "IN REPAIR" */
        .status-inrepair {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        /* Additional status variations */
        .status-maintenance {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .status-pending {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
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
                height: 100vh;
            }
            
            .dashboard-header {
                font-size: 24px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .search-form {
                flex-direction: column;
                gap: 10px;
            }
            
            .search-input {
                max-width: 100%;
            }
            
            .assets-section {
                height: calc(100vh - 500px); /* Adjust for mobile */
            }
            
            /* Reset column widths for mobile */
            .assets-table th:nth-child(4),
            .assets-table td:nth-child(4),
            .assets-table th:nth-child(5),
            .assets-table td:nth-child(5),
            .assets-table th:nth-child(6),
            .assets-table td:nth-child(6),
            .assets-table th:nth-child(7),
            .assets-table td:nth-child(7),
            .assets-table th:nth-child(8),
            .assets-table td:nth-child(8),
            .assets-table th:nth-child(9),
            .assets-table td:nth-child(9),
            .assets-table th:nth-child(10),
            .assets-table td:nth-child(10),
            .assets-table th:nth-child(11),
            .assets-table td:nth-child(11),
            .assets-table th:nth-child(13),
            .assets-table td:nth-child(13) {
                width: auto;
                min-width: auto;
            }
            
            .status-badge {
                padding: 4px 8px;
                font-size: 10px;
                min-width: 60px;
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
                <a href="dashboard.php" class="nav-item">
                    <span class="nav-icon"></span>
                    Dashboard
                </a>
                <a href="AdminDM.php" class="nav-item active">
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
            <h1 class="dashboard-header">Device Management</h1>
            
            <!-- Action Buttons Section -->
            <div class="action-buttons-section">
                <div class="action-buttons-header">Quick Actions</div>
                <div class="action-buttons">
                    <a href="add-asset.php" class="action-btn">
                        ➕ Add Asset
                    </a>
                    <a href="view-asset.php" class="action-btn">
                        👁️ View Assets
                    </a>
                    <a href="delete-asset.php" class="action-btn">
                        🗑️ Delete Asset
                    </a>
                </div>
            </div>

            <!-- Search Section -->
            <div class="search-section">
                <form class="search-form" method="GET">
                    <input type="text" name="search" class="search-input" placeholder="Search assets by any field (ID, name, category, serial number, location, status, etc.)..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    <button type="submit" class="search-button">🔍 Search</button>
                    <?php if (!empty($_GET['search'])): ?>
                        <a href="AdminDM.php" class="clear-search-btn">✖️ Clear</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Assets Table -->
            <div class="assets-section">
                <div class="assets-header">
                    <?php if (!empty($search)): ?>
                        Search Results for "<?= htmlspecialchars($search) ?>"
                    <?php else: ?>
                        Assets Overview
                    <?php endif; ?>
                    <span style="font-size: 14px; font-weight: normal; color: #6c757d;">
                        <?= count($devices) ?> asset(s) 
                        <?= !empty($search) ? 'found' : 'total' ?>
                    </span>
                </div>
                <div class="assets-table-container">
                    <table class="assets-table">
                        <thead>
                            <tr>
                                <th>Asset ID</th>
                                <th>Asset Name</th>
                                <th>Category</th>
                                <th>Brand/Model</th>
                                <th>Serial Number</th>
                                <th>Location</th>
                                <th>Assigned To</th>
                                <th>Purchase Date</th>
                                <th>Warranty Expiry</th>
                                <th>Value</th>
                                <th>Status</th>
                                <th>Supplier</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($devices as $device): ?>
                            <tr>
                                <td><?= htmlspecialchars($device['Asset_ID']) ?></td>
                                <td><strong><?= htmlspecialchars($device['Asset_Name']) ?></strong></td>
                                <td><?= htmlspecialchars($device['Category']) ?></td>
                                <td><?= htmlspecialchars($device['Brand_Model']) ?></td>
                                <td><code><?= htmlspecialchars($device['Serial_Number']) ?></code></td>
                                <td><?= htmlspecialchars($device['Location']) ?></td>
                                <td><?= htmlspecialchars($device['Assigned_To'] ?: 'Unassigned') ?></td>
                                <td style="text-align: center;"><?= htmlspecialchars($device['Purchase_Date']) ?></td>
                                <td style="text-align: center;"><?= htmlspecialchars($device['Warranty_Expiry']) ?></td>
                                <td style="text-align: right;">$<?= number_format($device['Asset_Value'], 2) ?></td>
                                <td>
                                    <?php
                                    // Handle status formatting for CSS classes
                                    $status = $device['Status'];
                                    $statusClass = 'status-' . strtolower(str_replace(' ', '', $status));
                                    ?>
                                    <span class="status-badge <?= $statusClass ?>">
                                        <?= htmlspecialchars($status) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($device['Supplier']) ?></td>
                                <td style="text-align: center;">
                                    <a href="update-asset.php?id=<?= urlencode($device['Asset_ID']) ?>" class="update-btn">
                                        ✏️ Update
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
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
    </script>
</body>
</html>