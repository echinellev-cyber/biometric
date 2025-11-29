<?php
// generate_all_fines.php
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
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

try {
    // First, let's debug and see what absences we find
    $debug_sql = "SELECT se.id, se.student_id, se.event_id, se.attendance_status, 
                         e.event_name, e.fine_amount, rs.id as student_db_id, rs.student_name
                  FROM students_events se
                  INNER JOIN admin_event e ON se.event_id = e.event_id
                  INNER JOIN register_student rs ON se.student_id = rs.uid
                  WHERE se.attendance_status = 'absent' 
                  AND NOT EXISTS (
                      SELECT 1 FROM admin_fines af 
                      WHERE af.attendance_id = se.id
                  )
                  LIMIT 10";
    
    $debug_result = $conn->query($debug_sql);
    $debug_count = $debug_result ? $debug_result->num_rows : 0;
    
    error_log("Found $debug_count absent records without fines");
    
    if ($debug_result && $debug_result->num_rows > 0) {
        while($debug_row = $debug_result->fetch_assoc()) {
            error_log("Absent student: " . $debug_row['student_name'] . " - " . $debug_row['event_name']);
        }
    }
    
    // Now generate fines for all absences
    $sql = "SELECT se.id as attendance_id, se.student_id, se.event_id, 
                   e.fine_amount, rs.id as student_db_id, rs.uid as student_uid
            FROM students_events se
            INNER JOIN admin_event e ON se.event_id = e.event_id
            INNER JOIN register_student rs ON se.student_id = rs.uid
            WHERE se.attendance_status = 'absent' 
            AND NOT EXISTS (
                SELECT 1 FROM admin_fines af 
                WHERE af.attendance_id = se.id
            )";
    
    $result = $conn->query($sql);
    
    if ($result === false) {
        throw new Exception("SQL Error: " . $conn->error);
    }
    
    $fines_created = 0;
    $errors = [];
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            // Insert fine for this absence
            $insert_sql = "INSERT INTO admin_fines 
                          (student_id, event_id, attendance_id, fine_type, amount, status, date_issued, due_date) 
                          VALUES (?, ?, ?, 'absent', ?, 'unpaid', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY))";
            
            $stmt = $conn->prepare($insert_sql);
            $fine_amount = $row['fine_amount'] ? $row['fine_amount'] : 100.00;
            
            $stmt->bind_param("iiid", 
                $row['student_db_id'], 
                $row['event_id'], 
                $row['attendance_id'], 
                $fine_amount
            );
            
            if ($stmt->execute()) {
                $fines_created++;
                error_log("Fine created for student ID: " . $row['student_uid']);
            } else {
                $errors[] = "Failed to create fine for student: " . $row['student_uid'];
            }
            $stmt->close();
        }
    }
    
    echo json_encode([
        'success' => true, 
        'count' => $fines_created,
        'debug_count' => $debug_count,
        'errors' => $errors,
        'message' => 'Fines generation completed'
    ]);
    
} catch (Exception $e) {
    error_log("Fines generation error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>