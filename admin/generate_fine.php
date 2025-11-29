<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "biometric";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['attendance_id']) && isset($_GET['student_id'])) {
    $attendance_id = $_GET['attendance_id'];
    $student_id = $_GET['student_id'];
    
    // Get event fine amount or use default
    $getFineSQL = "SELECT COALESCE(e.fine_amount, 100.00) as fine_amount 
                   FROM students_events se 
                   LEFT JOIN admin_event e ON se.event_id = e.event_id 
                   WHERE se.id = ?";
    $stmt = $conn->prepare($getFineSQL);
    $stmt->bind_param("i", $attendance_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $fineData = $result->fetch_assoc();
    $fine_amount = $fineData['fine_amount'];
    
    // Insert fine
    $insertSQL = "INSERT INTO admin_fines (attendance_id, student_id, amount, status, date_created) 
                  VALUES (?, ?, ?, 'unpaid', NOW())";
    $stmt = $conn->prepare($insertSQL);
    $stmt->bind_param("iid", $attendance_id, $student_id, $fine_amount);
    
    if ($stmt->execute()) {
        header("Location: fines.php?success=Fine+generated+successfully");
    } else {
        header("Location: fines.php?error=Failed+to+generate+fine");
    }
} else {
    header("Location: fines.php?error=Invalid+parameters");
}

$conn->close();
?>