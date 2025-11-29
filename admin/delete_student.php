<?php
// delete_student.php
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
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

// Get the student ID from POST data
$studentId = $_POST['studentId'];

// Prepare and execute delete statement
$stmt = $conn->prepare("DELETE FROM register_student WHERE id = ?");
$stmt->bind_param("i", $studentId);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Student deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error deleting student: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>