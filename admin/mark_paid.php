<?php
// mark_paid.php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "biometric";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$fineId = $_GET['id'] ?? 0;

if ($fineId) {
    $sql = "UPDATE admin_fines SET status = 'paid', paid_date = CURDATE() WHERE fine_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $fineId);
    
    if ($stmt->execute()) {
        header("Location: fines.php?message=Fine marked as paid");
        exit;
    } else {
        header("Location: fines.php?error=Error updating fine");
        exit;
    }
} else {
    header("Location: fines.php?error=Invalid fine ID");
    exit;
}

$conn->close();
?>