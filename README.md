# DigiApp Services - Employee Leave Management System

DigiApp Services is a comprehensive PHP-based web application for employee leave management, human resources administration, and time-attendance tracking. The application provides two distinct portals: an **Employee Portal** for self-service operations and an **Admin Portal** for full HR management.

---

## Architecture

The application is built with **PHP 7+/8+**, **MySQL/MariaDB**, **PDO**, and uses **Materialize CSS** for the frontend. It follows a classic LAMP architecture with session-based authentication and no external framework dependencies.

### Directory Structure

```
digiapp/
├── index.php                          # Employee login
├── forgot-password.php                # Employee password reset
├── apply-leave.php                    # Employee leave application
├── leavehistory.php                   # Employee leave history
├── messages.php                       # Employee inbox
├── chatwith-admin.php                 # Employee-admin chat
├── myprofile.php                      # Employee profile
├── emp-changepassword.php             # Employee change password
├── logout.php                         # Employee logout
├── elms.sql                           # Database schema
├── includes/
│   ├── config.php                     # Root DB connection (PDO)
│   ├── header.php                     # Employee layout header
│   └── sidebar.php                    # Employee sidebar navigation
├── assets/                            # CSS, JS, images, plugins
│   ├── css/                           # Modern.css, custom.css, alpha.css, etc.
│   ├── js/                            # JavaScript files
│   ├── plugins/                       # jQuery plugins (flot, inputmask, etc.)
│   └── images/                        # UI images
├── admin/
│   ├── index.php                      # Admin login
│   ├── dashboard.php                  # Admin dashboard
│   ├── logout.php                     # Admin logout
│   ├── changepassword.php             # Admin change password
│   ├── addemployee.php                # Create employee
│   ├── manageemployee.php             # List/activate/deactivate employees
│   ├── editemployee.php               # Edit employee profile
│   ├── adddepartment.php              # Create department
│   ├── managedepartments.php          # List/delete departments
│   ├── editdepartment.php             # Edit department
│   ├── addleavetype.php               # Create leave type
│   ├── manageleavetype.php            # List/delete leave types
│   ├── editleavetype.php              # Edit leave type
│   ├── leaves.php                     # All leave requests
│   ├── pending-leavehistory.php       # Pending leave requests
│   ├── approvedleave-history.php      # Approved leave requests
│   ├── notapproved-leaves.php         # Rejected leave requests
│   ├── leave-details.php              # Leave approval/rejection
│   ├── chatwith-employee.php          # Admin send message to employee
│   ├── manage-messages.php            # Admin message history
│   ├── add-advance.php                # Record salary advance
│   ├── manage-advances.php            # List/delete salary advances
│   ├── check_availability.php         # AJAX email availability check
│   ├── includes/
│   │   ├── config.php                 # Admin DB connection (PDO)
│   │   ├── header.php                 # Admin layout header
│   │   └── sidebar.php                # Admin sidebar navigation
│   └── code-pointeuse/                # ZKTeco time-attendance module
│       ├── index.php                  # Attendance dashboard
│       ├── dashboard.php              # Analytics with Chart.js
│       ├── sync.php                   # Device sync logic
│       ├── devices.php                # Device config
│       ├── api.php                    # API endpoints
│       ├── dashboard-api.php          # Dashboard data API
│       ├── manage.php                 # Device management
│       └── zkteco/                    # ZKTeco PHP SDK
└── translations.txt                    # Translation strings
```

---

## Database Schema

The application uses a single MySQL/MariaB database named **`elms`** with the following tables:

### Table: `admin`
Stores administrator credentials.

| Column | Type | Description |
|---|---|---|
| `id` | int(11) PK, AI | Primary key |
| `UserName` | varchar(100) | Admin username |
| `Password` | varchar(100) | Password hash |
| `updationDate` | timestamp | Last update date |

### Table: `tbldepartments`
Company departments.

| Column | Type | Description |
|---|---|---|
| `id` | int(11) PK, AI | Primary key |
| `DepartmentName` | varchar(150) | Full department name |
| `DepartmentShortName` | varchar(100) | Abbreviation |
| `DepartmentCode` | varchar(50) | Department code |
| `CreationDate` | timestamp | Creation date |

### Table: `tblemployees`
Employee accounts and personal information.

| Column | Type | Description |
|---|---|---|
| `id` | int(11) PK, AI | Primary key |
| `EmpId` | varchar(100) | Employee ID |
| `FirstName` | varchar(150) | First name |
| `LastName` | varchar(150) | Last name |
| `EmailId` | varchar(200) | Login username / email |
| `Password` | varchar(180) | Password hash |
| `Gender` | varchar(100) | Gender |
| `Dob` | varchar(100) | Date of birth |
| `Department` | varchar(255) | Department name |
| `Address` | varchar(255) | Address |
| `City` | varchar(200) | City |
| `Country` | varchar(150) | Country |
| `Phonenumber` | char(11) | Phone number |
| `Status` | int(1) | 1 = Active, 0 = Inactive |
| `RegDate` | timestamp | Registration date |

### Table: `tblleavetype`
Available leave types.

| Column | Type | Description |
|---|---|---|
| `id` | int(11) PK, AI | Primary key |
| `LeaveType` | varchar(200) | Leave type name |
| `Description` | mediumtext | Description |
| `CreationDate` | timestamp | Creation date |

### Table: `tblleaves`
Leave requests.

| Column | Type | Description |
|---|---|---|
| `id` | int(11) PK, AI | Primary key |
| `LeaveType` | varchar(110) | Type of leave |
| `ToDate` | varchar(120) | End date |
| `FromDate` | varchar(120) | Start date |
| `Description` | mediumtext | Reason for leave |
| `PostingDate` | timestamp | Request date |
| `AdminRemark` | mediumtext | Admin approval/rejection note |
| `AdminRemarkDate` | varchar(120) | Remark date |
| `Status` | int(1) | 0 = Pending, 1 = Approved, 2 = Rejected |
| `IsRead` | int(1) | 0 = Unread, 1 = Read |
| `empid` | int(11) FK | References tblemployees.id |

### Table: `tblmessages` (runtime-created)
Internal messaging between admin and employees.

| Column | Type | Description |
|---|---|---|
| `id` | int(11) PK, AI | Primary key |
| `emp_id` | int(11) | Employee recipient |
| `message` | text | Message content |
| `sender` | varchar(50) | "Employe" or "Admin" |
| `posting_date` | timestamp | Message date |
| `is_read` | int(1) | 0 = unread, 1 = read |

### Table: `tblsalaryadvances` (runtime-created)
Salary advance records.

| Column | Type | Description |
|---|---|---|
| `id` | int(11) PK, AI | Primary key |
| `emp_id` | int(11) | Employee FK |
| `amount` | decimal | Advance amount |
| `advance_month` | varchar(7) | Month (YYYY-MM) |
| `reason` | text | Justification |
| `posting_date` | timestamp | Record date |

---

## Application Modules

### 1. Authentication Module

#### Employee Login (`index.php`)
- Accepts **email OR EmpId** as username
- Validates password with bcrypt (primary) or MD5 (legacy fallback)
- Checks account active status (`Status = 1`)
- Session variables: `$_SESSION['emplogin']` and `$_SESSION['eid']`

#### Admin Login (`admin/index.php`)
- Username/password against `admin` table
- Session variable: `$_SESSION['alogin']`

#### Password Reset (`forgot-password.php`)
- Employee self-service reset requiring **email + EmpId** verification
- No email verification - database identity check only

#### Change Password (`emp-changepassword.php` / `admin/changepassword.php`)
- Requires current password verification
- Supports bcrypt and legacy MD5

### 2. Employee Profile Module (`myprofile.php`)

Employees can view and edit their personal information:
- **Read-only fields:** EmpId, EmailId
- **Editable fields:** FirstName, LastName, Gender, Dob, Department (dropdown), Address, City, Country, Phonenumber

### 3. Employee Management Module (Admin)

| File | Functionality |
|---|---|
| `addemployee.php` | Create new employee with full details, password hashing, AJAX email check |
| `manageemployee.php` | List all employees, activate/deactivate accounts |
| `editemployee.php` | Edit any employee profile (readonly EmpId/Email) |

### 4. Department Management Module (Admin)

| File | Functionality |
|---|---|
| `adddepartment.php` | Create department (name, short name, code) |
| `managedepartments.php` | List all departments, delete with confirmation |
| `editdepartment.php` | Edit department details |

### 5. Leave Type Management Module (Admin)

| File | Functionality |
|---|---|
| `addleavetype.php` | Create leave type with description |
| `manageleavetype.php` | List all leave types, delete |
| `editleavetype.php` | Edit leave type |

### 6. Leave Management Module

#### Employee Side
| File | Functionality |
|---|---|
| `apply-leave.php` | Submit leave request (type, dates, description). Validates date range. |
| `leavehistory.php` | View all own leave requests with status badges (Approved/Declined/Waiting) |

#### Admin Side
| File | Functionality |
|---|---|
| `leaves.php` | View all leave requests globally |
| `pending-leavehistory.php` | Filter: pending leaves only |
| `approvedleave-history.php` | Filter: approved leaves only |
| `notapproved-leaves.php` | Filter: rejected leaves only |
| `leave-details.php` | View details, approve/reject with remarks, mark as read |

### 7. Messaging Module

| File | Functionality |
|---|---|
| `messages.php` (Employee) | Inbox with read/unread status, mark as read |
| `chatwith-admin.php` (Employee) | Chat interface with admin, message history, auto-scroll |
| `chatwith-employee.php` (Admin) | Send message to any active employee |
| `manage-messages.php` (Admin) | Message history with filters (employee, date range), delete messages |

### 8. Salary Advances Module (Admin)

| File | Functionality |
|---|---|
| `add-advance.php` | Record salary advance for employee (amount, month, reason) |
| `manage-advances.php` | View advances by month filter, see monthly totals, delete records |

### 9. Dashboard Module (Admin)

The admin dashboard (`dashboard.php`) displays:
- **Statistics:** Total Employees, Total Departments, Total Leave Types, Pending Leaves
- **Leave Analytics:** Approved, Pending, Rejected, Total request counts
- **Recent Requests:** Last 5 leave requests with employee details and status

### 10. Time Attendance Module (ZKTeco Integration)

Located in `admin/code-pointeuse/`, this sub-module integrates with ZKTeco biometric time-clocks:

| File | Functionality |
|---|---|
| `index.php` | Attendance dashboard with KPIs and filters |
| `dashboard.php` | Advanced analytics (Chart.js): presence rate, absenteeism, overtime, anomalies, punctuality, legal compliance, seasonal analysis, department comparison, burnout risks |
| `sync.php` | Synchronization with ZKTeco devices |
| `devices.php` | Device configuration loader |
| `api.php` | API endpoints for attendance data |
| `dashboard-api.php` | Data provider for analytics |
| `manage.php` | Device management settings |
| `zkteco/` | Full ZKTeco PHP SDK library |

---

## Application Flow

### Authentication Flow

```
Employee:
  index.php -> Validate (email/EmpId + password) -> Session created -> myprofile.php
  Protected pages check: strlen($_SESSION['emplogin']) == 0 ? redirect to index.php

Admin:
  admin/index.php -> Validate (username + password) -> Session created -> dashboard.php
  Protected pages check: strlen($_SESSION['alogin']) == 0 ? redirect to index.php
```

### Leave Request Flow

```
Employee:
  apply-leave.php -> Submit (type, dates, description) -> Status=0, IsRead=0
  -> leavehistory.php (view own requests)

Admin:
  Notification bell shows unread leaves count
  pending-leavehistory.php -> leave-details.php
  -> Approve (Status=1) or Reject (Status=2) with AdminRemark
  -> leavehistory.php (employee sees updated status)
```

### Messaging Flow

```
Employee -> chatwith-admin.php -> Insert message (sender="Employe")
Admin -> chatwith-employee.php -> Insert message (sender="Admin")
Both -> messages.php (inbox with read/unread status)
```

---

## Installation Guide

### Prerequisites

- **PHP** 7.4 or higher
- **MySQL** 5.7+ or **MariaDB** 10.2+
- **Apache** with mod_rewrite (or Nginx)
- **WAMP / XAMPP / LAMP** stack recommended for local development

### Step 1: Clone or Copy Files

Place the application in your web server root:
```
C:\wamp64\www\digiapp\     (Windows/WAMP)
/var/www/html/digiapp/      (Linux/LAMP)
```

### Step 2: Create Database

1. Start MySQL/MariaDB service
2. Import the database schema:
   ```bash
   mysql -u root -p < elms.sql
   ```
   Or via phpMyAdmin: create database `elms`, then import `elms.sql`

### Step 3: Configure Database Connection

The application uses two separate PDO connection files:

**Employee-side:** `includes/config.php`
```php
<?php
error_reporting(0);
session_start();
$dbh = new PDO('mysql:dbname=elms;host=localhost;charset=utf8mb4', 'root', '');
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
?>
```

**Admin-side:** `admin/includes/config.php`
```php
<?php
error_reporting(0);
session_start();
$dbh = new PDO('mysql:dbname=elms;host=localhost;charset=utf8', 'root', '');
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
?>
```

Update the database credentials (host, username, password) in both files to match your MySQL configuration.

### Step 4: Verify File Permissions

Ensure the web server has read access to all files. No special write permissions are required as the application creates necessary tables at runtime (`tblmessages`, `tblsalaryadvances`).

### Step 5: Access the Application

- **Employee Portal:** `http://localhost/digiapp/index.php`
- **Admin Portal:** `http://localhost/digiapp/admin/index.php`

### Default Credentials

| Role | Username | Password |
|---|---|---|
| Admin | `admin` | Check `elms.sql` seed data for MD5 hash |

> Note: The default admin password is stored as an MD5 hash in the database. Change it immediately after first login.

### Step 6: Configure ZKTeco Time-Attendance (Optional)

The ZKTeco module in `admin/code-pointeuse/` requires:
- ZKTeco biometric devices on the same network
- Device IP/port configuration in `config/devices.json`
- PHP COM extension (Windows) or equivalent for device communication

---

## Available Options and Features

### Employee Portal Options

| Feature | Access |
|---|---|
| View/Edit Profile | `myprofile.php` |
| Change Password | `emp-changepassword.php` |
| Forgot Password | `forgot-password.php` |
| Apply for Leave | `apply-leave.php` |
| View Leave History | `leavehistory.php` |
| Contact Admin | `chatwith-admin.php` |
| View Messages | `messages.php` |
| Logout | `logout.php` |

### Admin Portal Options

| Feature | Access |
|---|---|
| Dashboard Statistics | `admin/dashboard.php` |
| Add Employee | `admin/addemployee.php` |
| Manage Employees | `admin/manageemployee.php` |
| Edit Employee | `admin/editemployee.php` |
| Add Department | `admin/adddepartment.php` |
| Manage Departments | `admin/managedepartments.php` |
| Edit Department | `admin/editdepartment.php` |
| Add Leave Type | `admin/addleavetype.php` |
| Manage Leave Types | `admin/manageleavetype.php` |
| Edit Leave Type | `admin/editleavetype.php` |
| View All Leaves | `admin/leaves.php` |
| Pending Leaves | `admin/pending-leavehistory.php` |
| Approved Leaves | `admin/approvedleave-history.php` |
| Rejected Leaves | `admin/notapproved-leaves.php` |
| Leave Details/Approval | `admin/leave-details.php` |
| Send Message to Employee | `admin/chatwith-employee.php` |
| Manage Messages | `admin/manage-messages.php` |
| Add Salary Advance | `admin/add-advance.php` |
| Manage Salary Advances | `admin/manage-advances.php` |
| Change Password | `admin/changepassword.php` |
| Time-Attendance Dashboard | `admin/code-pointeuse/dashboard.php` |
| Attendance Sync | `admin/code-pointeuse/sync.php` |
| Device Management | `admin/code-pointeuse/manage.php` |
| Logout | `admin/logout.php` |

---

## Technical Notes

- **Authentication:** Session-based with dual login support (email/EmpId for employees)
- **Password Security:** New passwords use `password_hash()` (bcrypt). Legacy MD5 fallback exists for backward compatibility.
- **Database:** PDO with prepared statements for SQL injection protection
- **Frontend:** Materialize CSS framework with jQuery
- **Reporting:** Chart.js for analytics dashboards
- **CSRF Protection:** Not implemented
- **Role-Based Access:** Two roles only - Employee (`emplogin`) and Admin (`alogin`)

---

## Troubleshooting

- **Login issues:** Verify database credentials in both `includes/config.php` and `admin/includes/config.php`
- **Blank pages:** Enable error reporting by removing `error_reporting(0)` temporarily
- **ZKTeco module not working:** Ensure PHP COM extension is installed and devices are network-accessible
- **Table not found errors:** The application auto-creates `tblmessages` and `tblsalaryadvances` at runtime
