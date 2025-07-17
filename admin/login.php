<?php
session_start();
include 'db_connect_safe.php';

// Get database status
$db_status = getDatabaseStatus();
$conn = getSafeConnection();

$error_message = '';

// Admin user initialization is now handled by auto_setup.php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if database is ready before processing login
    if (!$db_status['setup_required'] && $conn) {
        $adminID = $_POST['adminID'];
        $password = $_POST['password'];

        // Prepare statement to fetch admin record
        $sql = "SELECT adminID, password FROM admin WHERE adminID = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt !== false) {
            $stmt->bind_param("s", $adminID);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $hashed_password = $row['password'];

                // Check if password is correct
                if (password_verify($password, $hashed_password)) {
                    $_SESSION['adminID'] = $adminID;
                    $_SESSION['admin_logged_in'] = true;
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $error_message = "Invalid AdminID or Password!";
                }
            } else {
                $error_message = "Invalid AdminID or Password!";
            }

            $stmt->close();
        }
    } else {
        $error_message = "Database is not ready. Please wait for automatic setup to complete.";
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
            transition: all 0.3s ease;
            position: relative;
        }
        .form-group input:focus {
            background-color: #fff;
            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
            transform: translateY(-1px);
        }
        .form-group input:hover:not(:focus) {
            background-color: #f0f0f0;
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
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
        }
        .login-btn:hover {
            background-color: #e9e9e9;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .login-btn:active {
            transform: translateY(0);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }
        .login-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
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
            border-left: 4px solid #ff4444;
            animation: fadeInShake 0.5s ease-out;
        }
        
        .password-input-container {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #666;
            font-size: 14px;
            padding: 5px;
            user-select: none;
        }
        
        .password-toggle:hover {
            color: #333;
        }

        @keyframes fadeInShake {
            0% {
                opacity: 0;
                transform: translateX(-10px);
            }
            50% {
                opacity: 1;
                transform: translateX(5px);
            }
            100% {
                opacity: 1;
                transform: translateX(0);
            }
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
        <div style="margin-bottom: 25px;">
            <img src="images/logo/infinecs.png" alt="Logo" style="max-width: 130px; height: auto; display: block; margin: 0 auto;">
        </div>
        <h1 class="login-title">Login</h1>
        <h2 class="login-subtitle">Admin</h2>
        
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="adminID">AdminID:</label>
                <input type="text" id="adminID" name="adminID" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <div class="password-input-container">
                    <input type="password" id="password" name="password" required>
                    <button type="button" class="password-toggle" onclick="togglePassword()">Show</button>
                </div>
            </div>
            <?php if (!empty($error_message) && !$db_status['setup_required']): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>
            <button type="submit" class="login-btn" name="login" id="loginBtn">Login</button>
        </form>
        
        <div class="forgot-password">
            <a href="forgot-password.php">Forgot Password?</a>
        </div>
        
        <div class="signup-link">
            Don't have an Account? <a href="register.php">SignUp</a>
        </div>
    </div>

    <script>
        // Enhanced user experience functionality for admin
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-focus on first input field
            const adminIdInput = document.getElementById('adminID');
            if (adminIdInput) {
                adminIdInput.focus();
            }

            // Enhanced form submission
            const loginForm = document.querySelector('form');
            const loginBtn = document.getElementById('loginBtn');
            
            if (loginForm) {
                loginForm.addEventListener('submit', function(e) {
                    // Prevent double submission
                    if (loginBtn.disabled) {
                        e.preventDefault();
                        return false;
                    }
                    
                    // Add loading state
                    loginBtn.disabled = true;
                    loginBtn.textContent = 'Logging in...';
                    
                    // Re-enable button after 3 seconds as fallback
                    setTimeout(function() {
                        loginBtn.disabled = false;
                        loginBtn.textContent = 'Login';
                    }, 3000);
                });
            }

            // Enhanced keyboard navigation
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && e.target.tagName === 'INPUT') {
                    const inputs = Array.from(document.querySelectorAll('input[type="text"], input[type="password"]'));
                    const currentIndex = inputs.indexOf(e.target);
                    
                    if (currentIndex < inputs.length - 1) {
                        e.preventDefault();
                        inputs[currentIndex + 1].focus();
                    }
                }
            });

            // Silent automatic database setup functionality for admin
            <?php if ($db_status['setup_required'] && $db_status['server_connected']): ?>
                
                // Start silent setup in background
                startSilentDatabaseSetup();
                
            <?php endif; ?>
        });

        // Password visibility toggle
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleBtn = document.querySelector('.password-toggle');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleBtn.textContent = 'Hide';
            } else {
                passwordInput.type = 'password';
                toggleBtn.textContent = 'Show';
            }
        }

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
                // If setup failed, just continue - admin can still try to login
                // or the next page load will attempt setup again
            })
            .catch(error => {
                // Silent failure - admin won't see any error messages
                console.log('Silent setup attempt failed:', error);
            });
        }
    </script>
</body>
</html>