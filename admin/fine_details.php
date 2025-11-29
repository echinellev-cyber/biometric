<?php
// fine_details.php
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

// Fetch fine details
$sql = "SELECT f.*, s.uid, s.student_name, s.course, e.event_name, e.date as event_date
        FROM admin_fines f
        LEFT JOIN register_student s ON f.student_id = s.id
        LEFT JOIN admin_event e ON f.event_id = e.event_id
        WHERE f.fine_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $fineId);
$stmt->execute();
$result = $stmt->get_result();
$fine = $result->fetch_assoc();

if (!$fine) {
    die("Fine not found");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Fine Details</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .detail-table { width: 100%; border-collapse: collapse; }
        .detail-table td { padding: 10px; border-bottom: 1px solid #ddd; }
        .detail-table tr:last-child td { border-bottom: none; }
    </style>
</head>
<body>
    <h2>Fine Details #<?php echo $fine['fine_id']; ?></h2>
    <table class="detail-table">
        <tr><td><strong>Student ID:</strong></td><td><?php echo $fine['uid']; ?></td></tr>
        <tr><td><strong>Student Name:</strong></td><td><?php echo $fine['student_name']; ?></td></tr>
        <tr><td><strong>Course:</strong></td><td><?php echo $fine['course']; ?></td></tr>
        <tr><td><strong>Event:</strong></td><td><?php echo $fine['event_name'] . ' (' . $fine['event_date'] . ')'; ?></td></tr>
        <tr><td><strong>Fine Type:</strong></td><td><?php echo ucfirst($fine['fine_type']); ?></td></tr>
        <tr><td><strong>Amount:</strong></td><td>₱<?php echo number_format($fine['amount'], 2); ?></td></tr>
        <tr><td><strong>Date Issued:</strong></td><td><?php echo $fine['date_issued']; ?></td></tr>
        <tr><td><strong>Due Date:</strong></td><td><?php echo $fine['due_date']; ?></td></tr>
        <tr><td><strong>Status:</strong></td><td><?php echo ucfirst($fine['status']); ?></td></tr>
        <tr><td><strong>Description:</strong></td><td><?php echo $fine['description']; ?></td></tr>
    </table>
    <br>
    <a href="fines.php">← Back to Fines Management</a>
</body>
</html>
<?php $conn->close(); ?>