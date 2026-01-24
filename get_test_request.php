<?php
session_start();
require_once __DIR__ . '/includes/dp.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
  http_response_code(403);
  echo json_encode(['success' => false, 'message' => 'Unauthorized']);
  exit();
}

$test_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$current_user = (int) $_SESSION['user_id'];

if ($test_id === 0) {
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'Invalid request id']);
  exit();
}

$sql = "SELECT tr.id, tr.test_type, tr.priority, tr.notes, tr.status, tr.created_at, tr.appointment_id,
               tr.student_id, tr.doctor_id, d.fullname AS doctor_name, s.fullname AS student_name,
               a.appointment_date, a.appointment_time
        FROM test_requests tr
        JOIN users d ON tr.doctor_id = d.id
        JOIN users s ON tr.student_id = s.id
        LEFT JOIN appointments a ON tr.appointment_id = a.id
        WHERE tr.id = ? AND (tr.student_id = ? OR tr.doctor_id = ?)";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param('iii', $test_id, $current_user, $current_user);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  http_response_code(404);
  echo json_encode(['success' => false, 'message' => 'Test request not found']);
  exit();
}

$data = $result->fetch_assoc();
$stmt->close();

echo json_encode([
  'success' => true,
  'data' => [
    'id' => $data['id'],
    'test_type' => $data['test_type'],
    'priority' => $data['priority'],
    'notes' => $data['notes'],
    'status' => $data['status'],
    'created_at' => $data['created_at'],
    'appointment_id' => $data['appointment_id'],
    'appointment_date' => $data['appointment_date'],
    'appointment_time' => $data['appointment_time'],
    'doctor_name' => $data['doctor_name'],
    'student_name' => $data['student_name']
  ]
]);
?>
