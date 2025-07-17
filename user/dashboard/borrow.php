<?php
session_start();
if (!isset($_SESSION['userid']) || !isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
    header("Location: ../login.php");
    exit();
}

require_once '../../database/auto_database_check.php';
include '../db_connect.php';

// Check for success message from session
$success_message = '';
$error_message = '';
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// Get available assets for borrowing
$assets_sql = "SELECT Asset_ID, Asset_Name, Serial_Number, Category, Brand_Model, Status FROM assets WHERE Status = 'Active'";
$assets_result = $conn->query($assets_sql);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $asset_id = $_POST['asset_id'];
    $borrow_start_date = $_POST['borrow_start_date'];
    $borrow_end_date = $_POST['borrow_end_date'];
    $user_id = $_SESSION['userid'];

    try {
        // Get asset details
        $asset_sql = "SELECT Asset_Name FROM assets WHERE Asset_ID = ?";
        $stmt = $conn->prepare($asset_sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("s", $asset_id);
        $stmt->execute();
        $asset_result = $stmt->get_result();
        $asset = $asset_result->fetch_assoc();
        
        if (!$asset) {
            throw new Exception("Asset not found");
        }

        // Insert borrow request
        $insert_sql = "INSERT INTO borrow_requests (user_id, asset_id, asset_name, borrow_date, return_date) VALUES (?, ?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        if (!$insert_stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $insert_stmt->bind_param("sssss", $user_id, $asset_id, $asset['Asset_Name'], $borrow_start_date, $borrow_end_date);

        if ($insert_stmt->execute()) {
            $_SESSION['success_message'] = "Your borrow request has been submitted successfully! It is now pending admin approval.";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            throw new Exception("Execute failed: " . $insert_stmt->error);
        }

        $stmt->close();
        $insert_stmt->close();
        
    } catch (Exception $e) {
        $error_message = "Error submitting request: " . $e->getMessage();
        if (isset($stmt)) $stmt->close();
        if (isset($insert_stmt)) $insert_stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Borrow Request</title>
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
            top: 0;
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

        /* Main Content */
        .main-content {
            margin-left: 240px;
            flex: 1;
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

        .section-icon.asset-select {
            background-color: #e3f2fd;
            color: #1976d2;
        }

        .section-icon.schedule {
            background-color: #fff3e0;
            color: #f57c00;
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
            border-color: #28a745;
            box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.1);
        }

        .form-input:hover {
            border-color: #dee2e6;
        }

        .form-select {
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.2s ease;
            background-color: #fff;
            cursor: pointer;
        }

        .form-select:focus {
            outline: none;
            border-color: #28a745;
            box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.1);
        }

        /* Asset Selection Table */
        .asset-selection {
            margin-top: 15px;
        }

        .asset-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            overflow: hidden;
        }

        .asset-table th {
            background-color: #f8f9fa;
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
            color: #495057;
            border-bottom: 1px solid #dee2e6;
            font-size: 14px;
        }

        .asset-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #dee2e6;
            color: #495057;
            font-size: 14px;
        }

        .asset-table tr:hover {
            background-color: #f8f9fa;
        }

        .asset-radio {
            margin: 0;
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

        .success {
            background-color: #d1e7dd;
            color: #0f5132;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #badbcc;
        }

        /* Info Box */
        .info-box {
            background-color: #e3f2fd;
            border: 1px solid #90caf9;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .info-box h4 {
            color: #1976d2;
            margin-bottom: 8px;
            font-size: 16px;
        }

        .info-box p {
            color: #1565c0;
            font-size: 14px;
            line-height: 1.4;
            margin: 0;
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
                <a href="../View.php" class="nav-item">
                    <span class="nav-icon"></span>
                    View
                </a>
                <a href="../borrow-status.php" class="nav-item">
                    <span class="nav-icon"></span>
                    Borrow Status
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
            <?php if (!empty($error_message)): ?>
                <div class="error">
                    <strong>Error:</strong> <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div class="success">
                    <strong>Success:</strong> <?php echo htmlspecialchars($success_message); ?>
                    <br><br>
                    <a href="../borrow-status.php" style="color: #0f5132; text-decoration: underline;">View your request status here</a>
                </div>
            <?php endif; ?>

            <div class="header-section">
                <h1 class="page-title">Submit Borrow Request</h1>
                <div class="breadcrumb">
                    <a href="dashboard.php">Dashboard</a> / 
                    <a href="../View.php">View</a> / 
                    Submit Borrow Request
                </div>
                <a href="../View.php" class="back-button">Back to Asset List</a>
            </div>

            <div class="info-box">
                <h4>📋 Borrow Request Information</h4>
                <p>Select an asset from the available list below and specify your borrowing period. Your request will be sent to the admin for approval.</p>
            </div>

            <div class="form-container">
                <div class="form-header">
                    <h2>New Borrow Request</h2>
                    <p>Choose an asset and specify the borrowing period</p>
                </div>

                <div class="form-body">
                    <form method="POST">
                        <!-- Asset Selection Section -->
                        <div class="form-section">
                            <div class="section-header">
                                <div class="section-icon asset-select">📦</div>
                                <h3 class="section-title">Asset Selection</h3>
                            </div>
                            
                            <div class="asset-selection">
                                <div class="asset-table">
                                    <table class="asset-table">
                                        <thead>
                                            <tr>
                                                <th width="50">Select</th>
                                                <th>Asset Name</th>
                                                <th>Category</th>
                                                <th>Brand/Model</th>
                                                <th>Serial Number</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            if ($assets_result->num_rows > 0) {
                                                while ($asset = $assets_result->fetch_assoc()) {
                                                    echo "<tr>";
                                                    echo "<td><input type='radio' name='asset_id' value='" . $asset['Asset_ID'] . "' class='asset-radio' required></td>";
                                                    echo "<td>" . htmlspecialchars($asset['Asset_Name']) . "</td>";
                                                    echo "<td>" . htmlspecialchars($asset['Category']) . "</td>";
                                                    echo "<td>" . htmlspecialchars($asset['Brand_Model']) . "</td>";
                                                    echo "<td>" . htmlspecialchars($asset['Serial_Number']) . "</td>";
                                                    echo "</tr>";
                                                }
                                            } else {
                                                echo "<tr><td colspan='5' style='text-align: center; color: #6c757d; padding: 20px;'>No available assets found</td></tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Schedule Information Section -->
                        <div class="form-section">
                            <div class="section-header">
                                <div class="section-icon schedule">📅</div>
                                <h3 class="section-title">Borrowing Period</h3>
                            </div>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label required">Start Date</label>
                                    <input type="date" name="borrow_start_date" class="form-input" required min="<?php echo date('Y-m-d'); ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label required">End Date</label>
                                    <input type="date" name="borrow_end_date" class="form-input" required min="<?php echo date('Y-m-d'); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="button" class="btn btn-secondary" onclick="window.location.href='../View.php'">
                                ↺ Cancel
                            </button>
                            <button type="submit" class="btn btn-primary">
                                ✓ Submit Request
                            </button>
                        </div>
                    </form>
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
            item.addEventListener('click', function () {
                document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Date validation
        document.querySelector('input[name="borrow_start_date"]').addEventListener('change', function() {
            const startDate = this.value;
            const endDateInput = document.querySelector('input[name="borrow_end_date"]');
            endDateInput.min = startDate;
            
            if (endDateInput.value && endDateInput.value < startDate) {
                endDateInput.value = '';
            }
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const assetSelected = document.querySelector('input[name="asset_id"]:checked');
            if (!assetSelected) {
                e.preventDefault();
                alert('Please select an asset to borrow.');
                return;
            }

            const startDate = document.querySelector('input[name="borrow_start_date"]').value;
            const endDate = document.querySelector('input[name="borrow_end_date"]').value;
            
            if (new Date(endDate) <= new Date(startDate)) {
                e.preventDefault();
                alert('End date must be after start date.');
                return;
            }
        });
    </script>
</body>

</html>