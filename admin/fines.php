<?php
session_start();

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

// NEW: Handle auto-filtering when coming from events.php
$autoEventId = null;
$autoFineAmount = null;
$autoFilterActive = false;

if (isset($_GET['generate_fines']) && isset($_GET['event_id'])) {
    $autoEventId = intval($_GET['event_id']);
    $autoFineAmount = floatval($_GET['fine_amount'] ?? 0);
    $autoFilterActive = true;
}

// Handle mark as paid action
if (isset($_GET['mark_paid']) && isset($_GET['fine_id'])) {
    $fine_id = intval($_GET['fine_id']);
    
    $update_sql = "UPDATE admin_fines SET status = 'paid', paid_date = CURDATE() WHERE fine_id = ? AND fine_type IN ('present', 'absent')";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("i", $fine_id);
    
    if ($stmt->execute()) {
        $success_message = "Fine marked as paid successfully!";
    } else {
        $error_message = "Error updating fine: " . $conn->error;
    }
    $stmt->close();
}

// FIXED: Clean up any existing late fines
$cleanup_late_sql = "DELETE FROM admin_fines WHERE fine_type = 'late'";
$conn->query($cleanup_late_sql);

// FIXED: Improved fines generation logic - ONLY FOR ABSENT STUDENTS (NO LATE)
$cleanup_sql = "DELETE f FROM admin_fines f 
                LEFT JOIN admin_event e ON f.event_id = e.event_id 
                WHERE e.event_id IS NULL AND f.fine_type IN ('present', 'absent')";
$conn->query($cleanup_sql);

// FIXED: Better auto-insert logic for absent fines ONLY (no late fines)
$sql_auto = "
INSERT INTO admin_fines
  (student_id, event_id, fine_type, amount, description, date_issued, due_date, status, issued_by)
SELECT
  se.student_id,
  se.event_id,
  'absent' AS fine_type,
  COALESCE(ae.fine_amount, 50) AS amount,
  CONCAT('ABSENT - ', ae.event_name, ' (', DATE_FORMAT(ae.date, '%M %d, %Y'), ')') AS description,
  CURDATE() AS date_issued,
  DATE_ADD(CURDATE(), INTERVAL 30 DAY) AS due_date,
  'unpaid' AS status,
  ae.created_by AS issued_by
FROM students_events se
JOIN admin_event ae ON se.event_id = ae.event_id
LEFT JOIN admin_fines af ON af.student_id = se.student_id AND af.event_id = se.event_id AND af.fine_type = 'absent'
WHERE se.attendance_status = 'absent'
  AND af.fine_id IS NULL
  AND ae.fine_amount > 0
  AND ae.date <= CURDATE()
";
$conn->query($sql_auto);

// FIXED: Improved query to fetch fines data with event filtering - ONLY PRESENT AND ABSENT (NO LATE)
$sql = "SELECT 
            f.fine_id as record_id,
            f.student_id,
            f.fine_type,
            f.amount as fine_amount,
            f.status as fine_status,
            f.date_issued,
            f.due_date,
            f.paid_date,
            f.description,
            rs.uid as student_uid,
            rs.student_name,
            rs.course,
            rs.year_level,
            e.event_name,
            e.date as event_date,
            e.start_time,
            e.end_time,
            e.fine_amount as event_fine_amount,
            se.attendance_status,
            se.date_recorded
        FROM admin_fines f
        LEFT JOIN register_student rs ON f.student_id = rs.id
        LEFT JOIN admin_event e ON f.event_id = e.event_id
        LEFT JOIN students_events se ON f.student_id = se.student_id AND f.event_id = se.event_id
        WHERE f.fine_type IN ('present', 'absent')";  // ONLY SHOW PRESENT AND ABSENT FINES (NO LATE)

// Add event filter if auto-filter is active
if ($autoFilterActive && $autoEventId) {
    $sql .= " AND f.event_id = $autoEventId";
}

$sql .= " ORDER BY f.date_issued DESC, f.fine_id DESC LIMIT 100";

$result = $conn->query($sql);
$finesData = array();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $finesData[] = $row;
    }
}

// Get event details for notification if auto-filter is active
$eventDetails = null;
if ($autoFilterActive && $autoEventId) {
    $eventSql = "SELECT event_name, date FROM admin_event WHERE event_id = $autoEventId";
    $eventResult = $conn->query($eventSql);
    if ($eventResult->num_rows > 0) {
        $eventDetails = $eventResult->fetch_assoc();
    }
}

// Count fines by type for information
$count_sql = "SELECT fine_type, COUNT(*) as count FROM admin_fines WHERE fine_type IN ('present', 'absent') GROUP BY fine_type";
$count_result = $conn->query($count_sql);
$fine_counts = ['present' => 0, 'absent' => 0];
if ($count_result->num_rows > 0) {
    while($row = $count_result->fetch_assoc()) {
        $fine_counts[$row['fine_type']] = $row['count'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fines Management | ACI Biometric System</title>
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
    background: radial-gradient(circle, white, rgba(252, 247, 162, 1));
    min-height: 100vh;
    overflow-x: hidden;
    display: flex;
}

/* Watermark Background - FIXED */
body::before {
    content: "";
    position: fixed;
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

/* Sidebar fixed position */
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
}

/* Main content margin to account for fixed sidebar */
.main-content {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
    margin-left: 250px;
}

/* Make table header sticky */
.fines-table thead {
    position: sticky;
    top: 0;
    z-index: 100;
}

.fines-table th {
    background-color: #007836;
    color: white;
    font-weight: 500;
    position: sticky;
    top: 0;
    z-index: 101;
}

/* Auto-filter highlight */
.auto-filter-highlight {
    background-color: rgba(255, 245, 157, 0.3) !important;
    border-left: 4px solid #FFD700;
}

.auto-filter-highlight:hover {
    background-color: rgba(255, 245, 157, 0.5) !important;
}

/* Mobile responsiveness update */
@media (max-width: 768px) {
    body {
        flex-direction: column;
    }
    
    .sidebar {
        width: 100%;
        min-height: auto;
        padding: 10px 0;
        position: relative;
        height: auto;
    }
    
    .main-content {
        margin-left: 0;
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

    .search-section {
        flex-direction: column;
        align-items: stretch;
    }

    .fines-table {
        display: block;
        overflow-x: auto;
    }
    
    body::before {
        position: absolute;
        left: 0;
        background-size: 60%;
    }
}

        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid #444;
            margin-bottom: 20px;
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

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

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

        .search-section {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            align-items: center;
        }

        .search-bar {
            flex: 1;
            position: relative;
        }

        .search-bar input {
            width: 100%;
            padding: 10px 15px 10px 40px;
            border-radius: 6px;
            border: 1px solid #ddd;
            font-size: 14px;
            transition: all 0.3s;
        }

        .search-bar input:focus {
            outline: none;
            border-color: #007836;
            box-shadow: 0 0 0 2px rgba(0, 120, 54, 0.2);
        }

        .search-bar i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }

        .filter-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            background: white;
            color: #333;
            padding: 10px 15px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            border: 1px solid #ddd;
            transition: all 0.3s;
        }

        .filter-btn:hover {
            background: #f5f5f5;
        }

        .add-fine-btn {
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

        .add-fine-btn:hover {
            background: #00612b;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .fines-table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .fines-table th, 
        .fines-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .fines-table tr:nth-child(even) {
            background-color: rgba(0, 120, 54, 0.05);
        }

        .fines-table tr:hover {
            background-color: rgba(0, 120, 54, 0.1);
        }

        .fine-type {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }

        .type-present {
            background-color: #e8f5e9;
            color: #007836;
        }

        .type-absent {
            background-color: #ffebee;
            color: #e74c3c;
        }

        .status-unpaid {
            color: #e74c3c;
            font-weight: 500;
        }

        .status-paid {
            color: #007836;
            font-weight: 500;
        }

        .status-waived {
            color: #f39c12;
            font-weight: 500;
        }

        .fine-amount {
            font-weight: 600;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 500;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }

        .paid-btn {
            background: #007836;
            color: white;
        }

        .paid-btn:hover {
            background: #00612b;
            transform: translateY(-1px);
        }

        .paid-btn:disabled {
            background: #cccccc;
            cursor: not-allowed;
            transform: none;
        }

        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            gap: 5px;
        }

        .page-btn {
            padding: 8px 12px;
            border-radius: 4px;
            background: white;
            color: #333;
            text-decoration: none;
            border: 1px solid #ddd;
            transition: all 0.3s;
        }

        .page-btn.active {
            background: #007836;
            color: white;
            border-color: #007836;
        }

        .page-btn:hover:not(.active) {
            background: #f5f5f5;
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

        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
        }

        .error-message {
            background: #ffebee;
            color: #e74c3c;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid #e74c3c;
        }

        .success-message {
            background: #e8f5e9;
            color: #007836;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid #007836;
        }

        .filter-message {
            background: #e3f2fd;
            color: #1976d2;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid #1976d2;
        }

        .search-hint {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
            text-align: center;
            font-style: italic;
        }

        .fine-type-info {
            background: #fff3cd;
            color: #856404;
            padding: 10px 15px;
            border-radius: 6px;
            margin-bottom: 15px;
            border-left: 4px solid #ffc107;
            font-size: 14px;
        }

        .stats-container {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-card {
            flex: 1;
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-number {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .stat-present {
            color: #007836;
        }

        .stat-absent {
            color: #e74c3c;
        }

        .stat-label {
            font-size: 12px;
            color: #666;
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
            <li><a href="/biometric/admin/fines.php"  class="active"><i class="fas fa-exclamation-circle"></i> Fines Management</a></li>
            <li><a href="/biometric/admin/reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
        </ul>
    </aside>
    
    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <!-- Header -->
            <div class="header">
                <div>
                    <h1>Fines Management</h1>
                    <p>Manage student fines and penalty records</p>
                    <div class="breadcrumb">ACI Admin Panel » Fines Management</div>
                </div>
                <div class="user-profile">
                    <a href="/biometric/login/login.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>

            <!-- Success/Error Messages -->
            <?php if (isset($success_message)): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <!-- Statistics Cards -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-number stat-present"><?php echo $fine_counts['present']; ?></div>
                    <div class="stat-label">Present Fines</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number stat-absent"><?php echo $fine_counts['absent']; ?></div>
                    <div class="stat-label">Absent Fines</div>
                </div>
            </div>

            <!-- Fine Type Information -->
            <div class="fine-type-info">
                <i class="fas fa-info-circle"></i> 
                <strong>Fine Types:</strong> 
                <span class="type-present" style="margin: 0 10px;">Present</span> - 
                <span class="type-absent" style="margin: 0 10px;">Absent</span>
                | <strong>Late fines are completely excluded from this system.</strong>
            </div>

            <!-- NEW: Auto-filter Notification -->
            <?php if ($autoFilterActive && $eventDetails): ?>
                <div class="filter-message">
                    <i class="fas fa-filter"></i> 
                    Showing fines for event: <strong>"<?php echo $eventDetails['event_name']; ?>"</strong> 
                    on <?php echo date('M d, Y', strtotime($eventDetails['date'])); ?>
                    <a href="fines.php" style="margin-left: 15px; color: #1976d2; text-decoration: underline;">
                        <i class="fas fa-times"></i> Show All Fines
                    </a>
                </div>
            <?php endif; ?>

            <!-- Search and Action Buttons -->
            <div class="search-section">
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search by Student ID, Name, Event, Fine Type, or Status..." id="searchInput">
                </div>
                <a href="#" class="filter-btn">
                    <i class="fas fa-filter"></i> Filters
                </a>
            </div>

            <div class="search-hint">
                💡 Search by: Student ID • Student Name • Event Name • Fine Type (present/absent) • Status (paid/unpaid)
            </div>

            <!-- Fines Table -->
            <table class="fines-table">
                <thead>
                    <tr>
                        <th>Record ID</th>
                        <th>Student ID</th>
                        <th>Student Name</th>
                        <th>Event</th>
                        <th>Fine Type</th>
                        <th>Date Issued</th>
                        <th>Fine Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="fines-table-body">
                    <!-- Data will be populated by JavaScript -->
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="pagination">
                <a href="#" class="page-btn"><i class="fas fa-angle-left"></i></a>
                <a href="#" class="page-btn active">1</a>
                <a href="#" class="page-btn">2</a>
                <a href="#" class="page-btn">3</a>
                <a href="#" class="page-btn"><i class="fas fa-angle-right"></i></a>
            </div>
        </div>
    </main>

    <script>
        // Convert PHP data to JavaScript
        const finesData = <?php echo json_encode($finesData); ?>;
        const autoFilterActive = <?php echo $autoFilterActive ? 'true' : 'false'; ?>;
        
        // Format date function
        function formatDate(dateString) {
            if (!dateString) return 'N/A';
            
            const date = new Date(dateString);
            if (isNaN(date.getTime())) return 'N/A';
            
            return date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        }

        // Get status class
        function getStatusClass(status) {
            switch(status) {
                case 'unpaid': return 'status-unpaid';
                case 'paid': return 'status-paid';
                case 'waived': return 'status-waived';
                default: return '';
            }
        }

        // Get fine type class - ONLY PRESENT AND ABSENT
        function getFineTypeClass(type) {
            switch(type) {
                case 'present': return 'type-present';
                case 'absent': return 'type-absent';
                default: return 'type-absent'; // Default to absent for any other types
            }
        }

        // Render the table with fines data
        function renderFinesTable(data = finesData) {
            const finesTableBody = document.getElementById('fines-table-body');
            finesTableBody.innerHTML = '';
            
            if (data.length === 0) {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td colspan="9" class="no-data">
                        <?php if ($autoFilterActive): ?>
                            <i class="fas fa-search"></i><br>
                            No fines found for this event.<br>
                            <small>All students may have already been marked as present or fines haven't been generated yet.</small>
                        <?php else: ?>
                            No fines found.
                        <?php endif; ?>
                    </td>
                `;
                finesTableBody.appendChild(row);
            } else {
                data.forEach(record => {
                    // Double check to ensure no late fines are displayed
                    if (record.fine_type && record.fine_type.toLowerCase() === 'late') {
                        return; // Skip late fines completely
                    }
                    
                    const row = document.createElement('tr');
                    
                    // Add highlight class if auto-filter is active
                    if (autoFilterActive) {
                        row.classList.add('auto-filter-highlight');
                    }
                    
                    // Safely get values with null checks
                    const fine_amount = record.fine_amount || "0.00";
                    const student_uid = record.student_uid || "N/A";
                    const student_name = record.student_name || "Unknown";
                    const course = record.course || "N/A";
                    const year_level = record.year_level || "N/A";
                    const event_name = record.event_name || "Unknown Event";
                    const event_date = record.event_date || "N/A";
                    const fine_status = record.fine_status || "unpaid";
                    const fine_type = record.fine_type || "absent";
                    
                    const status_class = getFineTypeClass(fine_type);
                    
                    row.setAttribute('data-student-id', student_uid);
                    row.setAttribute('data-student-name', student_name);
                    row.setAttribute('data-event-name', event_name);
                    row.setAttribute('data-fine-type', fine_type);
                    row.setAttribute('data-status', fine_status);
                    
                    row.innerHTML = `
                        <td>#${record.record_id}</td>
                        <td>${student_uid}</td>
                        <td>${student_name}<br><small>${course} - Year ${year_level}</small></td>
                        <td>${event_name}<br><small>${formatDate(event_date)}</small></td>
                        <td><span class="fine-type ${status_class}">${fine_type.charAt(0).toUpperCase() + fine_type.slice(1)}</span></td>
                        <td><small>${formatDate(record.date_issued)}</small></td>
                        <td class="fine-amount ${fine_status === 'paid' ? 'status-paid' : ''}">
                            ${fine_status === 'paid' ? 'Paid' : '₱' + parseFloat(fine_amount).toFixed(2)}
                        </td>
                        <td class="${getStatusClass(fine_status)}">${fine_status.charAt(0).toUpperCase() + fine_status.slice(1)}</td>
                        <td>
                            ${fine_status === 'unpaid' ? 
                                `<a href="?mark_paid=true&fine_id=${record.record_id}" class="action-btn paid-btn" onclick="return confirm('Mark fine #${record.record_id} as paid?')">
                                    <i class="fas fa-check"></i> Mark Paid
                                </a>` : 
                                `<button class="action-btn paid-btn" disabled>
                                    <i class="fas fa-check"></i> Paid
                                </button>`
                            }
                        </td>
                    `;
                    finesTableBody.appendChild(row);
                });
            }
        }

        // Enhanced search functionality
        function filterFinesData() {
            const searchInput = document.getElementById('searchInput');
            const searchValue = searchInput.value.toLowerCase().trim();
            
            if (searchValue === '') {
                renderFinesTable(finesData);
            } else {
                const filteredData = finesData.filter(record => {
                    // Skip late fines in search results
                    if (record.fine_type && record.fine_type.toLowerCase() === 'late') {
                        return false;
                    }
                    
                    const studentId = (record.student_uid || '').toLowerCase();
                    const studentName = (record.student_name || '').toLowerCase();
                    const eventName = (record.event_name || '').toLowerCase();
                    const fineType = (record.fine_type || '').toLowerCase();
                    const status = (record.fine_status || '').toLowerCase();
                    const course = (record.course || '').toLowerCase();
                    
                    // Combine all searchable data
                    const searchableText = `
                        ${studentId}
                        ${studentName}
                        ${eventName}
                        ${fineType}
                        ${status}
                        ${course}
                    `.toLowerCase();
                    
                    // Check if any of the fields match the search value
                    return studentId.includes(searchValue) ||
                           studentName.includes(searchValue) ||
                           eventName.includes(searchValue) ||
                           fineType.includes(searchValue) ||
                           status.includes(searchValue) ||
                           course.includes(searchValue) ||
                           searchableText.includes(searchValue);
                });
                
                renderFinesTable(filteredData);
                
                // Show no results message if all rows are filtered out
                if (filteredData.length === 0 && searchValue !== '') {
                    const finesTableBody = document.getElementById('fines-table-body');
                    const noResultsRow = document.createElement('tr');
                    noResultsRow.innerHTML = `
                        <td colspan="9" class="no-data">
                            <i class="fas fa-search"></i><br>
                            No fines found matching "${searchValue}"
                        </td>
                    `;
                    finesTableBody.appendChild(noResultsRow);
                }
            }
        }

        // Event listeners
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            searchInput.value = '';
            renderFinesTable();
            
            searchInput.addEventListener('keyup', filterFinesData);
        });

        // Auto-refresh page after marking as paid
        <?php if (isset($_GET['mark_paid'])): ?>
        setTimeout(function() {
            window.location.href = window.location.pathname;
        }, 2000);
        <?php endif; ?>
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

<?php
$conn->close();
?>