CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    userID VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE assets (
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
);

