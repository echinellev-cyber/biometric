<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ACI Biometric Admin Dashboard</title>
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

        .loading {
            opacity: 0.7;
            pointer-events: none;
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
            <li><a href="#" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="/biometric/admin/admin-management.php" style="font-size: 14px"><i class="fas fa-user"></i>Admin Management</a></li>
            <li><a href="#" style="font-size: 14px"  onclick="launchBiometricApp(); return false;"><i class="fas fa-fingerprint"></i> Fingerprint Management</a></li>
            <li><a href="/biometric/admin/students.php"><i class="fas fa-users"></i> Student Management</a></li>
            <li><a href="/biometric/admin/events.php"><i class="fas fa-calendar-alt"></i> Events Management</a></li>            
            <li><a href="/biometric/admin/attendance.php"><i class="fas fa-clipboard-list"></i> Attendance Records</a></li>
            <li><a href="/biometric/admin/fines.php"><i class="fas fa-exclamation-circle"></i> Fines Management</a></li>
            <li><a href="/biometric/admin/reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
        </ul>
    </aside>
    

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <!-- Header -->
            <div class="header">
                <div>
                    <h1>ACI Admin Dashboard</h1>
                    <p>Biometric Attendance Tracking</p>
                    <div class="breadcrumb">ACI Admin Panel » Fingerprint Registration</div>
                </div>
                <div class="user-profile">
                    <a href="/biometric/login/login.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>

            <!-- Dashboard Cards -->
            <div class="dashboard-grid">
                <div class="card">
                    <h3>Registered Students</h3>
                    <div class="value" id="totalStudents">0</div>
                </div>

                <div class="card">
                    <h3>Today's Check-ins</h3>
                    <div class="value" id="todayCheckins">0</div>
                </div>

                <div class="card">
                    <h3>Pending Fines</h3>
                    <div class="value" id="pendingFines">0</div>
                </div>
            </div>

            <!-- Recent Activity Section -->
            <div class="activity-section">
                <div class="section-header">
                    <h2><i class="fas fa-history"></i> Recent Check-ins</h2>
                    <a href="/biometric/admin/attendance.php" class="view-all">View All Records</a>
                </div>

                <table class="activity-table">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Event</th>
                            <th>Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="recentCheckinsBody">
                        <!-- Data will be populated by JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        // Function to fetch dashboard data
        async function fetchDashboardData() {
            try {
                const response = await fetch('get_dashboard_data.php');
                const data = await response.json();
                
                if (data.success) {
                    // Update dashboard cards
                    document.getElementById('totalStudents').textContent = data.stats.total_students;
                    document.getElementById('todayCheckins').textContent = data.stats.today_checkins;
                    document.getElementById('pendingFines').textContent = data.stats.pending_fines;
                    
                    // Update recent check-ins table - FILTER OUT DELETED STUDENTS AND EVENTS
                    const tableBody = document.getElementById('recentCheckinsBody');
                    tableBody.innerHTML = '';
                    
                    if (data.recent_checkins.length === 0) {
                        tableBody.innerHTML = '<tr><td colspan="5" style="text-align: center; color: #666; font-style: italic;">No recent check-ins found</td></tr>';
                    } else {
                        // Filter out records where:
                        // 1. student_name is "Unknown" (deleted students)
                        // 2. event_name is "No Event" or null (deleted events)
                        const filteredCheckins = data.recent_checkins.filter(checkin => 
                            checkin.student_name !== 'Unknown' && 
                            checkin.student_name !== null &&
                            checkin.event_name !== 'No Event' &&
                            checkin.event_name !== null
                        );
                        
                        if (filteredCheckins.length === 0) {
                            tableBody.innerHTML = '<tr><td colspan="5" style="text-align: center; color: #666; font-style: italic;">No recent check-ins found</td></tr>';
                        } else {
                            filteredCheckins.forEach(checkin => {
                                const row = document.createElement('tr');
                                const statusClass = `status-${checkin.attendance_status}`;
                                
                                row.innerHTML = `
                                    <td>${checkin.uid || 'N/A'}</td>
                                    <td>${checkin.student_name || 'Unknown'}</td>
                                    <td>${checkin.event_name || 'No Event'}</td>
                                    <td>${formatDateTime(checkin.date_recorded)}</td>
                                    <td class="${statusClass}">${capitalizeFirst(checkin.attendance_status)}</td>
                                `;
                                tableBody.appendChild(row);
                            });
                        }
                    }
                } else {
                    console.error('Failed to fetch dashboard data:', data.message);
                }
            } catch (error) {
                console.error('Error fetching dashboard data:', error);
            }
        }

        // Utility functions
        function formatDateTime(dateString) {
            if (!dateString) return 'N/A';
            const date = new Date(dateString);
            return date.toLocaleString('en-US', {
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            });
        }

        function capitalizeFirst(string) {
            if (!string) return '';
            return string.charAt(0).toUpperCase() + string.slice(1);
        }

        // Biometric app function
        function launchBiometricApp() {
            const url = 'biometricapp://open?screen=fingerprint_registration';
            try {
                window.location.href = url;
                setTimeout(() => {
                    if (document.visibilityState === 'visible') {
                        alert('If the app did not open, install/enable the biometric app protocol handler.');
                    }
                }, 1500);
            } catch (e) {
                alert('Unable to launch the desktop app.');
            }
        }

        // Load data when page loads
        document.addEventListener('DOMContentLoaded', function() {
            fetchDashboardData();
            
            // Refresh data every 30 seconds
            setInterval(fetchDashboardData, 30000);
        });
    </script>
</body>
</html>