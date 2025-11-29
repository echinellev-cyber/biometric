<?php
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
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed']));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = trim($_POST['student_id']);
    $password = trim($_POST['password']);
    
    // Default password is "0000"
    $default_password = '0000';
    
    // Validate inputs
    if (empty($student_id) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'Please enter both Student ID and Password']);
        exit;
    }
    
    // Check if student exists and password matches default
    $sql = "SELECT id, uid, student_name, course, year_level FROM register_student WHERE uid = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $student_id, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $student = $result->fetch_assoc();
        
        // Set session variables
        $_SESSION['student_id'] = $student['id'];
        $_SESSION['student_uid'] = $student['uid'];
        $_SESSION['student_name'] = $student['student_name'];
        $_SESSION['student_course'] = $student['course'];
        $_SESSION['student_year'] = $student['year_level'];
        $_SESSION['student_logged_in'] = true;
        
        echo json_encode(['status' => 'success', 'message' => 'Login successful']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid Student ID or Password']);
    }
    
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}

$conn->close();
?>