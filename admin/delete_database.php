<?php
// Script to delete the database for testing purposes (Admin version)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rad";

echo "<h2>Database Deletion Script - Admin Panel</h2>";
echo "<p>This script will delete the '$dbname' database to allow testing of automatic creation.</p>";

try {
    // Connect to MySQL server
    $conn = new mysqli($servername, $username, $password);
    
    if ($conn->connect_error) {
        die("<p style='color: red;'>Connection failed: " . $conn->connect_error . "</p>");
    }
    
    echo "<p style='color: green;'>✓ Connected to MySQL server</p>";
    
    // Check if database exists
    $result = $conn->query("SHOW DATABASES LIKE '$dbname'");
    
    if ($result->num_rows > 0) {
        // Database exists, delete it
        echo "<p style='color: orange;'>Database '$dbname' found. Deleting...</p>";
        
        $sql = "DROP DATABASE $dbname";
        if ($conn->query($sql) === TRUE) {
            echo "<p style='color: green;'>✓ Database '$dbname' deleted successfully!</p>";
            echo "<p style='color: blue;'><strong>Database has been removed. You can now test the automatic creation by visiting the admin login page.</strong></p>";
        } else {
            echo "<p style='color: red;'>Error deleting database: " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color: blue;'>Database '$dbname' does not exist. Nothing to delete.</p>";
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><strong>Next Steps for Admin Testing:</strong></p>";
echo "<ol>";
echo "<li><a href='login.php' target='_blank'>Visit Admin Login Page</a> to test automatic database creation</li>";
echo "<li>The system should silently create the database in the background</li>";
echo "<li>Login with: <strong>AdminID: admin</strong> | <strong>Password: admin123</strong></li>";
echo "</ol>";

echo "<hr>";
echo "<p><strong>Alternative Testing Links:</strong></p>";
echo "<ul>";
echo "<li><a href='../user/login.php' target='_blank'>User Login Page</a> (UserID: user1 | Password: user123)</li>";
echo "</ul>";

echo "<p><em>Note: This script is for testing purposes only. In production, database deletion should be handled carefully.</em></p>";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Deletion - Admin Testing</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        h2 {
            color: #333;
        }
        a {
            color: #007bff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        ol li, ul li {
            margin: 10px 0;
        }
    </style>
</head>
<body>
</body>
</html> 