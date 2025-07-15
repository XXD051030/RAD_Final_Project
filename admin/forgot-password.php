<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $adminID = $_POST['adminID']; 
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    // Simple validation to check if passwords match
    if ($newPassword !== $confirmPassword) {
        $error = "New password and confirm password do not match!";
    } else {
        // Check if adminID exists
        $checkSql = "SELECT * FROM admin WHERE adminID = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("s", $adminid); // Use "s" for string
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            // Hash the new password
            $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            // SQL statement to update password
            $updateSql = "UPDATE admin SET password = ? WHERE adminID = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("ss", $hashedNewPassword, $adminid); // Use "ss" for two strings
            if ($updateStmt->execute()) {
                // Redirect to login page
                header("Location: login.php");
                exit();
            } else {
                $error = "Error resetting password: " . $conn->error;
            }
            $updateStmt->close();
        } else {
            $error = "AdminID not found!";
        }
        $checkStmt->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #e5e5e5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        .forgot-container {
            background-color: transparent;
            padding: 40px;
            text-align: center;
            width: 100%;
            max-width: 400px;
        }
        .forgot-title {
            font-size: 48px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
            letter-spacing: -1px;
        }
        .forgot-subtitle {
            font-size: 36px;
            font-weight: normal;
            color: #333;
            margin-bottom: 40px;
            letter-spacing: -0.5px;
        }
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        .form-group label {
            display: block;
            font-size: 18px;
            color: #333;
            margin-bottom: 8px;
            font-weight: normal;
        }
        .form-group input {
            width: 100%;
            padding: 15px 20px;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            background-color: #f8f8f8;
            outline: none;
            transition: background-color 0.2s ease;
        }
        .form-group input:focus {
            background-color: #fff;
            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
        }
        .reset-btn {
            width: 60%;
            padding: 15px;
            font-size: 18px;
            font-weight: normal;
            color: #333;
            background-color: #e8a332;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 30px 0 20px 0;
            transition: background-color 0.2s ease;
        }
        .reset-btn:hover {
            background-color: #e8a332;
        }
        .back-link {
            font-size: 16px;
            color: #333;
            margin-top: 20px;
        }
        .back-link a {
            color: #007bff;
            text-decoration: none;
            font-weight: normal;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
        .error-message {
            color: red;
            font-size: 16px;
            margin-bottom: 20px;
            padding: 10px;
            background-color: #ffe6e6;
            border-radius: 5px;
        }
        @media (max-width: 480px) {
            .forgot-container {
                padding: 20px;
            }
            .forgot-title {
                font-size: 40px;
            }
            .forgot-subtitle {
                font-size: 30px;
            }
        }
    </style>
</head>
<body>
    <div class="forgot-container">
        <h1 class="forgot-title">Forgot Password</h1>
        <h2 class="forgot-subtitle">Admin</h2>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="adminid">AdminID:</label>
                <input type="text" id="adminID" name="adminID" required> <!-- Changed to type="text" -->
            </div>
            <div class="form-group">
                <label for="new_password">Create New Password:</label>
                <input type="password" id="new_password" name="new_password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            <button type="submit" class="reset-btn" name="reset">Reset Password</button>
        </form>
        <div class="back-link">
            Back to <a href="login.php">Login</a>
        </div>
    </div>
</body>
</html>