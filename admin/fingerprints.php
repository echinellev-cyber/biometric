<?php
// Include your existing connection file
require_once '../connection.php';

// Initialize error and success messages
$error_message = '';
$success_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['register_student'])) {
        // Get form data
        $uid = $_POST['uid'] ?? '';
        $student_name = $_POST['student_name'] ?? '';
        $course = $_POST['course'] ?? '';
        $year_level = $_POST['year_level'] ?? '';
        $fingerprint_data = $_POST['fingerprint_data'] ?? '';
        
        try {
            // Validate required fields
            if (empty($uid) || empty($student_name) || empty($course) || empty($year_level) || empty($fingerprint_data)) {
                throw new Exception("All fields are required");
            }
            
            // Check if UID already exists
            $check_stmt = $conn->prepare("SELECT id FROM register_student WHERE uid = ?");
            $check_stmt->execute([$uid]);
            
            if ($check_stmt->fetch()) {
                throw new Exception("UID already exists");
            }
            
            // Insert into database
            $stmt = $conn->prepare("INSERT INTO register_student (uid, student_name, course, year_level, fingerprint_data) 
                                  VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$uid, $student_name, $course, $year_level, $fingerprint_data]);
            
            $success_message = "Student registered successfully!";
        } catch(PDOException $e) {
            $error_message = "Database Error: " . $e->getMessage();
        } catch(Exception $e) {
            $error_message = $e->getMessage();
        }
    }
}

// Fetch all registered students
$students = [];
try {
    $stmt = $conn->query("SELECT * FROM register_student ORDER BY registration_date DESC");
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error_message = "Error fetching students: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ACI Biometric Attendance System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../images/logo.png">
    <link rel="apple-touch-icon" href="../images/logo.png">
    <link rel="shortcut icon" href="../images/logo.png">

    <!-- Styles & Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100..900&display=swap" rel="stylesheet">
    <style>
        /* Your existing CSS styles remain unchanged */
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

        /* Watermark Background */
        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 220px;
            right: 0;
            bottom: 0;
            background-image: url('/biometric/images/logo.png');           
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
        }

        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid #444;
            margin-bottom: 20px;
        }

        .sidebar-header h3 {
            color: #fff;
            font-size: 20px;
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
        }

        .sidebar-menu a:hover {
            background-color: #444;
            color: white;
        }

        .sidebar-menu a.active {
            background: linear-gradient(to right, #FFD700, #FFEA70) !important; /* golden yellow to light yellow */
            color: #333;
        }

        .sidebar-menu i {
            margin-right: 10px;
            font-size: 18px;
        }

        /* Main Content Area */
        .main-content {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
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
            align-items: center;
        }

        .header h1 {
            color: #333;
            font-size: 28px;
            font-weight: 600;
        }

        .breadcrumb {
            color: #666;
            font-size: 12px;
            margin-top: 5px;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        /* Terms Section */
        .terms {
            margin-bottom: 30px;
        }

        .terms h2 {
            font-size: 20px;
            color: #444;
            margin-bottom: 10px;
            font-weight: 500;
        }

        .terms-list {
            list-style-type: none;
            display: flex;
            gap: 15px;
        }

        .terms-list li {
            background-color: rgba(255, 255, 255, 0.7);
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 14px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        /* Dashboard Cards */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: rgba(255, 255, 255, 0.2); /* Semi-transparent white */
            backdrop-filter: blur(5px); /* Creates the glass blur effect */
            -webkit-backdrop-filter: blur(10px); /* For Safari support */
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3); /* Subtle glass border */
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        /* Optional: Adds extra depth to the glass effect */
        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(
                135deg,
                rgba(255, 255, 255, 0.4) 0%,
                rgba(255, 255, 255, 0.1) 100%
            );
            border-radius: 10px;
            z-index: -1;
        }

        /* Hover effect */
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            background: rgba(255, 255, 255, 0.3);
        }

        .card h3 {
            font-size: 16px;
            color: #555;
            margin-bottom: 15px;
            font-weight: 500;
        }

        .card .value {
            font-size: 24px;
            font-weight: 600;
            color: #333;
        }

        /* Example Section */
        .example {
            background-color: rgba(255, 255, 255, 0.8);
            padding: 15px;
            border-radius: 8px;
            margin-top: 30px;
            font-size: 14px;
            color: #666;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        hr {
            border: none;
            height: 1px;
            background-color: #ddd;
            margin: 20px 0;
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            body {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                min-height: auto;
                padding: 10px 0;
            }
            
            .sidebar-menu {
                display: flex;
                overflow-x: auto;
                padding-bottom: 10px;
            }
            
            .sidebar-menu li {
                margin-bottom: 0;
                flex-shrink: 0;
            }
            
            .sidebar-menu a {
                padding: 10px 15px;
            }
        }

        .admin-button {
            background: #007836;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 500;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.2s;
        }
        
        .admin-button:hover {
            background: linear-gradient(to right, #FFD700, #FFEA70) ;
            color: #333;
        }
            
        /* Main Content */
        .app-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .app-description {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        /* Registration Module */
        .registration-module {
            display: flex;
            gap: 40px;
            margin-bottom: 30px;
        }
        
        .form-section, .fingerprint-section {
            flex: 1;
            background: rgba(255, 255, 255, 0.2); /* Semi-transparent background */
            backdrop-filter: blur(10px); /* Glass blur effect */
            -webkit-backdrop-filter: blur(10px); /* Safari support */
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3); /* Light glass border */
            position: relative;
            overflow: hidden;
            width: 450px;
            transition: all 0.3s ease;
        }

        /* Optional: Adds extra depth to the glass effect */
        .form-section::before, .fingerprint-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(
                135deg,
                rgba(255, 255, 255, 0.3) 0%,
                rgba(255, 255, 255, 0.1) 100%
            );
            border-radius: 8px;
            z-index: -1;
        }

        /* Hover effect to enhance the glass appearance */
        .form-section:hover, .fingerprint-section:hover {
            background: rgba(255, 255, 255, 0.25);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        }

        /* Adjust inner elements for better visibility on glass background */
        .section-title {
            color: #333;
            text-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }

        .form-group label {
            color: #444;
            text-shadow: 0 1px 1px rgba(255,255,255,0.5);
        }

        .status-message {
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(5px);
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #333;
        }
        
        /* Form Styles */
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
        
        .form-group input::placeholder {
            color: #aaa;
        }
        
        .clear-button {
            background: #f0f0f0;
            color: #333;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            font-weight: 500;
            cursor: pointer;
            margin-top: 10px;
        }
        
        /* Fingerprint Section */
        .fingerprint-scanner {
            background: #f9f9f9;
            border: 1px dashed #ccc;
            border-radius: 8px;
            height: 180px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            margin-bottom: 20px;
            position: relative;
        }
        
        .scanner-icon {
            width: 60px;
            height: 60px;
            color: #3b82f6;
            margin-bottom: 15px;
        }
        
        .scanner-instruction {
            color: #666;
            font-size: 14px;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .capture-button {
            background: #007836;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            font-weight: 500;
            cursor: pointer;
            width: 100%;
            margin-bottom: 10px;
        }

        .capture-button:hover {
            background: linear-gradient(to right, #FFD700, #FFEA70) ;
            color: black;
        }
        
        .retry-button {
            background: #f0f0f0;
            color: #333;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            font-weight: 500;
            cursor: pointer;
            width: 100%;
        }
       
        .retry-button:hover {
            background:  #6c757d;
            color: white;
        }
        
        /* Status Section */
        .status-section {
            margin-top: 20px;
        }
        
        .status-title {
            font-weight: 500;
            margin-bottom: 10px;
        }
        
        .status-message {
            background: #fff8e1;
            border: 1px solid #ffe0b2;
            padding: 12px;
            border-radius: 4px;
            font-size: 14px;
            color: #666;
        }
        
        .register-button {
            background: #007836;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            font-weight: 500;
            cursor: pointer;
            width: 100%;
            margin-top: 20px;
        }

        .register-button:hover {
            background: linear-gradient(to right, #FFD700, #FFEA70) ;
            color: black;
        }
        
        /* Footer */
        .footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e0e0e0;
            color: #666;
            font-size: 14px;
            width: 100%;
        }
        
        .progress {
            font-weight: 500;
        }
        
        .pagination {
            display: flex;
            gap: 10px;
        }

        /* Footer */
        .footer {
            background:  #007836;
            color: white;
            text-align: center;
            padding: 15px;
            margin-top: 30px;
            font-size: 12px;
        }
        
        .footer p {
            margin-bottom: 3px;
        }
        
        .footer-copyright {
            opacity: 0.7;
        }

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

        .search-box i {
            color: #777;
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(255, 255, 255, 0.2); /* Semi-transparent background */
            backdrop-filter: blur(10px); /* Glass blur effect */
            -webkit-backdrop-filter: blur(10px); /* Safari support */
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        thead {
            background: linear-gradient(to right, #007836, #005a28);
            color: white;
        }

        th {
            padding: 15px 20px;
            text-align: left;
            font-weight: 500;
        }

        tbody tr {
            border-bottom: 1px solid #eef0f5;
            
        }

        tbody tr:last-child {
            border-bottom: none;
        }

        tbody tr:hover {
            background: #f8f9ff;
        }

        td {
            padding: 15px 20px;
            color: #444;
        }

        .checklist {
            display: flex;
            gap: 10px;
        }

        .check-btn {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .check-btn.view {
            background: #4a6cf7;
            color: white;
        }

        .check-btn.edit {
            background: #28a745;
            color: white;
        }

        .check-btn.delete {
            background: #dc3545;
            color: white;
        }

        .check-btn:hover {
            transform: scale(1.1);
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            color: #777;
            font-size: 14px;
            border-top: 1px solid #eee;
        }

        @media (max-width: 992px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .dashboard {
                grid-template-columns: 1fr;
            }
            
            .sidebar {
                margin-bottom: 25px;
            }
        }

        @media (max-width: 768px) {
            .section-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .search-box {
                width: 100%;
            }
            
            .button-group {
                flex-direction: column;
            }
        }

        /* New styles for fingerprint animation */
        .fingerprint-animation {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 120, 54, 0.8);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            border-radius: 8px;
            opacity: 0;
            transition: opacity 0.3s;
            pointer-events: none;
        }

        .fingerprint-animation.active {
            opacity: 1;
        }

        .fingerprint-animation i {
            font-size: 40px;
            margin-bottom: 15px;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }

        .fingerprint-success {
            background-color: rgba(40, 167, 69, 0.8);
        }

        .fingerprint-error {
            background-color: rgba(220, 53, 69, 0.8);
        }

        /* Loading spinner */
        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top: 4px solid white;
            animation: spin 1s linear infinite;
            margin-bottom: 15px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Toast notification */
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #28a745;
            color: white;
            padding: 15px 25px;
            border-radius: 4px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transform: translateX(200%);
            transition: transform 0.3s ease-out;
            z-index: 1000;
        }

        .toast.show {
            transform: translateX(0);
        }

        .toast.error {
            background-color: #dc3545;
        }

        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background: white;
            border-radius: 8px;
            width: 500px;
            max-width: 90%;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        }

        .modal-header {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            margin: 0;
            color: #333;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }

        .modal-body {
            padding: 20px;
        }

        .detail-row {
            display: flex;
            margin-bottom: 15px;
        }

        .detail-label {
            font-weight: 500;
            color: #555;
            width: 120px;
        }

        .detail-value {
            color: #333;
            flex: 1;
        }

        .modal-footer {
            padding: 15px 20px;
            border-top: 1px solid #eee;
            text-align: right;
        }

        .modal-button {
            padding: 8px 16px;
            background: #007836;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .modal-button:hover {
            background: #005a28;
        }
    </style>
</head>
<body>
    <!-- Sidebar Navigation -->
    <aside class="sidebar">
        <div class="sidebar-header" style="display: flex">
            <img src="../images/logo.png" style="height: 50px; margin-right: 10px;">
            <h4 style="margin-top: 15px">ACI Admin Panel</h4>
        </div>
        <ul class="sidebar-menu">
            <li><a href="/biometric/admin/dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="/biometric/admin/admin-management.php" ><i class="fas fa-user-shield"></i> Admin Management</a></li>
            <li><a href="#" style="font-size: 14px"  onclick="launchBiometricApp(); return false;"><i class="fas fa-fingerprint"></i> Fingerprint Management</a></li>
            <li><a href="/biometric/admin/students.php"><i class="fas fa-users"></i> Student Management</a></li>
            <li><a href="/biometric/admin/events.php"><i class="fas fa-calendar-alt"></i> Events Management</a></li>            
            <li><a href="/biometric/admin/attendance.php"><i class="fas fa-clipboard-list"></i> Attendance Records</a></li>
            <li><a href="/biometric/admin/fines.php"><i class="fas fa-exclamation-circle"></i> Fines Management</a></li>
            <li><a href="/biometric/admin/reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
        </ul>
    </aside>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-content">
                <!-- Add your logo image here -->
                <img src="images/logo.jpg" alt="" class="logo">
                <div class="header-text">
                    <h1>One Touch, One Record</h1>
                    <div class="breadcrumb">ACI Admin Panel » Fingerprint Registration</div>
                </div>
            </div>
        </div>
        <div class="container">
            <!-- Toast Notification -->
            <div class="toast" id="toast"></div>
    
            <!-- Registration Module -->
            <div class="registration-module">
                <!-- Form Section -->
                <div class="form-section">
                    <h3 class="section-title">Student Registration</h3>
                    
                    <div class="form-group">
                        <label for="uid">UID*</label>
                        <input type="text" id="uid" placeholder="e.g., 22-10000">
                    </div>
                    
                    <div class="form-group">
                        <label for="student-name">Student Name*</label>
                        <input type="text" id="student-name" placeholder="Enter student name">
                    </div>
                    
                    
                    <div class="form-group">
                        <label for="course">Course*</label>
                        <select id="course">
                            <option value="" disabled selected>Select Course</option>
                            <option value="BSIT">BS in Information Technology</option>
                            <option value="BSED">BS in Education</option>
                            <option value="BSA"> BS in Accountancy</option>
                            <option value="BSBA">BS in Business Administration</option>
                            <option value="BSHM">BS in Hospitality Management</option>
                            <option value="BSTM">BS in Tourism Management</option>
                            <option value="BSE">BS in Engineering</option>
                            <option value="BSN">BS in Nursing</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="year-level">Year Level*</label>
                        <select id="year-level">
                            <option value="" disabled selected>Select year level</option>
                            <option value="1">1st Year</option>
                            <option value="2">2nd Year</option>
                            <option value="3">3rd Year</option>
                            <option value="4">4th Year</option>
                        </select>
                    </div>
                    
                    <button class="clear-button" id="clear-form">Clear Form</button>
                </div>
                
                <!-- Fingerprint Section -->
                <div class="fingerprint-section">
                    <h3 class="section-title">Fingerprint Capture</h3>
                    
                    <div class="fingerprint-scanner" id="fingerprint-scanner">
                        <div class="fingerprint-animation" id="fingerprint-animation">
                            <div class="spinner" id="scan-spinner"></div>
                            <p id="scan-message">Scanning fingerprint...</p>
                        </div>
                        <svg class="scanner-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#007836">
                            <path d="M12 14.5a2.5 2.5 0 0 1-2.5-2.5V9a2.5 2.5 0 0 1 5 0v3a2.5 2.5 0 0 1-2.5 2.5zM12 7.5A1.5 1.5 0 0 0 10.5 9v3a1.5 1.5 0 0 0 3 0V9A1.5 1.5 0 0 0 12 7.5z"/>
                            <path d="M12 13.5a1.5 1.5 0 0 1-1.5-1.5V9a1.5 1.5 0 0 1 3 0v3a1.5 1.5 0 0 1-1.5 1.5zM12 8a1 1 0 0 0-1 1v3a1 1 0 0 0 2 0V9a1 1 0 0 0-1-1z"/>
                            <path d="M15.5 9.17a.5.5 0 0 1 .5.5V12a4 4 0 0 1-8 0V9.67a.5.5 0 0 1 1 0V12a3 3 0 0 0 6 0V9.67a.5.5 0 0 1 .5-.5z"/>
                            <path d="M17.5 9.17a.5.5 0 0 1 .5.5V12a6 6 0 0 1-12 0V9.67a.5.5 0 0 1 1 0V12a5 5 0 0 0 10 0V9.67a.5.5 0 0 1 .5-.5z"/>
                            <path d="M19.5 9.17a.5.5 0 0 1 .5.5V12a8 8 0 0 1-16 0V9.67a.5.5 0 0 1 1 0V12a7 7 0 0 0 14 0V9.67a.5.5 0 0 1 .5-.5z"/>
                        </svg>
                        <p class="scanner-instruction">Place student's finger on the scanner</p>
                    </div>
                    
                    <button class="capture-button" id="capture-button">Capture Fingerprint</button>
                    <button class="retry-button" id="retry-button" style="display: none;">Retry Capture</button>
                    
                    <div class="status-section">
                        <div class="status-title">Capture Status:</div>
                        <div class="status-message" id="status-message">No fingerprint captured yet. Click "Capture Fingerprint" to begin.</div>
                    </div>
                    
                    <button class="register-button" id="register-button" disabled>Register Student</button>
                </div>
            </div>
            
            <!-- Student Records Table -->
            <div class="table-container">
                <table id="students-table">
                    <thead>
                        <tr>
                            <th>UID</th>
                            <th>Student Name</th>
                            <th>Course</th>
                            <th>Year Level</th>
                            <th>Checklist</th>
                        </tr>
                    </thead>
                    <tbody id="students-table-body">
                        <!-- Table rows will be populated by JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const uidInput = document.getElementById('uid');
    const studentNameInput = document.getElementById('student-name');
    const courseSelect = document.getElementById('course');
    const yearLevelSelect = document.getElementById('year-level');
    const clearFormButton = document.getElementById('clear-form');
    const captureButton = document.getElementById('capture-button');
    const retryButton = document.getElementById('retry-button');
    const registerButton = document.getElementById('register-button');
    const statusMessage = document.getElementById('status-message');
    const fingerprintScanner = document.getElementById('fingerprint-scanner');
    const fingerprintAnimation = document.getElementById('fingerprint-animation');
    const scanMessage = document.getElementById('scan-message');
    const scanSpinner = document.getElementById('scan-spinner');
    const toast = document.getElementById('toast');
    const studentsTableBody = document.getElementById('students-table-body');
    
    // State variables
    let fingerprintData = null;
    let isScanning = false;
    
    // Initialize the table with data from database
    function initializeTable() {
        fetchStudents();
    }
    
    // Fetch students from server
// Replace the fetchStudents function with this:
function fetchStudents() {
    fetch('get_students.php')
        .then(response => {
            // First check if the response is JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                return response.text().then(text => {
                    throw new Error(text || 'Server returned non-JSON response');
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                throw new Error(data.error);
            }
            if (data.success) {
                renderStudentsTable(data.data);
            } else {
                throw new Error(data.message || 'Error loading student data');
            }
        })
        .catch(error => {
            console.error('Error fetching students:', error);
            // Extract the actual error message if it's HTML
            let errorMessage = error.message;
            if (errorMessage.includes('<br />') || errorMessage.includes('<b>')) {
                errorMessage = 'Server error occurred. Please check the server logs.';
            }
            showToast('Error loading student data: ' + errorMessage, 'error');
        });
}
    
    // Render students table
    function renderStudentsTable(students) {
        studentsTableBody.innerHTML = '';
        
        if (!students || students.length === 0) {
            studentsTableBody.innerHTML = '<tr><td colspan="5" style="text-align: center;">No students found</td></tr>';
            return;
        }
        
        students.forEach(student => {
            const row = document.createElement('tr');
            
            row.innerHTML = `
                <td>${student.uid}</td>
                <td>${student.student_name}</td>
                <td>${student.course}</td>
                <td>${getYearLevelText(student.year_level)}</td>
                <td class="checklist">
                    <div class="check-btn view" data-id="${student.id}"><i class="fas fa-eye"></i></div>
                    <div class="check-btn edit" data-id="${student.id}"><i class="fas fa-edit"></i></div>
                    <div class="check-btn delete" data-id="${student.id}"><i class="fas fa-trash"></i></div>
                </td>
            `;
            
            studentsTableBody.appendChild(row);
        });
        
        // Add event listeners to action buttons
        document.querySelectorAll('.check-btn.view').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                viewStudent(id);
            });
        });
        
        document.querySelectorAll('.check-btn.edit').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                editStudent(id);
            });
        });
        
        document.querySelectorAll('.check-btn.delete').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                deleteStudent(id);
            });
        });
    }
    
    // View student details
    function viewStudent(id) {
        fetch(`get_student.php?id=${id}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(resp => {
                if (!resp.success) {
                    throw new Error(resp.error || 'Failed to load student');
                }
                
                // Create modal HTML
                const modalHTML = `
                    <div class="modal-overlay" id="view-modal">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h3>Student Details</h3>
                                <button class="close-modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <div class="detail-row">
                                    <span class="detail-label">UID:</span>
                                    <span class="detail-value">${student.uid}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Name:</span>
                                    <span class="detail-value">${student.student_name}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Course:</span>
                                    <span class="detail-value">${student.course}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Year Level:</span>
                                    <span class="detail-value">${getYearLevelText(student.year_level)}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Registered:</span>
                                    <span class="detail-value">${new Date(student.registration_date).toLocaleString()}</span>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button class="modal-button close-modal">Close</button>
                            </div>
                        </div>
                    </div>
                `;
                
                // Add modal to body
                document.body.insertAdjacentHTML('beforeend', modalHTML);
                
                // Add event listeners for closing modal
                document.querySelectorAll('.close-modal').forEach(button => {
                    button.addEventListener('click', () => {
                        document.getElementById('view-modal').remove();
                    });
                });
            })
            .catch(error => {
                console.error('Error fetching student:', error);
                showToast('Error loading student details: ' + error.message, 'error');
            });
    }
    
    // Edit student
    function editStudent(id) {
        fetch(`get_student.php?id=${id}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(student => {
                if (student.error) {
                    throw new Error(student.error);
                }
                
                // Fill form fields
                uidInput.value = student.uid;
                studentNameInput.value = student.student_name;
                courseSelect.value = student.course;
                yearLevelSelect.value = student.year_level;
                
                // Handle fingerprint data
                if (student.fingerprint_data) {
                    fingerprintData = student.fingerprint_data;
                    statusMessage.textContent = 'Fingerprint already registered.';
                    captureButton.style.display = 'none';
                    retryButton.style.display = 'block';
                    registerButton.disabled = false;
                }
                
                // Convert register button to update button
                registerButton.textContent = 'Update Student';
                registerButton.onclick = () => updateStudent(id);
                
                showToast(`Editing: ${student.student_name}`, 'info');
            })
            .catch(error => {
                console.error('Error fetching student:', error);
                showToast('Error loading student for editing: ' + error.message, 'error');
            });
    }
    
    // Update student function
    function updateStudent(id) {
        if (!validateForm()) return;
        
        const formData = new FormData();
        formData.append('id', id);
        formData.append('uid', uidInput.value);
        formData.append('student_name', studentNameInput.value);
        formData.append('course', courseSelect.value);
        formData.append('year_level', yearLevelSelect.value);
        if (fingerprintData) formData.append('fingerprint_data', fingerprintData);
        
        fetch('update_student.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                throw new Error(data.error);
            }
            if (data.success) {
                fetchStudents();
                clearFormButton.click(); // Reset form
                showToast('Student updated!', 'success');
            } else {
                showToast(data.message || 'Error updating student', 'error');
            }
        })
        .catch(error => {
            console.error('Error updating student:', error);
            showToast('Error updating student: ' + error.message, 'error');
        });
    }
    
    // Register student
    // Replace the registerStudent function with this:
function registerStudent() {
    if (!fingerprintData) {
        showToast('Please capture fingerprint first', 'error');
        return;
    }
    
    if (!validateForm()) {
        return;
    }
    
    const formData = new FormData();
    formData.append('uid', uidInput.value);
    formData.append('student_name', studentNameInput.value);
    formData.append('course', courseSelect.value);
    formData.append('year_level', yearLevelSelect.value);
    formData.append('fingerprint_data', fingerprintData);
    
    fetch('register_student.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        // First check if the response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            return response.text().then(text => {
                throw new Error(text || 'Server returned non-JSON response');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.error) {
            throw new Error(data.error);
        }
        if (data.success) {
            showToast(data.message || 'Student registered successfully!', 'success');
            clearFormButton.click();
            fetchStudents();
        } else {
            showToast(data.message || 'Error registering student', 'error');
        }
    })
    .catch(error => {
        console.error('Error registering student:', error);
        // Extract the actual error message if it's HTML
        let errorMessage = error.message;
        if (errorMessage.includes('<br />') || errorMessage.includes('<b>')) {
            errorMessage = 'Server error occurred. Please check the server logs.';
        }
        showToast('Error registering student: ' + errorMessage, 'error');
    });
}
    // Delete student
    function deleteStudent(id) {
        if (confirm('Are you sure you want to delete this student record?')) {
            fetch(`delete_student.php?id=${id}`, {
                method: 'DELETE'
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    throw new Error(data.error);
                }
                if (data.success) {
                    fetchStudents();
                    showToast('Student record deleted successfully', 'success');
                } else {
                    showToast(data.message || 'Error deleting student', 'error');
                }
            })
            .catch(error => {
                console.error('Error deleting student:', error);
                showToast('Error deleting student: ' + error.message, 'error');
            });
        }
    }
    
    // Clear form
    clearFormButton.addEventListener('click', function() {
        uidInput.value = '';
        studentNameInput.value = '';
        courseSelect.value = '';
        yearLevelSelect.value = '';
        resetFingerprintCapture();
        
        // Reset register button to original state
        registerButton.textContent = 'Register Student';
        registerButton.onclick = registerStudent;
    });
    
    // Capture fingerprint
    captureButton.addEventListener('click', function() {
        if (isScanning) return;
        
        // Validate form before capturing fingerprint
        if (!validateForm()) {
            return;
        }
        
        startFingerprintScan();
    });
    
    // Retry fingerprint capture
    retryButton.addEventListener('click', function() {
        resetFingerprintCapture();
    });
    
    // Set register button click handler
    registerButton.addEventListener('click', registerStudent);
    
    // Validate form
    function validateForm() {
        if (!uidInput.value) {
            showToast('Please enter UID', 'error');
            return false;
        }
        
        if (!studentNameInput.value) {
            showToast('Please enter student name', 'error');
            return false;
        }
        
        if (!courseSelect.value) {
            showToast('Please select course', 'error');
            return false;
        }
        
        if (!yearLevelSelect.value) {
            showToast('Please select year level', 'error');
            return false;
        }
        
        return true;
    }
    
    // Start fingerprint scan
    function startFingerprintScan() {
        isScanning = true;
        fingerprintAnimation.classList.add('active');
        captureButton.disabled = true;
        statusMessage.textContent = 'Scanning fingerprint... Please wait.';
        
        // Simulate fingerprint scan (in a real app, this would interface with a fingerprint scanner)
        setTimeout(() => {
            // 80% chance of success, 20% chance of error (for demo purposes)
            const isSuccess = Math.random() < 0.8;
            
            if (isSuccess) {
                completeFingerprintScan(true);
            } else {
                completeFingerprintScan(false);
            }
        }, 3000);
    }
    
    // Complete fingerprint scan
    function completeFingerprintScan(success) {
        isScanning = false;
        
        if (success) {
            // Generate random fingerprint data (in a real app, this would come from the scanner)
            fingerprintData = generateFingerprintData();
            
            fingerprintAnimation.classList.add('fingerprint-success');
            scanSpinner.style.display = 'none';
            scanMessage.innerHTML = '<i class="fas fa-check-circle"></i><br>Fingerprint captured successfully!';
            
            statusMessage.textContent = 'Fingerprint captured successfully. Click "Register Student" to complete registration.';
            captureButton.style.display = 'none';
            retryButton.style.display = 'block';
            registerButton.disabled = false;
            
            // Hide animation after 2 seconds
            setTimeout(() => {
                fingerprintAnimation.classList.remove('active');
                fingerprintAnimation.classList.remove('fingerprint-success');
                scanSpinner.style.display = 'block';
                scanMessage.textContent = 'Scanning fingerprint...';
            }, 2000);
        } else {
            fingerprintAnimation.classList.add('fingerprint-error');
            scanSpinner.style.display = 'none';
            scanMessage.innerHTML = '<i class="fas fa-times-circle"></i><br>Scan failed. Please try again.';
            
            statusMessage.textContent = 'Fingerprint capture failed. Please try again.';
            captureButton.style.display = 'none';
            retryButton.style.display = 'block';
            
            // Hide animation after 2 seconds
            setTimeout(() => {
                fingerprintAnimation.classList.remove('active');
                fingerprintAnimation.classList.remove('fingerprint-error');
                scanSpinner.style.display = 'block';
                scanMessage.textContent = 'Scanning fingerprint...';
            }, 2000);
        }
    }
    
    // Reset fingerprint capture
    function resetFingerprintCapture() {
        fingerprintData = null;
        isScanning = false;
        fingerprintAnimation.classList.remove('active');
        fingerprintAnimation.classList.remove('fingerprint-success');
        fingerprintAnimation.classList.remove('fingerprint-error');
        scanSpinner.style.display = 'block';
        scanMessage.textContent = 'Scanning fingerprint...';
        
        statusMessage.textContent = 'No fingerprint captured yet. Click "Capture Fingerprint" to begin.';
        captureButton.style.display = 'block';
        captureButton.disabled = false;
        retryButton.style.display = 'none';
        registerButton.disabled = true;
    }
    
    // Generate random fingerprint data (for demo purposes)
    function generateFingerprintData() {
        const chars = '0123456789ABCDEF';
        let result = '';
        for (let i = 0; i < 64; i++) {
            result += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return result;
    }
    
    // Show toast notification
    function showToast(message, type = 'success') {
        toast.textContent = message;
        toast.className = 'toast';
        toast.classList.add(type);
        toast.classList.add('show');
        
        setTimeout(() => {
            toast.classList.remove('show');
        }, 3000);
    }
    
    // Helper function to get year level text
    function getYearLevelText(yearLevel) {
        const levels = {
            '1': '1st Year',
            '2': '2nd Year',
            '3': '3rd Year',
            '4': '4th Year'
        };
        return levels[yearLevel] || yearLevel;
    }
    
    // Initialize the page
    initializeTable();
    
    // Display any PHP error messages
    <?php if (!empty($error_message)): ?>
        showToast('<?php echo addslashes($error_message); ?>', 'error');
    <?php elseif (!empty($success_message)): ?>
        showToast('<?php echo addslashes($success_message); ?>', 'success');
    <?php endif; ?>
});
</script>

 <script>
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