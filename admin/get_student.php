<?php
require_once '../connection.php';

// Set header to ensure JSON response
header('Content-Type: application/json');

$response = ['success' => false, 'data' => [], 'error' => ''];

try {
    // Check database connection
    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    $stmt = $conn->query("SELECT * FROM register_student ORDER BY registration_date DESC");
    $response['data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response['success'] = true;
} catch(PDOException $e) {
    $response['error'] = "Database Error: " . $e->getMessage();
} catch(Exception $e) {
    $response['error'] = $e->getMessage();
}

// Ensure no output before this
echo json_encode($response);
?>