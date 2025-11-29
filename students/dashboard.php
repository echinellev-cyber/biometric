<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ACI Biometric Student Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../images/logo.png">
    <link rel="apple-touch-icon" href="../images/logo.png">
    <link rel="shortcut icon" href="../images/logo.png">

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
    
    // Get today's date
    $today = date('Y-m-d');
    
    // Get today's status
    $today_status_sql = "SELECT se.attendance_status, ae.event_name 
                         FROM students_events se 
                         JOIN admin_event ae ON se.event_id = ae.event_id 
                         WHERE se.student_id = ? AND ae.date = ? 
                         ORDER BY se.date_recorded DESC 
                         LIMIT 1";
    $stmt = $conn->prepare($today_status_sql);
    $stmt->bind_param("is", $student_id, $today);
    $stmt->execute();
    $today_result = $stmt->get_result();
    $today_status = $today_result->fetch_assoc();
    
    // Get pending fines
    $fines_sql = "SELECT COUNT(*) as pending_fines, SUM(amount) as total_amount 
                   FROM admin_fines 
                   WHERE student_id = ? AND status = 'unpaid'";
    $stmt = $conn->prepare($fines_sql);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $fines_result = $stmt->get_result();
    $fines_data = $fines_result->fetch_assoc();
    
    // Get recent check-ins (last 7 records)
    $recent_sql = "SELECT se.attendance_status, se.time_in, se.time_out, ae.event_name, ae.date
                   FROM students_events se 
                   JOIN admin_event ae ON se.event_id = ae.event_id 
                   WHERE se.student_id = ? AND se.time_in IS NOT NULL
                   ORDER BY se.date_recorded DESC 
                   LIMIT 7";
    $stmt = $conn->prepare($recent_sql);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $recent_result = $stmt->get_result();
    $recent_checkins = array();
    
    while($row = $recent_result->fetch_assoc()) {
        $recent_checkins[] = $row;
    }
    
    $conn->close();
    ?>

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

        /* Quick Actions */
        .quick-actions {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .action-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #007836;
            color: white;
            padding: 10px 15px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s;
        }

        .action-btn:hover {
            background: #00612b;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
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

            .quick-actions {
                flex-direction: column;
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

        /* Fines Section */
        .fines-container {
            margin-top: 30px;
        }

        .fines-list {
            list-style: none;
        }

        .fine-item {
            background: rgba(255, 255, 255, 0.8);
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .fine-details {
            flex: 1;
        }

        .fine-amount {
            font-weight: 600;
            color: #e74c3c;
        }

        .fine-date {
            font-size: 12px;
            color: #666;
        }

        .fine-status {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-unpaid {
            background-color: #ffebee;
            color: #e74c3c;
        }

        .status-paid {
            background-color: #e8f5e9;
            color: #007836;
        }

        /* Event Calendar */
        .event-calendar {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .event-card {
            background: rgba(255, 255, 255, 0.8);
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .event-date {
            font-weight: 600;
            color: #007836;
            margin-bottom: 5px;
        }

        .event-name {
            font-weight: 500;
            margin-bottom: 5px;
        }

        .event-time {
            font-size: 12px;
            color: #666;
        }
    </style>
</head>

<body>
    <!-- Sidebar Navigation -->
    <aside class="sidebar">
        <div class="sidebar-header" style="display: flex">
            <img src="../images/logo.png" alt="Student Avatar" style="height: 50px; margin-right: 10px;">
            <h4 style="margin-top: 15px">ACI Student Panel</h4>
        </div>
        <ul class="sidebar-menu">
            <li><a href="/biometric/students/dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="/biometric/students/attendance.php"><i class="fas fa-clipboard-list"></i> My Attendance</a></li>
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
                    <h1>ACI Student Dashboard</h1>
                    <p>Biometric Attendance Tracking</p>
                    <div class="breadcrumb">ACI Student Panel » Dashboard</div>
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

            <!-- Dashboard Cards -->
            <div class="dashboard-grid">
                <div class="card">
                    <h3>Today's Status</h3>
                    <div class="value" style="font-size: 16px">
                        <?php
                        if ($today_status && isset($today_status['attendance_status'])) {
                            $status = $today_status['attendance_status'];
                            $color = '';
                            switch($status) {
                                case 'present': $color = '#007836'; break;
                                case 'late': $color = '#f39c12'; break;
                                case 'absent': $color = '#e74c3c'; break;
                                default: $color = 'gray';
                            }
                            echo "<span style='color: $color; text-transform: capitalize;'>" . $status . "</span>";
                            if (isset($today_status['event_name'])) {
                                echo "<div class='subtext'>" . $today_status['event_name'] . "</div>";
                            }
                        } else {
                            echo "<span style='color: gray;'>No events today</span>";
                        }
                        ?>
                    </div>
                </div>

                <div class="card">
                    <h3>Pending Fines</h3>
                    <div class="value">
                        <?php
                        if ($fines_data['pending_fines'] > 0) {
                            echo $fines_data['pending_fines'];
                            echo "<div class='subtext'>₱" . number_format($fines_data['total_amount'], 2) . " total</div>";
                        } else {
                            echo "0";
                            echo "<div class='subtext'>No pending fines</div>";
                        }
                        ?>
                    </div>
                </div>

                <div class="card">
                    <h3>Student ID</h3>
                    <div class="value" style="font-size: 18px"><?php echo htmlspecialchars($student_uid); ?></div>
                    <div class="subtext">Your student identifier</div>
                </div>
            </div>

            <!-- Recent Activity Section -->
            <div class="activity-section">
                <div class="section-header">
                    <h2><i class="fas fa-history"></i> Your Recent Check-ins</h2>
                    <a href="/biometric/students/attendance.php" class="view-all">View All Records</a>
                </div>

                <table class="activity-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Event</th>
                            <th>Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (!empty($recent_checkins)) {
                            foreach ($recent_checkins as $checkin) {
                                $date = date('M j, Y', strtotime($checkin['date']));
                                $time_in = $checkin['time_in'] ? date('g:i A', strtotime($checkin['time_in'])) : '-';
                                $status_class = 'status-' . $checkin['attendance_status'];
                                
                                echo "<tr>";
                                echo "<td>" . $date . "</td>";
                                echo "<td>" . htmlspecialchars($checkin['event_name']) . "</td>";
                                echo "<td>" . $time_in . "</td>";
                                echo "<td class='" . $status_class . "'>" . ucfirst($checkin['attendance_status']) . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4' style='text-align: center;'>No recent check-ins found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>