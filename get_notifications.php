<?php
session_start();
require_once 'includes/dp.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  http_response_code(403);
  echo json_encode(['success' => false, 'message' => 'Unauthorized']);
  exit();
}

$user_id = $_SESSION['user_id'];

// Fetch all notifications (both read and unread) for history
$query = "SELECT id, message, type, reference_id, is_read, created_at 
          FROM notifications 
          WHERE user_id = ? 
          ORDER BY created_at DESC 
          LIMIT 50";

$stmt = $mysqli->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$notifications = $result->fetch_all(MYSQLI_ASSOC);

// Count unread notifications
$unread_query = "SELECT COUNT(*) as unread_count FROM notifications WHERE user_id = ? AND is_read = FALSE";
$unread_stmt = $mysqli->prepare($unread_query);
$unread_stmt->bind_param('i', $user_id);
$unread_stmt->execute();
$unread_result = $unread_stmt->get_result();
$unread_row = $unread_result->fetch_assoc();
$unread_count = $unread_row['unread_count'];

$stmt->close();
$unread_stmt->close();

echo json_encode([
  'success' => true,
  'unread_count' => $unread_count,
  'notifications' => $notifications
]);
?>
