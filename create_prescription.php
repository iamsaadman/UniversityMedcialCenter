<?php
session_start();
require_once __DIR__ . '/includes/dp.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
  http_response_code(403);
  echo json_encode(['success' => false, 'message' => 'Unauthorized']);
  exit();
}

$doctor_id = (int) $_SESSION['user_id'];
$appointment_id = isset($_POST['appointment_id']) && $_POST['appointment_id'] !== '' ? (int) $_POST['appointment_id'] : null;
$student_id = isset($_POST['student_id']) ? (int) $_POST['student_id'] : 0;
$title = trim($_POST['title'] ?? 'Prescription');
$diagnosis = trim($_POST['diagnosis'] ?? '');
$medications = trim($_POST['medications'] ?? '');
$instructions = trim($_POST['instructions'] ?? '');
$follow_up_date = isset($_POST['follow_up_date']) && $_POST['follow_up_date'] !== '' ? $_POST['follow_up_date'] : null;
$complete_appointment = isset($_POST['complete_appointment']) ? (int) $_POST['complete_appointment'] : 0;

if ($medications === '') {
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'Medications are required']);
  exit();
}

// If appointment provided, lock to that student/doctor
if (!is_null($appointment_id)) {
  $appt = $mysqli->prepare('SELECT student_id FROM appointments WHERE id = ? AND doctor_id = ? LIMIT 1');
  $appt->bind_param('ii', $appointment_id, $doctor_id);
  $appt->execute();
  $appt_res = $appt->get_result();
  if ($row = $appt_res->fetch_assoc()) {
    $student_id = (int) $row['student_id'];
  } else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Appointment not found for this doctor']);
    exit();
  }
  $appt->close();
} else {
  // Validate student if not tied to an appointment
  $stu = $mysqli->prepare('SELECT id FROM users WHERE id = ? AND role = "student" LIMIT 1');
  $stu->bind_param('i', $student_id);
  $stu->execute();
  $stu_res = $stu->get_result();
  if ($stu_res->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Student not found']);
    exit();
  }
  $stu->close();
}

// Insert prescription
$sql = 'INSERT INTO prescriptions (appointment_id, doctor_id, student_id, title, diagnosis, medications, instructions, follow_up_date)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
$stmt = $mysqli->prepare($sql);
$stmt->bind_param(
  'iiisssss',
  $appointment_id,
  $doctor_id,
  $student_id,
  $title,
  $diagnosis,
  $medications,
  $instructions,
  $follow_up_date
);

if (!$stmt->execute()) {
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'Failed to save prescription']);
  exit();
}

$prescription_id = $stmt->insert_id;
$stmt->close();

// Optionally mark appointment completed
if ($complete_appointment && $appointment_id) {
  $up = $mysqli->prepare('UPDATE appointments SET status = "completed", updated_at = CURRENT_TIMESTAMP WHERE id = ? AND doctor_id = ?');
  $up->bind_param('ii', $appointment_id, $doctor_id);
  $up->execute();
  $up->close();
}

// Notify student
$doc_name = $_SESSION['fullname'] ?? 'Your doctor';
// Compose notification with prescription name (title or first medication line)
$raw_title = $title;
$first_med_line = '';
if ($medications !== '') {
  $parts = array_filter(array_map('trim', explode("\n", $medications)));
  $first_med_line = isset($parts[0]) ? $parts[0] : '';
}
$display_title = $raw_title;
if ($display_title === '' || strtolower($display_title) === 'prescription') {
  $display_title = $first_med_line !== '' ? $first_med_line : 'Prescription';
}
if (strlen($display_title) > 80) {
  $display_title = substr($display_title, 0, 77) . '...';
}
$notif_msg = "$doc_name prescribed: $display_title. Tap to view.";
$notif = $mysqli->prepare('INSERT INTO notifications (user_id, message, type, reference_id) VALUES (?, ?, "prescription", ?)');
if ($notif) {
  $notif->bind_param('isi', $student_id, $notif_msg, $prescription_id);
  $notif->execute();
  $notif->close();
}

echo json_encode(['success' => true, 'prescription_id' => $prescription_id, 'message' => 'Prescription created']);
