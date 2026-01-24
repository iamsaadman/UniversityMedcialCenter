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

// Fetch unread notifications
$query = "SELECT id, message, type, reference_id, created_at 
          FROM notifications 
          WHERE user_id = ? AND is_read = FALSE 
          ORDER BY created_at DESC 
          LIMIT 10";

$stmt = $mysqli->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$notifications = $result->fetch_all(MYSQLI_ASSOC);

$unread_count = count($notifications);

$stmt->close();

// Mark notifications as read (optional parameter)
if (isset($_POST['mark_read'])) {
  $mark_query = "UPDATE notifications SET is_read = TRUE WHERE user_id = ? AND is_read = FALSE";
  $mark_stmt = $mysqli->prepare($mark_query);
  $mark_stmt->bind_param('i', $user_id);
  $mark_stmt->execute();
  $mark_stmt->close();
}

echo json_encode([
  'success' => true,
  'unread_count' => $unread_count,
  'notifications' => $notifications
]);
?>
