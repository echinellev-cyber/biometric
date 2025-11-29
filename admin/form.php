
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
 background: linear-gradient(to right, #FFD700, #FFEA70) !important; /* golden yellow to light yellow */            color: #333;
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
            font-size: 14px;
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

        .capture-button:hover
        {
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
       
        .retry-button:hover
        {
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

        .register-button:hover
        {
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
    </style>
</head>
<body>
    <!-- Sidebar Navigation -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h3>Admin Panel</h3>
        </div>
        <ul class="sidebar-menu">
            <li><a href="/biometric/admin/dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
             <li class="has-submenu active">
                    <a href="#" class="active"><i class="fas fa-users"></i> Forms</a>
                    <ul class="submenu active" style="font-size: 12px">
                        <li><a href="#" class="active"><i class="fas fa-user-graduate"></i> • Add Student Record</a></li>
                        <li><a href="#"><i class="fas fa-user-shield"></i>•  Add System Administrator</a></li>
                    </ul>
                </li>    
            <li><a href="/biometric/admin/attendance.php#"><i class="fas fa-clipboard-list"></i> Attendance Record</a></li>
            <li><a href="/biometric/admin/reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
        </ul>
    </aside>
    <div class="container" >
        <!-- Header -->
        <div class="header" >
            <div class="header-content" >
                <!-- Add your logo image here -->
                <img src="images/logo.jpg" alt="" class="logo">
                <div class="header-text">
                    <h1>One Touch, One Record</h1>
                    <p>Biometric Attendance System</p>
                    <div class="breadcrumb">ACI Admin Panel » Reports</div>

                </div>
            </div>
       </div>
        <div class="container">
    
        <!-- Registration Module -->
        <div class="registration-module">
            <!-- Form Section -->
            <div class="form-section">
                <h3 class="section-title">Student Registration</h3>
                
                <div class="form-group">
                    <label for="student-id">UID*</label>
                    <input type="text" id="uid" placeholder="e.g., 22-10000">
                </div>
                
                <div class="form-group">
                    <label for="first-name">Student Name*</label>
                    <input type="text" id=student-name" placeholder="Enter student name">
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
                        <!-- <option value=""></option>
                        <option value=""></option>
                        <option value=""></option> -->
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
                
              <!-- <div class="form-group">
                    <div class="file-upload"> 
                        
                            <input type="file" id="fileUpload">
                            <label for="fileUpload" class="file-upload-label">
                            </label>
                    </div>
             </div> -->
                    
                
                <button class="clear-button">Clear Form</button>
            </div>
            
            <!-- Fingerprint Section -->
            <div class="fingerprint-section">
                <h3 class="section-title">Fingerprint Capture</h3>
                
                <div class="fingerprint-scanner">
                    <svg class="scanner-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#007836">
                        <path d="M12 14.5a2.5 2.5 0 0 1-2.5-2.5V9a2.5 2.5 0 0 1 5 0v3a2.5 2.5 0 0 1-2.5 2.5zM12 7.5A1.5 1.5 0 0 0 10.5 9v3a1.5 1.5 0 0 0 3 0V9A1.5 1.5 0 0 0 12 7.5z"/>
                        <path d="M12 13.5a1.5 1.5 0 0 1-1.5-1.5V9a1.5 1.5 0 0 1 3 0v3a1.5 1.5 0 0 1-1.5 1.5zM12 8a1 1 0 0 0-1 1v3a1 1 0 0 0 2 0V9a1 1 0 0 0-1-1z"/>
                        <path d="M15.5 9.17a.5.5 0 0 1 .5.5V12a4 4 0 0 1-8 0V9.67a.5.5 0 0 1 1 0V12a3 3 0 0 0 6 0V9.67a.5.5 0 0 1 .5-.5z"/>
                        <path d="M17.5 9.17a.5.5 0 0 1 .5.5V12a6 6 0 0 1-12 0V9.67a.5.5 0 0 1 1 0V12a5 5 0 0 0 10 0V9.67a.5.5 0 0 1 .5-.5z"/>
                        <path d="M19.5 9.17a.5.5 0 0 1 .5.5V12a8 8 0 0 1-16 0V9.67a.5.5 0 0 1 1 0V12a7 7 0 0 0 14 0V9.67a.5.5 0 0 1 .5-.5z"/>
                    </svg>
                    <p class="scanner-instruction">Place student's finger on the scanner</p>
                </div>
                
                <button class="capture-button">Capture Fingerprint</button>
                <button class="retry-button">Retry Capture</button>
                
                <div class="status-section">
                    <div class="status-title">Capture Status:</div>
                    <div class="status-message">No fingerprint captured yet. Click "Capture Fingerprint" to begin.</div>
                </div>
                
                <button class="register-button">Register Student</button>
            </div>
        </div>
          <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>UID</th>
                                    <th>Student Name</th>
                                    <th>Course</th>
                                    <th>Year Level</th>
                                    <th>Checklist</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>22-10262</td>
                                    <td>Chinelle Ventura</td>
                                    <td>BS Information Technology</td>
                                    <td>4th Year</td>
                                    <td class="checklist">
                                        <div class="check-btn view"><i class="fas fa-eye"></i></div>
                                        <div class="check-btn edit"><i class="fas fa-edit"></i></div>
                                        <div class="check-btn delete"><i class="fas fa-trash"></i></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>22-11111</td>
                                    <td>Irish Reyes</td>
                                    <td>BS Information Technology</td>
                                    <td>4th Year</td>
                                    <td class="checklist">
                                        <div class="check-btn view"><i class="fas fa-eye"></i></div>
                                        <div class="check-btn edit"><i class="fas fa-edit"></i></div>
                                        <div class="check-btn delete"><i class="fas fa-trash"></i></div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
   
                
                <!-- ____________________ADMIN MODULE ________________________________-->

                <!-- Admin Registration Form (hidden by default) -->
        <div id="admin-registration-module" class="registration-module" style="display: none;">
        <!-- Form Section -->
        <div class="form-section">
            <h3 class="section-title">Admin Registration</h3>
        
        <div class="form-group">
            <label for="admin-name">Full Name*</label>
            <input type="text" id="admin-name" placeholder="Enter admin name">
        </div>
       
        <div class="form-group">
            <label for="admin-username">Username*</label>
            <input type="text" id="admin-username" placeholder="Create username">
        </div>
        
        <div class="form-group">
            <label for="admin-password">Password*</label>
            <input type="password" id="admin-password" placeholder="Create password">
        </div>
        
        <!-- <div class="form-group">
            <label for="admin-role">Role*</label>
            <select id="admin-role">
                <option value="" disabled selected>Select role</option>
                <option value="admin">Admin</option>
                <option value="officer">Officer</option>
            </select>
        </div> -->
        
        <button class="register-button">Register Admin</button>
    </div>
    
    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Username</th>
                                    <th>Checklist</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Chinelle</td>
                                    <td>chinxlle</td>
                                    <td class="checklist">
                                        <div class="check-btn view"><i class="fas fa-eye"></i></div>
                                        <div class="check-btn edit"><i class="fas fa-edit"></i></div>
                                        <div class="check-btn delete"><i class="fas fa-trash"></i></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Irish</td>
                                    <td>irish</td>
                                    <td class="checklist">
                                        <div class="check-btn view"><i class="fas fa-eye"></i></div>
                                        <div class="check-btn edit"><i class="fas fa-edit"></i></div>
                                        <div class="check-btn delete"><i class="fas fa-trash"></i></div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
 
        <!-- Footer -->
         <!-- <div class="footer">
        <p>© 2025 One Touch, One Record – A Smart Biometric Solution for IT Student Attendance</p>
        <p class="footer-copyright">Developed by  Chinelle Ventura & Irish Vanessa Reyes | Aldersgate College, Inc.</p>
    </div> -->
    </div>
   
            
        </div>
    </main>

    <script>
document.addEventListener('DOMContentLoaded', function() {
    // Get menu items
    const studentMenuItem = document.querySelector('.submenu li:nth-child(1) a');
    const adminMenuItem = document.querySelector('.submenu li:nth-child(2) a');
    
    // Get form modules and table
    const studentModule = document.querySelector('.registration-module');
    const adminModule = document.getElementById('admin-registration-module');
    const studentTable = document.querySelector('.table-container');
    
    // Click handler for admin menu item
    adminMenuItem.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Update active menu item styling
        studentMenuItem.classList.remove('active');
        this.classList.add('active');
        
        // Hide student form and table, show admin form
        studentModule.style.display = 'none';
        studentTable.style.display = 'none';
        adminModule.style.display = 'flex';
    });
    
    // Click handler for student menu item
    studentMenuItem.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Update active menu item styling
        adminMenuItem.classList.remove('active');
        this.classList.add('active');
        
        // Hide admin form, show student form and table
        adminModule.style.display = 'none';
        studentModule.style.display = 'flex';
        studentTable.style.display = 'block';
    });
});
</script>
</body>
</html>