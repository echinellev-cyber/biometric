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
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100..900&display=swap" rel="stylesheet"><style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            position: relative;
            background: radial-gradient(circle, white,rgba(255, 250, 162, 1));
            min-height: 100vh;
            overflow-x: hidden;
            display: flex;
        }

        /* Watermark Background */
/* Watermark Background - FIXED */
body::before {
    content: "";
    position: fixed; /* CHANGED from absolute to fixed */
    top: 0;
    left: 220px; /* Start after sidebar */
    right: 0;
    bottom: 0;
    background-image: url('../images/logo.png'); /* CHANGED PATH */
    background-size: 100s%; /* Reduced from 100% to prevent spreading */
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
    position: fixed; /* ADD THIS */
    height: 100vh; /* ADD THIS */
    overflow-y: auto; /* ADD THIS */
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
            background: linear-gradient(to right, #FFD700, #FFEA70) !important;
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
    margin-left: 250px; /* ADD THIS */
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

        /* Reports Section */
        .reports-container {
            background: rgba(255, 255, 255, 0.2); /* Semi-transparent background */
             backdrop-filter: blur(10px); /* Glass blur effect */
            -webkit-backdrop-filter: blur(10px); /* Safari support */
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .reports-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #ddd;
        }

        .reports-title {
            font-size: 24px;
            color: #333;
            font-weight: 600;
        }

        .report-nav {
            display: flex;
            gap: 15px;
        }

        .report-nav a {
            color: #007836;
            text-decoration: none;
            font-weight: 500;
        }

        .report-nav a.active {
            color: #FFD700;
            text-decoration: underline;
        }

        .report-form {
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
        }

        .form-row {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            margin-bottom: 15px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .form-group input[type="date"],
        .form-group select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 200px;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .btn {
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
            border: none;
        }

        .btn-primary {
            background:rgb(137, 172, 133);
            color: white;
        }

        .btn-primary:hover {
            background-color: #00612b;
        }

        .btn-secondary {
            background:rgb(166, 163, 163);
            color: white;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        .report-preview {
            margin-top: 20px;
            
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .report-table th {
            background-color: #007836;
            color: white;
            padding: 12px;
            text-align: left;
        }

        .report-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #ddd;
        }

        .report-table tr:nth-child(even) {
            background: rgba(255, 255, 255, 0.2); /* Semi-transparent background */
            backdrop-filter: blur(10px); /* Glass blur effect */
            -webkit-backdrop-filter: blur(10px); /* Safari support */        }

        .report-table tr:hover {
            background-color: #f1f1f1;
        }

        .download-btn {
            display: inline-block;
            background:rgb(137, 172, 133);
            color: white;
            padding: 8px 16px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            transition: background-color 0.3s;
        }

        .download-btn:hover {
            background-color: #00612b;
        }

        .no-records {
            text-align: center;
            padding: 20px;
            color: #666;
            font-style: italic;
        }

        /* Status badges */
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-present {
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        .status-absent {
            background-color: #ffebee;
            color: #c62828;
        }

        .status-late {
            background-color: #fff3e0;
            color: #ef6c00;
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
            
            .reports-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .report-nav {
                width: 100%;
                justify-content: space-between;
            }
            
            .form-row {
                flex-direction: column;
                gap: 10px;
            }
            
            .form-group input[type="date"],
            .form-group select {
                width: 100%;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }
                /* Add this to your CSS section */
.logout-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    background:rgb(137, 172, 133);
    color: white;
    padding: 8px 15px;
    border-radius: 4px;
    text-decoration: none;
    font-size: 14px;
    transition: all 0.3s;
}

.logout-btn:hover {
    background:rgb(69, 156, 65);
    transform: translateY(-2px);
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
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
            <li><a href="/biometric/admin/students.php" ><i class="fas fa-users"></i> Student Management</a></li>
            <li><a href="/biometric/admin/events.php" ><i class="fas fa-calendar-alt"></i> Events Management</a></li>            
            <li><a href="/biometric/admin/attendance.php" ><i class="fas fa-clipboard-list"></i> Attendance Records</a></li>
            <li><a href="/biometric/admin/fines.php"  ><i class="fas fa-exclamation-circle"></i> Fines Management</a></li>
            <li><a href="/biometric/admin/reports.php" class="active"><i class="fas fa-chart-bar"></i> Reports</a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <div class="header">
                <div>
                    <h1>Reports Management</h1>
                    <div class="ACI breadcrumb">Admin Panel » Reports</div>
                </div>
                <div>
                    <a href="/biometric/login/login.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                 </div>
            </div>

            <div class="reports-container">
                <div class="reports-header">
                    <h2 class="reports-title">Generate Report</h2>
                </div>

                <div class="report-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="report-date">Date</label>
                            <input type="date" id="report-date" value="2025-10-11">
                        </div>
                        
                        <div class="form-group">
                            <label for="attendance-filter">Attendance Status</label>
                            <select id="attendance-filter">
                                <option value="all">All Students</option>
                                <option value="present">Present Only</option>
                                <option value="absent">Absent Only</option>
                                <option value="late">Late Only</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="department-filter">Department</label>
                            <select id="department-filter">
                                <option value="all">All Departments</option>
                                <option value="Information Technology">Information Technology</option>
                                <option value="Criminology">Criminology</option>
                                <option value="Electrical Engineering">Electrical Engineering</option>
                                <option value="Midwifery">Midwifery</option>
                                <option value="Business Administration">Business Administration</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="course-filter">Course</label>
                            <select id="course-filter">
                                <option value="all">All Courses</option>
                                <option value="BS in Information Technology">BS in Information Technology</option>
                                <option value="BS in Criminology">BS in Criminology</option>
                                <option value="BS in Electrical Engineering">BS in Electrical Engineering</option>
                                <option value="BS in Midwifery">BS in Midwifery</option>
                                <option value="BS in Business Administration">BS in Business Administration</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-actions">
                    <button class="btn btn-primary" id="preview-btn">
                        <i class="fas fa-eye"></i> Preview Report
                    </button>
                        <button class="btn btn-secondary" id="reset-btn">Reset Filters</button>
                    </div>
                </div>

                <div class="report-preview">
                    <h3>Report Preview</h3>
                    <table class="report-table">
                        <thead>
                        <tr>
                            <th>Date</th>
                            <th>Student ID Number</th>
                            <th>Student Name</th>
                            <th>Course</th>
                            <th>Year</th>
                            <th>Time In</th>
                            <th>Time Out</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody id="report-data">
                        <!-- Data will be populated here by JavaScript -->
                        </tbody>
                    </table>
                    <div id="no-records" class="no-records" style="display: none;">
                        No records found for the selected filters
                    </div>

                    <a href="#" class="download-btn" id="download-btn">Download CSV</a>
                </div>
            </div>
        </div>
    </main>

    <script>
        // DOM elements
        const reportDateInput = document.getElementById('report-date');
        const attendanceFilter = document.getElementById('attendance-filter');
        const departmentFilter = document.getElementById('department-filter');
        const courseFilter = document.getElementById('course-filter');
        const previewBtn = document.getElementById('preview-btn');
        const resetBtn = document.getElementById('reset-btn');
        const reportData = document.getElementById('report-data');
        const noRecordsMsg = document.getElementById('no-records');
        const downloadBtn = document.getElementById('download-btn');

        // Function to fetch real attendance data from database
        async function fetchAttendanceData(selectedDate) {
            try {
                const response = await fetch('get_attendance_report.php?date=' + selectedDate);
                const data = await response.json();
                
                return data;
            } catch (error) {
                console.error('Error fetching attendance data:', error);
                return [];
            }
        }

        // Function to filter records based on selected filters
        function filterRecords(data) {
            // Filter out records with "Unknown" student name or "N/A" student ID
            let filteredData = data.filter(record => 
                record.student_name !== 'Unknown' && 
                record.student_uid !== 'N/A' &&
                record.student_name !== null &&
                record.student_uid !== null
            );
            
            // Apply attendance status filter
            const attendanceStatus = attendanceFilter.value;
            if (attendanceStatus !== 'all') {
                filteredData = filteredData.filter(record => {
                    if (attendanceStatus === 'present') {
                        return record.attendance_status === 'present' || 
                               (record.time_in && record.time_in !== 'N/A');
                    } else if (attendanceStatus === 'absent') {
                        return record.attendance_status === 'absent' || 
                               (!record.time_in || record.time_in === 'N/A');
                    } else if (attendanceStatus === 'late') {
                        return record.attendance_status === 'late';
                    }
                    return true;
                });
            }
            
            // Apply department filter
            const department = departmentFilter.value;
            if (department !== 'all') {
                filteredData = filteredData.filter(record => 
                    record.course && record.course.includes(department)
                );
            }
            
            // Apply course filter
            const course = courseFilter.value;
            if (course !== 'all') {
                filteredData = filteredData.filter(record => 
                    record.course === course
                );
            }
            
            return filteredData;
        }

        // Function to determine status badge
        function getStatusBadge(record) {
            if (record.attendance_status) {
                if (record.attendance_status === 'present') {
                    return '<span class="status-badge status-present">Present</span>';
                } else if (record.attendance_status === 'absent') {
                    return '<span class="status-badge status-absent">Absent</span>';
                } else if (record.attendance_status === 'late') {
                    return '<span class="status-badge status-late">Late</span>';
                }
            }
            
            // Fallback logic based on time_in
            if (record.time_in && record.time_in !== 'N/A') {
                return '<span class="status-badge status-present">Present</span>';
            } else {
                return '<span class="status-badge status-absent">Absent</span>';
            }
        }

        // Function to filter and display records based on selected filters
        async function filterRecordsByDate() {
            const selectedDate = reportDateInput.value;
            
            // Show loading state
            reportData.innerHTML = '<tr><td colspan="8" style="text-align: center;">Loading...</td></tr>';
            noRecordsMsg.style.display = 'none';
            
            // Fetch real data from database
            const attendanceData = await fetchAttendanceData(selectedDate);
            
            // Clear existing data
            reportData.innerHTML = '';
            
            // Apply all filters
            const filteredData = filterRecords(attendanceData);
            
            if (filteredData.length > 0) {
                noRecordsMsg.style.display = 'none';
                
                // Populate table with filtered data
                filteredData.forEach(record => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${record.date || selectedDate}</td>
                        <td>${record.student_uid || 'N/A'}</td>
                        <td>${record.student_name || 'Unknown'}</td>
                        <td>${record.course || 'N/A'}</td>
                        <td>${record.year_level || 'N/A'}</td>
                        <td>${record.time_in || 'N/A'}</td>
                        <td>${record.time_out || 'N/A'}</td>
                        <td>${getStatusBadge(record)}</td>
                    `;
                    reportData.appendChild(row);
                });
                
                // Enable download button
                downloadBtn.style.display = 'inline-block';
            } else {
                noRecordsMsg.style.display = 'block';
                downloadBtn.style.display = 'none';
            }
        }

        // Function to reset the form and clear results
        function resetForm() {
            // Set to October 11, 2025
            reportDateInput.value = '2025-10-11';
            attendanceFilter.value = 'all';
            departmentFilter.value = 'all';
            courseFilter.value = 'all';
            reportData.innerHTML = '';
            noRecordsMsg.style.display = 'none';
            downloadBtn.style.display = 'none';
        }

        // Function to generate CSV content and trigger download
        async function downloadCSV() {
            const selectedDate = reportDateInput.value;
            const attendanceData = await fetchAttendanceData(selectedDate);
            
            // Apply all filters
            const filteredData = filterRecords(attendanceData);
            
            if (filteredData.length > 0) {
                // Create CSV headers
                let csvContent = "Date,Student ID,Student Name,Course,Year,Time In,Time Out,Event,Status\n";
                
                // Add data rows
                filteredData.forEach(record => {
                    const status = record.attendance_status || 
                                 (record.time_in && record.time_in !== 'N/A' ? 'present' : 'absent');
                    
                    csvContent += `"${record.date || selectedDate}","${record.student_uid || 'N/A'}","${record.student_name || 'Unknown'}","${record.course || 'N/A'}","${record.year_level || 'N/A'}","${record.time_in || 'N/A'}","${record.time_out || 'N/A'}","${record.event_name || 'No Event'}","${status}"\n`;
                });
                
                // Create download link
                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.setAttribute('href', url);
                link.setAttribute('download', `attendance_report_${selectedDate}.csv`);
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        }

        // Event listeners
        previewBtn.addEventListener('click', filterRecordsByDate);
        resetBtn.addEventListener('click', resetForm);
        downloadBtn.addEventListener('click', function(e) {
            e.preventDefault();
            downloadCSV();
        });

        // Initialize with October 11, 2025
        document.addEventListener('DOMContentLoaded', function() {
            resetForm();
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