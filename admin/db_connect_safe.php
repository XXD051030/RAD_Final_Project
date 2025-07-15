<?php
// Safe database connection for admin that returns status instead of terminating execution
$servername = "localhost";
$username = "root";  
$password = "";  
$dbname = "rad";

// Initialize connection variables
$conn = null;
$db_status = array(
    'server_connected' => false,
    'database_exists' => false,
    'tables_exist' => false,
    'connection' => null,
    'error_message' => '',
    'setup_required' => true
);

try {
    // First, try to connect to MySQL server without specifying database
    $temp_conn = new mysqli($servername, $username, $password);
    
    if ($temp_conn->connect_error) {
        $db_status['error_message'] = "MySQL server connection failed: " . $temp_conn->connect_error;
        $db_status['setup_required'] = true;
    } else {
        $db_status['server_connected'] = true;
        
        // Check if database exists
        $result = $temp_conn->query("SHOW DATABASES LIKE '$dbname'");
        if ($result && $result->num_rows > 0) {
            $db_status['database_exists'] = true;
            
            // Try to connect to the specific database
            $conn = new mysqli($servername, $username, $password, $dbname);
            if (!$conn->connect_error) {
                $db_status['connection'] = $conn;
                
                // Check if required tables exist for admin
                $admin_check = $conn->query("SHOW TABLES LIKE 'admin'");
                $users_check = $conn->query("SHOW TABLES LIKE 'users'");
                $assets_check = $conn->query("SHOW TABLES LIKE 'assets'");
                
                if ($admin_check && $admin_check->num_rows > 0 && 
                    $users_check && $users_check->num_rows > 0 && 
                    $assets_check && $assets_check->num_rows > 0) {
                    $db_status['tables_exist'] = true;
                    $db_status['setup_required'] = false;
                }
            }
        }
        $temp_conn->close();
    }
} catch (Exception $e) {
    $db_status['error_message'] = "Database connection error: " . $e->getMessage();
}

// Function to get database status
function getDatabaseStatus() {
    global $db_status;
    return $db_status;
}

// Function to get safe database connection
function getSafeConnection() {
    global $db_status;
    return $db_status['connection'];
}
?> 