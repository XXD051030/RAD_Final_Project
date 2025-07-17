<?php
session_start();
include 'db_connect_safe.php';

// Get database status
$db_status = getDatabaseStatus();
$conn = getSafeConnection();

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if database is ready before processing
    if (!$db_status['setup_required'] && $conn) {
        $userid = $_POST['userid']; 
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];

        // Validate password confirmation
        if ($newPassword !== $confirmPassword) {
            $error_message = "Passwords do not match!";
        } elseif (strlen($newPassword) < 6) {
            $error_message = "Password must be at least 6 characters long!";
        } else {
            // Check if UserID exists
            $checkSql = "SELECT * FROM users WHERE userID = ?";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->bind_param("s", $userid);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();

            if ($checkResult->num_rows > 0) {
                // Hash the new password
                $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                // SQL statement to update password
                $updateSql = "UPDATE users SET password = ? WHERE userID = ?";
                $updateStmt = $conn->prepare($updateSql);
                $updateStmt->bind_param("ss", $hashedNewPassword, $userid);
                if ($updateStmt->execute()) {
                    // Redirect to login page
                    header("Location: login.php");
                    exit();
                } else {
                    $error_message = "Error resetting password. Please try again.";
                }
                $updateStmt->close();
            } else {
                $error_message = "UserID not found!";
            }
            $checkStmt->close();
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
    <title>Forgot Password - User</title>
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
        .reset-btn {
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
        .reset-btn:hover {
            background-color: #e9e9e9;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .reset-btn:active {
            transform: translateY(0);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }
        .reset-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
            background-color: #e9ecef !important;
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

        /* Real-time validation styles */
        .password-hint {
            font-size: 14px;
            margin-top: 5px;
            padding: 8px 12px;
            border-radius: 4px;
            transition: all 0.3s ease;
        }
        
        .password-hint.invalid {
            color: #dc3545;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
        }
        
        .password-hint.valid {
            color: #155724;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
        }
        
        .password-hint.neutral {
            color: #6c757d;
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
        }
        
        /* Hidden state for initial load */
        .password-hint.hidden {
            display: none;
        }

        .validation-icon.hidden {
            display: none;
        }
        
        .input-valid {
            border: 2px solid #28a745 !important;
            background-color: #f8fff9 !important;
        }
        
        .input-invalid {
            border: 2px solid #dc3545 !important;
            background-color: #fff8f8 !important;
        }
        
        .input-neutral {
            border: 1px solid #e9ecef;
        }
        
        .validation-icon {
            position: absolute;
            right: 65px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 16px;
            font-weight: bold;
        }
        
        .validation-icon.valid {
            color: #28a745;
        }
        
        .validation-icon.invalid {
            color: #dc3545;
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
        <div style="margin-bottom: 25px;">
            <img src="../images/logo/infinecs.png" alt="Infinecs Logo" style="max-width: 130px; height: auto; display: block; margin: 0 auto;">
        </div>
        <h1 class="forgot-title">Forgot Password</h1>
        <h2 class="forgot-subtitle">User</h2>
        
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="forgotForm">
            <div class="form-group">
                <label for="userid">UserID:</label>
                <input type="text" id="userid" name="userid" required>
            </div>
            <div class="form-group">
                <label for="new_password">Create New Password:</label>
                <div class="password-input-container">
                    <input type="password" id="new_password" name="new_password" required>
                    <span class="validation-icon hidden" id="passwordIcon"></span>
                    <button type="button" class="password-toggle" onclick="togglePassword('new_password', this)">Show</button>
                </div>
                <div class="password-hint hidden" id="passwordHint">
                    Enter at least 6 characters
                </div>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password:</label>
                <div class="password-input-container">
                    <input type="password" id="confirm_password" name="confirm_password" required>
                    <span class="validation-icon hidden" id="confirmPasswordIcon"></span>
                    <button type="button" class="password-toggle" onclick="togglePassword('confirm_password', this)">Show</button>
                </div>
                <div class="password-hint hidden" id="confirmPasswordHint">
                    Confirm your password
                </div>
            </div>
            <?php if (!empty($error_message)): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>
            <button type="submit" class="reset-btn" id="resetBtn" name="reset">Reset Password</button>
        </form>
        
        <div class="back-link">
            Back to <a href="login.php">Login</a>
        </div>
    </div>

    <script>
        // Enhanced user experience functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-focus on first input field
            const useridInput = document.getElementById('userid');
            if (useridInput) {
                useridInput.focus();
            }

            // Real-time password validation
            const passwordInput = document.getElementById('new_password');
            const confirmPasswordInput = document.getElementById('confirm_password');
            const passwordHint = document.getElementById('passwordHint');
            const confirmPasswordHint = document.getElementById('confirmPasswordHint');
            const passwordIcon = document.getElementById('passwordIcon');
            const confirmPasswordIcon = document.getElementById('confirmPasswordIcon');
            const resetBtn = document.getElementById('resetBtn');

            // Real-time password validation
            passwordInput.addEventListener('input', function() {
                validatePassword();
                validateConfirmPassword();
                updateSubmitButton();
            });

            confirmPasswordInput.addEventListener('input', function() {
                validateConfirmPassword();
                updateSubmitButton();
            });

            function validatePassword() {
                const password = passwordInput.value;
                const length = password.length;
                
                if (length === 0) {
                    // Hide hint and icon when no input
                    passwordHint.className = 'password-hint hidden';
                    passwordInput.className = 'input-neutral';
                    passwordIcon.className = 'validation-icon hidden';
                    passwordIcon.textContent = '';
                } else if (length < 6) {
                    passwordHint.textContent = `Current: ${length}/6 characters (Need ${6 - length} more)`;
                    passwordHint.className = 'password-hint invalid';
                    passwordInput.className = 'input-invalid';
                    passwordIcon.textContent = '✗';
                    passwordIcon.className = 'validation-icon invalid';
                } else {
                    passwordHint.textContent = `✓ Password length is valid (${length} characters)`;
                    passwordHint.className = 'password-hint valid';
                    passwordInput.className = 'input-valid';
                    passwordIcon.textContent = '✓';
                    passwordIcon.className = 'validation-icon valid';
                }
            }

            function validateConfirmPassword() {
                const password = passwordInput.value;
                const confirmPassword = confirmPasswordInput.value;
                
                if (confirmPassword.length === 0) {
                    // Hide hint and icon when no input
                    confirmPasswordHint.className = 'password-hint hidden';
                    confirmPasswordInput.className = 'input-neutral';
                    confirmPasswordIcon.className = 'validation-icon hidden';
                    confirmPasswordIcon.textContent = '';
                } else if (password !== confirmPassword) {
                    confirmPasswordHint.textContent = '✗ Passwords do not match';
                    confirmPasswordHint.className = 'password-hint invalid';
                    confirmPasswordInput.className = 'input-invalid';
                    confirmPasswordIcon.textContent = '✗';
                    confirmPasswordIcon.className = 'validation-icon invalid';
                } else if (password.length >= 6) {
                    confirmPasswordHint.textContent = '✓ Passwords match';
                    confirmPasswordHint.className = 'password-hint valid';
                    confirmPasswordInput.className = 'input-valid';
                    confirmPasswordIcon.textContent = '✓';
                    confirmPasswordIcon.className = 'validation-icon valid';
                } else {
                    confirmPasswordHint.textContent = 'Wait for valid password first';
                    confirmPasswordHint.className = 'password-hint neutral';
                    confirmPasswordInput.className = 'input-neutral';
                    confirmPasswordIcon.className = 'validation-icon hidden';
                    confirmPasswordIcon.textContent = '';
                }
            }

            function updateSubmitButton() {
                const password = passwordInput.value;
                const confirmPassword = confirmPasswordInput.value;
                const userid = document.getElementById('userid').value;
                
                const isPasswordValid = password.length >= 6;
                const isConfirmPasswordValid = confirmPassword === password && password.length >= 6;
                const isFormComplete = userid.trim() !== '';
                
                if (isPasswordValid && isConfirmPasswordValid && isFormComplete) {
                    resetBtn.disabled = false;
                    resetBtn.style.opacity = '1';
                } else {
                    resetBtn.disabled = true;
                    resetBtn.style.opacity = '0.4';
                }
            }

            // Enhanced form submission
            const forgotForm = document.getElementById('forgotForm');
            
            if (forgotForm) {
                forgotForm.addEventListener('submit', function(e) {
                    // Client-side validation
                    const password = document.getElementById('new_password').value;
                    const confirmPassword = document.getElementById('confirm_password').value;
                    
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
                    if (resetBtn.disabled) {
                        e.preventDefault();
                        return false;
                    }
                    
                    // Add loading state
                    resetBtn.disabled = true;
                    resetBtn.textContent = 'Resetting Password...';
                    
                    // Re-enable button after 3 seconds as fallback
                    setTimeout(function() {
                        resetBtn.disabled = false;
                        resetBtn.textContent = 'Reset Password';
                    }, 3000);
                });
            }

            // Update submit button when other fields change
            document.getElementById('userid').addEventListener('input', updateSubmitButton);

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
            const resetBtn = document.getElementById('resetBtn');
            resetBtn.parentNode.insertBefore(errorDiv, resetBtn);
        }
    </script>
</body>
</html>