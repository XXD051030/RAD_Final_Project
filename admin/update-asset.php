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

// Get asset ID (GET for display, POST for update)
$id = $_GET['id'] ?? $_POST['id'] ?? null;
if (!$id) {
    header('Location: AdminDM.php?error=Invalid asset ID');
    exit();
}

// Fetch asset from DB (GET request only)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $conn->prepare("SELECT * FROM assets WHERE asset_id = ?");
    $stmt->bind_param("s", $id); // Change to string
    $stmt->execute();
    $result = $stmt->get_result();
    $asset = $result->fetch_assoc();
    $stmt->close();

    if (!$asset) {
        header('Location: AdminDM.php?error=Asset not found');
        exit();
    }
}

// Handle update submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $asset_name = trim($_POST['asset_name']);
    $category = trim($_POST['category']);
    $serial_number = trim($_POST['serial_number']);
    $brand_model = trim($_POST['brand_model']);
    $location = trim($_POST['location']);
    $assigned_to = trim($_POST['assigned_to']);
    $purchase_date = $_POST['purchase_date'];
    $Warranty_Expiry = $_POST['Warranty_Expiry'];
    $asset_value = $_POST['asset_value'];
    $status = trim($_POST['status']);
    $supplier = trim($_POST['supplier']);

    $stmt = $conn->prepare("UPDATE assets SET asset_name=?, category=?, serial_number=?, brand_model=?, location=?, assigned_to=?, purchase_date=?, Warranty_Expiry=?, asset_value=?, status=?, supplier=? WHERE asset_id=?");
    $stmt->bind_param("sssssssssdss", $asset_name, $category, $serial_number, $brand_model, $location, $assigned_to, $purchase_date, $Warranty_Expiry, $asset_value, $status, $supplier, $id); // Change last param to string
    if ($stmt->execute()) {
        header("Location: AdminDM.php?updated=1");
        exit();
    } else {
        $error = "Update failed: " . $stmt->error;
    }
    $stmt->close();
}
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
        /* Content */
        .content {
            margin-left: 220px;
            padding: 30px;
            background-color: #fff;
            min-height: 100vh;
        }
        h2 {
            color: #2c3e50;
        }
        .actions {
            margin-bottom: 20px;
        }
        .button {
            padding: 8px 16px;
            margin-right: 10px;
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
        /* Form */
        .form-container {
            background-color: rgb(207, 207, 207);
            padding: 30px;
            width: 600px;
            margin: 30px auto;
            border-radius: 8px;
            box-shadow: 0 0 10px #aaa;
        }
        .form-container h2 {
            margin-bottom: 20px;
        }
        .form-row {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        .form-row label {
            width: 180px;
            font-weight: bold;
        }
        .form-row input[type="text"],
        .form-row input[type="date"],
        .form-row input[type="number"] {
            flex: 1;
            padding: 6px 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .form-actions {
            text-align: right;
            margin-top: 30px;
        }
        .form-actions button {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            margin-left: 10px;
            font-size: 14px;
            cursor: pointer;
        }
        .form-actions .cancel {
            background-color: #c9302c;
        }
        .form-actions .add {
            background-color: #4CAF50;
            color: white;
        }
        .error {
            color: red;
            font-weight: bold;
            margin-bottom: 20px;
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
        <?php if (isset($_GET['updated'])): ?>
            <p style="color: green; font-weight: bold;">✅ Asset successfully updated!</p>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <p class="error"><?php echo htmlspecialchars($_GET['error']); ?></p>
        <?php endif; ?>
        <h2>Admin Device Management</h2>
        <div>
            <a href="AdminDM.php"><button class="button">Back to Asset</button></a>
        </div>
        <!-- Form -->
        <div class="form-container">
            <h2>Update Asset</h2>
    <?php if (!empty($error)): ?>
        <p style="color:red; font-weight:bold;">Error: <?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="post">
        <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
        <label>Asset Name: <input type="text" name="asset_name" value="<?= htmlspecialchars($asset['asset_name'] ?? '') ?>" required></label><br>
        <label>Category: <input type="text" name="category" value="<?= htmlspecialchars($asset['category'] ?? '') ?>" required></label><br>
        <label>Serial Number: <input type="text" name="serial_number" value="<?= htmlspecialchars($asset['serial_number'] ?? '') ?>" required></label><br>
        <label>Brand Model: <input type="text" name="brand_model" value="<?= htmlspecialchars($asset['brand_model'] ?? '') ?>"></label><br>
        <label>Location: <input type="text" name="location" value="<?= htmlspecialchars($asset['location'] ?? '') ?>"></label><br>
        <label>Assigned To: <input type="text" name="assigned_to" value="<?= htmlspecialchars($asset['assigned_to'] ?? '') ?>"></label><br>
        <label>Purchase Date: <input type="date" name="purchase_date" value="<?= htmlspecialchars($asset['purchase_date'] ?? '') ?>"></label><br>
        <label>Warranty Expiry: <input type="date" name="Warranty_Expiry" value="<?= htmlspecialchars($asset['Warranty_Expiry'] ?? '') ?>"></label><br>
        <label>Asset Value: <input type="number" step="0.01" name="asset_value" value="<?= htmlspecialchars($asset['asset_value'] ?? '') ?>"></label><br>
        <label>Status: <input type="text" name="status" value="<?= htmlspecialchars($asset['status'] ?? '') ?>"></label><br>
        <label>Supplier: <input type="text" name="supplier" value="<?= htmlspecialchars($asset['supplier'] ?? '') ?>"></label><br>
                <div class="form-actions">
                    <button class="cancel" type="reset">Cancel</button>
                    <button class="add" type="submit">Update</button>
                </div>
            </form>
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