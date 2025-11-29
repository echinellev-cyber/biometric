<?php
session_start();

// Check if user is logged in, redirect to login if not
if (!isset($_SESSION['admin_id'])) {
    header("Location: /login/login.php");
    exit();
}

// Get current user's role
$current_user_role = $_SESSION['role'];
$current_user_id = $_SESSION['admin_id'];
require_once '../connection.php';

// Check if connection was established properly
if (!isset($conn) || !($conn instanceof PDO)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set JSON header for all responses
header('Content-Type: application/json');

// Handle view/edit actions
if (isset($_GET['action']) && ($_GET['action'] === 'view' || $_GET['action'] === 'edit') && isset($_GET['id'])) {
    try {
        $stmt = $conn->prepare("SELECT * FROM admin WHERE admin_id = ?");
        $stmt->execute([$_GET['id']]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$admin) {
            echo json_encode(['success' => false, 'error' => 'Admin not found']);
            exit;
        }
        
        $admin['id'] = $admin['admin_id'];
        echo json_encode(['success' => true, 'data' => $admin]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// Handle update action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_admin'])) {
    // ROLE CHECK: Only super_admin and admin can update accounts
    if ($current_user_role !== 'super_admin' && $current_user_role !== 'admin') {
        echo json_encode(['success' => false, 'error' => 'You don\'t have permission to update admin accounts']);
        exit;
    }
    
    $id = $_POST['id'];
    $fullName = $_POST['fullName'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $role = $_POST['role'];
    $department = $_POST['department'] ?? null;
    
    try {
        $stmt = $conn->prepare("UPDATE admin SET full_name = ?, email = ?, username = ?, role = ?, department = ? WHERE admin_id = ?");
        $stmt->execute([$fullName, $email, $username, $role, $department, $id]);
        
        ob_clean();
        echo json_encode(['success' => true, 'message' => 'Admin updated successfully']);
    } catch (PDOException $e) {
        ob_clean();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// Handle delete action with super admin protection
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    // ROLE CHECK: Only super_admin and admin can delete accounts
    if ($current_user_role !== 'super_admin' && $current_user_role !== 'admin') {
        echo json_encode(['success' => false, 'error' => 'You don\'t have permission to delete admin accounts']);
        exit;
    }
    
    try {
        $stmt = $conn->prepare("SELECT * FROM admin WHERE admin_id = ?");
        $stmt->execute([$_GET['id']]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($admin && $admin['role'] === 'super_admin') {
            echo json_encode(['success' => false, 'error' => 'Cannot delete super admin accounts']);
            exit;
        }
        
        // PREVENT SELF-DELETION: User cannot delete their own account
        if ($admin && $admin['admin_id'] == $current_user_id) {
            echo json_encode(['success' => false, 'error' => 'Cannot delete your own account']);
            exit;
        }
        
        $stmt = $conn->prepare("DELETE FROM admin WHERE admin_id = ?");
        $stmt->execute([$_GET['id']]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// Reset to HTML content type for the main page
header('Content-Type: text/html');

$success = '';
$error = '';

// Create admin table if not exists
try {
    $conn->query("SELECT 1 FROM admin LIMIT 1");
} catch (PDOException $e) {
   $createTableSQL = "CREATE TABLE IF NOT EXISTS admin (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NULL DEFAULT NULL,
    role ENUM('super_admin', 'admin', 'student_leader', 'sas_director', 'sas_adviser', 'chairperson') NOT NULL DEFAULT 'admin',
    department VARCHAR(100) NULL DEFAULT NULL,
    date_created DATETIME DEFAULT CURRENT_TIMESTAMP
)";
    
    $conn->exec($createTableSQL);
    
    $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $conn->exec("INSERT INTO admin (full_name, email, username, password, role) 
                VALUES ('System Admin', 'admin@aldersgate.edu.ph', 'admin', '$hashedPassword', 'super_admin')");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ROLE CHECK: Student leaders cannot create admin accounts
    if ($current_user_role === 'student_leader') {
        $error = json_encode(['message' => "You don't have permission to create admin accounts!", 'timeout' => 3000]);
    } else {
        $fullName = $_POST['fullName'] ?? '';
        $email = $_POST['email'] ?? '';
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'admin';
        $department = $_POST['department'] ?? '';
        
        if (empty($fullName) || empty($email) || empty($username) || empty($password)) {
            $error = json_encode(['message' => "All fields are required!", 'timeout' => 3000]);
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = json_encode(['message' => "Invalid email format!", 'timeout' => 3000]);
        } else {
            try {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO admin (full_name, email, username, password, role, department) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$fullName, $email, $username, $hashedPassword, $role, $department]);
                
                $_POST = [];
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $error = json_encode(['message' => "Username or email already exists!", 'timeout' => 3000]);
                } else {
                    $error = json_encode(['message' => "Error creating admin: " . $e->getMessage(), 'timeout' => 3000]);
                }
            }
        }
    }
}

// Fetch existing admins - Initialize as empty array first
$admins = [];

try {
    $stmt = $conn->query("SELECT * FROM admin ORDER BY date_created DESC");
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching admins: " . $e->getMessage();
    error_log($error);
    // Ensure $admins remains an empty array even if there's an error
    $admins = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ACI Biometric - Admin Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="icon" type="image/png" href="../images/logo.png">
    <link rel="apple-touch-icon" href="../images/logo.png">
    <link rel="shortcut icon" href="../images/logo.png">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            position: relative;
            background: radial-gradient(circle, white,rgb(243, 236, 117));
            min-height: 100vh;
            overflow-x: hidden;
            display: flex;
        }

        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url('../images/logo.png');
            background-size: 100%;
            background-repeat: no-repeat;
            background-position: center;
            opacity: 0.15;
            z-index: -1;
            pointer-events: none;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            background-color: #007836;
            color: white;
            min-height: 100vh;
            padding: 20px 0;
            transition: all 0.3s;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
        }

        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }

        .sidebar-header img {
            height: 50px;
            margin-right: 10px;
        }

        .sidebar-header h4 {
            color: #fff;
            font-size: 18px;
            margin-top: 0;
        }

        .sidebar-menu {
            list-style: none;
        }

        .sidebar-menu li {
            margin-bottom: 5px;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #ddd;
            text-decoration: none;
            transition: all 0.3s;
            font-size: 14px;
        }

        .sidebar-menu a:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .sidebar-menu a.active {
            background: linear-gradient(to right, #FFD700, #FFEA70) !important;
            color: #333;
            font-weight: 500;
        }

        .sidebar-menu i {
            margin-right: 10px;
            font-size: 16px;
            width: 20px;
            text-align: center;
        }

        /* Main Content Area */
        .main-content {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            margin-left: 250px;
            transition: margin-left 0.3s;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Header */
        .header {
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 15px;
        }

        .header h1 {
            color: #333;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .breadcrumb {
            color: #666;
            font-size: 12px;
            margin-top: 5px;
        }

        .logout-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgb(137, 172, 133);
            color: white;
            padding: 8px 15px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s;
            white-space: nowrap;
        }

        .logout-btn:hover {
            background: rgb(69, 156, 65);
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        /* Admin Form */
        .admin-form {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
        }

        .form-col {
            flex: 1;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
            color: #444;
        }

        .form-group input, 
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .role-selector-full {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            width: 100%;
            margin-top: 8px;
        }
        
        .role-option {
            flex: 1;
            min-width: 120px;
        }

        .role-badge {
            display: block;
            padding: 12px 15px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
            border: 2px solid transparent;
        }

        .role-badge.super {
            background: #007836;
            color: white;
        }

        .role-badge.admin {
            background: #007836;
            color: white;
        }
        
        .role-badge.student_leader {
            background: #007836;
            color: white;
        }
        
        .role-badge.sas_director {
            background: #007836;
            color: white;
        }
        
        .role-badge.sas_adviser {
            background: #007836;
            color: white;
        }

        .role-radio:checked + .role-badge {
            border-color: white;
            box-shadow: 0 0 0 2px #333;
            transform: translateY(-2px);
            background: linear-gradient(to right, #FFD700, #FFEA70);
            color: black;
        }
        
        .role-radio:checked + .role-badge.student_leader,
        .role-radio:checked + .role-badge.sas_director,
        .role-radio:checked + .role-badge.sas_adviser {
            border-color: white;
            box-shadow: 0 0 0 2px #333;
            transform: translateY(-2px);
            background: linear-gradient(to right, #FFD700, #FFEA70);
            color: black;
        }

        .role-badge:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        .role-radio {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .register-button {
            background: #007836;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            width: auto;
            min-width: 200px;
            float: right;
        }

        .register-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            background: linear-gradient(to right, #FFD700, #FFEA70);
            color: black;
        }

        /* Records Container */
        .records-container {
            background: #f9f9ff;
            border-radius: 12px;
            padding: 25px;
            border: 1px solid #e6e9f0;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .search-box {
            display: flex;
            align-items: center;
            background: white;
            border: 1px solid #d1d5e0;
            border-radius: 8px;
            padding: 5px 15px;
            width: 300px;
        }

        .search-box input {
            border: none;
            padding: 10px;
            width: 100%;
            font-size: 15px;
        }

        .search-box input:focus {
            outline: none;
        }

        /* Cards Container */
        .cards-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .admin-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border: 1px solid #e6e9f0;
            transition: all 0.3s ease;
        }

        .admin-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f0f0f0;
        }

        .admin-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #007836, #005a28);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
        }

        .admin-info h3 {
            color: #333;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .admin-id {
            color: #666;
            font-size: 12px;
            font-weight: 500;
        }

        .card-details {
            margin-bottom: 20px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            padding: 8px 0;
        }

        .detail-row:last-child {
            margin-bottom: 0;
        }

        .detail-row .label {
            color: #666;
            font-size: 14px;
            font-weight: 500;
            flex: 1;
        }

        .detail-row .value {
            color: #333;
            font-size: 14px;
            font-weight: 500;
            flex: 2;
            text-align: right;
        }

        /* Card Action Buttons */
        .card-actions {
            display: flex;
            gap: 10px;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        .card-actions .action-btn {
            flex: 1;
            min-width: 80px;
            padding: 10px 15px;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }

        .card-actions .view-btn {
            background: #4a6cf7;
            color: white;
        }

        .card-actions .edit-btn {
            background: #28a745;
            color: white;
        }

        .card-actions .delete-btn {
            background: #dc3545;
            color: white;
        }

        .card-actions .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        /* No Data State */
        .no-data {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }

        .no-data i {
            font-size: 64px;
            margin-bottom: 20px;
            color: #ccc;
        }

        .no-data p {
            font-size: 16px;
            margin: 0;
        }

        /* Role Badges */
        .role-super_admin {
            background: #f3e8ff;
            color: #6f42c1;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 500;
        }

        .role-admin {
            background: #e6ffed;
            color: #007836;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .role-student_leader {
            background: #fff3cd;
            color: #856404;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .role-sas_director {
            background: #d1ecf1;
            color: #0c5460;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .role-sas_adviser {
            background: #d4edda;
            color: #155724;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 500;
        }

        .department-badge {
            background: #e3f2fd;
            color: #1976d2;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal-content {
            background: white;
            padding: 20px;
            border-radius: 8px;
            width: 500px;
            max-width: 100%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .close-modal {
            float: right;
            cursor: pointer;
            font-size: 24px;
        }

        /* Notifications */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 5px;
            color: white;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 1000;
            transform: translateX(150%);
            animation: slideIn 0.5s forwards;
        }

        .notification.success {
            background: #007836;
        }

        .notification.error {
            background: #dc3545;
        }

        .notification i {
            font-size: 18px;
        }

        @keyframes slideIn {
            to { transform: translateX(0); }
        }

        .fade-out {
            animation: fadeOut 0.5s forwards;
        }

        @keyframes fadeOut {
            to { opacity: 0; transform: translateX(150%); }
        }

        @keyframes fadeOutCard {
            from { opacity: 1; transform: translateY(0); }
            to { opacity: 0; transform: translateY(-20px); }
        }

        .cancel-edit {
            background: #6c757d;
            margin-bottom: 10px;
        }

        .cancel-edit:hover {
            background: #5a6268;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
            clear: both;
            flex-wrap: wrap;
            gap: 10px;
        }

        .admin-subrole-container {
            margin-top: 15px;
            padding: 15px;
            background: rgba(0, 120, 54, 0.05);
            border-radius: 6px;
            border-left: 4px solid #007836;
        }

        .admin-subrole-container h4 {
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
            color: #444;
        }

        /* Mobile Menu Toggle */
        .mobile-menu-toggle {
            display: none;
            position: fixed;
            top: 15px;
            left: 15px;
            background: #007836;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px;
            font-size: 18px;
            cursor: pointer;
            z-index: 1001;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        /* Overlay for mobile menu */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
        
        .sidebar-overlay.active {
            display: block;
        }

        /* Mobile Responsiveness */
        @media (max-width: 1024px) {
            .sidebar {
                width: 220px;
            }
            
            .main-content {
                margin-left: 220px;
            }
        }

        @media (max-width: 768px) {
            body {
                flex-direction: column;
            }
            
            .mobile-menu-toggle {
                display: block;
            }
            
            .sidebar {
                width: 280px;
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
                padding: 70px 15px 20px;
            }
            
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .header h1 {
                font-size: 24px;
            }
            
            .admin-form {
                padding: 20px;
            }
            
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .section-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .search-box {
                width: 100%;
            }
            
            .role-selector-full {
                flex-direction: column;
            }
            
            .role-option {
                width: 100%;
            }

            .register-button {
                width: 100%;
                float: none;
                min-width: auto;
            }
            
            .form-actions {
                justify-content: stretch;
            }
            
            /* Mobile Cards */
            .cards-container {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .admin-card {
                padding: 15px;
            }
            
            .card-header {
                gap: 12px;
                margin-bottom: 15px;
            }
            
            .admin-avatar {
                width: 50px;
                height: 50px;
                font-size: 20px;
            }
            
            .admin-info h3 {
                font-size: 16px;
            }
            
            .card-actions {
                flex-direction: column;
                gap: 8px;
            }
            
            .card-actions .action-btn {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .main-content {
                padding: 60px 10px 15px;
            }
            
            .header h1 {
                font-size: 20px;
            }
            
            .admin-form {
                padding: 15px;
            }
            
            .form-group input, 
            .form-group select {
                padding: 8px 10px;
                font-size: 14px;
            }
            
            .role-badge {
                padding: 10px 12px;
                font-size: 13px;
            }
            
            .register-button {
                padding: 10px 15px;
                font-size: 14px;
            }
            
            .modal-content {
                padding: 15px;
            }
            
            .mobile-menu-toggle {
                top: 10px;
                left: 10px;
                padding: 8px;
            }
            
            .sidebar-header h4 {
                font-size: 16px;
            }
            
            .sidebar-menu a {
                padding: 10px 15px;
                font-size: 13px;
            }
            
            .cards-container {
                gap: 12px;
            }
            
            .admin-card {
                padding: 12px;
            }
            
            .detail-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
            
            .detail-row .value {
                text-align: left;
                width: 100%;
            }
        }

        .role-badge.chairperson {
            background: #007836;
            color: white;
        }

        .role-chairperson {
            background: #d6d4e0;
            color: #4a4453;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay"></div>

    <!-- Sidebar Navigation -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="../images/logo.png" alt="ACI Logo">
            <h4>ACI Admin Panel</h4>
        </div>

        <ul class="sidebar-menu">
            <li><a href="/admin/dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <?php if ($current_user_role !== 'student_leader'): ?>
            <li><a href="/admin/admin-management.php" class="active"><i class="fas fa-user-shield"></i> Admin Management</a></li>
            <?php endif; ?>
            <li><a href="#" onclick="launchBiometricApp(); return false;"><i class="fas fa-fingerprint"></i> Fingerprint Management</a></li>
            <li><a href="/admin/students.php"><i class="fas fa-users"></i> Student Management</a></li>
            <li><a href="/admin/events.php"><i class="fas fa-calendar-alt"></i> Events Management</a></li>            
            <li><a href="/admin/attendance.php"><i class="fas fa-clipboard-list"></i> Attendance Records</a></li>
            <li><a href="/admin/fines.php"><i class="fas fa-exclamation-circle"></i> Fines Management</a></li>
            <li><a href="/admin/reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
        </ul>
    </aside>

    <div class="main-content">
        <div class="container">
            <!-- Header -->
            <div class="header">
                <div>
                    <h1>Admin Management</h1>
                    <div class="breadcrumb">ACI Admin Panel » System » Admin Management</div>
                </div>
                <a href="/login/login.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>

            <!-- Admin Creation Form -->
            <?php if ($current_user_role !== 'student_leader'): ?>
            <div class="admin-form">
                <?php if (!empty($success)): ?>
                    <div class="notification success">
                        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($error)): ?>
                    <div class="notification error" data-timeout="<?= json_decode($error, true)['timeout'] ?? 3000 ?>">
                        <i class="fas fa-exclamation-circle"></i> <?= json_decode($error, true)['message'] ?>
                    </div>
                <?php endif; ?>

                <h2>Create New Admin</h2>
                <form id="adminForm" method="POST" action="admin-management.php">
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="fullName">Full Name*</label>
                                <input type="text" id="fullName" name="fullName" placeholder="Juan Dela Cruz" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email Address*</label>
                                <input type="email" id="email" name="email" placeholder="admin@aldersgate.edu.ph" required>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="username">Username*</label>
                                <input type="text" id="username" name="username" placeholder="Unique username" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Password*</label>
                                <input type="password" id="password" name="password" placeholder="password" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label>Admin Role*</label>
                                <div class="role-selector-full">
                                    <div class="role-option">
                                        <input type="radio" id="role_admin" name="role" value="admin" class="role-radio" checked>
                                        <label for="role_admin" class="role-badge admin">Admin</label>
                                    </div>
                                    <div class="role-option">
                                        <input type="radio" id="role_student_leader" name="role" value="student_leader" class="role-radio">
                                        <label for="role_student_leader" class="role-badge student_leader">Student Leader</label>
                                    </div>
                                </div>
                                
                                <!-- Admin Sub-Role Selection (shown only when Admin is selected) -->
                                <div id="adminSubRoleContainer" class="admin-subrole-container" style="display: none;">
                                    <h4>Select Admin Type:</h4>
                                    <div class="role-selector-full">
                                        <div class="role-option">
                                            <input type="radio" id="subrole_sas_director" name="admin_subrole" value="sas_director" class="role-radio">
                                            <label for="subrole_sas_director" class="role-badge sas_director">SAS Director</label>
                                        </div>
                                        <div class="role-option">
                                            <input type="radio" id="subrole_sas_adviser" name="admin_subrole" value="sas_adviser" class="role-radio">
                                            <label for="subrole_sas_adviser" class="role-badge sas_adviser">SAS Adviser</label>
                                        </div>
                                        <div class="role-option">
                                            <input type="radio" id="subrole_chairperson" name="admin_subrole" value="chairperson" class="role-radio">
                                            <label for="subrole_chairperson" class="role-badge chairperson">Chairperson</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="department">Department</label>
                               <select id="department" name="department" class="form-control">
                                    <option value="">Select Department</option>
                                    <option value="Arts, Science, Education & Information Technology">Arts, Science, Education & Information Technology</option>
                                    <option value="Business, Management & Accountancy">Business, Management & Accountancy</option>
                                    <option value="Criminology">Criminology</option>
                                    <option value="Engineering & Technology">Engineering & Technology</option>
                                    <option value="Medical Sciences">Medical Sciences</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="register-button">
                            <i class="fas fa-user-plus"></i> Create Admin Account
                        </button>
                    </div>
                </form>
            </div>
            <?php else: ?>
            <div class="notification info" style="background: #17a2b8; margin-bottom: 30px;">
                <i class="fas fa-info-circle"></i> You don't have permission to manage admin accounts.
            </div>
            <?php endif; ?>

            <!-- Admin Records Cards -->
            <div class="records-container">
                <div class="section-header">
                    <h2>Admin Accounts</h2>
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchAdmins" placeholder="Search admins...">
                    </div>
                </div>

                <div class="cards-container">
                    <?php if (empty($admins)): ?>
                    <div class="no-data">
                        <i class="fas fa-users"></i>
                        <p>No admin accounts found</p>
                    </div>
                    <?php else: ?>
                        <?php foreach ($admins as $admin): ?>
                        <div class="admin-card">
                            <div class="card-header">
                                <div class="admin-avatar">
                                    <i class="fas fa-user-shield"></i>
                                </div>
                                <div class="admin-info">
                                    <h3><?= htmlspecialchars($admin['full_name']) ?></h3>
                                    <p class="admin-id">ID: <?= htmlspecialchars($admin['admin_id']) ?></p>
                                </div>
                            </div>
                            
                            <div class="card-details">
                                <div class="detail-row">
                                    <span class="label">Username:</span>
                                    <span class="value"><?= htmlspecialchars($admin['username']) ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="label">Email:</span>
                                    <span class="value"><?= htmlspecialchars($admin['email']) ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="label">Role:</span>
                                    <span class="role-<?= $admin['role'] ?>">
                                        <?php 
                                        switch($admin['role']) {
                                            case 'super_admin': echo 'Super Admin'; break;
                                            case 'admin': echo 'Admin'; break;
                                            case 'student_leader': echo 'Student Leader'; break;
                                            case 'sas_director': echo 'SAS Director'; break;
                                            case 'sas_adviser': echo 'SAS Adviser'; break;
                                            case 'chairperson': echo 'Chairperson'; break;
                                            default: echo $admin['role']; break;
                                        }
                                        ?>
                                    </span>
                                </div>
                                <div class="detail-row">
                                    <span class="label">Department:</span>
                                    <span class="value">
                                        <?php if (!empty($admin['department'])): ?>
                                            <?= htmlspecialchars($admin['department']) ?>
                                        <?php else: ?>
                                            <span style="color: #999; font-style: italic;">Not assigned</span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>
                            
                            <?php if ($current_user_role !== 'student_leader'): ?>
                            <div class="card-actions">
                                <button class="action-btn view-btn" data-id="<?= $admin['admin_id'] ?>">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <button class="action-btn edit-btn" data-id="<?= $admin['admin_id'] ?>">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <?php if ($admin['role'] !== 'super_admin' && $admin['admin_id'] != $current_user_id): ?>
                                    <button class="action-btn delete-btn" data-id="<?= $admin['admin_id'] ?>">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- View/Edit Modal -->
    <div id="adminModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2 id="modalTitle">Admin Details</h2>
            <form id="editAdminForm">
                <input type="hidden" id="edit_id" name="id">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" id="edit_fullName" name="fullName" class="form-control">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="edit_email" name="email" class="form-control">
                </div>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" id="edit_username" name="username" class="form-control">
                </div>
                <div class="form-group">
                    <label>Department</label>
                    <select id="edit_department" name="department" class="form-control">
                        <option value="">Select Department</option>
                        <option value="Arts, Science, Education & Information Technology">Arts, Science, Education & Information Technology</option>
                        <option value="Business, Management & Accountancy">Business, Management & Accountancy</option>
                        <option value="Criminology">Criminology</option>
                        <option value="Engineering & Technology">Engineering & Technology</option>
                        <option value="Medical Sciences">Medical Sciences</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <div class="role-selector-full">
                        <div class="role-option">
                            <input type="radio" id="edit_role_super_admin" name="role" value="super_admin" class="role-radio">
                            <label for="edit_role_super_admin" class="role-badge super">Super Admin</label>
                        </div>
                        <div class="role-option">
                            <input type="radio" id="edit_role_admin" name="role" value="admin" class="role-radio">
                            <label for="edit_role_admin" class="role-badge admin">Admin</label>
                        </div>
                        <div class="role-option">
                            <input type="radio" id="edit_role_student_leader" name="role" value="student_leader" class="role-radio">
                            <label for="edit_role_student_leader" class="role-badge student_leader">Student Leader</label>
                        </div>
                        <div class="role-option">
                            <input type="radio" id="edit_role_sas_director" name="role" value="sas_director" class="role-radio">
                            <label for="edit_role_sas_director" class="role-badge sas_director">SAS Director</label>
                        </div>
                        <div class="role-option">
                            <input type="radio" id="edit_role_sas_adviser" name="role" value="sas_adviser" class="role-radio">
                            <label for="edit_role_sas_adviser" class="role-badge sas_adviser">SAS Adviser</label>
                        </div>
                    </div>
                </div>
                <div class="form-group" id="edit_actions" style="margin-top:20px; display:none; display: flex; justify-content: flex-end; gap: 10px; flex-wrap: wrap;">
                    <button type="button" class="register-button cancel-edit">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="register-button">
                        <i class="fas fa-save"></i> Update Admin
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Mobile menu functionality
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
            const sidebarOverlay = document.querySelector('.sidebar-overlay');
            const sidebar = document.querySelector('.sidebar');
            
            function toggleSidebar() {
                sidebar.classList.toggle('active');
                sidebarOverlay.classList.toggle('active');
                document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
            }

            mobileMenuToggle.addEventListener('click', toggleSidebar);
            sidebarOverlay.addEventListener('click', toggleSidebar);

            // Close sidebar when clicking on a menu item (on mobile)
            if (window.innerWidth <= 768) {
                const menuItems = document.querySelectorAll('.sidebar-menu a');
                menuItems.forEach(item => {
                    item.addEventListener('click', toggleSidebar);
                });
            }

            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    sidebar.classList.remove('active');
                    sidebarOverlay.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });

            // Role Selection
            document.getElementById('role_admin').checked = true;
            toggleAdminSubRole(true);
            
            const roleRadios = document.querySelectorAll('input[name="role"]');
            roleRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.checked) {
                        document.querySelectorAll('.role-badge').forEach(badge => {
                            badge.style.opacity = '0.7';
                        });
                        this.nextElementSibling.style.opacity = '1';
                        
                        // Show/hide admin sub-role selection
                        toggleAdminSubRole(this.value === 'admin');
                    }
                });
            });

            // Auto-hide notifications
            document.querySelectorAll('.notification').forEach(notification => {
                const timeout = notification.dataset.timeout || 3000;
                setTimeout(() => {
                    notification.classList.add('fade-out');
                    setTimeout(() => notification.remove(), 500);
                }, timeout);
            });
        });

        // Function to toggle admin sub-role visibility
        function toggleAdminSubRole(show) {
            const adminSubRoleContainer = document.getElementById('adminSubRoleContainer');
            if (show) {
                adminSubRoleContainer.style.display = 'block';
            } else {
                adminSubRoleContainer.style.display = 'none';
            }
        }

        // Form Submission with validation - CREATE ADMIN
        document.getElementById('adminForm').addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent default form submission
            
            const fullName = document.getElementById('fullName').value;
            const email = document.getElementById('email').value;
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const selectedRole = document.querySelector('input[name="role"]:checked').value;
            const department = document.getElementById('department').value;
            
            if (!fullName || !email || !username || !password) {
                showNotification('Please fill all required fields', 'error');
                return;
            }

            // For admin role, check if a sub-role is selected
            if (selectedRole === 'admin') {
                const adminSubRole = document.querySelector('input[name="admin_subrole"]:checked');
                if (!adminSubRole) {
                    showNotification('Please select an admin type', 'error');
                    return;
                }
            }

            // Submit the form via AJAX
            const formData = new FormData(this);
            
            // Add the actual role value (for admin, use the sub-role value)
            if (selectedRole === 'admin') {
                const adminSubRole = document.querySelector('input[name="admin_subrole"]:checked').value;
                formData.set('role', adminSubRole);
            }
            
            fetch('admin-management.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                // Check if response is JSON
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    return response.json();
                }
                return response.text();
            })
            .then(data => {
                if (typeof data === 'object' && data.message) {
                    // JSON response (from successful creation)
                    showNotification(data.message, 'success');
                    this.reset();
                    // Reset to default role selection
                    document.getElementById('role_admin').checked = true;
                    toggleAdminSubRole(true);
                    setTimeout(() => location.reload(), 1000); // Refresh to show new admin
                } else {
                    // Regular HTML response (page reload)
                    window.location.reload();
                }
            })
            .catch(error => {
                showNotification('Error creating admin: ' + error.message, 'error');
                console.error('Error:', error);
            });
        });

        // View functionality
        document.addEventListener('click', function(e) {
            if (e.target.closest('.view-btn')) {
                const adminId = e.target.closest('.view-btn').getAttribute('data-id');
                fetchAdminData(adminId, false);
            }
            
            // Edit functionality
            if (e.target.closest('.edit-btn')) {
                const adminId = e.target.closest('.edit-btn').getAttribute('data-id');
                fetchAdminData(adminId, true);
            }
            
            // Delete functionality
            if (e.target.closest('.delete-btn')) {
                const adminId = e.target.closest('.delete-btn').getAttribute('data-id');
                const card = e.target.closest('.admin-card');
                
                // Get the role from the role badge
                const roleBadge = card.querySelector('.role-badge');
                const isSuperAdmin = roleBadge.textContent.trim() === 'Super Admin';
                
                if (isSuperAdmin) {
                    showNotification('Cannot delete super admin accounts', 'error');
                    return;
                }
                
                if (confirm('Are you sure you want to delete this admin?')) {
                    fetch('admin-management.php?action=delete&id=' + adminId, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json'
                        }
                    })
                    .then(handleFetchResponse)
                    .then(data => {
                        if (data.success) {
                            card.style.animation = 'fadeOutCard 0.3s ease';
                            setTimeout(() => {
                                card.remove();
                                showNotification('Admin deleted successfully', 'success');
                            }, 300);
                        } else {
                            showNotification('Error: ' + (data.error || 'Unknown error'), 'error');
                        }
                    })
                    .catch(error => {
                        showNotification('Delete failed: ' + error.message, 'error');
                        console.error('Error:', error);
                    });
                }
            }
            
            // Close modal
            if (e.target.classList.contains('close-modal') || e.target.classList.contains('cancel-edit')) {
                document.getElementById('adminModal').style.display = 'none';
            }
        });

        // Edit form submission - UPDATE ADMIN
        document.getElementById('editAdminForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('update_admin', 'true');
            
            fetch('admin-management.php', {
                method: 'POST',
                body: formData
            })
            .then(handleFetchResponse)
            .then(data => {
                if (data.success) {
                    showNotification('Admin updated successfully', 'success');
                    document.getElementById('adminModal').style.display = 'none';
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showNotification('Error: ' + (data.error || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                showNotification('Update failed: ' + error.message, 'error');
                console.error('Error:', error);
            });
        });

        // Helper function to fetch admin data
        function fetchAdminData(adminId, isEdit) {
            const action = isEdit ? 'edit' : 'view';
            fetch('admin-management.php?action=' + action + '&id=' + adminId)
                .then(handleFetchResponse)
                .then(data => {
                    if (data.success && data.data) {
                        document.getElementById('edit_id').value = data.data.admin_id || data.data.id;
                        document.getElementById('edit_fullName').value = data.data.full_name || '';
                        document.getElementById('edit_email').value = data.data.email || '';
                        document.getElementById('edit_username').value = data.data.username || '';
                        document.getElementById('edit_department').value = data.data.department || '';
                        
                        // Set the appropriate role radio button as checked
                        const roleRadios = {
                            'super_admin': document.getElementById('edit_role_super_admin'),
                            'admin': document.getElementById('edit_role_admin'),
                            'student_leader': document.getElementById('edit_role_student_leader'),
                            'sas_director': document.getElementById('edit_role_sas_director'),
                            'sas_adviser': document.getElementById('edit_role_sas_adviser')
                        };
                        
                        // Uncheck all first
                        Object.values(roleRadios).forEach(radio => {
                            if (radio) radio.checked = false;
                        });
                        
                        // Check the appropriate one
                        if (roleRadios[data.data.role]) {
                            roleRadios[data.data.role].checked = true;
                        } else {
                            // Default to admin if role not found
                            roleRadios['admin'].checked = true;
                        }
                        
                        // Update visual states
                        document.querySelectorAll('.role-badge').forEach(badge => {
                            badge.style.opacity = '0.7';
                        });
                        
                        const checkedRadio = document.querySelector('input[name="role"]:checked');
                        if (checkedRadio && checkedRadio.nextElementSibling) {
                            checkedRadio.nextElementSibling.style.opacity = '1';
                        }
                        
                        document.getElementById('modalTitle').textContent = isEdit ? 'Edit Admin' : 'Admin Details';
                        
                        const inputs = document.querySelectorAll('#editAdminForm input, #editAdminForm select');
                        inputs.forEach(input => {
                            input.readOnly = !isEdit;
                            input.disabled = !isEdit;
                        });
                        
                        document.getElementById('edit_actions').style.display = isEdit ? 'flex' : 'none';
                        document.getElementById('adminModal').style.display = 'flex';
                    } else {
                        showNotification('Failed to load admin data', 'error');
                    }
                })
                .catch(error => {
                    showNotification('Error: ' + error.message, 'error');
                    console.error('Error:', error);
                });
        }

        // Helper function to handle fetch responses
        function handleFetchResponse(response) {
            const contentType = response.headers.get('content-type');
            if (!contentType && !contentType.includes('application/json')) {
                return response.text().then(text => {
                    throw new Error('Expected JSON, got: ' + text);
                });
            }
            return response.json();
        }

        // Function to show notification
        function showNotification(message, type, timeout = 3000) {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                ${message}
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.classList.add('fade-out');
                setTimeout(() => notification.remove(), 500);
            }, timeout);
        }

        function launchBiometricApp() {
            const url = 'biometricapp://open?screen=fingerprint_registration';
            try {
                window.location.href = url;
                setTimeout(() => {
                    if (document.visibilityState === 'visible') {
                        // alert('If the app did not open, install/enable the biometric app protocol handler.');
                    }
                }, 1500);
            } catch (e) {
                alert('Unable to launch the desktop app.');
            }
        }
    </script>
</body>
</html>