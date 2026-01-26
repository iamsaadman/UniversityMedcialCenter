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
$status = isset($_POST['status']) ? $_POST['status'] : '';

// Validate appointment ID
if ($appointment_id === 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid appointment ID']);
    exit();
}

// Validate status
$valid_statuses = ['pending', 'confirmed', 'completed', 'cancelled'];
if (!in_array($status, $valid_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit();
}

// Verify appointment belongs to this doctor
$verify_query = "SELECT id, student_id FROM appointments WHERE id = ? AND doctor_id = ?";
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

$appointment = $verify_result->fetch_assoc();
$student_id = $appointment['student_id'];
$verify_stmt->close();

// Update only the status
$update_query = "UPDATE appointments SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND doctor_id = ?";
$update_stmt = $mysqli->prepare($update_query);
$update_stmt->bind_param('sii', $status, $appointment_id, $doctor_id);

if ($update_stmt->execute()) {
    // Send notification to student
    $status_text = ucfirst($status);
    $notification_message = "Your appointment status has been updated to: $status_text";
    $notification_type = 'appointment_' . $status;

    $notif_query = "INSERT INTO notifications (user_id, message, type, reference_id, created_at) VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)";
    $notif_stmt = $mysqli->prepare($notif_query);
    if ($notif_stmt) {
        $notif_stmt->bind_param('issi', $student_id, $notification_message, $notification_type, $appointment_id);
        $notif_stmt->execute();
        $notif_stmt->close();
    }

    echo json_encode(['success' => true, 'message' => 'Appointment status updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update status: ' . $update_stmt->error]);
}

$update_stmt->close();
?>
