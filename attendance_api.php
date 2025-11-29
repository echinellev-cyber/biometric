<?php
require_once 'connection.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Use the correct variable name $conn from connection.php
if (!$conn) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Get active events for today and future dates
        $today = date('Y-m-d');
        $stmt = $conn->prepare("
            SELECT event_id, event_name, date, start_time, end_time, location, description, is_mandatory
            FROM admin_event 
            WHERE date >= ? 
            ORDER BY date ASC, start_time ASC
        ");
        $stmt->execute([$today]);
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'events' => $events
        ]);
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
            exit;
        }
        
        $action = $input['action'] ?? '';
        
        if ($action === 'record_attendance') {
            $studentId = $input['student_id'] ?? '';
            $eventId = $input['event_id'] ?? '';
            $checkInTime = $input['check_in_time'] ?? date('Y-m-d H:i:s');
            
            if (empty($studentId) || empty($eventId)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Student ID and Event ID are required']);
                exit;
            }
            
            // Check if student exists
            $stmt = $conn->prepare("SELECT id, uid, student_name, course, year_level FROM register_student WHERE uid = ?");
            $stmt->execute([$studentId]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$student) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Student not found']);
                exit;
            }
            
            // Check if event exists and is active
            $stmt = $conn->prepare("SELECT * FROM admin_event WHERE event_id = ? AND date >= CURDATE()");
            $stmt->execute([$eventId]);
            $event = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$event) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Event not found or not active']);
                exit;
            }
            
            // Check if attendance already recorded for this event
            $stmt = $conn->prepare("SELECT * FROM admin_attendance WHERE student_id = ? AND event_id = ?");
            $stmt->execute([$student['id'], $eventId]);
            $existingAttendance = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existingAttendance) {
                http_response_code(409);
                echo json_encode([
                    'success' => false, 
                    'message' => 'Attendance already recorded for this event',
                    'existing_record' => $existingAttendance
                ]);
                exit;
            }
            
            // Calculate if student is late
            $eventStartTime = $event['date'] . ' ' . $event['start_time'];
            $checkInDateTime = new DateTime($checkInTime);
            $eventStartDateTime = new DateTime($eventStartTime);
            $minutesLate = 0;
            $status = 'present';
            
            if ($checkInDateTime > $eventStartDateTime) {
                $diff = $checkInDateTime->diff($eventStartDateTime);
                $minutesLate = ($diff->h * 60) + $diff->i;
                $status = 'late';
            }
            
            // Record attendance
            $stmt = $conn->prepare("
                INSERT INTO admin_attendance 
                (student_id, event_id, check_in_time, status, minutes_late) 
                VALUES (?, ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([
                $student['id'],
                $eventId,
                $checkInTime,
                $status,
                $minutesLate
            ]);
            
            if ($result) {
                // Also update students_events table
                $stmt = $conn->prepare("
                    UPDATE students_events 
                    SET attendance_status = ?, time_in = ?, recorded_by = NULL, date_recorded = NOW()
                    WHERE student_id = ? AND event_id = ?
                ");
                $stmt->execute([$status, $checkInTime, $student['id'], $eventId]);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Attendance recorded successfully',
                    'student' => $student,
                    'event' => $event,
                    'attendance' => [
                        'status' => $status,
                        'check_in_time' => $checkInTime,
                        'minutes_late' => $minutesLate
                    ]
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to record attendance']);
            }
            
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>
