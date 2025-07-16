<?php
session_start();
if (!isset($_SESSION['userid']) || !isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
    header("Location: ../login.php");
    exit();
}

include '../db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $asset_name = $_POST['Asset_Name'];
    $serial_number = $_POST['Serial_Number'];
    $purchase_date = $_POST['Purchase_Date'];
    $warranty_expiry = $_POST['Warranty_Expiry'];
    $status = $_POST['Status'];

    $sql = "INSERT INTO assets (Asset_Name, Serial_Number, Purchase_Date, Warranty_Expiry, Status) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $asset_name, $serial_number, $purchase_date, $warranty_expiry, $status);

    if ($stmt->execute()) {
        header("Location: ../View.php");
    } else {
        $error_message = "Error: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrow Asset</title>
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

        .section-icon.asset-info {
            background-color: #e3f2fd;
            color: #1976d2;
        }

        .section-icon.schedule {
            background-color: #fff3e0;
            color: #f57c00;
        }

        .section-icon.status {
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

        /* Status Badge */
        .status-preview {
            display: inline-block;
            padding: 6px 12px;
            background-color: #d4edda;
            color: #155724;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 8px;
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
            background-color: #cce7ff;
            border: 1px solid #66d1ff;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .info-box h4 {
            color: #0066cc;
            margin-bottom: 8px;
            font-size: 16px;
        }

        .info-box p {
            color: #004080;
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
                <a href="../View.php" class="nav-item active">
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
            <?php if (isset($error_message)): ?>
                <div class="error">
                    <strong>Error:</strong> <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <div class="header-section">
                <h1 class="page-title">Borrow Asset</h1>
                <div class="breadcrumb">
                    <a href="dashboard.php">Dashboard</a> / 
                    <a href="../View.php">View</a> / 
                    Borrow Asset
                </div>
                <a href="../View.php" class="back-button">Back to Asset List</a>
            </div>

            <div class="info-box">
                <h4>📋 Asset Borrowing Information</h4>
                <p>Fill in the asset details below to add a new borrowed item to the system. All required fields must be completed before submission.</p>
            </div>

            <div class="form-container">
                <div class="form-header">
                    <h2>New Asset Registration</h2>
                    <p>Enter the details of the asset you want to borrow</p>
                </div>

                <div class="form-body">
                    <form method="POST">
                        <!-- Asset Information Section -->
                        <div class="form-section">
                            <div class="section-header">
                                <div class="section-icon asset-info">📦</div>
                                <h3 class="section-title">Asset Information</h3>
                            </div>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label required">Asset Name</label>
                                    <input type="text" name="Asset_Name" class="form-input" required placeholder="e.g., Dell Laptop, HP Printer">
                                </div>
                                <div class="form-group">
                                    <label class="form-label required">Serial Number</label>
                                    <input type="text" name="Serial_Number" class="form-input" required placeholder="e.g., SN-001, ABC123">
                                </div>
                            </div>
                        </div>

                        <!-- Schedule Information Section -->
                        <div class="form-section">
                            <div class="section-header">
                                <div class="section-icon schedule">📅</div>
                                <h3 class="section-title">Date Information</h3>
                            </div>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label required">Purchase Date</label>
                                    <input type="date" name="Purchase_Date" class="form-input" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label required">Warranty Expiry Date</label>
                                    <input type="date" name="Warranty_Expiry" class="form-input" required>
                                </div>
                            </div>
                        </div>

                        <!-- Status Information Section -->
                        <div class="form-section">
                            <div class="section-header">
                                <div class="section-icon status">✅</div>
                                <h3 class="section-title">Status Configuration</h3>
                            </div>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label required">Current Status</label>
                                    <select name="Status" class="form-select" required>
                                        <option value="">Select status...</option>
                                        <option value="Active" selected>Active</option>
                                        <option value="Pending">Pending</option>
                                        <option value="Under Review">Under Review</option>
                                    </select>
                                    <div class="status-preview">ACTIVE</div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Additional Notes</label>
                                    <input type="text" class="form-input" placeholder="Optional notes or comments" readonly>
                                    <small style="color: #6c757d; font-size: 12px; margin-top: 4px;">This field is for future enhancements</small>
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

        // Status preview update
        document.querySelector('select[name="Status"]').addEventListener('change', function() {
            const preview = document.querySelector('.status-preview');
            preview.textContent = this.value.toUpperCase();
            
            // Update preview colors based on status
            preview.className = 'status-preview';
            if (this.value === 'Active') {
                preview.style.backgroundColor = '#d4edda';
                preview.style.color = '#155724';
            } else if (this.value === 'Pending') {
                preview.style.backgroundColor = '#fff3cd';
                preview.style.color = '#856404';
            } else if (this.value === 'Under Review') {
                preview.style.backgroundColor = '#cce7ff';
                preview.style.color = '#0066cc';
            }
        });
    </script>
</body>

</html>