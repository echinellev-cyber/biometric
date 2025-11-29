<?php
// update_fine_status.php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "biometric";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['fine_id']) && isset($_GET['status'])) {
    $fine_id = $_GET['fine_id'];
    $status = $_GET['status'];
    
    $updateSQL = "UPDATE admin_fines SET status = ? WHERE fine_id = ?";
    $stmt = $conn->prepare($updateSQL);
    $stmt->bind_param("si", $status, $fine_id);
    
    if ($stmt->execute()) {
        header("Location: fines.php?success=Fine+status+updated");
    } else {
        header("Location: fines.php?error=Failed+to+update+fine+status");
    }
} else {
    header("Location: fines.php?error=Invalid+parameters");
}

$conn->close();
?>