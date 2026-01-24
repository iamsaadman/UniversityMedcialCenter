<?php
session_start();
require_once 'includes/dp.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
  header('Location: login.php');
  exit();
}

$student_id = $_SESSION['user_id'];
$student_name = $_SESSION['fullname'] ?? 'Student';

$appointment_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$appointment_date = isset($_POST['appointment_date']) ? $_POST['appointment_date'] : '';
$appointment_time = isset($_POST['appointment_time']) ? $_POST['appointment_time'] : '';
$reason_for_visit = isset($_POST['reason_for_visit']) ? trim($_POST['reason_for_visit']) : '';
$notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';

if ($appointment_id === 0) {
  header('Location: studentreschudleappointment.php?error=' . urlencode('Invalid appointment selection'));
  exit();
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $appointment_date)) {
  header('Location: studentreschudleappointment.php?id=' . $appointment_id . '&error=' . urlencode('Invalid date format'));
  exit();
}

if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $appointment_time)) {
  header('Location: studentreschudleappointment.php?id=' . $appointment_id . '&error=' . urlencode('Invalid time format'));
  exit();
}

$appointment_time = substr($appointment_time, 0, 5);

if (empty($reason_for_visit)) {
  header('Location: studentreschudleappointment.php?id=' . $appointment_id . '&error=' . urlencode('Reason for visit is required'));
  exit();
}

$verify_stmt = $mysqli->prepare('SELECT doctor_id FROM appointments WHERE id = ? AND student_id = ?');
if (!$verify_stmt) {
  header('Location: studentreschudleappointment.php?id=' . $appointment_id . '&error=' . urlencode('Database error'));
  exit();
}
$verify_stmt->bind_param('ii', $appointment_id, $student_id);
$verify_stmt->execute();
$verify_res = $verify_stmt->get_result();
$appt_row = $verify_res->fetch_assoc();
$verify_stmt->close();

if (!$appt_row) {
  header('Location: studentreschudleappointment.php?error=' . urlencode('Appointment not found'));
  exit();
}

$doctor_id = (int)$appt_row['doctor_id'];

$update_stmt = $mysqli->prepare(
  'UPDATE appointments SET appointment_date = ?, appointment_time = ?, reason_for_visit = ?, notes = ?, status = "pending", updated_at = CURRENT_TIMESTAMP WHERE id = ? AND student_id = ?'
);
if (!$update_stmt) {
  header('Location: studentreschudleappointment.php?id=' . $appointment_id . '&error=' . urlencode('Database error'));
  exit();
}
$update_stmt->bind_param('ssssii', $appointment_date, $appointment_time, $reason_for_visit, $notes, $appointment_id, $student_id);

if ($update_stmt->execute()) {
  $update_stmt->close();

  $notify_stmt = $mysqli->prepare('INSERT INTO notifications (user_id, message, type, reference_id, created_at) VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)');
  if ($notify_stmt) {
    $msg_doctor = sprintf('%s requested to reschedule appointment to %s at %s.', $student_name, $appointment_date, $appointment_time);
    $type_doctor = 'appointment_pending';
    $notify_stmt->bind_param('issi', $doctor_id, $msg_doctor, $type_doctor, $appointment_id);
    $notify_stmt->execute();

    $msg_student = 'Your reschedule request was sent to your doctor.';
    $type_student = 'appointment_pending';
    $notify_stmt->bind_param('issi', $student_id, $msg_student, $type_student, $appointment_id);
    $notify_stmt->execute();

    $notify_stmt->close();
  }

  header('Location: studentreschudleappointment.php?id=' . $appointment_id . '&success=1');
  exit();
}

$error_text = 'Failed to update appointment';
if ($update_stmt->error) {
  $error_text = 'Update failed: ' . $update_stmt->error;
}
$update_stmt->close();

header('Location: studentreschudleappointment.php?id=' . $appointment_id . '&error=' . urlencode($error_text));
exit();
?>
