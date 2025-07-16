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
 
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $asset_id = trim($_POST['asset_id']);
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

    $stmt = $conn->prepare("INSERT INTO assets (Asset_ID, Asset_Name, Category, Serial_Number, Brand_Model, Location, Assigned_To, Purchase_Date, Warranty_Expiry, Asset_Value, Status, Supplier) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssssdss", $asset_id, $asset_name, $category, $serial_number, $brand_model, $location, $assigned_to, $purchase_date, $Warranty_Expiry, $asset_value, $status, $supplier);
    if ($stmt->execute()) {
        header("Location: AdminDM.php?added=1");
        exit();
    } else {
        $error = "Add failed: " . $stmt->error;
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Asset</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #f5f7fa;
            color: #333;
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
            white-space: nowrap;
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

        /* Main Content */
        .content {
            margin-left: 240px;
            padding: 30px;
            min-height: 100vh;
        }

        .header-section {
            margin-bottom: 30px;
        }

        .page-title {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        .breadcrumb {
            color: #6c757d;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .breadcrumb a {
            color: #4a90e2;
            text-decoration: none;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            padding: 10px 20px;
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
            transition: background-color 0.2s ease;
        }

        .back-button:hover {
            background-color: #5a6268;
        }

        .back-button::before {
            content: "←";
            margin-right: 8px;
            font-weight: bold;
        }

        /* Form Container */
        .form-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05), 0 10px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 1000px;
        }

        .form-header {
            background: linear-gradient(135deg, #6b7c93, #5a6c7f);
            color: white;
            padding: 25px 30px;
            border-bottom: 1px solid #e9ecef;
        }

        .form-header h2 {
            font-size: 24px;
            font-weight: 600;
            margin: 0;
        }

        .form-header p {
            margin: 8px 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .form-body {
            padding: 30px;
        }

        /* Form Sections */
        .form-section {
            margin-bottom: 35px;
        }

        .form-section:last-child {
            margin-bottom: 0;
        }

        .section-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f1f3f4;
        }

        .section-icon {
            width: 24px;
            height: 24px;
            margin-right: 12px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: bold;
        }

        .section-icon.basic {
            background-color: #e3f2fd;
            color: #1976d2;
        }

        .section-icon.technical {
            background-color: #f3e5f5;
            color: #7b1fa2;
        }

        .section-icon.financial {
            background-color: #e8f5e8;
            color: #388e3c;
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full-width {
            grid-column: span 2;
        }

        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 6px;
            font-size: 14px;
        }

        .form-label.required::after {
            content: " *";
            color: #dc3545;
        }

        .form-input {
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.2s ease;
            background-color: #fff;
        }

        .form-input:focus {
            outline: none;
            border-color: #4a90e2;
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
        }

        .form-input:hover {
            border-color: #dee2e6;
        }

        /* Form Actions */
        .form-actions {
            margin-top: 40px;
            padding-top: 25px;
            border-top: 1px solid #e9ecef;
            display: flex;
            justify-content: flex-end;
            gap: 15px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        .btn-primary {
            background-color: #28a745;
            color: white;
        }

        .btn-primary:hover {
            background-color: #218838;
            transform: translateY(-1px);
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
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

            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-group.full-width {
                grid-column: span 1;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
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
        <?php if (isset($error)): ?>
            <div class="error">
                <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="header-section">
            <h1 class="page-title">Add New Asset</h1>
            <div class="breadcrumb">
                <a href="dashboard.php">Dashboard</a> / 
                <a href="AdminDM.php">Device Management</a> / 
                Add Asset
            </div>
            <a href="AdminDM.php" class="back-button">Back to Asset List</a>
        </div>

        <div class="form-container">
            <div class="form-header">
                <h2>Asset Information</h2>
                <p>Fill in the details below to add a new asset to the system</p>
            </div>

            <div class="form-body">
                <form method="post">
                    <!-- Basic Information Section -->
                    <div class="form-section">
                        <div class="section-header">
                            <div class="section-icon basic">ℹ️</div>
                            <h3 class="section-title">Basic Information</h3>
                        </div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label required">Asset ID</label>
                                <input type="text" name="asset_id" class="form-input" required placeholder="e.g., A001">
                            </div>
                            <div class="form-group">
                                <label class="form-label required">Asset Name</label>
                                <input type="text" name="asset_name" class="form-input" required placeholder="e.g., Dell Laptop">
                            </div>
                            <div class="form-group">
                                <label class="form-label required">Category</label>
                                <input type="text" name="category" class="form-input" required placeholder="e.g., IT Equipment">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Brand Model</label>
                                <input type="text" name="brand_model" class="form-input" placeholder="e.g., Dell Inspiron 15">
                            </div>
                        </div>
                    </div>

                    <!-- Technical Details Section -->
                    <div class="form-section">
                        <div class="section-header">
                            <div class="section-icon technical">🔧</div>
                            <h3 class="section-title">Technical Details</h3>
                        </div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label required">Serial Number</label>
                                <input type="text" name="serial_number" class="form-input" required placeholder="e.g., SN-001">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Status</label>
                                <input type="text" name="status" class="form-input" placeholder="e.g., Active">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Location</label>
                                <input type="text" name="location" class="form-input" placeholder="e.g., IT Department">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Assigned To</label>
                                <input type="text" name="assigned_to" class="form-input" placeholder="e.g., John Doe">
                            </div>
                        </div>
                    </div>

                    <!-- Financial & Date Information Section -->
                    <div class="form-section">
                        <div class="section-header">
                            <div class="section-icon financial">💰</div>
                            <h3 class="section-title">Financial & Date Information</h3>
                        </div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Purchase Date</label>
                                <input type="date" name="purchase_date" class="form-input">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Warranty Expiry</label>
                                <input type="date" name="Warranty_Expiry" class="form-input">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Asset Value</label>
                                <input type="number" step="0.01" name="asset_value" class="form-input" placeholder="e.g., 1200.00">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Supplier</label>
                                <input type="text" name="supplier" class="form-input" placeholder="e.g., Dell Malaysia">
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="reset" class="btn btn-secondary">
                            ↺ Reset Form
                        </button>
                        <button type="submit" class="btn btn-primary">
                            ✓ Add Asset
                        </button>
                    </div>
                </form>
            </div>
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