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

// Get all upcoming events (today and future)
function getUpcomingEvents($conn, $includePast = false) {
    $today = date('Y-m-d');
    $sql = "SELECT * FROM admin_event";
    
    if (!$includePast) {
        $sql .= " WHERE date >= ?";
    }
    
    $sql .= " ORDER BY date ASC, start_time ASC";
    
    $stmt = $conn->prepare($sql);
    
    if (!$includePast) {
        $stmt->bind_param("s", $today);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $events = [];
    
    while($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
    
    $stmt->close();
    return $events;
}

// Get student's fines
function getStudentFines($conn, $studentId) {
    $stmt = $conn->prepare("
        SELECT af.*, ae.event_name, ae.date 
        FROM admin_fines af 
        LEFT JOIN admin_event ae ON af.event_id = ae.event_id 
        WHERE af.student_id = ? AND af.status = 'unpaid'
        ORDER BY af.date_issued DESC
    ");
    $stmt->bind_param("i", $studentId);
    $stmt->execute();
    $result = $stmt->get_result();
    $fines = [];
    
    while($row = $result->fetch_assoc()) {
        $fines[] = $row;
    }
    
    $stmt->close();
    return $fines;
}

// Get events and fines
$events = getUpcomingEvents($conn, false); // Show only upcoming events
$studentFines = getStudentFines($conn, $student_id);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Events & Fines | ACI Biometric System</title>
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
            justify-content: flex-end;
            margin-bottom: 20px;
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

        .search-bar {
            position: relative;
            margin-bottom: 20px;
        }

        .search-bar input {
            width: 100%;
            padding: 10px 15px 10px 40px;
            border-radius: 6px;
            border: 1px solid #ddd;
            font-size: 14px;
        }

        .search-bar i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }

        .section-title {
            color: #007836;
            font-size: 22px;
            margin: 30px 0 15px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #007836;
        }

        .events-table, .fines-table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .events-table th, 
        .events-table td,
        .fines-table th,
        .fines-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .events-table th,
        .fines-table th {
            background-color: #007836;
            color: white;
            font-weight: 500;
        }

        .events-table tr:nth-child(even),
        .fines-table tr:nth-child(even) {
            background-color: rgba(0, 120, 54, 0.05);
        }

        .events-table tr:hover,
        .fines-table tr:hover {
            background-color: rgba(0, 120, 54, 0.1);
        }

        .fine-enabled-yes {
            color: #007836;
            font-weight: 500;
        }

        .fine-enabled-no {
            color: #666;
            font-weight: 500;
        }

        .fine-status-unpaid {
            color: #e74c3c;
            font-weight: 500;
        }

        .fine-status-paid {
            color: #007836;
            font-weight: 500;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #f0f0f0;
            color: #333;
            text-decoration: none;
            transition: all 0.3s;
        }

        .action-btn:hover {
            background: #007836;
            color: white;
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

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 25px;
            border-radius: 8px;
            width: 80%;
            max-width: 600px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .modal-header h2 {
            color: #007836;
            font-size: 22px;
        }

        .close-btn {
            color: #aaa;
            font-size: 24px;
            cursor: pointer;
        }

        .close-btn:hover {
            color: #333;
        }

        .event-details {
            margin-top: 20px;
        }
        
        .detail-row {
            display: flex;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .detail-label {
            font-weight: 600;
            color: #555;
            width: 120px;
        }
        
        .detail-value {
            flex: 1;
            color: #333;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            border: none;
            font-weight: 500;
        }

        .btn-secondary {
            background-color: #f0f0f0;
            color: #333;
        }

        .btn-secondary:hover {
            background-color: #e0e0e0;
        }

        .currency {
            font-weight: 500;
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
            
            .events-table,
            .fines-table {
                display: block;
                overflow-x: auto;
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
            <li><a href="/biometric/students/events.php" class="active"><i class="fas fa-calendar-alt"></i> School Events</a></li>
            <li><a href="/biometric/students/fines.php"><i class="fas fa-exclamation-circle"></i> My Fines</a></li>
        </ul>
    </aside>
    
   <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <div class="header">
                <h1>School Events & Fines</h1>
                <p>View upcoming school events and your current fines</p>
                <div class="user-profile">
                    <div style="margin-right: 15px; text-align: right;">
                        <div style="font-weight: 500; color: #333;"><?php echo htmlspecialchars($student_name); ?></div>
                        <div style="font-size: 12px; color: #666;"><?php echo htmlspecialchars($student_course); ?> - Year <?php echo htmlspecialchars($student_year); ?></div>
                    </div>
                    <a href="/biometric/login/logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>

            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search events by name or location..." id="searchInput">
            </div>

            <!-- Upcoming Events Section -->
            <h2 class="section-title">
                <i class="fas fa-calendar-alt"></i> Upcoming Events
            </h2>
            
            <table class="events-table">
                <thead>
                    <tr>
                        <th>Event Name</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Location</th>
                        <th>Fine Enabled</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody id="eventsTableBody">
                    <?php if (empty($events)): ?>
                    <tr>
                        <td colspan="6" class="no-data">
                            <i class="fas fa-calendar-times"></i><br>
                            No upcoming events found
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($events as $event): ?>
                    <tr data-id="<?php echo htmlspecialchars($event['event_id'] ?? ''); ?>">
                        <td><?php echo htmlspecialchars($event['event_name'] ?? 'N/A'); ?></td>
                        <td><?php echo isset($event['date']) ? date('M d, Y', strtotime($event['date'])) : 'N/A'; ?></td>
                        <td>
                            <?php echo isset($event['start_time']) ? date('g:i A', strtotime($event['start_time'])) : 'N/A'; ?> - 
                            <?php echo isset($event['end_time']) ? date('g:i A', strtotime($event['end_time'])) : 'N/A'; ?>
                        </td>
                        <td><?php echo htmlspecialchars($event['location'] ?? 'N/A'); ?></td>
                        <td class="<?php echo ($event['fine_amount'] ?? 0) > 0 ? 'fine-enabled-yes' : 'fine-enabled-no'; ?>">
                            <?php echo ($event['fine_amount'] ?? 0) > 0 ? 'Yes' : 'No'; ?>
                        </td>
                        <td>
                            <a href="#" class="action-btn view-event-btn" title="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- My Fines Section -->
            <h2 class="section-title">
                <i class="fas fa-exclamation-circle"></i> My Current Fines
            </h2>
            
            <table class="fines-table">
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Date Issued</th>
                        <th>Due Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody id="finesTableBody">
                    <?php if (empty($studentFines)): ?>
                    <tr>
                        <td colspan="6" class="no-data">
                            <i class="fas fa-check-circle"></i><br>
                            No outstanding fines
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($studentFines as $fine): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($fine['event_name'] ?? 'Event'); ?></td>
                        <td><?php echo isset($fine['date_issued']) ? date('M d, Y', strtotime($fine['date_issued'])) : 'N/A'; ?></td>
                        <td><?php echo isset($fine['due_date']) ? date('M d, Y', strtotime($fine['due_date'])) : 'N/A'; ?></td>
                        <td class="currency">₱<?php echo number_format($fine['amount'] ?? 0, 2); ?></td>
                        <td class="fine-status-<?php echo htmlspecialchars($fine['status'] ?? 'unpaid'); ?>">
                            <?php echo ucfirst($fine['status'] ?? 'unpaid'); ?>
                        </td>
                        <td><?php echo htmlspecialchars($fine['description'] ?? 'Absence from event'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- View Event Modal -->
            <div id="viewEventModal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2>Event Details</h2>
                        <span class="close-btn">&times;</span>
                    </div>
                    <div class="event-details">
                        <div class="detail-row">
                            <div class="detail-label">Event Name:</div>
                            <div class="detail-value" id="viewEventName"></div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Date:</div>
                            <div class="detail-value" id="viewEventDate"></div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Time:</div>
                            <div class="detail-value" id="viewEventTime"></div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Location:</div>
                            <div class="detail-value" id="viewEventLocation"></div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Fine Enabled:</div>
                            <div class="detail-value" id="viewEventFineEnabled"></div>
                        </div>
                        <div class="detail-row" id="fineAmountRow" style="display: none;">
                            <div class="detail-label">Fine Amount:</div>
                            <div class="detail-value" id="viewEventFineAmount"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary close-view-btn">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Event data storage
        let events = <?php echo json_encode($events); ?>;

        // DOM elements
        const viewEventModal = document.getElementById("viewEventModal");
        const closeBtns = document.querySelectorAll(".close-btn");
        
        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const rows = document.querySelectorAll('.events-table tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchValue) ? '' : 'none';
            });
        });
        
        // Modal functionality
        function openModal(modal) {
            modal.style.display = "block";
            document.body.style.overflow = "hidden";
        }
        
        function closeModal(modal) {
            modal.style.display = "none";
            document.body.style.overflow = "auto";
        }
        
        // Close modals
        closeBtns.forEach(btn => {
            btn.onclick = function() {
                const modal = this.closest('.modal');
                closeModal(modal);
            }
        });
        
        // Close when clicking outside modal
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                closeModal(event.target);
            }
        }
        
        document.querySelector('.close-view-btn').onclick = function() {
            closeModal(viewEventModal);
        }
        
        // Format date for display
        function formatDisplayDate(dateString) {
            const options = { year: 'numeric', month: 'short', day: 'numeric' };
            return new Date(dateString).toLocaleDateString('en-US', options);
        }
        
        // Format time for display
        function formatDisplayTime(timeString) {
            if (!timeString) return 'N/A';
            const [hours, minutes] = timeString.split(':');
            const hour = parseInt(hours);
            const ampm = hour >= 12 ? 'PM' : 'AM';
            const displayHour = hour % 12 || 12;
            return `${displayHour}:${minutes} ${ampm}`;
        }
        
        // View event details
        document.querySelectorAll('.view-event-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const eventId = parseInt(this.closest('tr').getAttribute('data-id'));
                const event = events.find(e => e.event_id == eventId);
                
                if (event) {
                    document.getElementById('viewEventName').textContent = event.event_name || 'N/A';
                    document.getElementById('viewEventDate').textContent = formatDisplayDate(event.date);
                    document.getElementById('viewEventTime').textContent = 
                        `${formatDisplayTime(event.start_time)} - ${formatDisplayTime(event.end_time)}`;
                    document.getElementById('viewEventLocation').textContent = event.location || 'N/A';
                    
                    const hasFine = event.fine_amount && event.fine_amount > 0;
                    document.getElementById('viewEventFineEnabled').textContent = hasFine ? 'Yes' : 'No';
                    
                    if (hasFine) {
                        document.getElementById('viewEventFineAmount').textContent = '₱' + parseFloat(event.fine_amount).toFixed(2);
                        document.getElementById('fineAmountRow').style.display = 'flex';
                    } else {
                        document.getElementById('fineAmountRow').style.display = 'none';
                    }
                    
                    openModal(viewEventModal);
                }
            });
        });
    </script>
</body>
</html>