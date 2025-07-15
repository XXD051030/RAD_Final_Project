<?php
session_start();
include 'user/db_connect_safe.php';

// Get database status
$db_status = getDatabaseStatus();
$conn = getSafeConnection();

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if database is ready before processing signup
    if (!$db_status['setup_required'] && $conn) {
        $userID = $_POST['userID'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirmPassword'];

        // Validate password confirmation
        if ($password !== $confirmPassword) {
            $error_message = "Passwords do not match!";
        } elseif (strlen($password) < 6) {
            $error_message = "Password must be at least 6 characters long!";
        } else {
            // Check if userID already exists
            $check_sql = "SELECT userID FROM users WHERE userID = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("s", $userID);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error_message = "UserID already exists! Please choose a different one.";
            } else {
                // Hash password before storing
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new user
                $sql = "INSERT INTO users (userID, password) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ss", $userID, $hashed_password);

                if ($stmt->execute()) {
                    // Redirect to login page
                    header("Location: user/login.php");
                    exit();
                } else {
                    $error_message = "Error creating account. Please try again.";
                }
                $stmt->close();
            }
            $check_stmt->close();
        }
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
    <title>Sign Up - user</title>
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
        .signup-container {
            background-color: transparent;
            padding: 40px;
            text-align: center;
            width: 100%;
            max-width: 400px;
        }
        .signup-title {
            font-size: 48px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
            letter-spacing: -1px;
        }
        .signup-subtitle {
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
        .signup-btn {
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
        .signup-btn:hover {
            background-color: #e9e9e9;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .signup-btn:active {
            transform: translateY(0);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }
        .signup-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        .login-link {
            font-size: 16px;
            color: #333;
            margin-top: 20px;
        }
        .login-link a {
            color: #007bff;
            text-decoration: none;
            font-weight: normal;
        }
        .login-link a:hover {
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
            .signup-container {
                padding: 20px;
            }
            .signup-title {
                font-size: 40px;
            }
            .signup-subtitle {
                font-size: 30px;
            }
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <h1 class="signup-title">Sign Up</h1>
        <h2 class="signup-subtitle">user</h2>
        
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="signupForm">
            <div class="form-group">
                <label for="userID">UserID:</label>
                <input type="text" id="userID" name="userID" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <div class="password-input-container">
                    <input type="password" id="password" name="password" required>
                    <button type="button" class="password-toggle" onclick="togglePassword('password', this)">Show</button>
                </div>
            </div>
            <div class="form-group">
                <label for="confirmPassword">Confirm Password:</label>
                <div class="password-input-container">
                    <input type="password" id="confirmPassword" name="confirmPassword" required>
                    <button type="button" class="password-toggle" onclick="togglePassword('confirmPassword', this)">Show</button>
                </div>
            </div>
            <?php if (!empty($error_message)): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>
            <button type="submit" class="signup-btn" id="signupBtn">Sign Up</button>
        </form>
        
        <div class="login-link">
            Already have an Account? <a href="user/login.php">Login</a>
        </div>
    </div>

    <script>
        // Enhanced user experience functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-focus on first input field
            const useridInput = document.getElementById('userID');
            if (useridInput) {
                useridInput.focus();
            }

            // Enhanced form submission
            const signupForm = document.getElementById('signupForm');
            const signupBtn = document.getElementById('signupBtn');
            
            if (signupForm) {
                signupForm.addEventListener('submit', function(e) {
                    // Client-side validation
                    const password = document.getElementById('password').value;
                    const confirmPassword = document.getElementById('confirmPassword').value;
                    
                    if (password !== confirmPassword) {
                        e.preventDefault();
                        showError('Passwords do not match!');
                        return false;
                    }
                    
                    if (password.length < 6) {
                        e.preventDefault();
                        showError('Password must be at least 6 characters long!');
                        return false;
                    }
                    
                    // Prevent double submission
                    if (signupBtn.disabled) {
                        e.preventDefault();
                        return false;
                    }
                    
                    // Add loading state
                    signupBtn.disabled = true;
                    signupBtn.textContent = 'Creating Account...';
                    
                    // Re-enable button after 3 seconds as fallback
                    setTimeout(function() {
                        signupBtn.disabled = false;
                        signupBtn.textContent = 'Sign Up';
                    }, 3000);
                });
            }

            // Enhanced keyboard navigation
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && e.target.tagName === 'INPUT') {
                    const inputs = Array.from(document.querySelectorAll('input[type="text"], input[type="email"], input[type="password"]'));
                    const currentIndex = inputs.indexOf(e.target);
                    
                    if (currentIndex < inputs.length - 1) {
                        e.preventDefault();
                        inputs[currentIndex + 1].focus();
                    }
                }
            });

            // Silent automatic database setup functionality
            <?php if ($db_status['setup_required'] && $db_status['server_connected']): ?>
                
                // Start silent setup in background
                startSilentDatabaseSetup();
                
            <?php endif; ?>
        });

        // Password visibility toggle
        function togglePassword(inputId, toggleBtn) {
            const passwordInput = document.getElementById(inputId);
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleBtn.textContent = 'Hide';
            } else {
                passwordInput.type = 'password';
                toggleBtn.textContent = 'Show';
            }
        }

        // Show error message
        function showError(message) {
            // Remove existing error messages
            const existingError = document.querySelector('.error-message');
            if (existingError) {
                existingError.remove();
            }
            
            // Create new error message
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.textContent = message;
            
            // Insert before submit button
            const signupBtn = document.getElementById('signupBtn');
            signupBtn.parentNode.insertBefore(errorDiv, signupBtn);
        }

        function startSilentDatabaseSetup() {
            // Make AJAX request to auto_setup.php silently
            fetch('user/auto_setup.php', {
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
                // If setup failed, just continue - user can still try to signup
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