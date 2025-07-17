<?php
// Auto database check and initialization script
// This file is included by login pages to ensure database exists
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rad";

try {
    // First, connect to MySQL server without specifying database
    $conn = new mysqli($servername, $username, $password);
    
    // Check connection
    if ($conn->connect_error) {
        error_log("Database connection failed: " . $conn->connect_error);
        return;
    }
    
    // Check if database exists, create if not
    $result = $conn->query("SHOW DATABASES LIKE '$dbname'");
    if ($result->num_rows == 0) {
        $sql = "CREATE DATABASE $dbname";
        if (!$conn->query($sql)) {
            error_log("Error creating database: " . $conn->error);
            return;
        }
    }
    
    // Connect to the specific database
    $conn->select_db($dbname);
    
    // Create users table if not exists
    $result = $conn->query("SHOW TABLES LIKE 'users'");
    if ($result->num_rows == 0) {
        $sql = "CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            userID VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            status ENUM('active', 'inactive') DEFAULT 'active'
        )";
        
        if ($conn->query($sql)) {
            // Insert default user
            $testUser = "user1";
            $testPassword = password_hash("user123", PASSWORD_DEFAULT);
            $testEmail = "user1@example.com";
            
            $insertSql = "INSERT INTO users (userID, password, email) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($insertSql);
            $stmt->bind_param("sss", $testUser, $testPassword, $testEmail);
            $stmt->execute();
            $stmt->close();
        }
    } else {
        // Check if new columns exist, add them if not
        $columns = $conn->query("SHOW COLUMNS FROM users LIKE 'email'");
        if ($columns->num_rows == 0) {
            $conn->query("ALTER TABLE users ADD COLUMN email VARCHAR(255)");
            $conn->query("ALTER TABLE users ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
            $conn->query("ALTER TABLE users ADD COLUMN status ENUM('active', 'inactive') DEFAULT 'active'");
        }
    }
    
    // Create admin table if not exists
    $result = $conn->query("SHOW TABLES LIKE 'admin'");
    if ($result->num_rows == 0) {
        $sql = "CREATE TABLE admin (
            id INT AUTO_INCREMENT PRIMARY KEY,
            adminID VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            status ENUM('active', 'inactive') DEFAULT 'active'
        )";
        
        if ($conn->query($sql)) {
            // Insert default admin
            $adminUser = "admin";
            $adminPassword = password_hash("admin123", PASSWORD_DEFAULT);
            $adminEmail = "admin@infinecs.com";
            
            $insertSql = "INSERT INTO admin (adminID, password, email) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($insertSql);
            $stmt->bind_param("sss", $adminUser, $adminPassword, $adminEmail);
            $stmt->execute();
            $stmt->close();
        }
    } else {
        // Check if new columns exist, add them if not
        $columns = $conn->query("SHOW COLUMNS FROM admin LIKE 'email'");
        if ($columns->num_rows == 0) {
            $conn->query("ALTER TABLE admin ADD COLUMN email VARCHAR(255)");
            $conn->query("ALTER TABLE admin ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
            $conn->query("ALTER TABLE admin ADD COLUMN status ENUM('active', 'inactive') DEFAULT 'active'");
        }
    }
    
    // Create assets table if not exists
    $assetsTableCreated = false;
    $result = $conn->query("SHOW TABLES LIKE 'assets'");
    if ($result->num_rows == 0) {
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
        
        if ($conn->query($sql)) {
            $assetsTableCreated = true;
        }
    }
    
    // Create borrow_requests table if not exists
    $result = $conn->query("SHOW TABLES LIKE 'borrow_requests'");
    if ($result->num_rows == 0) {
        $sql = "CREATE TABLE borrow_requests (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id VARCHAR(255) NOT NULL,
            asset_id VARCHAR(10) NOT NULL,
            asset_name VARCHAR(100) NOT NULL,
            borrow_date DATE NOT NULL,
            return_date DATE NOT NULL,
            purpose TEXT,
            status ENUM('pending', 'approved', 'declined') DEFAULT 'pending',
            admin_response TEXT,
            admin_notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $conn->query($sql);
    }
    
    // Insert sample assets data if table was just created
    if ($assetsTableCreated) {
        $assetsData = [
            ['A001', 'Aircon', 'Security', 'Taylor-White 429', 'SN-70946', 'Admin Department', 'Priya A/P Kumar', '2024-04-23', '2027-04-23', 1481.00, 'Retired', 'HP Distributor'],
            ['A002', 'Chair', 'Security', 'Baxter Inc 186', 'SN-47196', 'HR Department', 'Siti Binti Aminah', '2024-02-22', '2027-02-21', 1709.48, 'Active', 'IKEA Malaysia'],
            ['A003', 'Router', 'Office Device', 'Garcia, Pearson and Fernandez 220', 'SN-13501', 'IT Department', 'Tan Chee Seng', '2020-07-01', '2023-07-01', 1974.30, 'Retired', 'Dell Malaysia'],
            ['A004', 'Camera', 'Security', 'House and Sons 689', 'SN-35485', 'Admin Department', 'Priya A/P Kumar', '2021-10-15', '2024-10-14', 1039.44, 'Active', 'IKEA Malaysia'],
            ['A005', 'Laptop', 'IT Equipment', 'Tucker-Lewis 897', 'SN-65724', 'Finance Department', 'Lim Wei Ling', '2023-10-14', '2026-10-13', 1686.58, 'Retired', 'Samsung Malaysia'],
            ['A006', 'Laptop', 'Networking', 'Martin Inc 355', 'SN-25906', 'Admin Department', 'Lim Wei Ling', '2023-09-11', '2026-09-10', 1682.27, 'Retired', 'Samsung Malaysia'],
            ['A007', 'Aircon', 'Furniture', 'House-Glover 873', 'SN-68008', 'Admin Department', 'Mohd Faiz Bin Ismail', '2022-10-20', '2025-10-19', 1113.46, 'Active', 'IKEA Malaysia'],
            ['A008', 'Projector', 'Furniture', 'Horton-Cross 869', 'SN-42406', 'HR Department', 'Mohd Faiz Bin Ismail', '2025-02-06', '2028-02-06', 1175.36, 'Active', 'Lenovo Malaysia'],
            ['A009', 'Printer', 'Security', 'Higgins, Moore and Phillips 392', 'SN-40735', 'HR Department', 'Nur Aisyah Binti Osman', '2020-11-14', '2023-11-14', 911.54, 'Retired', 'Dell Malaysia'],
            ['A010', 'Chair', 'Office Device', 'Fitzgerald, Brown and Edwards 492 ', 'SN-30028', 'Finance Department', 'Siti Binti Aminah', '2020-10-19', '2023-10-19', 1063.79, 'In Repair', 'Samsung Malaysia'],
            ['A011', 'Camera', 'IT Equipment', 'Woods, Calhoun and Schmidt 311', 'SN-65069', 'Admin Department', 'Lakshmi A/P Devan', '2020-11-09', '2023-11-09', 1913.70, 'Active', 'Samsung Malaysia'],
            ['A012', 'Projector', 'IT Equipment', 'Miller, Lopez and Larson 460', 'SN-49139', 'Admin Department', 'Suresh A/L Raj', '2022-05-17', '2025-05-16', 1261.15, 'Retired', 'Samsung Malaysia'],
            ['A013', 'Router', 'Mobile Device', 'Wilson-Zamora 324', 'SN-45774', 'Admin Department', 'Priya A/P Kumar', '2022-06-20', '2025-06-19', 446.44, 'In Repair', 'IKEA Malaysia'],
            ['A014', 'Camera', 'Security', 'Wheeler, Harvey and Barnes 269', 'SN-71261', 'Finance Department', 'Lakshmi A/P Devan', '2025-05-04', '2028-05-03', 1254.60, 'In Repair', 'Samsung Malaysia'],
            ['A015', 'Laptop', 'IT Equipment', 'Mills, Donovan and Harris 758', 'SN-66179', 'Finance Department', 'Priya A/P Kumar', '2023-01-18', '2026-01-17', 690.77, 'In Repair', 'HP Distributor'],
            ['A016', 'Router', 'Mobile Device', 'Baker and Sons 434', 'SN-54236', 'Admin Department', 'Chong Jia Hao', '2022-12-05', '2025-12-04', 1603.22, 'In Repair', 'Lenovo Malaysia'],
            ['A017', 'Projector', 'IT Equipment', 'Stewart-Walton 867', 'SN-80702', 'IT Department', 'Nur Aisyah Binti Osman', '2020-12-28', '2023-12-28', 758.76, 'Active', 'IKEA Malaysia'],
            ['A018', 'Laptop', 'Office Device', 'Ellis PLC 304', 'SN-12671', 'Finance Department', 'Tan Chee Seng', '2021-03-23', '2024-03-22', 601.95, 'Retired', 'Dell Malaysia'],
            ['A019', 'Router', 'Mobile Device', 'Medina PLC 816', 'SN-43586', 'HR Department', 'Lim Wei Ling', '2022-08-18', '2025-08-17', 1369.43, 'Retired', 'IKEA Malaysia'],
            ['A020', 'Chair', 'Furniture', 'Mahoney Inc 210', 'SN-85849', 'IT Department', 'Chong Jia Hao', '2022-06-14', '2025-06-13', 1321.27, 'In Repair', 'HP Distributor'],
            ['A021', 'Router', 'IT Equipment', 'Mitchell-Kim 706', 'SN-92213', 'Finance Department', 'Siti Binti Aminah', '2024-12-29', '2027-12-29', 1515.51, 'In Repair', 'IKEA Malaysia'],
            ['A022', 'Printer', 'Networking', 'Turner, Riggs and Roman 901', 'SN-15383', 'HR Department', 'Mohd Faiz Bin Ismail', '2024-11-15', '2027-11-15', 1085.40, 'In Repair', 'Dell Malaysia'],
            ['A023', 'Desk', 'IT Equipment', 'Contreras PLC 969', 'SN-65057', 'Admin Department', 'Siti Binti Aminah', '2022-12-03', '2025-12-02', 1093.64, 'In Repair', 'IKEA Malaysia'],
            ['A024', 'Chair', 'Mobile Device', 'Garcia, Humphrey and Baker 270', 'SN-88386', 'HR Department', 'Lakshmi A/P Devan', '2021-04-14', '2024-04-13', 1693.12, 'Retired', 'HP Distributor'],
            ['A025', 'Camera', 'Security', 'Tran, Nelson and Jacobs 706', 'SN-45179', 'HR Department', 'Tan Chee Seng', '2023-04-17', '2026-04-16', 1729.17, 'Active', 'Lenovo Malaysia'],
            ['A026', 'Router', 'Mobile Device', 'Watts LLC 683', 'SN-89997', 'Admin Department', 'Nur Aisyah Binti Osman', '2024-09-03', '2027-09-03', 445.92, 'In Repair', 'Canon Supplier'],
            ['A027', 'Router', 'Furniture', 'King-Odonnell 916', 'SN-43862', 'HR Department', 'Chong Jia Hao', '2020-10-21', '2023-10-21', 1808.51, 'Retired', 'Lenovo Malaysia'],
            ['A028', 'Laptop', 'Networking', 'Walters LLC 295', 'SN-21221', 'Finance Department', 'Wei Ming Tan', '2020-09-30', '2023-09-30', 1050.28, 'Retired', 'Canon Supplier'],
            ['A029', 'Projector', 'Security', 'Cook and Sons 828', 'SN-74332', 'Admin Department', 'Ahmad Bin Abdullah', '2022-08-08', '2025-08-07', 548.89, 'Active', 'HP Distributor'],
            ['A030', 'Router', 'Furniture', 'Figueroa PLC 779', 'SN-86225', 'HR Department', 'Priya A/P Kumar', '2024-01-05', '2027-01-04', 1285.56, 'In Repair', 'HP Distributor'],
            ['A031', 'Monitor', 'Furniture', 'Smith, Jones and Ware 460', 'SN-69473', 'HR Department', 'Chong Jia Hao', '2021-08-23', '2024-08-22', 802.25, 'Active', 'IKEA Malaysia'],
            ['A032', 'Desk', 'IT Equipment', 'Johnson, Wood and Tran 860', 'SN-80236', 'Finance Department', 'Tan Chee Seng', '2024-01-12', '2027-01-11', 746.22, 'In Repair', 'Lenovo Malaysia'],
            ['A033', 'Aircon', 'Networking', 'Williams LLC 711', 'SN-47093', 'IT Department', 'Tan Chee Seng', '2020-09-17', '2023-09-17', 873.99, 'In Repair', 'Canon Supplier'],
            ['A034', 'Laptop', 'Security', 'Clark-Wright 646', 'SN-26591', 'HR Department', 'Ahmad Bin Abdullah', '2021-04-01', '2024-03-31', 1958.87, 'Retired', 'Lenovo Malaysia'],
            ['A035', 'Chair', 'Security', 'Deleon-Henson 989', 'SN-74340', 'IT Department', 'Ahmad Bin Abdullah', '2023-10-31', '2026-10-30', 1318.50, 'In Repair', 'HP Distributor'],
            ['A036', 'Desk', 'Office Device', 'Brady, Frost and Young 152', 'SN-43092', 'Admin Department', 'Siti Binti Aminah', '2021-10-03', '2024-10-02', 1715.39, 'In Repair', 'HP Distributor'],
            ['A037', 'Aircon', 'Security', 'Keith Inc 802', 'SN-17026', 'Finance Department', 'Lim Wei Ling', '2021-11-25', '2024-11-24', 1697.83, 'In Repair', 'Dell Malaysia'],
            ['A038', 'Printer', 'Networking', 'Middleton, Patton and Jenkins 882', 'SN -64548', 'Finance Department', 'Mohd Faiz Bin Ismail', '2022-06-27', '2025-06-26', 1008.60, 'In Repair', 'Lenovo Malaysia'],
            ['A039', 'Camera', 'Furniture', 'Moore-Haynes 682', 'SN-91399', 'IT Department', 'Lakshmi A/P Devan', '2023-08-18', '2026-08-17', 1936.17, 'Active', 'Canon Supplier'],
            ['A040', 'Router', 'Furniture', 'Garrison and Sons 776', 'SN-20641', 'Finance Department', 'Tan Chee Seng', '2022-01-27', '2025-01-26', 678.10, 'Active', 'Canon Supplier'],
            ['A041', 'Camera', 'Mobile Device', 'Bailey-Hoover 805', 'SN-87832', 'Admin Department', 'Chong Jia Hao', '2022-04-24', '2025-04-23', 452.22, 'In Repair', 'IKEA Malaysia'],
            ['A042', 'Projector', 'IT Equipment', 'Johnson and Sons 803', 'SN-40595', 'HR Department', 'Arun A/L Raj', '2023-10-06', '2026-10-05', 1343.66, 'Active', 'HP Distributor'],
            ['A043', 'Monitor', 'Office Device', 'Ramirez-Jones 763', 'SN-29528', 'HR Department', 'Lim Wei Ling', '2022-10-01', '2025-09-30', 514.26, 'Active', 'Lenovo Malaysia'],
            ['A044', 'Aircon', 'Furniture', 'May-Ross 549', 'SN-26299', 'Admin Department', 'Wei Ming Tan', '2024-12-07', '2027-12-07', 886.55, 'In Repair', 'Lenovo Malaysia'],
            ['A045', 'Monitor', 'Mobile Device', 'Crosby-Wilson 548', 'SN-20543', 'IT Department', 'Suresh A/L Raj', '2022-08-06', '2025-08-05', 1575.25, 'Retired', 'Lenovo Malaysia'],
            ['A046', 'Printer', 'Office Device', 'Carpenter LLC 790', 'SN-85392', 'IT Department', 'Arun A/L Raj', '2020-09-12', '2023-09-12', 1714.27, 'Retired', 'Dell Malaysia'],
            ['A047', 'Projector', 'Networking', 'Carlson LLC 767', 'SN-67963', 'HR Department', 'Lim Wei Ling', '2023-01-16', '2026-01-15', 1995.11, 'In Repair', 'IKEA Malaysia'],
            ['A048', 'Printer', 'Mobile Device', 'Obrien-Dixon 456', 'SN-63523', 'HR Department', 'Nur Aisyah Binti Osman', '2023-08-26', '2026-08-25', 1472.21, 'Active', 'Lenovo Malaysia'],
            ['A049', 'Projector', 'Furniture', 'Lee, Williams and Graham 778', 'SN-62488', 'IT Department', 'Priya A/P Kumar', '2024-10-18', '2027-10-18', 540.90, 'In Repair', 'Lenovo Malaysia'],
            ['A050', 'Camera', 'Networking', 'Mcdaniel, Bentley and Mclaugh 101', 'SN-78450', 'Admin Department', 'Suresh A/L Raj', '2022-01-19', '2025-01-18', 486.73, 'Retired', 'Lenovo Malaysia']
        ];
        
        $insertSql = "INSERT INTO assets (Asset_ID, Asset_Name, Category, Brand_Model, Serial_Number, Location, Assigned_To, Purchase_Date, Warranty_Expiry, Asset_Value, Status, Supplier) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insertSql);
        
        if ($stmt) {
            $stmt->bind_param("sssssssssdss", $assetID, $assetName, $category, $brandModel, $serialNumber, $location, $assignedTo, $purchaseDate, $warrantyExpiry, $assetValue, $status, $supplier);
            
            foreach ($assetsData as $data) {
                list($assetID, $assetName, $category, $brandModel, $serialNumber, $location, $assignedTo, $purchaseDate, $warrantyExpiry, $assetValue, $status, $supplier) = $data;
                $stmt->execute();
            }
            $stmt->close();
        }
    }
    
    $conn->close();
    
} catch (Exception $e) {
    error_log("Auto database setup error: " . $e->getMessage());
}
?> 