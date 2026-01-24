<?php
session_start();
require_once 'includes/dp.php';

// Check if user is logged in (doctor or student)
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
  http_response_code(403);
  echo json_encode(['success' => false, 'message' => 'Unauthorized']);
  exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];
$appointment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($appointment_id === 0) {
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'Invalid appointment ID']);
  exit();
}

// Fetch appointment details based on user role
if ($user_role === 'doctor') {
  // For doctors, join with student (patient) info
  $query = "SELECT a.*, u.fullname as patient_name, u.fullname as doctor_name
            FROM appointments a 
            JOIN users u ON a.student_id = u.id
            LEFT JOIN users d ON a.doctor_id = d.id
            WHERE a.id = ? AND a.doctor_id = ?";
  
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param('ii', $appointment_id, $user_id);
} else if ($user_role === 'student') {
  // For students, join with doctor info
  $query = "SELECT a.*, d.fullname as doctor_name
            FROM appointments a 
            JOIN users d ON a.doctor_id = d.id 
            WHERE a.id = ? AND a.student_id = ?";
  
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param('ii', $appointment_id, $user_id);
} else {
  http_response_code(403);
  echo json_encode(['success' => false, 'message' => 'Unauthorized role']);
  exit();
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  http_response_code(404);
  echo json_encode(['success' => false, 'message' => 'Appointment not found or access denied']);
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
