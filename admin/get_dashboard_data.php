<?php
// get_dashboard_data.php
header('Content-Type: application/json');
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
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$response = ['success' => true];

try {
    // Get total registered students
    $student_sql = "SELECT COUNT(*) as total FROM register_student";
    $student_result = $conn->query($student_sql);
    $student_count = $student_result->fetch_assoc()['total'] ?? 0;
    
    // Get today's check-ins - FIXED: Include all attendance records from today
    $today = date('Y-m-d');
    $checkin_sql = "SELECT COUNT(*) as total FROM students_events 
                   WHERE DATE(date_recorded) = ? 
                   AND attendance_status IN ('present', 'late')";
    $checkin_stmt = $conn->prepare($checkin_sql);
    $checkin_stmt->bind_param("s", $today);
    $checkin_stmt->execute();
    $checkin_result = $checkin_stmt->get_result();
    $checkin_count = $checkin_result->fetch_assoc()['total'] ?? 0;
    
    // Get pending fines count - ONLY from events with fines currently enabled
    $fines_sql = "SELECT COUNT(*) as total 
                  FROM admin_fines af 
                  INNER JOIN admin_event ae ON af.event_id = ae.event_id 
                  WHERE af.status = 'unpaid' 
                  AND ae.fine_amount IS NOT NULL 
                  AND ae.fine_amount > 0";
    $fines_result = $conn->query($fines_sql);
    $fines_count = $fines_result->fetch_assoc()['total'] ?? 0;
    
    // Get recent check-ins (last 10) - FIXED: Include all recent attendance
    $recent_sql = "SELECT se.*, s.uid, s.student_name, e.event_name 
                  FROM students_events se 
                  LEFT JOIN register_student s ON se.student_id = s.id 
                  LEFT JOIN admin_event e ON se.event_id = e.event_id 
                  WHERE se.attendance_status IN ('present', 'late')
                  ORDER BY se.date_recorded DESC 
                  LIMIT 10";
    $recent_result = $conn->query($recent_sql);
    $recent_checkins = [];
    
    if ($recent_result && $recent_result->num_rows > 0) {
        while($row = $recent_result->fetch_assoc()) {
            $recent_checkins[] = $row;
        }
    }
    
    $response['stats'] = [
        'total_students' => $student_count,
        'today_checkins' => $checkin_count,
        'pending_fines' => $fines_count
    ];
    
    $response['recent_checkins'] = $recent_checkins;
    
} catch (Exception $e) {
    $response = ['success' => false, 'message' => 'Error fetching data: ' . $e->getMessage()];
}

$conn->close();
echo json_encode($response);
?>