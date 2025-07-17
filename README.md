# RAD - Final Project

A Assets management web based system.

## 🚀 Features

### Core System Features
- **Automatic Database Setup**: No manual configuration required - the system automatically creates and configures the database
- **Dual Portal System**: Separate user and admin interfaces with unified modern design
- **Enhanced User Authentication**: 
  - Secure login system with password hashing for both portals
  - Real-time password validation with live character count
  - Visual feedback for password strength and confirmation matching
  - Smart form validation prevents submission until all requirements are met
- **Silent Error Handling**: Seamless user experience with background problem resolution

### Admin Portal Features
- **Advanced Device Management**: 
  - Modern card-based interface with optimized table layouts
  - Intelligent search across all asset fields (case-insensitive)
  - Fixed-position controls with scrollable content area
  - Optimized column widths for better data visibility
- **Request Management**: 
  - Real-time borrow request processing
  - Borrow period display instead of request dates
  - Status tracking with visual indicators
- **Alert System**: Warranty expiry notifications and activity logging

### User Portal Features
- **Asset Browsing**: Clean interface for viewing available assets
- **Borrow Request System**: 
  - Easy asset borrowing with date selection
  - Real-time status tracking
  - Borrow period visualization
- **Activity Monitoring**: 
  - Booking status updates in alert dashboard
  - Recent activity log with status changes
  - Visual status indicators

### Design & UX Features
- **Unified Modern Design**: Consistent styling across all pages
- **Responsive Layout**: Optimized for desktop and mobile devices
- **Enhanced Navigation**: Fixed navigation with proper text wrapping
- **Smart Tables**: 
  - Fixed headers with scrollable content
  - Optimized column widths for different data types
  - Custom scrollbars for better user experience
- **Status Visualization**: Color-coded status badges for quick identification

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
   - **User Portal**: `http://localhost/dashboard/rad-final/user/login.php`
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
- **URL**: `http://localhost/dashboard/rad-final/user/login.php`

### Admin Login
- **AdminID**: `admin123`
- **Password**: `123456`
- **URL**: `http://localhost/dashboard/rad-final/admin/login.php`

## 📁 Project Structure

```
rad-final/
├── admin/                     # Admin portal files
│   ├── login.php             # Admin login with auto-setup
│   ├── dashboard.php         # Admin dashboard with modern design
│   ├── AdminDM.php           # Enhanced device management
│   ├── borrow-requests.php   # Request management system
│   ├── alert.php             # Admin alert dashboard
│   ├── account-management.php # User account management
│   ├── add-asset.php         # Asset creation interface
│   ├── update-asset.php      # Asset modification interface
│   ├── view-asset.php        # Asset viewing interface
│   ├── delete-asset.php      # Asset deletion interface
│   └── db_connect_safe.php   # Safe database connection
├── user/                     # User portal files
│   ├── login.php            # User login with auto-setup
│   ├── signup.php           # User registration
│   ├── View.php             # Asset viewing interface
│   ├── borrow-status.php    # Borrow request tracking
│   ├── dashboard/           # User dashboard section
│   │   ├── dashboard.php    # Main user dashboard
│   │   ├── alert.php        # User alerts with booking status
│   │   └── borrow.php       # Asset borrowing interface
│   └── db_connect_safe.php  # Safe database connection
├── database/                # Database management
│   └── auto_database_check.php # Automatic setup utilities
├── images/                  # System assets
│   └── logo/               # Logo files
└── README.md               # This documentation
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



## 🚀 Quick Start Guide

1. **Start XAMPP**: Apache + MySQL services
2. **Open Browser**: Navigate to either user or admin login page
3. **Login Immediately**: Use default credentials (setup happens automatically)
   - **User**: `user1` / `user123`
   - **Admin**: `admin123` / `123456`
4. **Start Using**: Access dashboard and asset management features

## 🆕 Latest Features & Improvements

### Enhanced Admin Device Management
- **Smart Search**: Search across all asset fields (ID, name, category, brand, serial number, location, status, etc.)
- **Case-Insensitive Search**: Find assets regardless of text case
- **Optimized Table Layout**: 
  - Fixed header with scrollable content
  - Optimized column widths for better readability
  - Custom scrollbars for improved visual experience
- **Professional Data Display**:
  - Serial numbers in monospace font
  - Currency formatting for asset values
  - Color-coded status badges
  - Centered date displays

### Improved Navigation & UX
- **Fixed Navigation**: Menu items properly wrap for longer text (e.g., "Device Management", "Account Management")
- **Consistent Design**: Unified modern styling across all admin pages
- **Responsive Layout**: Works seamlessly on desktop and mobile devices
- **Enhanced Buttons**: Modern button design with hover effects and better spacing

### Advanced Booking System
- **Borrow Period Display**: Shows actual borrowing timeframe instead of request creation date
- **Real-Time Status Tracking**: Visual indicators for request status changes
- **User Activity Logs**: Comprehensive booking history in user alert dashboard
- **Status Notifications**: Clear status updates (Pending, Approved, Declined, Returned)

### UI/UX Enhancements
- **Fixed Content Areas**: Important controls stay visible while browsing large datasets
- **Smart Column Sizing**: Appropriate widths for different data types
- **Visual Status System**: Color-coded badges for quick status identification
- **Improved Readability**: Better typography and spacing throughout the system

## 📝 Development Notes

- **Framework**: Pure PHP with MySQL
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Database**: MySQL with automatic setup
- **Architecture**: MVC-inspired structure with dual portal system
- **Responsive**: Modern responsive design with mobile-first approach
- **User Experience**: Completely seamless with silent error recovery

### Recent Enhancements (v0.9.6+)
- **Enhanced Search**: Comprehensive search across all asset fields with case-insensitive matching
- **Improved UI/UX**: 
  - Fixed navigation with proper text wrapping for longer menu items
  - Optimized table layouts with appropriate column widths
  - Fixed-position controls with scrollable content areas
  - Custom scrollbar styling for better visual experience
- **Better Data Visualization**: 
  - Status badges with color coding
  - Proper currency and date formatting
  - Serial numbers in monospace font for clarity
- **Activity Tracking**: 
  - User activity logs for booking status changes
  - Real-time status updates in alert dashboards
  - Borrow period display instead of request timestamps

## 🆘 Support

The system is designed to be self-healing and requires minimal support:

1. **Most Issues Auto-Resolve**: The system automatically fixes database problems
2. **Check XAMPP Status**: Ensure Apache and MySQL services are running
3. **Wait and Refresh**: If something seems wrong, wait 10 seconds and refresh
4. **Clean Browser Cache**: Clear cache if pages don't load correctly
5. **Restart XAMPP**: Last resort for persistent issues

## 🌟 Key Benefits

- **Zero Configuration**: Works out of the box with automatic setup
- **Self-Healing**: Automatically fixes common problems
- **User-Friendly**: Intuitive interface requiring no technical knowledge
- **Dual System**: Separate optimized workflows for users and administrators
- **Production Ready**: Secure, robust, and scalable implementation
- **Modern Design**: Clean, professional interface with excellent user experience
- **Comprehensive Search**: Find assets quickly using any field or partial information
- **Real-Time Updates**: Live status tracking and activity monitoring
- **Mobile Optimized**: Full functionality on all devices

## 🎯 System Capabilities

### Asset Management
- **Complete Asset Lifecycle**: From procurement to retirement
- **Advanced Search**: Search by ID, name, category, serial number, location, status, etc.
- **Status Tracking**: Visual status indicators (Active, Retired, In Repair, etc.)
- **Warranty Management**: Automatic expiry tracking and alerts

### Booking System
- **User-Friendly Requests**: Simple asset borrowing interface
- **Period-Based Tracking**: Clear display of borrow periods vs request dates
- **Status Management**: Real-time status updates (Pending, Approved, Declined, Returned)
- **Activity Logging**: Complete audit trail of all booking activities

### Administrative Features
- **Request Processing**: Efficient approval/decline workflow
- **User Management**: Account creation and management
- **Alert Dashboard**: Proactive notifications for warranty expiry
- **Data Export**: Easy data management and reporting

---

## 📅 Version Information

**Current Version**: V1.1
**Last Updated**: July 2025 
**Major Features**: Enhanced device management, advanced search, improved UI/UX, booking system optimization

### Version History
- **v0.9.6**: Major UI/UX improvements, enhanced search functionality, optimized table layouts
- **v0.9.5**: Booking system improvements, status tracking enhancements
- **v0.9.0**: Initial release with dual portal system and automatic setup

---

*RAD - Making resource allocation simple, efficient, and transparent.*

**System Status**: Production Ready ✅  
**Maintenance**: Self-healing with automatic problem resolution  
**Support**: Comprehensive documentation and troubleshooting guides included