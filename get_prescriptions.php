<?php
session_start();
require_once __DIR__ . '/includes/dp.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
  http_response_code(403);
  echo json_encode(['success' => false, 'message' => 'Unauthorized']);
  exit();
}

$user_id = (int) $_SESSION['user_id'];
$role = $_SESSION['role'] ?? '';
$student_filter = isset($_GET['student_id']) ? (int) $_GET['student_id'] : null;

$where = '';
$params = [];
$types = '';

if ($role === 'student') {
  $where = 'p.student_id = ?';
  $params[] = $user_id;
  $types .= 'i';
} elseif ($role === 'doctor') {
  if ($student_filter) {
    $where = 'p.doctor_id = ? AND p.student_id = ?';
    $params = [$user_id, $student_filter];
    $types = 'ii';
  } else {
    $where = 'p.doctor_id = ?';
    $params[] = $user_id;
    $types .= 'i';
  }
} else {
  http_response_code(403);
  echo json_encode(['success' => false, 'message' => 'Unauthorized']);
  exit();
}

$sql = "SELECT p.id, p.title, p.diagnosis, p.medications, p.instructions, p.follow_up_date, p.created_at,
               d.fullname AS doctor_name, s.fullname AS student_name
        FROM prescriptions p
        JOIN users d ON p.doctor_id = d.id
        JOIN users s ON p.student_id = s.id
        WHERE $where
        ORDER BY p.created_at DESC
        LIMIT 25";

$stmt = $mysqli->prepare($sql);
if ($params) {
  $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$res = $stmt->get_result();
$data = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode(['success' => true, 'data' => $data]);
