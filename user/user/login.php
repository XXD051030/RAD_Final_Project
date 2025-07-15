<?php
session_start(); // Start session at the beginning
include 'db_connect_safe.php';

// Get database status
$db_status = getDatabaseStatus();
$conn = getSafeConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if database is ready before processing login
    if (!$db_status['setup_required'] && $conn) {
        $userid = $_POST['userid'];
        $password = $_POST['password'];

        // Check if user exists
        $sql = "SELECT userID, password FROM users WHERE userID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $userid);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            // Verify hashed password
            if (password_verify($password, $row['password'])) {
                $_SESSION['userid'] = $userid;
                $_SESSION['logged_in'] = true;
                header("Location: dashboard/dashboard.php");
                exit();
            } else {
                $error_message = "Invalid UserID or password!";
            }
        } else {
            $error_message = "Invalid UserID or password!";
        }

        $stmt->close();
    } else {
        $error_message = "Database is not ready. Please wait for automatic setup to complete.";
    }
}

if ($conn) {
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - user</title>
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
        <h2 class="login-subtitle">user</h2>
        
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="userid">UserID:</label>
                <input type="text" id="userid" name="userid" required>
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
            <a href="forgot_password.php">Forgot Password?</a>
        </div>
        
        <div class="signup-link">
            Don't have an Account? <a href="signup.php">SignUp</a>
        </div>
    </div>

    <script>
        // Silent automatic database setup functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Check if setup is required and silently fix it
            <?php if ($db_status['setup_required'] && $db_status['server_connected']): ?>
                
                // Start silent setup in background
                startSilentDatabaseSetup();
                
            <?php endif; ?>
        });

        function startSilentDatabaseSetup() {
            // Make AJAX request to auto_setup.php silently
            fetch('auto_setup.php', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Setup completed successfully - silently reload page
                    window.location.reload();
                }
                // If setup failed, just continue - user can still try to login
                // or the next page load will attempt setup again
            })
            .catch(error => {
                // Silent failure - user won't see any error messages
                console.log('Silent setup attempt failed:', error);
            });
        }
    </script>
</body>
</html>