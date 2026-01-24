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

// Mark all unread notifications as read
$query = "UPDATE notifications SET is_read = TRUE WHERE user_id = ? AND is_read = FALSE";
$stmt = $mysqli->prepare($query);
$stmt->bind_param('i', $user_id);

if ($stmt->execute()) {
  echo json_encode(['success' => true, 'message' => 'All notifications marked as read']);
} else {
  echo json_encode(['success' => false, 'message' => 'Failed to mark notifications as read']);
}

$stmt->close();
?>
