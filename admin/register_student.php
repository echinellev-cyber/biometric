<?php
require_once '../connection.php';

// Set header to ensure JSON response
header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    // Get POST data
    $uid = $_POST['uid'] ?? '';
    $student_name = $_POST['student_name'] ?? '';
    $course = $_POST['course'] ?? '';
    $year_level = $_POST['year_level'] ?? '';
    $fingerprint_data = $_POST['fingerprint_data'] ?? '';
    
    // Validate required fields
    if (empty($uid) || empty($student_name) || empty($course) || empty($year_level) || empty($fingerprint_data)) {
        throw new Exception("All fields are required");
    }
    
    // Check if UID already exists
    $check_stmt = $conn->prepare("SELECT id FROM register_student WHERE uid = ?");
    $check_stmt->execute([$uid]);
    
    if ($check_stmt->fetch()) {
        throw new Exception("UID already exists");
    }
    
    // Insert into database
    $stmt = $conn->prepare("INSERT INTO register_student (uid, student_name, course, year_level, fingerprint_data) 
                          VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$uid, $student_name, $course, $year_level, $fingerprint_data]);
    
    $response['success'] = true;
    $response['message'] = "Student registered successfully!";
} catch(PDOException $e) {
    $response['message'] = "Database Error: " . $e->getMessage();
} catch(Exception $e) {
    $response['message'] = $e->getMessage();
}

// Ensure no output before this
echo json_encode($response);
?>