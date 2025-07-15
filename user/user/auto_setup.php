<?php
// Automatic database setup script
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rad";

$response = array(
    'success' => false,
    'message' => '',
    'steps' => array(),
    'error' => ''
);

try {
    // Step 1: Connect to MySQL server
    $response['steps'][] = "Connecting to MySQL server...";
    $conn = new mysqli($servername, $username, $password);
    
    if ($conn->connect_error) {
        throw new Exception("MySQL server connection failed: " . $conn->connect_error);
    }
    
    $response['steps'][] = "✓ Connected to MySQL server successfully";
    
    // Step 2: Check and create database
    $response['steps'][] = "Checking if database exists...";
    $result = $conn->query("SHOW DATABASES LIKE '$dbname'");
    
    if ($result->num_rows == 0) {
        $response['steps'][] = "Database '$dbname' does not exist. Creating...";
        $sql = "CREATE DATABASE $dbname";
        if ($conn->query($sql) === TRUE) {
            $response['steps'][] = "✓ Database '$dbname' created successfully";
        } else {
            throw new Exception("Error creating database: " . $conn->error);
        }
    } else {
        $response['steps'][] = "✓ Database '$dbname' already exists";
    }
    
    // Step 3: Connect to the specific database
    $conn->select_db($dbname);
    
    // Step 4: Check and create users table
    $response['steps'][] = "Checking users table...";
    $result = $conn->query("SHOW TABLES LIKE 'users'");
    
    if ($result->num_rows == 0) {
        $response['steps'][] = "Creating users table...";
        $sql = "CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            userID VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL
        )";
        
        if ($conn->query($sql) === TRUE) {
            $response['steps'][] = "✓ Users table created successfully";
            
            // Insert default test user
            $testUser = "user1";
            $testPassword = password_hash("user123", PASSWORD_DEFAULT);
            
            $insertSql = "INSERT INTO users (userID, password) VALUES (?, ?)";
            $stmt = $conn->prepare($insertSql);
            $stmt->bind_param("ss", $testUser, $testPassword);
            
            if ($stmt->execute()) {
                $response['steps'][] = "✓ Default test user created (UserID: user1, Password: user123)";
            } else {
                $response['steps'][] = "⚠ Warning: Could not create test user";
            }
            $stmt->close();
        } else {
            throw new Exception("Error creating users table: " . $conn->error);
        }
    } else {
        $response['steps'][] = "✓ Users table already exists";
    }
    
    // Step 5: Check and create assets table
    $response['steps'][] = "Checking assets table...";
    $result = $conn->query("SHOW TABLES LIKE 'assets'");
    
    if ($result->num_rows == 0) {
        $response['steps'][] = "Creating assets table...";
        $sql = "CREATE TABLE assets (
            Asset_ID VARCHAR(10) PRIMARY KEY,
            Asset_Name VARCHAR(100) NOT NULL,
            Category VARCHAR(50) NOT NULL,
            Brand_Model VARCHAR(100),
            Serial_Number VARCHAR(50) UNIQUE,
            Location VARCHAR(100),
            Assigned_To VARCHAR(100),
            Purchase_Date DATE,
            Warranty_Expiry DATE,
            Asset_Value DECIMAL(10, 2),
            Status VARCHAR(20),
            Supplier VARCHAR(100)
        )";
        
        if ($conn->query($sql) === TRUE) {
            $response['steps'][] = "✓ Assets table created successfully";
            
            // Insert sample data
            $response['steps'][] = "Inserting sample asset data...";
            $assetsData = [
                ['A001', 'Laptop', 'IT Equipment', 'Dell Inspiron 15', 'SN-001', 'IT Department', 'John Doe', '2024-01-15', '2027-01-15', 1200.00, 'Active', 'Dell Malaysia'],
                ['A002', 'Printer', 'Office Equipment', 'HP LaserJet Pro', 'SN-002', 'Admin Department', 'Jane Smith', '2024-01-20', '2027-01-20', 300.00, 'Active', 'HP Malaysia'],
                ['A003', 'Monitor', 'IT Equipment', 'Samsung 24inch', 'SN-003', 'IT Department', 'Bob Wilson', '2024-02-01', '2027-02-01', 250.00, 'Active', 'Samsung Malaysia']
            ];
            
            $insertSql = "INSERT INTO assets (Asset_ID, Asset_Name, Category, Brand_Model, Serial_Number, Location, Assigned_To, Purchase_Date, Warranty_Expiry, Asset_Value, Status, Supplier) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insertSql);
            
            $successCount = 0;
            foreach ($assetsData as $data) {
                $stmt->bind_param("sssssssssdss", ...$data);
                if ($stmt->execute()) {
                    $successCount++;
                }
            }
            $stmt->close();
            
            $response['steps'][] = "✓ Inserted $successCount sample assets";
        } else {
            throw new Exception("Error creating assets table: " . $conn->error);
        }
    } else {
        $response['steps'][] = "✓ Assets table already exists";
    }
    
    $response['success'] = true;
    $response['message'] = "Database setup completed successfully!";
    $response['steps'][] = "🎉 Setup completed! You can now login to the system.";
    
} catch (Exception $e) {
    $response['success'] = false;
    $response['error'] = $e->getMessage();
    $response['message'] = "Setup failed: " . $e->getMessage();
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}

echo json_encode($response);
?> 