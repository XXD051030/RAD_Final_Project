<?php
session_start();
include 'db_connect.php';

$error_message = '';

// Check connection in db_connect.php
if (!$conn) {
    $error_message = "Database connection failed: " . mysqli_connect_error();
}

// Update password to hashed value if not already done (one-time fix)
if ($_SERVER["REQUEST_METHOD"] != "POST" && empty($error_message)) {
    $adminID = "admin123";
    $plain_password = "123456";
    $hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);
    $check_sql = "SELECT adminID FROM admin WHERE adminID = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $adminID);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    if ($check_result->num_rows == 0) {
        $insert_sql = "INSERT INTO admin (adminID, password) VALUES (?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("ss", $adminID, $hashed_password);
        $insert_stmt->execute();
        $insert_stmt->close();
    } else {
        $update_sql = "UPDATE admin SET password = ? WHERE adminID = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ss", $hashed_password, $adminID);
        $update_stmt->execute();
        $update_stmt->close();
    }
    $check_stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $adminID = $_POST['adminID'];
    $password = $_POST['password'];

    // Prepare statement to fetch admin record
    $sql = "SELECT adminID, password FROM admin WHERE adminID = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        $error_message = "Prepare failed: " . $conn->error;
    } else {
        $stmt->bind_param("s", $adminID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $hashed_password = $row['password'];
            // Debugging: Log the retrieved hash and input password
            error_log("Time: " . date('Y-m-d H:i:s') . " AdminID: $adminID, Hashed Password: $hashed_password, Input Password: $password");

            // Check if password is correct
            if (password_verify($password, $hashed_password)) {
                $_SESSION['adminID'] = $adminID;
                $_SESSION['logged_in'] = true;
                header("Location: dashboard.php");
                exit();
            } else {
                $error_message = "Invalid AdminID or Password. (Verification failed: Hash: $hashed_password)";
            }
        } else {
            $error_message = "Invalid AdminID or Password. (No record found for AdminID: $adminID)";
        }

        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Admin</title>
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
        .login-container {
            background-color: transparent;
            padding: 40px;
            text-align: center;
            width: 100%;
            max-width: 400px;
        }
        .login-title {
            font-size: 48px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
            letter-spacing: -1px;
        }
        .login-subtitle {
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
        .login-btn {
            width: 60%;
            padding: 15px;
            font-size: 18px;
            font-weight: normal;
            color: #333;
            background-color: #f8f8f8;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 30px 0 20px 0;
            transition: background-color 0.2s ease;
        }
        .login-btn:hover {
            background-color: #e9e9e9;
        }
        .forgot-password {
            font-size: 16px;
            color: #333;
            margin-bottom: 40px;
        }
        .forgot-password a {
            color: #333;
            text-decoration: none;
        }
        .forgot-password a:hover {
            text-decoration: underline;
        }
        .signup-link {
            font-size: 16px;
            color: #333;
            margin-top: 20px;
        }
        .signup-link a {
            color: #007bff;
            text-decoration: none;
            font-weight: normal;
        }
        .signup-link a:hover {
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
            .login-container {
                padding: 20px;
            }
            .login-title {
                font-size: 40px;
            }
            .login-subtitle {
                font-size: 30px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1 class="login-title">Login</h1>
        <h2 class="login-subtitle">Admin</h2>
        
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="adminID">AdminID:</label>
                <input type="text" id="adminID" name="adminID" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <?php if (!empty($error_message)): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>
            <button type="submit" class="login-btn" name="login">Login</button>
        </form>
        
        <div class="forgot-password">
            <a href="forgot-password.php">Forgot Password?</a>
        </div>
        
        <div class="signup-link">
            Don't have an Account? <a href="register.php">SignUp</a>
        </div>
    </div>
</body>
</html>