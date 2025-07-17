<?php
session_start();
if (!isset($_SESSION['adminID']) || !isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

require_once '../database/auto_database_check.php';
include 'db_connect.php';

// Handle approve/decline actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = $_POST['request_id'];
    $action = $_POST['action'];
    $admin_notes = $_POST['admin_notes'] ?? '';
    
    if ($action === 'approve') {
        $status = 'approved';
    } elseif ($action === 'decline') {
        $status = 'declined';
    }
    
    $update_sql = "UPDATE borrow_requests SET status = ?, admin_notes = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ssi", $status, $admin_notes, $request_id);
    $stmt->execute();
    $stmt->close();
    
    header("Location: borrow-requests.php");
    exit();
}

// Get filter parameter
$status_filter = $_GET['status'] ?? 'all';

// Query to get borrow requests
$sql = "SELECT br.*, u.userID FROM borrow_requests br 
        LEFT JOIN users u ON br.user_id = u.userID";

if ($status_filter !== 'all') {
    $sql .= " WHERE br.status = ?";
}

$sql .= " ORDER BY br.created_at DESC";

if ($status_filter !== 'all') {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $status_filter);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}

// Calculate stats
$stats_sql = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
    SUM(CASE WHEN status = 'declined' THEN 1 ELSE 0 END) as declined
    FROM borrow_requests";
$stats_result = $conn->query($stats_sql);
$stats = $stats_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrow Requests Management</title>
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
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .stat-card {
            background: white;
            border: 2px solid #333;
            border-radius: 8px;
            padding: 25px 20px;
            text-align: center;
        }
        .stat-number {
            font-size: 48px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        .stat-label {
            font-size: 14px;
            color: #333;
            line-height: 1.3;
        }
        .filter-section {
            background: white;
            border: 2px solid #333;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .filter-buttons {
            display: flex;
            gap: 10px;
        }
        .filter-btn {
            padding: 10px 20px;
            border: 1px solid #dee2e6;
            background: white;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            color: #333;
            font-size: 14px;
        }
        .filter-btn:hover {
            background-color: #f8f9fa;
        }
        .filter-btn.active {
            background-color: #4a90e2;
            color: white;
            border-color: #4a90e2;
        }
        .requests-section {
            background: white;
            border: 2px solid #333;
            border-radius: 8px;
            overflow: hidden;
        }
        .requests-header {
            background: white;
            padding: 20px 25px;
            border-bottom: 2px solid #333;
            font-size: 20px;
            font-weight: bold;
            color: #333;
        }
        .requests-table {
            width: 100%;
            border-collapse: collapse;
        }
        .requests-table th {
            background-color: #f8f9fa;
            padding: 15px 25px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 1px solid #dee2e6;
            font-size: 16px;
        }
        .requests-table td {
            padding: 15px 25px;
            border-bottom: 1px solid #dee2e6;
            color: #333;
        }
        .requests-table tr {
            background-color: #e3f2fd;
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
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        .status-approved {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status-declined {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        .action-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .approve-btn {
            background-color: #28a745;
            color: white;
        }
        .approve-btn:hover {
            background-color: #218838;
        }
        .decline-btn {
            background-color: #dc3545;
            color: white;
        }
        .decline-btn:hover {
            background-color: #c82333;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 30px;
            border-radius: 8px;
            width: 500px;
            max-width: 90%;
        }
        .modal-header {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #333;
        }
        .modal-body {
            margin-bottom: 20px;
        }
        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            font-size: 14px;
        }
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
                <a href="AdminDM.php" class="nav-item">
                    <span class="nav-icon"></span>
                    Device Management
                </a>
                <a href="borrow-requests.php" class="nav-item active">
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
            <h1 class="dashboard-header">Borrow Requests Management</h1>
            
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total']; ?></div>
                    <div class="stat-label">Total<br>Requests</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['pending']; ?></div>
                    <div class="stat-label">Pending<br>Requests</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['approved']; ?></div>
                    <div class="stat-label">Approved<br>Requests</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['declined']; ?></div>
                    <div class="stat-label">Declined<br>Requests</div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="filter-section">
                <div class="filter-buttons">
                    <a href="borrow-requests.php?status=all" class="filter-btn <?php echo $status_filter === 'all' ? 'active' : ''; ?>">All Requests</a>
                    <a href="borrow-requests.php?status=pending" class="filter-btn <?php echo $status_filter === 'pending' ? 'active' : ''; ?>">Pending</a>
                    <a href="borrow-requests.php?status=approved" class="filter-btn <?php echo $status_filter === 'approved' ? 'active' : ''; ?>">Approved</a>
                    <a href="borrow-requests.php?status=declined" class="filter-btn <?php echo $status_filter === 'declined' ? 'active' : ''; ?>">Declined</a>
                </div>
            </div>

            <!-- Requests Table -->
            <div class="requests-section">
                <div class="requests-header">
                    Borrow Requests
                </div>
                <table class="requests-table">
                    <thead>
                        <tr>
                            <th>Request ID</th>
                            <th>User</th>
                            <th>Asset Name</th>
                            <th>Asset ID</th>
                            <th>Borrow Period</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $statusClass = 'status-' . strtolower($row['status']);
                                echo "<tr>";
                                echo "<td>#" . $row['id'] . "</td>";
                                echo "<td>" . htmlspecialchars($row['user_id']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['asset_name']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['asset_id']) . "</td>";
                                // Display borrow period (borrow_date to return_date)
                                $borrowStart = date('M j, Y', strtotime($row['borrow_date']));
                                $borrowEnd = date('M j, Y', strtotime($row['return_date']));
                                echo "<td>" . $borrowStart . " - " . $borrowEnd . "</td>";
                                echo "<td><span class='status-badge $statusClass'>" . $row['status'] . "</span></td>";
                                echo "<td>";
                                if ($row['status'] === 'pending') {
                                    echo "<div class='action-buttons'>";
                                    echo "<button class='action-btn approve-btn' onclick='openModal(" . $row['id'] . ", \"approve\")'>Approve</button>";
                                    echo "<button class='action-btn decline-btn' onclick='openModal(" . $row['id'] . ", \"decline\")'>Decline</button>";
                                    echo "</div>";
                                } else {
                                    echo "<span style='color: #6c757d; font-size: 12px;'>Processed</span>";
                                }
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7' style='text-align: center; padding: 40px; color: #6c757d;'>No requests found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal for Approve/Decline -->
    <div id="actionModal" class="modal">
        <div class="modal-content">
            <div class="modal-header" id="modalTitle">Action Required</div>
            <div class="modal-body">
                <form id="actionForm" method="POST">
                    <input type="hidden" id="requestId" name="request_id">
                    <input type="hidden" id="actionType" name="action">
                    
                    <div class="form-group">
                        <label class="form-label">Admin Notes (Optional)</label>
                        <textarea name="admin_notes" class="form-control" rows="3" placeholder="Add any notes or comments..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                <button type="submit" form="actionForm" class="btn btn-primary" id="confirmBtn">Confirm</button>
            </div>
        </div>
    </div>

    <script>
        function logout() {
            if (confirm('Are you sure you want to log out?')) {
                window.location.href = 'logout.php';
            }
        }

        function openModal(requestId, action) {
            const modal = document.getElementById('actionModal');
            const title = document.getElementById('modalTitle');
            const confirmBtn = document.getElementById('confirmBtn');
            
            document.getElementById('requestId').value = requestId;
            document.getElementById('actionType').value = action;
            
            if (action === 'approve') {
                title.textContent = 'Approve Request';
                confirmBtn.textContent = 'Approve';
                confirmBtn.className = 'btn btn-primary';
                confirmBtn.style.backgroundColor = '#28a745';
            } else {
                title.textContent = 'Decline Request';
                confirmBtn.textContent = 'Decline';
                confirmBtn.className = 'btn btn-primary';
                confirmBtn.style.backgroundColor = '#dc3545';
            }
            
            modal.style.display = 'block';
        }

        function closeModal() {
            document.getElementById('actionModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('actionModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>
<?php
$conn->close();
?> 