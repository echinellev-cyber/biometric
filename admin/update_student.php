<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "biometric";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

// Get POST data
$studentId = $_POST['studentId'];
$studentName = $_POST['studentName'];
$studentUid = $_POST['studentUid'];
$course = $_POST['course'];
$yearLevel = $_POST['yearLevel'];

// Update student in database
$sql = "UPDATE register_student SET uid=?, student_name=?, course=?, year_level=? WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssi", $studentUid, $studentName, $course, $yearLevel, $studentId);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Student updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error updating student: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>