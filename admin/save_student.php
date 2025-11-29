<?php
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
    echo json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['studentId']) ? intval($_POST['studentId']) : 0;
    $name = $_POST['studentName'];
    $uid = $_POST['studentUid'];
    $course = $_POST['course'];
    $yearLevel = $_POST['yearLevel'];
    
    if ($id > 0) {
        // Update existing student
        $sql = "UPDATE register_student SET student_name = ?, uid = ?, course = ?, year_level = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $name, $uid, $course, $yearLevel, $id);
    } else {
        // Insert new student
        $sql = "INSERT INTO register_student (student_name, uid, course, year_level, fingerprint_data) VALUES (?, ?, ?, ?, '')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $name, $uid, $course, $yearLevel);
    }
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Student saved successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error saving student: ' . $stmt->error]);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>