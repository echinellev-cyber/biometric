<?php
require_once 'connection.php';

header('Content-Type: application/json');

// Get raw POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Check for valid JSON
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid JSON data',
        'error' => json_last_error_msg()
    ]);
    exit;
}

// Check required action parameter
if (empty($data['action'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => 'Action parameter is required'
    ]);
    exit;
}

try {
    switch ($data['action']) {
        case 'enroll':
            enrollFingerprint($data);
            break;
        case 'verify':
            verifyFingerprint($data);
            break;
        case 'delete':
            deleteFingerprint($data);
            break;
        default:
            http_response_code(400);
            echo json_encode([
                'success' => false, 
                'message' => 'Invalid action'
            ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Database error',
        'error' => $e->getMessage()
    ]);
}

function enrollFingerprint($data) {
    global $conn;
    
    // Validate all required fields
    $required = ['uid', 'student_name', 'course', 'year_level', 'fingerprint_data'];
    $missing = array_diff($required, array_keys(array_filter($data)));
    
    if (!empty($missing)) {
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'message' => 'Missing required fields',
            'missing_fields' => array_values($missing)
        ]);
        return;
    }
    
    try {
        $conn->beginTransaction();
        
        // Check if student exists
        $checkStmt = $conn->prepare("SELECT id FROM register_student WHERE uid = :uid");
        $checkStmt->execute([':uid' => $data['uid']]);
        $studentExists = $checkStmt->rowCount() > 0;
        
        if ($studentExists) {
            $stmt = $conn->prepare("UPDATE register_student SET 
                student_name = :student_name,
                course = :course,
                year_level = :year_level,
                fingerprint_data = :fingerprint_data,
                last_updated = NOW()
                WHERE uid = :uid");
        } else {
            $stmt = $conn->prepare("INSERT INTO register_student 
                (uid, student_name, course, year_level, fingerprint_data) 
                VALUES (:uid, :student_name, :course, :year_level, :fingerprint_data)");
        }
        
        $result = $stmt->execute([
            ':uid' => $data['uid'],
            ':student_name' => $data['student_name'],
            ':course' => $data['course'],
            ':year_level' => $data['year_level'],
            ':fingerprint_data' => $data['fingerprint_data']
        ]);
        
        if ($result) {
            $conn->commit();
            echo json_encode([
                'success' => true,
                'message' => $studentExists ? 'Student updated' : 'Student registered'
            ]);
        } else {
            $conn->rollBack();
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Database operation failed',
                'error' => $stmt->errorInfo()
            ]);
        }
    } catch (Exception $e) {
        $conn->rollBack();
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'message' => 'Enrollment failed',
            'error' => $e->getMessage()
        ]);
    }
}


function verifyFingerprint($data) {
    global $conn;
    
    if (empty($data['fingerprint_data'])) {
        echo json_encode(['success' => false, 'message' => 'No fingerprint data provided']);
        return;
    }
    
    try {
        $stmt = $conn->prepare("SELECT * FROM register_student WHERE fingerprint_data = :fingerprint_data");
        $stmt->execute([':fingerprint_data' => $data['fingerprint_data']]);
        
        if ($stmt->rowCount() > 0) {
            $student = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'student' => $student]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Fingerprint not found']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Verification failed: ' . $e->getMessage()]);
    }
}

function deleteFingerprint($data) {
    global $conn;
    
    if (empty($data['uid'])) {
        echo json_encode(['success' => false, 'message' => 'No UID provided']);
        return;
    }
    
    try {
        $conn->beginTransaction();
        
        $stmt = $conn->prepare("UPDATE register_student SET fingerprint_data = NULL WHERE uid = :uid");
        $stmt->execute([':uid' => $data['uid']]);
        
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Fingerprint data deleted successfully']);
    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode(['success' => false, 'message' => 'Deletion failed: ' . $e->getMessage()]);
    }
}
?>