<?php
session_start();

// Check if student is logged in
if (!isset($_SESSION['student_logged_in']) || $_SESSION['student_logged_in'] !== true) {
    header("Location: /biometric/login/login.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "biometric";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get student ID from session
$student_id = $_SESSION['student_id'];
$student_name = $_SESSION['student_name'];
$student_uid = $_SESSION['student_uid'];
$student_course = $_SESSION['student_course'];
$student_year = $_SESSION['student_year'];

// Initialize variables
$attendanceRecords = [];
$summary = [
    'total_events' => 0,
    'present' => 0,
    'absent' => 0,
    'late' => 0
];

try {
    // Get attendance records from students_events table (your actual table structure)
    $stmt = $conn->prepare("
        SELECT 
            se.time_in,
            se.time_out,
            se.attendance_status,
            ae.event_name,
            ae.date,
            ae.start_time,
            ae.end_time,
            ae.location
        FROM students_events se
        JOIN admin_event ae ON se.event_id = ae.event_id
        WHERE se.student_id = ?
        ORDER BY ae.date DESC, se.time_in DESC
    ");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while($row = $result->fetch_assoc()) {
        $attendanceRecords[] = $row;
    }
    
    // Calculate summary
    $summary['total_events'] = count($attendanceRecords);
    foreach ($attendanceRecords as $record) {
        switch ($record['attendance_status']) {
            case 'present':
                $summary['present']++;
                break;
            case 'late':
                $summary['late']++;
                break;
            case 'absent':
                $summary['absent']++;
                break;
        }
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ACI Biometric - My Attendance</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Favicon -->
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
            background: radial-gradient(circle, white, rgb(243, 236, 117));
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

        .user-profile {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-info {
            text-align: right;
        }

        .user-name {
            font-weight: 500;
            color: #333;
        }

        .user-role {
            font-size: 12px;
            color: #666;
        }

        .user-profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #007836;
        }

        /* Dashboard Cards */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

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

        .card .subtext {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }

        /* Recent Activity Section */
        .activity-section {
            margin-top: 30px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .section-header h2 {
            color: #333;
            font-size: 20px;
            font-weight: 600;
        }

        .view-all {
            color: #007836;
            font-size: 14px;
            text-decoration: none;
            font-weight: 500;
        }

        .view-all:hover {
            text-decoration: underline;
        }

        .activity-table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .activity-table th, 
        .activity-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .activity-table th {
            background-color: #007836;
            color: white;
            font-weight: 500;
        }

        .activity-table tr:nth-child(even) {
            background-color: rgba(0, 120, 54, 0.05);
        }

        .activity-table tr:hover {
            background-color: rgba(0, 120, 54, 0.1);
        }

        .status-present {
            color: #007836;
            font-weight: 500;
        }

        .status-absent {
            color: #e74c3c;
            font-weight: 500;
        }

        .status-late {
            color: #f39c12;
            font-weight: 500;
        }

        .status-excused {
            color: #3498db;
            font-weight: 500;
        }

        /* Attendance Filters */
        .filters {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }

        .filter-btn {
            padding: 8px 15px;
            border-radius: 20px;
            background: #f0f0f0;
            color: #555;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }

        .filter-btn.active {
            background: #007836;
            color: white;
        }

        .filter-btn:hover {
            background: #e0e0e0;
        }

        /* Search Box */
        .search-box {
            display: flex;
            margin-bottom: 20px;
        }

        .search-box input {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 6px 0 0 6px;
            flex: 1;
            outline: none;
        }

        .search-box button {
            padding: 10px 15px;
            background: #007836;
            color: white;
            border: none;
            border-radius: 0 6px 6px 0;
            cursor: pointer;
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

            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .filters {
                flex-wrap: wrap;
            }

            .activity-table {
                display: block;
                overflow-x: auto;
            }
        }

        /* Logout Button */
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
        }

        .logout-btn:hover {
            background: rgb(69, 156, 65);
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .breadcrumb {
            color: #666;
            font-size: 12px;
            margin-top: 5px;
        }

        /* Attendance Summary */
        .attendance-summary {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }

        .summary-card {
            flex: 1;
            background: rgba(255, 255, 255, 0.8);
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .summary-card h3 {
            font-size: 14px;
            color: #555;
            margin-bottom: 10px;
        }

        .summary-value {
            font-size: 24px;
            font-weight: 600;
        }

        /* Time Range Selector */
        .time-range {
            margin-bottom: 20px;
        }

        .time-range select {
            padding: 8px 15px;
            border-radius: 6px;
            border: 1px solid #ddd;
        }
    </style>
</head>

<body>
    <!-- Sidebar Navigation -->
    <aside class="sidebar">
        <div class="sidebar-header" style="display: flex">
            <img src="../images/logo.png" alt="ACI Logo" style="height: 50px; margin-right: 10px;">
            <h4 style="margin-top: 15px">ACI Student Panel</h4>
        </div>
        <ul class="sidebar-menu">
            <li><a href="/biometric/students/dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="/biometric/students/attendance.php" class="active"><i class="fas fa-clipboard-list"></i> My Attendance</a></li>
            <li><a href="/biometric/students/events.php"><i class="fas fa-calendar-alt"></i> School Events</a></li>
            <li><a href="/biometric/students/fines.php"><i class="fas fa-exclamation-circle"></i> My Fines</a></li>
        </ul>
    </aside>
    
    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <!-- Header -->
            <div class="header">
                <div>
                    <h1>My Attendance Records</h1>
                    <p>Attendance status for all school events</p>
                    <div class="breadcrumb">ACI Student Panel » My Attendance</div>
                </div>
                <div class="user-profile">
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($student_name); ?></div>
                        <div class="user-role"><?php echo htmlspecialchars($student_course); ?> - Year <?php echo htmlspecialchars($student_year); ?></div>
                    </div>
                    <a href="/biometric/login/logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>

            <!-- Attendance Summary -->
            <div class="attendance-summary">
                <div class="summary-card">
                    <h3>Total Events</h3>
                    <div class="summary-value"><?php echo $summary['total_events']; ?></div>
                </div>
                <div class="summary-card">
                    <h3>Present</h3>
                    <div class="summary-value status-present"><?php echo $summary['present']; ?></div>
                </div>
                <div class="summary-card">
                    <h3>Absent</h3>
                    <div class="summary-value status-absent"><?php echo $summary['absent']; ?></div>
                </div>
                <div class="summary-card">
                    <h3>Late Arrivals</h3>
                    <div class="summary-value status-late"><?php echo $summary['late']; ?></div>
                </div>
            </div>

            <!-- Filters and Search -->
            <div class="filters">
                <button class="filter-btn active" onclick="filterRecords('all')">All</button>
                <button class="filter-btn" onclick="filterRecords('present')">Present</button>
                <button class="filter-btn" onclick="filterRecords('absent')">Absent</button>
                <button class="filter-btn" onclick="filterRecords('late')">Late</button>
            </div>

            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Search events..." onkeyup="searchEvents()">
                <button><i class="fas fa-search"></i></button>
            </div>

            <!-- Attendance Table -->
            <div class="section-header">
                <h2><i class="fas fa-history"></i> Attendance History</h2>
            </div>

            <table class="activity-table" id="attendanceTable">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Event</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Status</th>
                        <th>Location</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($attendanceRecords)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 20px; color: #666;">
                            No attendance records found.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($attendanceRecords as $record): ?>
                        <tr class="attendance-row" data-status="<?php echo $record['attendance_status']; ?>">
                            <td><?php echo date('M d, Y', strtotime($record['date'])); ?></td>
                            <td><?php echo htmlspecialchars($record['event_name']); ?></td>
                            <td><?php echo $record['time_in'] ? date('g:i A', strtotime($record['time_in'])) : '-'; ?></td>
                            <td><?php echo $record['time_out'] ? date('g:i A', strtotime($record['time_out'])) : '-'; ?></td>
                            <td class="status-<?php echo $record['attendance_status']; ?>">
                                <?php echo ucfirst($record['attendance_status']); ?>
                            </td>
                            <td><?php echo htmlspecialchars($record['location']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script>
        function filterRecords(status) {
            const rows = document.querySelectorAll('.attendance-row');
            const filterBtns = document.querySelectorAll('.filter-btn');
            
            // Update active button
            filterBtns.forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            
            // Filter rows
            rows.forEach(row => {
                if (status === 'all' || row.getAttribute('data-status') === status) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
        
        function searchEvents() {
            const input = document.getElementById('searchInput').value.toLowerCase();
            const rows = document.querySelectorAll('.attendance-row');
            
            rows.forEach(row => {
                const eventName = row.cells[1].textContent.toLowerCase();
                const location = row.cells[5].textContent.toLowerCase();
                
                if (eventName.includes(input) || location.includes(input)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>