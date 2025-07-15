<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $adminID = $_POST['adminID'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    
    $error_message = "";
    $success_message = "";

    // Validate password confirmation
    if ($password !== $confirmPassword) {
        $error_message = "Passwords do not match!";
    } else {
        // Check if userID already exists
        $check_sql = "SELECT adminID FROM admin WHERE adminID = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $adminID);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error_message = "adminID already exists! Please choose a different one.";
        } else {
            // Insert new admin
            $sql = "INSERT INTO admin (adminID, password) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $adminID, $password);

            if ($stmt->execute()) {
                // Redirect to login page
                header("Location: login.php");
                exit(); // Ensure no further code is executed
            } else {
                $error_message = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
        $check_stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            background-color: #e0e0e0;
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
            text-align: center;
        }
        h2 {
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            padding: 10px 20px;
            background-color: #fff;
            border: 1px solid #ccc;
            border-radius: 5px;
            cursor: pointer;
        }
        .link {
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class禁止: class="container">
        <h2>Sign Up Admin</h2>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
            <div class="form-group">
                <label for="adminID">AdminID:</label><br>
                <input type="text" id="adminID" name="adminID" placeholder="Enter AdminID" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label><br>
                <input type="email" id="email" name="email" placeholder="Enter Email" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label><br>
                <input type="password" id="password" name="password" placeholder="Enter Password" required>
            </div>
            <div class="form-group">
                <label for="confirmPassword">Confirm Password:</label><br>
                <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm Password" required>
            </div>
            <button type="submit">Sign Up</button>
        </form>
        
        <?php if (!empty($error_message)): ?>
            <div style="color: red; margin: 15px 0; padding: 10px; background-color: #ffe6e6; border-radius: 5px;">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <div class="link">
            Already have an Account? <a href="login.php">Login</a>
        </div>
    </div>
</body>
</html>