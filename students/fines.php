<?php
session_start();

// Check if student is logged in
if (!isset($_SESSION['student_logged_in']) || !$_SESSION['student_logged_in']) {
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

$student_id = $_SESSION['student_id'];
$student_name = $_SESSION['student_name'];
$student_uid = $_SESSION['student_uid'];
$student_course = $_SESSION['student_course'];
$student_year = $_SESSION['student_year'];

// Fetch fines data for this student
$fines_sql = "SELECT 
                f.fine_id,
                f.fine_type,
                f.amount,
                f.description,
                f.date_issued,
                f.due_date,
                f.status,
                f.paid_date,
                f.receipt_number,
                e.event_name,
                e.date as event_date,
                a.attendance_status
              FROM admin_fines f
              LEFT JOIN admin_event e ON f.event_id = e.event_id
              LEFT JOIN students_events a ON f.attendance_id = a.id
              WHERE f.student_id = ?
              ORDER BY f.date_issued DESC";

$stmt = $conn->prepare($fines_sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$fines_result = $stmt->get_result();

$total_fines = 0;
$unpaid_fines = 0;
$paid_fines = 0;
$unpaid_fines_list = [];
$paid_fines_list = [];

if ($fines_result->num_rows > 0) {
    while($fine = $fines_result->fetch_assoc()) {
        $total_fines += $fine['amount'];
        
        if ($fine['status'] === 'paid') {
            $paid_fines += $fine['amount'];
            $paid_fines_list[] = $fine;
        } else {
            $unpaid_fines += $fine['amount'];
            $unpaid_fines_list[] = $fine;
        }
    }
}
$stmt->close();

// 🔥 AUTO-GENERATE MISSING FINES 🔥
$missing_fines_sql = "SELECT se.*, e.event_name, e.date as event_date, e.fine_amount 
                     FROM students_events se 
                     JOIN admin_event e ON se.event_id = e.event_id 
                     WHERE se.student_id = ? 
                     AND se.attendance_status IN ('absent', 'late')
                     AND e.fine_amount > 0
                     AND NOT EXISTS (
                         SELECT 1 FROM admin_fines af 
                         WHERE af.attendance_id = se.id
                     )";
$missing_stmt = $conn->prepare($missing_fines_sql);
$missing_stmt->bind_param("i", $student_id);
$missing_stmt->execute();
$missing_result = $missing_stmt->get_result();

$new_fines_generated = 0;
if ($missing_result->num_rows > 0) {
    while($absence = $missing_result->fetch_assoc()) {
        $fine_amount = $absence['fine_amount'];
        
        $insert_fine_sql = "INSERT INTO admin_fines 
                           (student_id, event_id, attendance_id, fine_type, amount, 
                            description, date_issued, due_date, status) 
                           VALUES (?, ?, ?, ?, ?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'unpaid')";
        $insert_stmt = $conn->prepare($insert_fine_sql);
        $description = "Attendance violation: ABSENT - " . $absence['event_name'];
        $insert_stmt->bind_param("iiisds", 
            $student_id, 
            $absence['event_id'], 
            $absence['id'], 
            $absence['attendance_status'], 
            $fine_amount, 
            $description
        );
        $insert_stmt->execute();
        $insert_stmt->close();
        $new_fines_generated++;
    }
    
    // 🔄 RE-FETCH FINES AFTER CREATING NEW ONES
    if ($new_fines_generated > 0) {
        $stmt = $conn->prepare($fines_sql);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $fines_result = $stmt->get_result();
        
        // Reset counters
        $total_fines = 0;
        $unpaid_fines = 0;
        $paid_fines = 0;
        $unpaid_fines_list = [];
        $paid_fines_list = [];
        
        if ($fines_result->num_rows > 0) {
            while($fine = $fines_result->fetch_assoc()) {
                $total_fines += $fine['amount'];
                
                if ($fine['status'] === 'paid') {
                    $paid_fines += $fine['amount'];
                    $paid_fines_list[] = $fine;
                } else {
                    $unpaid_fines += $fine['amount'];
                    $unpaid_fines_list[] = $fine;
                }
            }
        }
        $stmt->close();
    }
}
$missing_stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Fines | ACI Biometric System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="icon" type="image/png" href="../images/logo.png">
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

        .sidebar {
            width: 250px;
            background-color: #007836;
            color: white;
            min-height: 100vh;
            padding: 20px 0;
        }

        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid #444;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }

        .sidebar-header img {
            height: 50px;
            margin-right: 10px;
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
            background: linear-gradient(to right, #FFD700, #FFEA70);
            color: #333;
        }

        .sidebar-menu i {
            margin-right: 10px;
            font-size: 18px;
        }

        .main-content {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            margin-bottom: 30px;
        }

        .header h1 {
            color: #333;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .header p {
            color: #666;
            margin-bottom: 20px;
        }

        .user-profile {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .user-info {
            text-align: left;
        }

        .user-name {
            font-weight: 500;
            color: #333;
        }

        .user-role {
            font-size: 12px;
            color: #666;
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
            transition: all 0.3s;
        }

        .logout-btn:hover {
            background: rgb(69, 156, 65);
            transform: translateY(-2px);
        }

        /* Fines Summary Cards */
        .fines-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .summary-card {
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }

        .summary-card h3 {
            font-size: 16px;
            color: #555;
            margin-bottom: 10px;
        }

        .summary-value {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .summary-subtext {
            font-size: 12px;
            color: #666;
        }

        .total-fines { color: #333; }
        .unpaid-fines { color: #e74c3c; }
        .paid-fines { color: #007836; }

        /* Fines Tables */
        .fines-section {
            margin-bottom: 30px;
        }

        .section-title {
            color: #007836;
            font-size: 22px;
            margin: 30px 0 15px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #007836;
        }

        .fines-table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .fines-table th, 
        .fines-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .fines-table th {
            background-color: #007836;
            color: white;
            font-weight: 500;
        }

        .fines-table tr:nth-child(even) {
            background-color: rgba(0, 120, 54, 0.05);
        }

        .fines-table tr:hover {
            background-color: rgba(0, 120, 54, 0.1);
        }

        .status-unpaid {
            color: #e74c3c;
            font-weight: 500;
        }

        .status-paid {
            color: #007836;
            font-weight: 500;
        }

        .fine-type {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }

        .type-absent {
            background-color: #ffebee;
            color: #e74c3c;
        }

        .currency {
            font-weight: 600;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
        }

        .no-data i {
            font-size: 48px;
            margin-bottom: 15px;
            color: #ddd;
        }

        /* Alert for new fines */
        .alert {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 20px;
            color: #856404;
        }

        .alert i {
            margin-right: 8px;
        }

        @media (max-width: 768px) {
            body {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                min-height: auto;
            }
            
            .sidebar-menu {
                display: flex;
                overflow-x: auto;
            }
            
            .sidebar-menu li {
                flex-shrink: 0;
            }
            
            .fines-summary {
                grid-template-columns: 1fr;
            }
            
            .fines-table {
                display: block;
                overflow-x: auto;
            }
            
            .user-profile {
                flex-direction: column;
                gap: 10px;
                align-items: flex-start;
            }
        }
    </style>
</head>

<body>
    <!-- Sidebar Navigation -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="../images/logo.png" alt="ACI Logo">
            <h4>ACI Student Panel</h4>
        </div>
        <ul class="sidebar-menu">
            <li><a href="/biometric/students/dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="/biometric/students/attendance.php"><i class="fas fa-clipboard-list"></i> My Attendance</a></li>
            <li><a href="/biometric/students/events.php"><i class="fas fa-calendar-alt"></i> School Events</a></li>
            <li><a href="/biometric/students/fines.php" class="active"><i class="fas fa-exclamation-circle"></i> My Fines</a></li>
        </ul>
    </aside>
    
    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <div class="header">
                <h1>My Fines</h1>
                <p>View and manage your outstanding fines</p>
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

            <?php if ($new_fines_generated > 0): ?>
            <div class="alert">
                <i class="fas fa-info-circle"></i>
                <strong>Notice:</strong> <?php echo $new_fines_generated; ?> new fine(s) have been automatically generated for attendance violations.
            </div>
            <?php endif; ?>

            <!-- Fines Summary -->
            <div class="fines-summary">
                <div class="summary-card">
                    <h3>Total Fines</h3>
                    <div class="summary-value total-fines">₱<?php echo number_format($total_fines, 2); ?></div>
                    <div class="summary-subtext">All time fines</div>
                </div>
                <div class="summary-card">
                    <h3>Unpaid Fines</h3>
                    <div class="summary-value unpaid-fines">₱<?php echo number_format($unpaid_fines, 2); ?></div>
                    <div class="summary-subtext"><?php echo count($unpaid_fines_list); ?> outstanding fine(s)</div>
                </div>
                <div class="summary-card">
                    <h3>Paid Fines</h3>
                    <div class="summary-value paid-fines">₱<?php echo number_format($paid_fines, 2); ?></div>
                    <div class="summary-subtext"><?php echo count($paid_fines_list); ?> paid fine(s)</div>
                </div>
            </div>

            <!-- Unpaid Fines Section -->
            <div class="fines-section">
                <h2 class="section-title">
                    <i class="fas fa-exclamation-triangle"></i> Unpaid Fines
                </h2>
                
                <table class="fines-table">
                    <thead>
                        <tr>
                            <th>Event</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Date Issued</th>
                            <th>Due Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($unpaid_fines_list)): ?>
                        <tr>
                            <td colspan="7" class="no-data">
                                <i class="fas fa-check-circle"></i><br>
                                No unpaid fines
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($unpaid_fines_list as $fine): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($fine['event_name'] ?? 'N/A'); ?></td>
                            <td>
                                <span class="fine-type type-absent">
                                    <?php 
                                    // RENAME "LATE" TO "ABSENT" IN DISPLAY
                                    $display_type = ($fine['fine_type'] === 'late') ? 'absent' : ($fine['fine_type'] ?? 'absent');
                                    echo ucfirst($display_type); 
                                    ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($fine['description'] ?? 'Attendance violation'); ?></td>
                            <td><?php echo isset($fine['date_issued']) ? date('M d, Y', strtotime($fine['date_issued'])) : 'N/A'; ?></td>
                            <td><?php echo isset($fine['due_date']) ? date('M d, Y', strtotime($fine['due_date'])) : 'N/A'; ?></td>
                            <td class="currency">₱<?php echo number_format($fine['amount'], 2); ?></td>
                            <td class="status-unpaid">Unpaid</td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paid Fines Section -->
            <div class="fines-section">
                <h2 class="section-title">
                    <i class="fas fa-check-circle"></i> Paid Fines
                </h2>
                
                <table class="fines-table">
                    <thead>
                        <tr>
                            <th>Event</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Date Issued</th>
                            <th>Paid Date</th>
                            <th>Amount</th>
                            <th>Receipt</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($paid_fines_list)): ?>
                        <tr>
                            <td colspan="7" class="no-data">
                                <i class="fas fa-receipt"></i><br>
                                No paid fines
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($paid_fines_list as $fine): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($fine['event_name'] ?? 'N/A'); ?></td>
                            <td>
                                <span class="fine-type type-absent">
                                    <?php 
                                    // RENAME "LATE" TO "ABSENT" IN DISPLAY
                                    $display_type = ($fine['fine_type'] === 'late') ? 'absent' : ($fine['fine_type'] ?? 'absent');
                                    echo ucfirst($display_type); 
                                    ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($fine['description'] ?? 'Attendance violation'); ?></td>
                            <td><?php echo isset($fine['date_issued']) ? date('M d, Y', strtotime($fine['date_issued'])) : 'N/A'; ?></td>
                            <td><?php echo isset($fine['paid_date']) ? date('M d, Y', strtotime($fine['paid_date'])) : 'N/A'; ?></td>
                            <td class="currency">₱<?php echo number_format($fine['amount'], 2); ?></td>
                            <td><?php echo htmlspecialchars($fine['receipt_number'] ?? 'N/A'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>