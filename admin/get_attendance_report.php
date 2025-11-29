<?php
// get_attendance_report.php
header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "biometric";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode([]);
    exit;
}

// Get date from query parameter
$selectedDate = $_GET['date'] ?? '';

if (empty($selectedDate)) {
    echo json_encode([]);
    exit;
}

try {
    // Query to get attendance records for the selected date
    $sql = "SELECT 
                se.date_recorded as date,
                se.time_in,
                se.time_out,
                se.attendance_status,
                s.uid as student_uid,
                s.student_name,
                s.course,
                s.year_level,
                e.event_name
            FROM students_events se
            LEFT JOIN register_student s ON se.student_id = s.id
            LEFT JOIN admin_event e ON se.event_id = e.event_id
            WHERE DATE(se.date_recorded) = ?
            ORDER BY se.date_recorded DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $selectedDate);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $attendanceData = [];
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $attendanceData[] = $row;
        }
    }
    
    echo json_encode($attendanceData);
    
} catch (Exception $e) {
    error_log("Attendance report error: " . $e->getMessage());
    echo json_encode([]);
}

$conn->close();
?>