<?php
session_start();
require_once __DIR__ . '/includes/dp.php';

header('Content-Type: application/json');

// Ensure doctor is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
  http_response_code(403);
  echo json_encode(['success' => false, 'message' => 'Unauthorized']);
  exit();
}

$doctor_id = (int) $_SESSION['user_id'];

// Collect and validate input
$student_id     = isset($_POST['student_id']) ? (int) $_POST['student_id'] : 0;
$test_type      = isset($_POST['test_type']) ? trim($_POST['test_type']) : '';
$priority       = isset($_POST['priority']) ? trim($_POST['priority']) : 'Normal';
$notes          = isset($_POST['notes']) ? trim($_POST['notes']) : '';
$appointment_id = (isset($_POST['appointment_id']) && $_POST['appointment_id'] !== '') ? (int) $_POST['appointment_id'] : null;

if ($student_id === 0 || $test_type === '') {
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'Missing required fields']);
  exit();
}

$allowed_priorities = ['Normal', 'Urgent', 'Critical'];
if (!in_array($priority, $allowed_priorities, true)) {
  $priority = 'Normal';
}

// Validate student exists
$student_stmt = $mysqli->prepare('SELECT id FROM users WHERE id = ? AND role = "student" LIMIT 1');
$student_stmt->bind_param('i', $student_id);
$student_stmt->execute();
$student_result = $student_stmt->get_result();
if ($student_result->num_rows === 0) {
  http_response_code(404);
  echo json_encode(['success' => false, 'message' => 'Student not found']);
  exit();
}
$student_stmt->close();

// If appointment id provided, ensure it belongs to doctor and student
$appointment_id_param = null;
if (!is_null($appointment_id) && $appointment_id > 0) {
  $appt_stmt = $mysqli->prepare('SELECT id FROM appointments WHERE id = ? AND doctor_id = ? AND student_id = ? LIMIT 1');
  $appt_stmt->bind_param('iii', $appointment_id, $doctor_id, $student_id);
  $appt_stmt->execute();
  $appt_result = $appt_stmt->get_result();
  if ($appt_result->num_rows === 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Appointment not found for this patient']);
    exit();
  }
  $appt_stmt->close();
  $appointment_id_param = $appointment_id;
}

// Insert test request
$insert_sql = 'INSERT INTO test_requests (doctor_id, student_id, appointment_id, test_type, priority, notes) VALUES (?, ?, ?, ?, ?, ?)';
$insert_stmt = $mysqli->prepare($insert_sql);
if (!$insert_stmt) {
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'Database error: ' . $mysqli->error]);
  exit();
}

$insert_stmt->bind_param('iiisss', $doctor_id, $student_id, $appointment_id_param, $test_type, $priority, $notes);

if ($insert_stmt->execute()) {
  $test_request_id = $insert_stmt->insert_id;
  $insert_stmt->close();

  // Create notification for student
  $priority_label = ucfirst(strtolower($priority));
  $notif_message = "New test recommended: $test_type (Priority: $priority_label). Tap to view details.";
  $notif_stmt = $mysqli->prepare('INSERT INTO notifications (user_id, message, type, reference_id) VALUES (?, ?, "test_request", ?)');
  if ($notif_stmt) {
    $notif_stmt->bind_param('isi', $student_id, $notif_message, $test_request_id);
    if (!$notif_stmt->execute()) {
      $notif_error = $notif_stmt->error;
    }
    $notif_stmt->close();
  } else {
    $notif_error = $mysqli->error;
  }

  if (isset($notif_error)) {
    echo json_encode(['success' => true, 'message' => 'Test request saved but notification failed: ' . $notif_error, 'test_request_id' => $test_request_id]);
  } else {
    echo json_encode(['success' => true, 'message' => 'Test request sent', 'test_request_id' => $test_request_id]);
  }
} else {
  $insert_stmt->close();
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'Failed to create test request: ' . $insert_stmt->error]);
}
?>
