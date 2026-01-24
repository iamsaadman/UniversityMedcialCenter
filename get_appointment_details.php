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
$appointment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($appointment_id === 0) {
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'Invalid appointment ID']);
  exit();
}

// Fetch appointment details
$query = "SELECT a.*, u.fullname as patient_name 
          FROM appointments a 
          JOIN users u ON a.student_id = u.id 
          WHERE a.id = ? AND a.doctor_id = ?";

$stmt = $mysqli->prepare($query);
$stmt->bind_param('ii', $appointment_id, $doctor_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  http_response_code(404);
  echo json_encode(['success' => false, 'message' => 'Appointment not found']);
  $stmt->close();
  exit();
}

$appointment = $result->fetch_assoc();
$stmt->close();

// Return appointment details as JSON
echo json_encode([
  'success' => true,
  'appointment' => $appointment
]);
?>
