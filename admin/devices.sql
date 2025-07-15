CREATE TABLE devices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    type VARCHAR(50),
    serial_number VARCHAR(100),
    model VARCHAR(100),
    asset_tag VARCHAR(50),
    purchase_date DATE,
    warranty_end DATE,
    lifespan VARCHAR(50),
    status VARCHAR(50),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,  -- Automatically record added time
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP  -- Automatically record update time
);
