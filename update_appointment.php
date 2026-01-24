<?php
session_start();
require_once 'includes/dp.php';

// Check if doctor is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
  http_response_code(403);
  echo json_encode(['success' => false, 'message' => 'Unauthorized']);
  exit();
}

$doctor_id = $_SESSION['user_id'];
$appointment_id = isset($_POST['id']) ? intval($_POST['id']) : 0;

if ($appointment_id === 0) {
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'Invalid appointment ID']);
  exit();
}

// Verify appointment belongs to this doctor
$verify_query = "SELECT id FROM appointments WHERE id = ? AND doctor_id = ?";
$verify_stmt = $mysqli->prepare($verify_query);
$verify_stmt->bind_param('ii', $appointment_id, $doctor_id);
$verify_stmt->execute();
$verify_result = $verify_stmt->get_result();

if ($verify_result->num_rows === 0) {
  http_response_code(404);
  echo json_encode(['success' => false, 'message' => 'Appointment not found']);
  $verify_stmt->close();
  exit();
}

$verify_stmt->close();

// Validate and sanitize inputs
$appointment_date = isset($_POST['appointment_date']) ? $_POST['appointment_date'] : '';
$appointment_time = isset($_POST['appointment_time']) ? $_POST['appointment_time'] : '';
$reason_for_visit = isset($_POST['reason_for_visit']) ? $_POST['reason_for_visit'] : '';
$status = isset($_POST['status']) ? $_POST['status'] : '';
$notes = isset($_POST['notes']) ? $_POST['notes'] : '';

// Validate date and time format
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $appointment_date)) {
  echo json_encode(['success' => false, 'message' => 'Invalid date format']);
  exit();
}

// Accept both HH:MM and HH:MM:SS formats
if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $appointment_time)) {
  echo json_encode(['success' => false, 'message' => 'Invalid time format']);
  exit();
}

// Normalize time to HH:MM format (remove seconds if present)
$appointment_time = substr($appointment_time, 0, 5);

// Validate status
$valid_statuses = ['pending', 'confirmed', 'completed', 'cancelled'];
if (!in_array($status, $valid_statuses)) {
  echo json_encode(['success' => false, 'message' => 'Invalid status']);
  exit();
}

// Validate reason for visit
if (empty($reason_for_visit)) {
  echo json_encode(['success' => false, 'message' => 'Reason for visit is required']);
  exit();
}

// Update appointment
$update_query = "UPDATE appointments 
                 SET appointment_date = ?, 
                     appointment_time = ?, 
                     reason_for_visit = ?, 
                     status = ?, 
                     notes = ?, 
                     updated_at = CURRENT_TIMESTAMP
                 WHERE id = ? AND doctor_id = ?";

$update_stmt = $mysqli->prepare($update_query);
if (!$update_stmt) {
  echo json_encode(['success' => false, 'message' => 'Database error: ' . $mysqli->error]);
  exit();
}

$update_stmt->bind_param('sssssii', $appointment_date, $appointment_time, $reason_for_visit, $status, $notes, $appointment_id, $doctor_id);

if ($update_stmt->execute()) {
  // Get student info for notification
  $student_query = "SELECT a.student_id, u.fullname as patient_name FROM appointments a JOIN users u ON a.student_id = u.id WHERE a.id = ?";
  $student_stmt = $mysqli->prepare($student_query);
  $student_stmt->bind_param('i', $appointment_id);
  $student_stmt->execute();
  $student_result = $student_stmt->get_result();
  $student_info = $student_result->fetch_assoc();
  $student_stmt->close();

  if ($student_info) {
    $student_id = $student_info['student_id'];
    
    // Create notification message
    $status_text = ucfirst($status);
    $notification_message = "Your appointment has been updated. Status: $status_text";
    
    // Insert notification
    $notification_query = "INSERT INTO notifications (user_id, message, type, reference_id, created_at) VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)";
    $notification_stmt = $mysqli->prepare($notification_query);
    if ($notification_stmt) {
      $notification_type = 'appointment_' . $status;
      $notification_stmt->bind_param('issi', $student_id, $notification_message, $notification_type, $appointment_id);
      $notification_stmt->execute();
      $notification_stmt->close();
    }
  }

  echo json_encode(['success' => true, 'message' => 'Appointment updated successfully']);
} else {
  echo json_encode(['success' => false, 'message' => 'Failed to update appointment: ' . $update_stmt->error]);
}

$update_stmt->close();
?>
