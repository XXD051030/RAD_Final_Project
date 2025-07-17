# RAD - Resource Allocation Dashboard

A comprehensive web-based system for managing assets and resources with automatic database setup functionality for both user and admin portals.

## 🚀 Features

- **Automatic Database Setup**: No manual configuration required - the system automatically creates and configures the database
- **Dual Portal System**: Separate user and admin interfaces
- **Enhanced User Authentication**: 
  - Secure login system with password hashing for both portals
  - Real-time password validation with live character count
  - Visual feedback for password strength and confirmation matching
  - Smart form validation prevents submission until all requirements are met
- **Asset Management**: Track and manage organizational assets
- **Responsive Design**: Modern, mobile-friendly interface
- **Real-time Status Updates**: Live feedback during system initialization
- **Silent Error Handling**: Seamless user experience with background problem resolution

## 📋 Prerequisites

- **XAMPP**: Apache and MySQL services
- **PHP**: Version 7.4 or higher
- **Web Browser**: Modern browser with JavaScript enabled

## 🛠️ Installation & Setup

### Step 1: XAMPP Configuration
1. **Install XAMPP** if not already installed
2. **Start Services**:
   - Open XAMPP Control Panel
   - Start **Apache** service (should show green)
   - Start **MySQL** service (should show green)

### Step 2: Project Setup
1. **Extract/Copy** the project files to:
   ```
   /Applications/XAMPP/xamppfiles/htdocs/dashboard/rad-final/
   ```

2. **Access the System**:
   - Open your web browser
   - **User Portal**: `http://localhost/dashboard/rad-final/user/user/login.php`
   - **Admin Portal**: `http://localhost/dashboard/rad-final/admin/login.php`

### Step 3: Automatic Database Setup
The system will automatically:
- ✅ Check database connectivity
- ✅ Create the `rad` database if it doesn't exist
- ✅ Create required tables (`users`, `admin`, `assets`)
- ✅ Insert sample data for testing
- ✅ Create default test users for both user and admin portals

**No manual setup required!** The system handles everything automatically and silently in the background.

## 🔑 Default Login Credentials

After automatic setup, you can login with:

### User Login
- **UserID**: `user1`
- **Password**: `user123`
- **URL**: `http://localhost/dashboard/rad-final/user/user/login.php`

### Admin Login
- **AdminID**: `admin123`
- **Password**: `123456`
- **URL**: `http://localhost/dashboard/rad-final/admin/login.php`

## 📁 Project Structure

```
rad-final/
├── admin/                  # Admin portal files
│   ├── login.php          # Admin login with auto-setup
│   ├── db_connect_safe.php # Safe database connection
│   ├── auto_setup.php     # Automatic setup script
│   ├── dashboard.php      # Admin dashboard
│   └── ...
├── user/                   # User portal files
│   └── user/
│       ├── login.php       # User login with auto-setup
│       ├── db_connect_safe.php  # Safe database connection
│       ├── auto_setup.php  # Automatic setup script
│       ├── dashboard/      # User dashboard
│       └── ...
└── README.md              # This file
```

## 🔧 Troubleshooting

### Common Issues & Solutions

#### 1. "Cannot Access Login Page"
- **Solution**: Ensure XAMPP Apache service is running (green status in XAMPP Control Panel)
- Check the correct URL path for your portal
- Verify file permissions

#### 2. Page Loads But Login Doesn't Work
- **Solution**: The system is likely setting up the database in the background
- Wait a few seconds and try refreshing the page
- The system will automatically create all necessary components

#### 3. XAMPP MySQL Service Won't Start
- **Solution**: 
  - Check that port 3306 is not blocked
  - Restart XAMPP services
  - Check for other MySQL installations

#### 4. Blank Page or PHP Errors
- **Solution**: 
  - Ensure PHP is enabled in XAMPP
  - Check Apache error logs
  - Verify all files are in the correct directory

## 🎯 System Workflow

1. **First Visit**: System detects missing database/tables
2. **Silent Auto Setup**: Automatically creates database and tables in background
3. **Seamless Experience**: User sees clean login page throughout
4. **Ready to Use**: User can immediately login with default credentials
5. **No Interruptions**: All setup happens transparently

## 🔒 Security Features

- **Password Hashing**: All passwords are securely hashed using PHP's `password_hash()`
- **Real-time Password Validation**: 
  - Minimum 6 characters requirement with live character count
  - Visual feedback with color-coded input fields and validation icons
  - Real-time password confirmation matching
  - Disabled submit button until all validation passes
- **SQL Injection Protection**: Prepared statements used throughout
- **Session Management**: Secure session handling for user authentication
- **Input Validation**: Form inputs are validated and sanitized both client-side and server-side
- **Separation of Concerns**: User and admin portals are completely separate

## 📊 Database Schema

### Users Table
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    userID VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);
```

### Admin Table
```sql
CREATE TABLE admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    adminID VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);
```

### Assets Table
```sql
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
```

## 🚀 Quick Start Guide

1. **Start XAMPP**: Apache + MySQL services
2. **Open Browser**: Navigate to either user or admin login page
3. **Login Immediately**: Use default credentials (setup happens automatically)
   - **User**: `user1` / `user123`
   - **Admin**: `admin123` / `123456`
4. **Start Using**: Access dashboard and asset management features

## 📝 Development Notes

- **Framework**: Pure PHP with MySQL
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Database**: MySQL with automatic setup
- **Architecture**: MVC-inspired structure with dual portal system
- **Responsive**: Bootstrap-inspired responsive design
- **User Experience**: Completely seamless with silent error recovery

## 🆘 Support

The system is designed to be self-healing and requires minimal support:

1. **Most Issues Auto-Resolve**: The system automatically fixes database problems
2. **Check XAMPP Status**: Ensure Apache and MySQL services are running
3. **Wait and Refresh**: If something seems wrong, wait 10 seconds and refresh
4. **Clean Browser Cache**: Clear cache if pages don't load correctly
5. **Restart XAMPP**: Last resort for persistent issues

## 🌟 Key Benefits

- **Zero Configuration**: Works out of the box
- **Self-Healing**: Automatically fixes common problems
- **User-Friendly**: No technical knowledge required
- **Dual System**: Separate user and admin workflows
- **Production Ready**: Secure and robust implementation

---

**Last Updated**: July 2025  
**Version**: 1.0 