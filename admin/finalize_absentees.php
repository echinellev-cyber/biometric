<?php
require_once 'connection.php';

date_default_timezone_set('Asia/Manila'); // Adjust if needed

// Get all events that already ended
$events = $conn->query("
    SELECT event_id, event_name, date, end_time, fine_amount
    FROM admin_event
    WHERE CONCAT(date, ' ', end_time) < NOW()
")->fetchAll(PDO::FETCH_ASSOC);

foreach ($events as $event) {
    $eventId = $event['event_id'];
    $fineAmount = $event['fine_amount'];

    // Get students still ABSENT for this event
    $students = $conn->prepare("
        SELECT se.id AS student_event_id, se.student_id
        FROM students_events se
        WHERE se.event_id = ? AND se.attendance_status = 'absent'
    ");
    $students->execute([$eventId]);
    $absentStudents = $students->fetchAll(PDO::FETCH_ASSOC);

    foreach ($absentStudents as $row) {
        $studentId = $row['student_id'];

        // Check if fine already exists (avoid duplicates)
        $checkFine = $conn->prepare("
            SELECT fine_id FROM admin_fines 
            WHERE student_id = ? AND event_id = ? AND fine_type = 'absent'
        ");
        $checkFine->execute([$studentId, $eventId]);
        $existingFine = $checkFine->fetch();

        if (!$existingFine) {
            // Insert fine for ABSENT student
            $insertFine = $conn->prepare("
                INSERT INTO admin_fines (student_id, event_id, fine_type, amount, description, date_issued, due_date, status)
                VALUES (?, ?, 'absent', ?, CONCAT('Absent for event: ', ?), CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'unpaid')
            ");
            $insertFine->execute([$studentId, $eventId, $fineAmount, $event['event_name']]);
        }
    }
}

echo "✅ Finalization complete. Absentees have been fined only after event ended.";
?>
